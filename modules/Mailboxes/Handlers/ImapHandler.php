<?php
namespace SpiceCRM\modules\Mailboxes\Handlers;

/**
 * Class ImapHandler
 */
class ImapHandler extends TransportHandler
{
    private $transport;

    private $message_ids = [];

    const TYPE_TEXT        = 0;
    const TYPE_MULTIPART   = 1;
    const TYPE_MESSAGE     = 2;
    const TYPE_APPLICATION = 3;
    const TYPE_AUDIO       = 4;
    const TYPE_IMAGE       = 5;
    const TYPE_VIDEO       = 6;
    const TYPE_OTHER       = 7;

    const ENC_7BIT             = 0;
    const ENC_8BIT             = 1;
    const ENC_BINARY           = 2;
    const ENC_BASE64           = 3;
    const ENC_QUOTED_PRINTABLE = 4;

    protected $incoming_settings = [
        'imap_pop3_protocol_type',
        'imap_pop3_host',
        'imap_pop3_port',
        'imap_pop3_encryption',
        'imap_pop3_username',
        'imap_pop3_password',
    ];

    protected $outgoing_settings = [
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'imap_pop3_username',
        'imap_pop3_password',
    ];

    /**
     * initTransportHandler
     *
     * Initialized the transport handler
     *
     * @return void
     */
    protected function initTransportHandler()
    {
        if ($this->checkConfiguration($this->outgoing_settings)['result']) {
            //initialize transport
            $this->transport = (new \Swift_SmtpTransport(
                $this->mailbox->smtp_host,
                $this->mailbox->smtp_port,
                ($this->mailbox->smtp_encryption == 'none') ? null : $this->mailbox->smtp_encryption
            ))
                ->setUsername($this->mailbox->imap_pop3_username)
                ->setPassword($this->mailbox->imap_pop3_password)
                ->setStreamOptions([
                    'verify_peer' => $this->mailbox->smtp_verify_peer,
                    'verify_peer_name' => $this->mailbox->smtp_verify_peer_name,
                    'allow_self_signed' => $this->mailbox->smtp_allow_self_signed,
                ]);

            // initialize mailer
            $this->transport_handler = new \Swift_Mailer($this->transport);
        }
    }

    /**
     * testConnection
     *
     * Tests both the IMAP and SMTP connections
     *
     * @return mixed
     */
    public function testConnection($testEmail)
    {
        if ($this->mailbox->inbound_comm == 1) {
            $response['imap'] = $this->testImapConnection();
        }

        if ($this->mailbox->outbound_comm == 'single' || $this->mailbox->outbound_comm = 'mass') {
            $response['smtp'] = $this->testSmtpConnection($testEmail);
        }

        return $response;
    }

