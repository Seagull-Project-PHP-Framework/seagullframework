<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | InstantMessageMgr.php                                                     |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: InstantMessageMgr.php,v 1.28 2005/05/22 10:21:39 demian Exp $

require_once 'DB/DataObject.php';

define('SGL_PRIVATE_MAIL', false);
define('SGL_ALERT_OBSCENITY',   1);
define('SGL_ALERT_WARNING',     2);

/**
 * Simulates internal mail, basic messaging class.
 *
 * @package messaging
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.28 $
 * @since   PHP 4.1
 */
class InstantMessageMgr extends SGL_Manager
{
    function InstantMessageMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Messages';
        $this->template     = 'imInbox.html';

        $this->_aActionsMapping =  array(
            'read'      => array('read'),
            'insert'    => array('insert', 'redirectToDefault'),
            'compose'   => array('compose'),
            'reply'     => array('reply'),
            'sendAlert' => array('sendAlert', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'inbox'     => array('inbox'),
            'outbox'    => array('outbox'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'inbox';
        $input->submitted   = $req->get('submitted');
        $input->messageID   = $req->get('frmMessageID');
        $input->aRecipients = $req->get('frmRecipients');
        $input->msgFromID   = $req->get('frmMsgFromID');
        $input->deleteArray = $req->get('frmDelete');
        $input->from        = ($req->get('frmFrom')) ? $req->get('frmFrom'):0;
        $input->accuseeID   = $req->get('frmSubjID');
        $input->alertType   = $req->get('frmAlertType');
        $input->fromOutbox  = $req->get('frmFromOutbox');

        $instantMessage = new stdClass();
        $instantMessage->user_id_to = $req->get('frmUserIdTo');
        $instantMessage->user_id_from = $req->get('frmUserIdFrom');
        $instantMessage->subject = $req->get('frmSubject');
        $instantMessage->body = $req->get('frmBodyName', $allowTags = true);
        $input->instantMessage = $instantMessage;
    }

    function _cmd_inbox(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  display messages of current user, inbox
        //  mapping as follows, same applies for delete status
        //  |-------------------------------------|
        //  |   sender   | recipient   |  code    |
        //  |-------------------------------------|
        //  |   unread   |  unread     |    3     |
        //  |     read   |  unread     |    2     |
        //  |   unread   |   read      |    1     |
        //  |     read   |   read      |    0     |
        //  |-------------------------------------|

        $output->template = 'imInbox.html';
        $output->sectionTitle = 'Inbox';

        // Get the user id from the current session
        $uid = SGL_Session::getUid();

        $query =
            " SELECT    *, u.username AS from_username, u.first_name AS first_name, u.last_name AS last_name
              FROM      {$this->conf['table']['instant_message']} AS im, {$this->conf['table']['user']} AS u
              WHERE     im.user_id_to = $uid
              AND       u.usr_id = im.user_id_from
              AND       im.delete_status in (2, 3)
              ORDER BY msg_time DESC
            ";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        //  determine if pagination is required
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data']) ) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        } else {
            SGL::logMessage(' No pager', null, null, PEAR_LOG_DEBUG);
        }
    }

    function _cmd_outbox(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  display messages of current user, outbox
        //  mapping as follows for active/deleted
        //  |-------------------------------------|
        //  |   sender   | recipient   |  code    |
        //  |-------------------------------------|
        //  |   exists   |  exists     |    3     |
        //  |   deleted  |  exists     |    2     |
        //  |   exists   |  deleted    |    1     |
        //  |   deleted  |  deleted    |    0     |
        //  |-------------------------------------|

        $output->template = 'imOutbox.html';
        $output->sectionTitle = 'Sent Messages';

        // Get the user id from the current session
        $uid = SGL_Session::getUid();

        $query =
            " SELECT *
              FROM {$this->conf['table']['instant_message']} AS im, {$this->conf['table']['user']} AS u
              WHERE im.user_id_from = $uid
              AND u.usr_id = im.user_id_to
              AND im.delete_status <> 2
              ORDER BY msg_time DESC
            ";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        //  determine if pagination is required
        $output->aPagedData = $aPagedData;

        if (is_array($aPagedData['data']) && count($aPagedData['data']) ) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        } else {
            SGL::logMessage(' No pager', null, null, PEAR_LOG_DEBUG);
        }

        // NOTE: it is not enough to disable the button...you need to disable
        // the functionality. Without this next line you would still be
        // able to reply to yourself. In fact, if you type the url
        // without the outbox indicator you would still get the reply button
        // This is not a serious problem so I am not prohibiting it in code.
        //
        //  flag as coming from outbox to later disable 'reply'
        $output->urlParams = 'frmFromOutbox/1/';
    }

