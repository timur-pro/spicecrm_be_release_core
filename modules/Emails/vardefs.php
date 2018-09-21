<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
* SugarCRM Community Edition is a customer relationship management program developed by
* SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
* 
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU Affero General Public License version 3 as published by the
* Free Software Foundation with the addition of the following permission added
* to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
* IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
* OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
* 
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
* details.
* 
* You should have received a copy of the GNU Affero General Public License along with
* this program; if not, see http://www.gnu.org/licenses or write to the Free
* Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
* 02110-1301 USA.
* 
* You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
* SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
* 
* The interactive user interfaces in modified source and object code versions
* of this program must display Appropriate Legal Notices, as required under
* Section 5 of the GNU Affero General Public License version 3.
* 
* In accordance with Section 7(b) of the GNU Affero General Public License version 3,
* these Appropriate Legal Notices must retain the display of the "Powered by
* SugarCRM" logo. If the display of the logo is not reasonably feasible for
* technical reasons, the Appropriate Legal Notices must display the words
* "Powered by SugarCRM".
********************************************************************************/


$dictionary['Email'] = array(
    'table' => 'emails',
    'acl_fields' => false,
    'comment' => 'Contains a record of emails sent to and from the Sugar application',
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'vname' => 'LBL_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => true,
            'comment' => 'Unique identifier',
        ),
        'date_entered' => array(
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'required' => true,
            'comment' => 'Date record created',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'required' => true,
            'comment' => 'Date record last modified',
        ),
        'assigned_user_id' => array(
            'name' => 'assigned_user_id',
            'rname' => 'user_name',
            'id_name' => 'assigned_user_id',
            'vname' => 'LBL_ASSIGNED_TO',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'reportable' => true,
            'dbType' => 'id',
            'comment' => 'User ID that last modified record',
        ),
        'assigned_user_name' => array(
            'name' => 'assigned_user_name',
            'vname' => 'LBL_ASSIGNED_TO',
            'type' => 'varchar',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'users',
        ),
        'modified_user_id' => array(
            'name' => 'modified_user_id',
            'rname' => 'user_name',
            'id_name' => 'modified_user_id',
            'vname' => 'LBL_MODIFIED_BY',
            'type' => 'assigned_user_name',
            'table' => 'users',
            'isnull' => 'false',
            'reportable' => true,
            'dbType' => 'id',
            'comment' => 'User ID that last modified record',
        ),
        'created_by' => array(
            'name' => 'created_by',
            'vname' => 'LBL_CREATED_BY',
            'type' => 'id',
            'len' => '36',
            'reportable' => false,
            'comment' => 'User name who created record',
        ),
        'deleted' => array(
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
            'comment' => 'Record deletion indicator',
        ),
        /**
         * DEPRECATED FOR 5.0
         * 'from_addr' => array (
         * 'name' => 'from_addr',
         * 'vname' => 'LBL_FROM',
         * 'type' => 'id',
         * 'comment' => 'Email address of the person sending the email',
         * ),
         * 'reply_to_addr' => array (
         * 'name' => 'reply_to_addr',
         * 'vname' => 'LBL_REPLY_TO_ADDRESS',
         * 'type' => 'id',
         * 'comment' => 'Email address of person indicated in the Reply-to email field',
         * ),
         * 'to_addrs' => array (
         * 'name' => 'to_addrs',
         * 'vname' => 'LBL_TO',
         * 'type' => 'id',
         * 'comment' => 'Email address(es) of person(s) to receive the email',
         * ),
         * 'cc_addrs' => array (
         * 'name' => 'cc_addrs',
         * 'vname' => 'LBL_CC',
         * 'type' => 'id',
         * 'comment' => 'Email address(es) of person(s) to receive a carbon copy of the email',
         * ),
         * 'bcc_addrs' => array (
         * 'name' => 'bcc_addrs',
         * 'vname' => 'LBL_BCC',
         * 'type' => 'id',
         * 'comment' => 'Email address(es) of person(s) to receive a blind carbon copy of the email',
         * ),
         */


        'from_addr_name' => array(
            'name' => 'from_addr_name',
            'type' => 'varchar',
            'vname' => 'LBL_FROM',
            'source' => 'non-db',
        ),
        'reply_to_addr_name' => array(
            'name' => 'reply_to_addr_name',
            'type' => 'varchar',
            'vname' => 'LBL_REPLY_TO_ADDR_NAME',
            'source' => 'non-db',
        ),
        'to_addrs_names' => array(
            'name' => 'to_addrs_names',
            'type' => 'varchar',
            'vname' => 'LBL_TO',
            'source' => 'non-db',
        ),
        'cc_addrs_names' => array(
            'name' => 'cc_addrs_names',
            'type' => 'varchar',
            'vname' => 'LBL_CC',
            'source' => 'non-db',
        ),
        'bcc_addrs_names' => array(
            'name' => 'bcc_addrs_names',
            'type' => 'varchar',
            'vname' => 'LBL_BCC',
            'source' => 'non-db',
        ),
        'raw_source' => array(
            'name' => 'raw_source',
            'type' => 'varchar',
            'vname' => 'raw_source',
            'source' => 'non-db',
        ),
        'description_html' => array(
            'name' => 'description_html',
            'type' => 'html',
            'vname' => 'LBL_BODY_HTML',
            'source' => 'non-db',
        ),
        'description' => array(
            'name' => 'description',
            'type' => 'text',
            'vname' => 'LBL_BODY',
            'source' => 'non-db',
        ),
        'date_sent' => array(
            'name' => 'date_sent',
            'vname' => 'LBL_DATE_SENT',
            'type' => 'datetime',
        ),
        'message_id' => array(
            'name' => 'message_id',
            'vname' => 'LBL_MESSAGE_ID',
            'type' => 'varchar',
            'len' => 255,
            'comment' => 'ID of the email item obtained from the email transport system',
        ),
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_SUBJECT',
            'type' => 'name',
            'dbType' => 'varchar',
            'required' => false,
            'len' => '255',
            'comment' => 'The subject of the email',
        ),
        'type' => array(
            'name' => 'type',
            'vname' => 'LBL_LIST_TYPE',
            'type' => 'enum',
            'options' => 'dom_email_types',
            'len' => 100,
            'massupdate' => false,
            'comment' => 'Type of email (ex: draft)',
        ),
        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'dom_email_status',
        ),
        'openness' => array(
            'name' => 'openness',
            'vname' => 'LBL_OPENNESS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'dom_email_openness'
        ),
        'flagged' => array(
            'name' => 'flagged',
            'vname' => 'LBL_EMAIL_FLAGGED',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
            'comment' => 'flagged status',
        ),
        'reply_to_status' => array(
            'name' => 'reply_to_status',
            'vname' => 'LBL_EMAIL_REPLY_TO_STATUS',
            'type' => 'bool',
            'required' => false,
            'reportable' => false,
            'comment' => 'I you reply to an email then reply to status of original email is set',
        ),
        'intent' => array(
            'name' => 'intent',
            'vname' => 'LBL_INTENT',
            'type' => 'varchar',
            'len' => 100,
            'default' => 'pick',
            'comment' => 'Target of action used in Inbound Email assignment',
        ),
        'mailbox_id' => array(
            'name' => 'mailbox_id',
            'vname' => 'LBL_MAILBOXES',
            'type' => 'mailbox',
            'dbtype' => 'varchar',
            'len' => '36',
            'reportable' => false,
        ),
        'created_by_link' => array(
            'name' => 'created_by_link',
            'type' => 'link',
            'relationship' => 'emails_created_by',
            'vname' => 'LBL_CREATED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'modified_user_link' => array(
            'name' => 'modified_user_link',
            'type' => 'link',
            'relationship' => 'emails_modified_user',
            'vname' => 'LBL_MODIFIED_BY_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
        ),
        'assigned_user_link' => array(
            'name' => 'assigned_user_link',
            'type' => 'link',
            'relationship' => 'emails_assigned_user',
            'vname' => 'LBL_ASSIGNED_TO_USER',
            'link_type' => 'one',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db',
            'default' => true
        ),

        'parent_name' => array(
            'name' => 'parent_name',
            'type' => 'parent',
            'vname' => 'LBL_RELATED_TO',
            'reportable' => false,
            'source' => 'non-db',
        ),
        'parent_type' => array(
            'name' => 'parent_type',
            'type' => 'varchar',
            'reportable' => false,
            'len' => 100,
            'comment' => 'Identifier of Sugar module to which this email is associated (deprecated as of 4.2)',
        ),
        'parent_id' => array(
            'name' => 'parent_id',
            'type' => 'id',
            'len' => '36',
            'reportable' => false,
            'comment' => 'ID of Sugar object referenced by parent_type (deprecated as of 4.2)',
        ),

        /* relationship collection attributes */
        /* added to support InboundEmail */
        'accounts' => array(
            'name' => 'accounts',
            'vname' => 'LBL_EMAILS_ACCOUNTS_REL',
            'type' => 'link',
            'relationship' => 'emails_accounts_rel',
            'module' => 'Accounts',
            'bean_name' => 'Account',
            'source' => 'non-db'
        ),
        'bugs' => array(
            'name' => 'bugs',
            'vname' => 'LBL_EMAILS_BUGS_REL',
            'type' => 'link',
            'relationship' => 'emails_bugs_rel',
            'module' => 'Bugs',
            'bean_name' => 'Bug',
            'source' => 'non-db'
        ),
        'cases' => array(
            'name' => 'cases',
            'vname' => 'LBL_EMAILS_CASES_REL',
            'type' => 'link',
            'relationship' => 'emails_cases_rel',
            'module' => 'Cases',
            'bean_name' => 'Case',
            'source' => 'non-db'
        ),
        'contacts' => array(
            'name' => 'contacts',
            'vname' => 'LBL_EMAILS_CONTACTS_REL',
            'type' => 'link',
            'relationship' => 'emails_contacts_rel',
            'module' => 'Contacts',
            'bean_name' => 'Contact',
            'source' => 'non-db'
        ),
        'leads' => array(
            'name' => 'leads',
            'vname' => 'LBL_EMAILS_LEADS_REL',
            'type' => 'link',
            'relationship' => 'emails_leads_rel',
            'module' => 'Leads',
            'bean_name' => 'Lead',
            'source' => 'non-db'
        ),
        'opportunities' => array(
            'name' => 'opportunities',
            'vname' => 'LBL_EMAILS_OPPORTUNITIES_REL',
            'type' => 'link',
            'relationship' => 'emails_opportunities_rel',
            'module' => 'Opportunities',
            'bean_name' => 'Opportunity',
            'source' => 'non-db'
        ),
        'project' => array(
            'name' => 'project',
            'vname' => 'LBL_EMAILS_PROJECT_REL',
            'type' => 'link',
            'relationship' => 'emails_projects_rel',
            'module' => 'Projects',
            'bean_name' => 'Project',
            'source' => 'non-db'
        ),
        'projecttask' => array(
            'name' => 'projecttask',
            'vname' => 'LBL_EMAILS_PROJECT_TASK_REL',
            'type' => 'link',
            'relationship' => 'emails_project_task_rel',
            'module' => 'ProjectTasks',
            'bean_name' => 'ProjectTask',
            'source' => 'non-db'
        ),
        'prospects' => array(
            'name' => 'prospects',
            'vname' => 'LBL_EMAILS_PROSPECT_REL',
            'type' => 'link',
            'relationship' => 'emails_prospects_rel',
            'module' => 'Prospects',
            'bean_name' => 'Prospect',
            'source' => 'non-db'
        ),


        'tasks' => array(
            'name' => 'tasks',
            'vname' => 'LBL_EMAILS_TASKS_REL',
            'type' => 'link',
            'relationship' => 'emails_tasks_rel',
            'module' => 'Tasks',
            'bean_name' => 'Task',
            'source' => 'non-db'
        ),
        'users' => array(
            'name' => 'users',
            'vname' => 'LBL_EMAILS_USERS_REL',
            'type' => 'link',
            'relationship' => 'emails_users_rel',
            'module' => 'Users',
            'bean_name' => 'User',
            'source' => 'non-db'
        ),
        'notes' => array(
            'name' => 'notes',
            'vname' => 'LBL_EMAILS_NOTES_REL',
            'type' => 'link',
            'relationship' => 'emails_notes_rel',
            'module' => 'Notes',
            'bean_name' => 'Note',
            'source' => 'non-db'
        ),
        'to_be_sent' => [
            'name' => 'to_be_sent',
            'vname' => 'LBL_TO_BE_SENT',
            'source' => 'non-db',
            'type' => 'bool',
            'default' => false,
        ],
        // SNIP
        'meetings' => array(
            'name' => 'meetings',
            'vname' => 'LBL_EMAILS_MEETINGS_REL',
            'type' => 'link',
            'relationship' => 'emails_meetings_rel',
            'module' => 'Meetings',
            'bean_name' => 'Meeting',
            'source' => 'non-db'
        ),

        'mailboxes' => array(
            'name' => 'mailboxes',
            'vname' => 'LBL_MAILBOXES',
            'type' => 'link',
            'relationship' => 'mailboxes_emails_rel',
            'link_type' => 'one',
            'source' => 'non-db',
            'duplicate_merge' => 'disabled',
            'massupdate' => false,
            'module' => 'Mailboxes',
            'bean_name' => 'Mailbox',
        ),

        'mailbox_name' => array(
            'name' => 'mailbox_name',
            'rname' => 'name',
            'id_name' => 'mailbox_id',
            'vname' => 'LBL_MAILBOXES',
            'type' => 'relate',
            'table' => 'mailboxes',
            'join_name' => 'mailboxes',
            'isnull' => 'true',
            'module' => 'Mailboxes',
            'dbType' => 'varchar',
            'link' => 'mailboxes',
            'len' => '255',
            'source' => 'non-db',
            'unified_search' => true,
            'required' => true,
            'importable' => 'required',
        ),

        'body' => array(
            'name' => 'body',
            'type' => 'blob',
            'dbType' => 'longblob',
            'vname' => 'LBL_EMAIL_BODY',
            'comment' => 'the body of the email',
        ),

        'from_addr' => array(
            'name' => 'from_addr',
            'type' => 'varchar',
            'vname' => 'LBL_FROM',
            'comment' => 'sender\'s address',
        ),

        'reply_to_addr' => array(
            'name' => 'reply_to_addr',
            'type' => 'varchar',
            'vname' => 'LBL_REPLY_TO_ADDR',
            'comment' => 'reply-to address',
        ),

        'to_addrs' => array(
            'name' => 'to_addrs',
            'type' => 'text',
            'vname' => 'LBL_TO_ADDRS',
            'comment' => 'recipients\' addresses',
        ),

        'cc_addrs' => array(
            'name' => 'cc_addrs',
            'type' => 'text',
            'vname' => 'LBL_CC_ADDRS',
            'comment' => 'carbon copy addresses',
        ),

        'bcc_addrs' => array(
            'name' => 'bcc_addrs',
            'type' => 'text',
            'vname' => 'LBL_BCC_ADDRS',
            'comment' => 'blind carbon copy addresses',
        ),
        'recipient_addresses' => array(
            'name' => 'recipient_addresses',
            'type' => 'text',
            'source' => 'non-db',
            'vname' => 'LBL_RECIPIENT_ADDRESSES',
            'comment' => 'array with recipient addresses',
        )
        /* end relationship collections */

    ), /* end fields() array */
    'relationships' => array(
        'emails_assigned_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'assigned_user_id',
            'relationship_type' => 'one-to-many'
        ),
        'emails_modified_user' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'modified_user_id',
            'relationship_type' => 'one-to-many'
        ),
        'emails_created_by' => array(
            'lhs_module' => 'Users',
            'lhs_table' => 'users',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'created_by',
            'relationship_type' => 'one-to-many'
        ),
        'emails_notes_rel' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
        ),
        'emails_contacts_rel' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Contacts',
            'rhs_table' => 'contacts',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Contacts',
        ),
        'emails_accounts_rel' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Accounts',
            'rhs_table' => 'accounts',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Accounts',
        ),
        'emails_leads_rel' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Leads',
            'rhs_table' => 'leads',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'emails_beans',
            'join_key_lhs' => 'email_id',
            'join_key_rhs' => 'bean_id',
            'relationship_role_column' => 'bean_module',
            'relationship_role_column_value' => 'Leads',
        ),
        // SNIP
        'emails_meetings_rel' => array(
            'lhs_module' => 'Emails',
            'lhs_table' => 'emails',
            'lhs_key' => 'id',
            'rhs_module' => 'Meetings',
            'rhs_table' => 'meetings',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
        ),
        'mailboxes_emails_rel' => array(
            'lhs_module' => 'Mailboxes',
            'lhs_table' => 'mailboxes',
            'lhs_key' => 'id',
            'rhs_module' => 'Emails',
            'rhs_table' => 'emails',
            'rhs_key' => 'mailbox_id',
            'relationship_type' => 'one-to-many',
        )

    ), // end relationships
    'indices' => array(
        array(
            'name' => 'idx_email_name',
            'type' => 'index',
            'fields' => array('name')
        ),
        array(
            'name' => 'idx_message_id',
            'type' => 'index',
            'fields' => array('message_id')
        ),
        array(
            'name' => 'idx_email_parent_id',
            'type' => 'index',
            'fields' => array('parent_id')
        ),
        array(
            'name' => 'idx_email_assigned',
            'type' => 'index',
            'fields' => array('assigned_user_id', 'type', 'status')
        ),
    ) // end indices
);
//BEGIN PHP7.1 compatibility: avoid PHP Fatal error:  Uncaught Error: Cannot use string offset as an array
global $dictionary;
//END
#create relationship to parent
$dictionary['Email']['fields']['emailtemplate_id'] = array(
    'name' => 'emailtemplate_id',
    'type' => 'id',
    'vname' => 'LBL_EMAILTEMPLATE'
);