    /**
     * fetchEmails
     *
     * Fetches Emails and saves them in the internal DB
     * It also fetches the attachments
     *
     * @return array
     */
    public function fetchEmails()
    {
        global $sugar_config;

        $imap_status = $this->checkConfiguration($this->incoming_settings);
        if (!$imap_status['result']) {
            return [
                'result' => false,
                'errors' => 'No IMAP connection set up. Missing values for: '
                    . implode(', ', $imap_status['missing']),
            ];
        }

        $stream = $this->getImapStream($this->mailbox->imap_inbox_dir);

        $imapErrors = imap_errors();

        if ($imapErrors) {
            $GLOBALS['log']->error('IMAP connection failure: ' . implode(';', $imapErrors));
        }

        $this->initMessageIDs();

        if ($this->mailbox->last_checked != '') {
            if (isset($sugar_config['mailboxes']['delta_t'])) {
                $dateSince = date(
                    'd-M-Y',
                    strtotime(
                        '-' . (int) $sugar_config['mailboxes']['delta_t'] . ' day',
                        strtotime($this->mailbox->last_checked)
                    )
                );
            } else {
                $dateSince = date('d-M-Y', strtotime($this->mailbox->last_checked));
            }

            $items = imap_search($stream, 'SINCE ' . $dateSince);
        } else {
            $items = imap_search($stream, 'ALL');
        }

        if ($this->mailbox->log_level == \Mailbox::LOG_DEBUG) {
            $GLOBALS['log']->error($this->mailbox->name . ': ' . count($items) . ' emails in mailbox since '
                . date('d-M-Y', strtotime($dateSince)));
        }

        $new_mail_count = 0;
        $checked_mail_count = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                ++$checked_mail_count;
                $email = \BeanFactory::getBean('Emails');
                $overview = imap_fetch_overview($stream, $item);
                $header = imap_headerinfo($stream, $item);

                if ($this->emailExists($overview[0]->message_id)) {
                    continue;
                }

                $email->mailbox_id = $this->mailbox->id;
                $email->message_id = $overview[0]->message_id;
                $email->name = $email->name = $this->parseSubject($overview[0]->subject);
                $email->date_sent = date('Y-m-d H:i:s', strtotime($overview[0]->date));
                $email->from_addr = $this->parseAddress($overview[0]->from);
                $email->to_addrs = $this->parseAddress($overview[0]->to); // todo multiple addresses
                if (isset($header->ccaddress)) {
                    $email->cc_addrs = $header->ccaddress;
                }
                if (isset($header->bccaddress)) {
                    $email->bcc_addrs = $header->bccaddress;
                }
                $email->type = 'inbound';
                $email->status = 'unread';
                $email->openness = \Email::OPENNESS_OPEN;

                $structure = new ImapStructure($stream, $item);
                $structure->parseStructure();

                $email->body = $structure->getEmailBody();
                try {
                    $email->save();
                } catch (\Exception $e) {
                    $GLOBALS['log']->error('Could not save email: ' . $email->name);
                    continue;
                }


                foreach ($structure->getAttachments() as $attachment) {
                    \SpiceCRM\includes\SpiceAttachments\SpiceAttachments::saveEmailAttachment('Emails', $email->id, $attachment);
                }

                $email->processEmail();

                if ($new_mail_count > 100) {
                    break;
                }

                ++$new_mail_count;
            }