    function _cmd_compose(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'imCompose.html';
        $output->masterTemplate = 'masterLeftCol.html';
        $output->sectionTitle = 'Compose Message';
        $output->wysiwyg = true;
        $hiddenFields = '';
        $aToNames = array();
        $aToUserIDs = array();

        // Get the user id from the current session
        $uid = SGL_Session::getUid();

        // Setup most of the message here
        $output->messageFrom  = $uid;
        $output->messageSubject = '';
        $output->messageBody = '';
        $output->cancelRedirect = SGL_Url::makeLink('', 'contact', 'messaging');

        $counter = 0;
        // Setup the addressees
        foreach ($input->aRecipients as $recipientID) {
            $tmpUser = DB_DataObject::factory($this->conf['table']['user']);
            $tmpUser->get($recipientID);

            // All users except admin types have to obey privacy settings
            if (SGL_Session::getRoleId() != SGL_ADMIN) {
                if (SGL_PRIVATE_MAIL && (!$tmpUser->is_acct_active || !$tmpUser->is_email_public)) {
                    // Silently skip those who wish to be left alone.
                    $counter++;
                    continue;
                }
            }

            $aToNames[] = $tmpUser->username;
            $aToUserIDs[] = $tmpUser->usr_id;
            unset($tmpUser);
        }

        if (!is_array($aToNames) || count($aToNames) == 0) {
            SGL::raiseMsg('Message could not be sent, maybe some of the recipient are blocking mail');
            $aParams = array(
                 'moduleName'    => 'messaging',
                 'managerName'   => 'contact',
                 );
            SGL_HTTP::redirect($aParams);
        }

        //  implode usernames to a string
        $output->messageToUsernames = implode('; ', $aToNames);

        //  create hidden fields for userIDS
        foreach ($aToUserIDs as $userID) {
            $hiddenFields .= "\n<input type='hidden' name='frmUserIdTo[]' value='$userID'>";
        }

        $output->messageToIds = $hiddenFields;
    }

    function _cmd_reply(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        SGL_DB::setConnection();

        //  as compose and reply are very similar, they use the same template
        $output->template = 'imCompose.html';
        $output->masterTemplate = 'masterLeftCol.html';
        $output->wysiwyg = true;
        $hiddenFields = '';

        $user = DB_DataObject::factory($this->conf['table']['user']);
        if (!is_numeric($input->msgFromID)) {
            SGL::raiseError('Invalid user ID passed to ' .  __CLASS__ . '::' . __FUNCTION__,
                            SGL_ERROR_INVALIDARGS);
        }
        $user->get($input->msgFromID);
        $hiddenFields .= "\n<input type='hidden' name='frmUserIdTo[]' value='{$user->usr_id}' />";
        $output->messageToIds = $hiddenFields;
        $output->messageToUsernames = $user->username;
        $output->sectionTitle = 'Reply';
        $output->cancelRedirect = SGL_Url::makeLink('inbox', 'instantmessage', 'messaging');

        //  prepare reply message
        $origMsg = DB_DataObject::factory($this->conf['table']['instant_message']);
        $origMsg->get($input->messageID);
        if (empty($origMsg)) {
            SGL::raiseMsg('Message could not be retrieved');
            $aParams = array(
                 'moduleName'    => 'messaging',
                 'managerName'   => 'instantmessage',
                 );
            SGL_HTTP::redirect($aParams);
        }

        $res = $this->_verifyUserAccess($origMsg);
        if (empty($res)) {
            SGL::raiseMsg('Message could not be retrieved');
            $aParams = array(
                 'moduleName'    => 'messaging',
                 'managerName'   => 'instantmessage',
                 );
            SGL_HTTP::redirect($aParams);
        }

        $output->messageSubject = 'RE: ' . $origMsg->subject;
        $body = wordwrap(strip_tags($origMsg->body), 50, SGL_String::getCrlf());

        //  process body according to browser type
        require_once 'Net/UserAgent/Detect.php';

        //  if browser is non IE 5 compliant, iow, it can't handle
        //  the html widget
        if (Net_UserAgent_Detect::getBrowser('ie5up') != 'ie5up') {
            $output->messageBody = "\r\n\r\n > " . $body;

            //  if it is, format reply with html
        } else {
            $output->messageBody = "<br /><pre> > " . $body . '</pre>';
        }
        $output->messageFrom  = SGL_Session::getUid();
    }

    function _cmd_insert(&$input, &$output) // send
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // Get the user id from the current session
        $uid = SGL_Session::getUid();
        $sender_id = $input->instantMessage->user_id_from;

