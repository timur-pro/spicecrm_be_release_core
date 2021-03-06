<?php

namespace SpiceCRM\modules\Emails\KREST\controllers;

use BeanFactory;
use Exception;
use Email;
use KREST\ForbiddenException;
use KREST\NotFoundException;
use SpiceCRM\includes\SpiceAttachments\SpiceAttachments;
use SpiceCRM\includes\SpiceFTSManager\SpiceFTSHandler;
use SpiceCRM\modules\Mailboxes\Handlers\OutlookAttachmentHandler;
use UploadFile;

class EmailsKRESTController
{
    /**
     * getEmail
     *
     * Returns the Email UUID for a given Message ID, or null if the Email doesn't exist in the Spice DB.
     *
     * Available in KREST as POST under /module/Emails/groupware/getemail
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws NotFoundException
     */
    public function getEmail($req, $res, $args) {
        $postBody = $req->getParsedBody();
        $moduleHandler = new \KRESTModuleHandler();
        $result = [];

        global $db;

        $message_id = filter_var($postBody['message_id'], FILTER_SANITIZE_STRING);

        $email = BeanFactory::getBean('Emails');

        // get linked items
        if ($email->retrieve_by_string_fields(['message_id' => $message_id])) {
            $result['email_id']    = $email->id;
            $result['attachments'] = $email->getExternalAttachments();
            $linkedBeansObj = $db->query("SELECT bean_module, bean_id FROM emails_beans WHERE email_id = '$email->id'");
            while ($linkedBean = $db->fetchByAssoc($linkedBeansObj)) {
                $bean = BeanFactory::getBean($linkedBean['bean_module'], $linkedBean['bean_id']);
                if ($bean) {
                    $result['linkedBeans'][$linkedBean['bean_id']] = [
                        'id'     => $linkedBean['bean_id'],
                        'module' => $linkedBean['bean_module'],
                        'name'   => $bean->get_summary_text(),
                        'data'   => $moduleHandler->mapBeanToArray($linkedBean['bean_module'], $bean),
                    ];
                }
            }
        } else {
            throw new NotFoundException('Email not found');
        }

        return $res->write(json_encode($result));
    }

    /**
     * process
     *
     * Goes thru the list of email processors assigned to this email's mailbox and runs the processing.
     *
     * @param $req
     * @param $res
     * @param $args
     * @throws NotFoundException
     * @throws \KREST\Exception
     */
    public function process($req, $res, $args) {
        $email = self::getEmailBean($args['id']);

        $email->processEmail();
    }

    /**
     * todo check if it still works
     * todo possibly merge with saveEmailWithBeans
     *
     * @param $req
     * @param $res
     * @param $args
     * @return array|string
     * @throws Exception
     */
    public function saveAttachments($req, $res, $args) {
        $postBody = $req->getParsedBody();

        $emailId = filter_var($postBody['email_id'], FILTER_SANITIZE_STRING);
        $email = \BeanFactory::getBean('Emails', $emailId);

        if (isset($postBody['outlookAttachments'])) {
            return $res->write(json_encode($this->handleOutlookAttachments($email, $postBody)));
        } else {
            // todo correct http status
            return $res->write(json_encode("Error: No Outlook Attachments found"));
        }
    }