            $this->mailbox->last_checked = date('Y-m-d H:i:s');
            $this->mailbox->save();
        }

        imap_close($stream);

        if ($this->mailbox->log_level == \Mailbox::LOG_DEBUG) {
            $GLOBALS['log']->error($this->mailbox->name . ': ' . $checked_mail_count . ' emails checked');
        }
        if ($this->mailbox->log_level == \Mailbox::LOG_DEBUG) {
            $GLOBALS['log']->error($this->mailbox->name . ': ' . $new_mail_count . ' emails fetched');
        }

        if (isset($this->mailbox->allow_external_delete) && $this->mailbox->allow_external_delete == true) {
            $this->fetchDeleted();
        }

        return ['new_mail_count' => $new_mail_count];
    }

    /**
     * fetchDeleted
     *
     * Fetches Emails from the trash folder and marks them as deleted in the database
     *
     * @return array
     */
    public function fetchDeleted() {
        $deleted_mail_count = 0;

        $stream = $this->getImapStream($this->mailbox->imap_trash_dir);

        $items = imap_search($stream, 'ALL');


        foreach ($items as $item) {
            $overview  = imap_fetch_overview($stream, $item);
            $email     = \BeanFactory::getBean('Emails')
                ->get_full_list(
                    '',
                    'message_id= "' . $overview[0]->message_id . '"'
                )[0]
            ;

            if (!$this->emailExists($overview[0]->message_id)) {
                continue;
            }

            $email->deleted = 1;
            $email->save();

            ++$deleted_mail_count;
        }

        imap_close($stream);

        return ['deleted_mail_count' => $deleted_mail_count];
    }

    /**
     * getMailboxes
     *
     * Returns the mailbox folders
     *
     * @return array
     */
    public function getMailboxes()
    {
        $mailboxes = imap_getmailboxes($this->getImapStream(), $this->mailbox->getRef(), '*');

        return [
            'result'    => true,
            'mailboxes' => $this->getMailboxNames($mailboxes),
        ];
    }

    /**
     * getImapStream
     *
     * Gets IMAP connection stream
     *
     * @return resource
     */
    private function getImapStream($folder = "INBOX")
    {
        $stream = imap_open(
            $this->mailbox->getRef() . $folder,
            $this->mailbox->imap_pop3_username,
            $this->mailbox->imap_pop3_password
        );

        return $stream;
    }

    /**
     * getMailboxNames
     *
     * Extracts the actual folder names
     * Changes character encoding to UTF-8
     *
     * @param array $mailboxes
     * @return array
     */
    private function getMailboxNames(array $mailboxes)
    {
        $names = [];

        foreach ($mailboxes as $mailbox) {
            array_push(
                $names,
                mb_convert_encoding(
                    substr($mailbox->name, strpos($mailbox->name, '}') + 1),
                    "UTF8",
                    "UTF7-IMAP"
                )
            );
        }

        return $names;
    }

    /**
     * emailExists
     *
     * Checks if an email with the given message ID already exists in the database
     *
     * @param $message_id
     * @return bool
     */
    private function emailExists($message_id)
    {
        // Check if the Email exists in the current mailbox
        if (in_array($message_id, $this->message_ids)) {
            return true;
        }

        // Check if the Email exists for a different mailbox
        global $db;
        $query = "SELECT DISTINCT id FROM emails WHERE message_id='" . $message_id . "'";
        $q = $db->query($query);
        $result = $db->fetchByAssoc($q);

        if (!empty($result)) { // Substitute the old mailbox ID with the current one
            $query2 = "UPDATE emails SET mailbox_id='" . $this->mailbox->id . "' WHERE id='" . $result['id'] . "'";
            $q2 = $db->query($query2);
            $result2 = $db->fetchByAssoc($q2);
        }

        return false;
    }

    /**
     * initMessageIDs
     *
     * Initializes an array containing the already existing message_ids
     *
     * @return void
     */
    private function initMessageIDs()
    {
        global $db;

        $query = "SELECT DISTINCT message_id FROM emails WHERE mailbox_id = '" . $this->mailbox->id . "'";

        $q = $db->query($query);

        while ($row = $db->fetchRow($q)) {
            array_push($this->message_ids, $row['message_id']);
        }
    }

    /**
     * testImapConnection
     *
     * Tests connection to the IMAP server
     *
     * @return mixed
     */
    private function testImapConnection()
    {
        $imap_status = $this->checkConfiguration($this->incoming_settings);
        if (!$imap_status['result']) {
            $response = [
                'result' => false,
                'errors' => 'No IMAP connection set up. Missing values for: '
                    . implode(', ', $imap_status['missing']),
            ];
            return $response;
        }

        $this->getImapStream();

        $response['errors'] = imap_errors();

        if ($response['errors']) {
            $response['result'] = false;
        } else {
            $response['result'] = true;
        }

        return $response;
    }

    /**
     * testSmtpConnection
     *
     * Tests connection to the SMTP server by sending a dummy email
     *
     * @return mixed
     */
    private function testSmtpConnection($testEmail)
    {
        $smtp_status = $this->checkConfiguration($this->outgoing_settings);
        if (!$smtp_status['result']) {
            $response = [
                'result' => false,
                'errors' => 'No SMTP connection set up. Missing values for: '
                    . implode(', ', $smtp_status['missing']),
            ];
            return $response;
        }

        try {
            $this->transport_handler->getTransport()->start();

            $this->sendMail(\Email::getTestEmail($this->mailbox, $testEmail));
            $response['result'] = true;
        } catch (\Swift_TransportException $e) {
            $response['errors'] = $e->getMessage();
            $GLOBALS['log']->info($e->getMessage());
            $response['result'] = false;
        } catch (Exception $e) {
            $response['errors'] = $e->getMessage();
            $GLOBALS['log']->info($e->getMessage());
            $response['result'] = false;
        }

        return $response;
    }

    /**
     * composeEmail
     *
     * Converts the Email bean object into the structure used by the transport handler
     *
     * @param \Email $email
     * @return \Swift_Message
     */
    protected function composeEmail($email)
    {
        $this->checkEmailClass($email);

        $message = (new \Swift_Message($email->name))
            ->setFrom([$this->mailbox->imap_pop3_username => $this->mailbox->imap_pop3_display_name])
            ->setBody($email->body, 'text/html')
        ;

        if ($this->mailbox->catch_all_address == '') {
            $toAddressess = [];
            foreach ($email->to() as $address) {
                array_push($toAddressess, $address['email']);
            }
            $message->setTo($toAddressess);
        } else { // send everything to the catch all address
            $message->setTo([$this->mailbox->catch_all_address]);
        }



        if (!empty($email->cc_addrs)) {
            $ccAddressess = [];
            foreach ($email->cc() as $address) {
                array_push($ccAddressess, $address['email']);
            }
            $message->setCc($ccAddressess);
        }

        if (!empty($email->bcc_addrs)) {
            $bccAddressess = [];
            foreach ($email->bcc() as $address) {
                array_push($bccAddressess, $address['email']);
            }
            $message->setBcc($bccAddressess);
        }

        if ($this->mailbox->reply_to != '') {
            $message->setReplyTo($this->mailbox->reply_to);
        }

        foreach (json_decode (\SpiceCRM\includes\SpiceAttachments\SpiceAttachments::getAttachmentsForBean('Emails', $email->id)) as $att) {
            $message->attach(
                \Swift_Attachment::fromPath('upload://' . $att->filemd5)->setFilename($att->filename)
            );
        }

        return $message;
    }

    /**
     * dispatch
     *
     * Sends the converted Email
     *
     * @param $message
     * @return array
     */
    protected function dispatch($message)
    {
        try {
            // todo Call to undefined method Swift_RfcComplianceException::isFatal()
            // this error message shows on the first try
            $result = [
                'result'     => $this->transport_handler->send($message),
                'message_id' => $message->getId(),
            ];

        } catch (\Swift_RfcComplianceException $exception) {
            $result = [
                'result' => false,
                'errors' => $exception->getMessage(),
            ];
            $GLOBALS['log']->info($exception->getMessage());
        } catch (\Swift_TransportException $exception) {
            $result = [
                'result' => false,
                'errors' => "Cannot inititalize connection.",
            ];
            $GLOBALS['log']->info($exception->getMessage());
        } catch (\Exception $exception) {
            $result = [
                'result' => false,
                'errors' => $exception->getMessage(),
            ];
            $GLOBALS['log']->info($exception->getMessage());
        }

        if (($result['result'] == true || $result == true) && $this->mailbox->imap_sent_dir != '') {
            $msg = $message->toString();
            imap_append($this->getImapStream(), $this->mailbox->getSentFolder(), $msg . "\r\n");
        }

        return $result;
    }

    /**
     * checkConfiguration
     *
     * Checks the existence of all necessary configuration settings.
     *
     * @param $settings
     * @return array
     */
    protected function checkConfiguration($settings)
    {
        $response = parent::checkConfiguration($settings);


        // If there is no incoming communication and SMTP authentication is disabled the password is allowed to be empty
        foreach ($response['missing'] as $index => $missingSetting) {
            if ($missingSetting == 'imap_pop3_password' && $this->mailbox->inbound_comm == 0
                && ($this->mailbox->smtp_auth == 0 || !isset($this->mailbox->smtp_auth))) {
                unset($response['missing'][$index]);
            }
        }

        /**
         * If after removing the password from the missing fields array in the case above, the missing fields array
         * is empty, then the response result should be reset to true.
         */

        if (empty($response['missing']) && $response['result'] == false) {
            $response['result'] = true;
        }

        return $response;
    }

    /**
     * parseSubject
     *
     * Parses the subject of the email and converts the encoding if necessary.
     *
     * @param $imapSubject
     * @return string
     */
    private function parseSubject($imapSubject) {
        $decodedSubject = imap_mime_header_decode($imapSubject);
        $subject = '';

        if ((substr(strtolower($decodedSubject[0]->charset), 0 ,3) == 'iso')
        || ($decodedSubject[0]->charset == 'Windows-1252')) {
            $subject = utf8_encode($decodedSubject[0]->text);
        } else {
            $subject = $decodedSubject[0]->text;
        }

        return $subject;
    }

    /**
     * parseAddress
     *
     * Parses the email address and converts the encoding if necessary.
     *
     * @param $imapAddress
     * @return string
     */
    private function parseAddress($imapAddress) {
        $decodedAddress = imap_mime_header_decode($imapAddress);
        $address = '';

        foreach ($decodedAddress as $addressPart) {
            if (strtolower($addressPart->charset) != 'utf-8'
                && strtolower($addressPart->charset) != 'default') {
                $address .= utf8_encode($addressPart->text);
            } else {
                $address .= $addressPart->text;
            }
        }

        return str_replace(',', '', $address);
    }
}