        // Make sure sender is the current user (spoof proofing)
        if (empty($sender_id) || $sender_id != $uid) {
            SGL::raiseMsg('Sender not found');
            $aParams = array( 'moduleName'    => 'messaging',
                              'managerName'   => 'instantmessage' );
            SGL_HTTP::redirect($aParams);
        }

        $user = DB_DataObject::factory($this->conf['table']['user']);
        $user->usr_id = $sender_id;
        if ($user->find() != 1 || $user->fetch() == false) {
            SGL::raiseMsg('Sender not found');
            $aParams = array( 'moduleName'    => 'messaging',
                              'managerName'   => 'instantmessage' );
            SGL_HTTP::redirect($aParams);
        }

        //  process body according to browser type
//        require_once 'Net/UserAgent/Detect.php';
//        $browser = & new Net_UserAgent_Detect();
//        if (!isset($browser->browser) || !$browser->browser['ie5up']) {
//            $body = $input->instantMessage->body;
//            $body = chunk_split(strip_tags($body), 80, "<br />");
//            $body = "<pre>" . $body . '</pre>';
//            $input->instantMessage->body = $body;
//        }

        $subject = strip_tags($input->instantMessage->subject);

        $counter = 0;
        foreach ($input->instantMessage->user_id_to as $receiver_id) {
            // verify each receiver

            $receiver = DB_DataObject::factory($this->conf['table']['user']);
            $receiver->usr_id = $receiver_id;
            if ($receiver->find() != 1 || $receiver->fetch() == false) {
                // Make sure they don't pass an invalid user id
                $counter++;
                continue;
            }

            if (SGL_Session::getRoleId() != SGL_ADMIN) {
                if (SGL_PRIVATE_MAIL && (!$tmpUser->is_acct_active || !$tmpUser->is_email_public)) {
                    // Skip users who chose to be anonymous or have inactive
                    // accounts
                    $counter++;
                    continue;
                }
            }

            $message = DB_DataObject::factory($this->conf['table']['instant_message']);

            $message->instant_message_id = $this->dbh->nextId('instant_message');
            $message->user_id_from = $uid;  // or $sender_id
            $message->user_id_to = $receiver_id;
            $message->msg_time = SGL_Date::getTime();
            $message->subject = $subject;
            if ($message->subject == '') {
                $message->subject = SGL_Output::translate('no subject');
            }
            $message->body = $input->instantMessage->body;

            // set default unread/undeleted status see $this->inbox()
            // delete status should come from user preference...keep sent email (3) or not (2)
            $message->read_status = 3;
            $message->delete_status = 3;

            if ($message->insert() == false) {
                $counter++;
                continue;
            }

            unset($message);
        }