$dictionary['Email']['fields']['emailtemplate_name'] = array(
    'source' => 'non-db',
    'name' => 'emailtemplate_name',
    'vname' => 'LBL_EMAILTEMPLATE',
    'type' => 'relate',
    'len' => '255',
    'id_name' => 'emailtemplate_id',
    'module' => 'EmailTemplates',
    'link' => 'emailtemplates_link',
    'join_name' => 'emailtemplates',
    'rname' => 'name'
);

$dictionary['Email']['fields']['emailtemplates_link'] = array(
    'name' => 'emailtemplates_link',
    'type' => 'link',
    'relationship' => 'emailtemplates_emails',
    'link_type' => 'one',
    'side' => 'right',
    'source' => 'non-db',
    'vname' => 'LBL_EMAILTEMPLATES_EMAILS_LINK',
);

#create index
$dictionary['Email']['indices']['emailtemplates_emails_emailtemplate_id'] = array('name' => 'emailtemplates_emails_emailtemplate_id', 'type' => 'index', 'fields' => array('emailtemplate_id'));

if (file_exists('modules/ServiceTickets/ServiceTicket.php')) {
    $dictionary['Email']['fields']['servicetickets'] = array(
        'name' => 'servicetickets',
        'vname' => 'LBL_EMAILS_SERVICETICKETS_REL',
        'type' => 'link',
        'relationship' => 'emails_servicetickets_rel',
        'module' => 'ServiceTickets',
        'bean_name' => 'ServiceTicket',
        'source' => 'non-db',
        'default' => true
    );
}
if (file_exists('modules/ServiceOrders/ServiceOrder.php')) {
    $dictionary['Email']['fields']['serviceorders'] = array(
        'name' => 'serviceorders',
        'vname' => 'LBL_EMAILS_SERVICEORDERS_REL',
        'type' => 'link',
        'relationship' => 'emails_serviceorders_rel',
        'module' => 'ServiceOrders',
        'bean_name' => 'ServiceOrder',
        'source' => 'non-db',
        'default' => true
    );
}

VardefManager::createVardef('Emails', 'Email', array('default'));