    /**
     * saveEmailWithBeans
     *
     * Saves an Email sent by external add-ons (Gmail, Outlook)
     * and additionally saves the Email to Bean relations.
     *
     * Available in KREST as POST under /module/Emails/groupware/saveemailwithbeans
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function saveEmailWithBeans($req, $res, $args) {
        $postBody = $req->getParsedBody();

        if (!isset($postBody['email'])) {
            throw new \Exception('Email missing');
        }
        if (!isset($postBody['beans'])) {
            throw new \Exception('Beans missing');
        }

        $email = $this->externalDataToEmail($postBody['email'], $postBody['beans']);

        $result  = [];
        $isFirst = true;
        foreach ($postBody['beans'] as $bean) {
            // ignore the first bean, as it is set as the parent for the email
            if ($isFirst) {
                $isFirst = false;
                continue;
            }

            // correction for boolean type of 'selected' parameter from Gmail add-on
            $result[] = $email->assignBeanToEmail($bean["id"], $bean["module"]);
        }

        return $res->write(json_encode(['email_id' => $email->id, 'linked_beans' => $result]));
    }

    /**
     * search
     *
     * Performs a search on all the beans using FTS.
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     */
    public function search($req, $res, $args) {
        $postBody = $req->getParsedBody();
        global $db;

        $ftsManager = new SpiceFTSHandler();

        // get the modules that are fts enabled and have a link to emails
        $modulesObject = $db->query("SELECT sysfts.module FROM relationships, sysfts
            WHERE lhs_module = 'Emails' AND module = rhs_module UNION SELECT sysfts.module
             FROM relationships, sysfts WHERE rhs_module = 'Emails' AND module = lhs_module");

        $modules = [];
        while ($module = $db->fetchByAssoc($modulesObject)) {
            $modules[] = $module['module'];
        }

        $beans = [];
        $results = $ftsManager->getGlobalSearchResults(implode(',', $modules), $postBody['searchterm'], $postBody);
        foreach ($results as $module => $result) {
            foreach ($result['hits'] as $hit) {
                $beans[] = [
                    'id'           => $hit['_id'],
                    'module'       => $module,
                    'summary_text' => $hit['_source']['summary_text'],
                    'score'        => $hit['_score']
                ];
            }
        }

        usort($beans, function($a, $b) {
            return $a['score'] > $b['score'] ? 1 : -1;
        });

        return $res->write(json_encode($beans));
    }

    /**
     * setOpenness
     *
     * Changes the openness ot the email.
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws NotFoundException
     * @throws \KREST\Exception
     */
    public function setOpenness($req, $res, $args) {
        $email = self::getEmailBean($args['id']);

        $email->openness = $args['openness'];
        $email->save();

        return $res->write(json_encode(['status' => 'success']));
    }

    /**
     * setStatus
     *
     * Changes the status of the email.
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws NotFoundException
     * @throws \KREST\Exception
     */
    public function setStatus($req, $res, $args) {
        // todo: check auf erlaubte stati
        $email = self::getEmailBean($args['id']);

        $email->status = $args['status'];
        $email->save();

        return $res->write(json_encode(['status' => 'success']));
    }

    /**
     * parseMsgAttachment
     *
     * Parses a SpiceAttachment in .msg format and saves it as an Email Bean.
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function parseMsgAttachment($req, $res, $args) {
        $attachmentId = filter_var($args['attachmentId'], FILTER_SANITIZE_STRING);
        $attachment   = json_decode(SpiceAttachments::getAttachment($attachmentId));
        $email = Email::convertMsgToEmail($attachment->filemd5);
        $email->save();
        return $res->withJson($email);
    }

    /**
     * previewMsgAttachment
     *
     * Parses a SpiceAttachment in .msg format and converts it to an Email Bean, but doesn't save it.
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function previewMsgFromAttachment($req, $res, $args) {
        $attachmentId = filter_var($args['attachmentId'], FILTER_SANITIZE_STRING);
        $attachment   = json_decode(SpiceAttachments::getAttachment($attachmentId));
        $email = \BeanFactory::getBean('Emails');
        $email->convertMsgToEmail($attachment->filemd5);

        $KRESTModuleHandler = new \KRESTModuleHandler();

        $emailResponse = $KRESTModuleHandler->mapBeanToArray('EMails', $email, array(), false, false);

        // prepare email addresses
        // email addresse temporär umschreiben ..

        // todo utf8 beachten
        $emailResponse['body'] = $emailResponse['body'];

        return $res->withJson($emailResponse);
    }

    /**
     * createEmailFromMSGFile
     *
     * Saves the posted .msg file (base64 encoded), converts it into an Email Bean and adds the relationships.
     *
     * @param $req
     * @param $res
     * @param $args
     * @return mixed
     * @throws Exception
     */
    public function createEmailFromMSGFile($req, $res, $args) {
        $postBody = $body = $req->getParsedBody();
        $postParams = $_GET;
        // $result = SpiceAttachments::saveAttachmentHashFiles($args['beanName'], $args['beanId'], array_merge($postBody, $postParams));

        $email = \BeanFactory::getBean('Emails');
        $email->id = create_guid();
        $email->new_with_id = true;
        $email->filename = $postBody['filename'];
        $email->file_mime_type = $postBody['filemimetype'];

        // create a guid for the email and save the message as file with the bean id
        $emailId = create_guid();
        $upload_file = new UploadFile('file');
        $decodedFile = base64_decode($postBody['file']);
        $upload_file->set_for_soap($email->id, $decodedFile);
        $upload_file->final_move($email->id, true);

        // convert the message
        $email->convertMsgToEmail($email->id, $postBody['beanModule'], $postBody['beanId']);
        $email->save();

        $KRESTModuleHandler = new \KRESTModuleHandler();

        return $res->write(json_encode($KRESTModuleHandler->get_bean_detail('Emails', $email->id, null)));
    }

    /**
     * externalDataToEmail
     *
     * Converts the data from the external add-ons (Gmail, Outlook) into an Email bean and saves it.
     *
     * @param $data
     * @param $beans
     * @return Email
     * @throws Exception
     */
    private function externalDataToEmail($data, $beans) {
        try {
            $email = Email::findByMessageId($data['message_id']);

            return $email;
        } catch (Exception $e) {
            $email = \BeanFactory::getBean('Emails');

            $email->name          = $data['subject'];
            $email->body          = $data['body'];
            $email->message_id    = $data['message_id'];
            $email->date_sent     = date('Y-m-d H:i:s', strtotime($data['date']));
            $email->type          = Email::TYPE_INBOUND;
            $email->status        = Email::STATUS_READ;
            $email->openness      = Email::OPENNESS_OPEN;
            $email->from_addr     = $data['from'];
            $email->to_addrs      = $data['to'];
            $email->reply_to_addr = $data['replyto'];

            // add parent_type and parent_id -> the first bean from the list
            $firstBean = \BeanFactory::getBean($beans[0]['module'], $beans[0]['id']);
            if ($firstBean) {
                $email->parent_type = $beans[0]['module'];
                $email->parent_id   = $beans[0]['id'];
            }

            if (isset($data['cc'])) {
                $email->cc_addrs = $data['cc'];
            }
            if (isset($data['bcc'])) {
                $email->bcc_addrs = $data['bcc'];
            }

            $email->save();
        }

        /**
         * Deal with the Outlook add-in attachments
         */
        if (isset($data['outlookAttachments'])) {
            $this->handleOutlookAttachments($email, $data);
        }

        /**
         * Deal with the GMail plugin attachments
         */
        if (isset($data['googleAttachments'])) {
            $this->handleGmailAttachments($email, $data['googleAttachments']);
        }

        return $email;
    }

    /**
     * findEmailByMessageId
     *
     * Finds the email in the database by its message_id.
     *
     * todo maybe use Email::findByMessageId instead
     *
     * @param $message_id
     * @return Email
     */
    private function findEmailByMessageId($message_id) {
        global $db;

        $query = "SELECT * FROM emails WHERE message_id = '" . $message_id . "' LIMIT 1";

        $result = $db->query($query);
        if ($result->num_rows === 0) {
            return null;
        }
        $row = $result->fetch_assoc();
        $email = \BeanFactory::getBean('Emails', $row['id']);
        return $email;
    }

    /**
     *getEmailBean
     *
     * Retrieves the email bean or throws exceptions.
     *
     * @param $emailId
     * @return Email
     * @throws NotFoundException
     * @throws \KREST\Exception
     */
    private static function getEmailBean($emailId) {
        $email = BeanFactory::getBean('Emails', $emailId);
        if (!$email) {
            throw (new NotFoundException('Record not found.'))->setLookedFor(id);
        }

        if (!$email->ACLAccess('edit')) {
            throw (new ForbiddenException("Forbidden to edit Email."))->setErrorCode('noModuleEdit');
        }

        return $email;
    }

    /**
     * handleOutlookAttachments
     *
     * Initializes the OutlookAttachmentHandler and saves the Outlook attachments sent from the Outlook add-in
     *
     * @param Email $email
     * @param $attachmentData
     * @return array
     * @throws Exception
     */
    private function handleOutlookAttachments(Email $email, $attachmentData) {
        $attachmentHandler = new OutlookAttachmentHandler($email, $attachmentData);
        return $attachmentHandler->saveAttachments();
    }

    private function setEmailAttachment($beanId, $post = [])
    {
        $upload_file = new UploadFile('file');
        if ($post['file']) {
            $decodedFile = base64_decode($post['file']);
            $upload_file->set_for_soap($beanId, $decodedFile);
            $upload_file->final_move($beanId, true);
        } else if (isset($_FILES['file']) && $upload_file->confirm_upload()) {
            $upload_file->use_proxy = $_FILES['file']['proxy'] ? true : false;
            $upload_file->final_move($beanId, true);
        }
    }
}