        if ($counter > 0) {
            SGL::raiseMsg('Messages did not reach all recipients');
        } else {
            SGL::raiseMsg('Message sent successfully');
        }
    }

    function _cmd_read(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'imRead.html';
        $output->sectionTitle = 'Read';

        // Get the user id from the current session
        $uid = SGL_Session::getUid();

        $message_id = $input->messageID;

        $message = DB_DataObject::factory($this->conf['table']['instant_message']);
        $message->whereAdd('instant_message_id = ' . $message_id);
        if ($message->find() != 1 || $message->fetch() == false) {
            SGL::raiseMsg('Message could not be retrieved');
            $aParams = array( 'moduleName'    => 'messaging',
                              'managerName'   => 'instantmessage' );
            SGL_HTTP::redirect($aParams);
        }

        $message->getLinks('link_%s');
        $res = $this->_verifyUserAccess($message);
        if (empty($res)) {
            SGL::raiseMsg('Message could not be retrieved');
            $aParams = array( 'moduleName'    => 'messaging',
                              'managerName'   => 'instantmessage' );
            SGL_HTTP::redirect($aParams);
        }

        if ($message->user_id_from == $uid) {
            $currentUser = 'sender';
        } elseif ($message->user_id_to == $uid) {
            $currentUser = 'recipient';
        } else {
            // user IS a ...
            SGL::raiseMsg('Message could not be retrieved');
            $aParams = array( 'moduleName'    => 'messaging',
                              'managerName'   => 'instantmessage' );
            SGL_HTTP::redirect($aParams);
        }

        //  get target update code
        $currentCode = $message->read_status;
        $statusCode = $this->_getStatusCode($currentUser, $currentCode);

        //  only update status if it has changed
        if ($statusCode != $currentCode) {
            $message->read_status = $statusCode;
            $message->update();
        }

        $message->disableReply = ($input->fromOutbox) ? true : false;
        $output->cancelRedirect = ($input->fromOutbox) ? 'outbox' : 'inbox';
        $output->instantMessage = $message;
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'imRead.html';

        // Get the user id from the current session
        $uid = SGL_Session::getUid();

        $counter = 0;
        foreach ($input->deleteArray as $index => $message_id) {
            $message = DB_DataObject::factory($this->conf['table']['instant_message']);
            $message->whereAdd('instant_message_id = ' . $message_id);
            if ($message->find() != 1 || $message->fetch() == false) {
                $counter++;
                continue;
            }

            if ($message->user_id_from == $uid) {
                $currentUser = 'sender';
            } elseif ($message->user_id_to == $uid) {
                $currentUser = 'recipient';
            } else {
                // user IS a ...
                $counter++;
                continue;
            }

            //  get target update code
            $currentCode = $message->delete_status;
            $statusCode = $this->_getStatusCode($currentUser, $currentCode);
            $message->delete_status = $statusCode;

            //  if safeDelete is enabled, just set item status to 0, don't delete
            $safeDelete = $this->conf['site']['safeDelete'];
            if ($message->delete_status == 0 && !$safeDelete) {
                if (!$message->delete()) {
                    $counter++;
                    continue;
                }
            } else {
                if (!$message->update()) {
                    $counter++;
                    continue;
                }
            }

            unset($message);
        }

        if ($counter > 0) {
            SGL::raiseMsg('Not all messages were deleted successfully');
        } else {
            SGL::raiseMsg('message deleted successfully');
        }
    }

    function _cmd_sendAlert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if ($input->alertType > 0) {
            switch ($input->alertType) {

            case SGL_ALERT_OBSCENITY:
                $messageBody =  'This alert has been sent because a user would like to'
                    . ' advise you of obscene content.';
                break;
            case SGL_ALERT_WARNING:
                $messageBody =  'foo';
                break;
            }
            //  who sent message -> logged on user
            $fromID = SGL_Session::getUid();
            $accuser = DataObjects_Usr::staticGet($fromID);
            $accusee = DataObjects_Usr::staticGet($input->accuseeID);
            $messageBody .= "<br /><br /><strong>Complaint filed by:</strong> " . $accuser->username . " (id = $accuser->usr_id)";
            $messageBody .= '<br /><br /><strong>Person Accused:</strong> ' . $accusee->username . " (id = $accusee->usr_id)";
            $message = DB_DataObject::factory($this->conf['table']['instant_message']);
            $message->subject = 'ALERT: profanity notice';
            $message->body = $messageBody;

            //  set default unread/undeleted status
            //  see $this->inbox()
            $message->read_status = 3;
            $message->delete_status = 3;
            $message->user_id_to = SGL_ADMIN;
            $message->user_id_from = $fromID;
            $message->msg_time = SGL_Date::getTime();

            //  insert message
            $message->insert();
            SGL::raiseMsg('Your alert has been sent successfully');
        } else {
            SGL::raiseError('Incorrect params supplied to alert type', SGL_ERROR_INVALIDARGS);
        }
    }

    function _verifyUserAccess($instantMessage)
    {
        // Get the user id from the current session
        $uid = SGL_Session::getUid();

        // Do not display messages you did not send or receive
        if ($instantMessage->user_id_to != $uid && $instantMessage->user_id_from != $uid) {
            return false;
        }

        if ($instantMessage->user_id_to == $uid && $instantMessage->delete_status < 2) {
            // disable receiver reading messages he deleted
            return false;
        } elseif ($instantMessage->user_id_from == $uid &&
            ($instantMessage->delete_status == 2 || $instantMessage->delete_status == 0) ) {

            // disable sender reading messages he deleted
            // Override for cases where the sender is the receiver
            if ($instantMessage->user_id_to != $instantMessage->user_id_from) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets status codes for Message object (exists, deleted, read, unread).
     *
     * @access  private
     * @param   string  $currentUser    method client
     * @param   int     $iCurrentCode   input code
     * @return  int     $iStatusCode    output code
     * @see     inbox()
     * @see     outbox()
     */
    function _getStatusCode($currentUser, $iCurrentCode)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        switch ($iCurrentCode) {

        case 3:
            $iStatusCode = ($currentUser == 'sender')? 2:1;
            break;
        case 2:
            $iStatusCode = ($currentUser == 'sender')? 2:0;
            break;
        case 1:
            $iStatusCode = ($currentUser == 'sender')? 0:1;
            break;
        default:
            $iStatusCode = 0;
        }
        return $iStatusCode;
    }
}
?>
