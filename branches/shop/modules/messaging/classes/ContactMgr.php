<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | ContactMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: ContactMgr.php,v 1.24 2005/05/13 14:55:48 demian Exp $

/**
 * Manages Contacts.
 *
 * @package messaging
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.24 $
 * @since   PHP 4.1
 * @todo    needs to access a range of registered users, currently incomplete
 */
class ContactMgr extends SGL_Manager
{
    var $aRedirectParams = '';

    function ContactMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'messaging';
        $this->pageTitle    = 'Contact Manager';
        $this->template     = 'contacts.html';

        $this->_aActionsMapping =  array(
            'insert' => array('insert', 'redirectToDefault'), 
            'delete' => array('delete', 'redirectToDefault'), 
            'list'   => array('list'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = 'masterLeftCol.html';
        $input->template    = $this->template;
        $input->from        = ($req->get('frmFrom'))?$req->get('frmFrom'):0;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $this->submitted    = $req->get('submitted');
        $input->userID      = $req->get('frmUserID');
        $input->deleteArray = $req->get('frmDelete');
        $input->totalItems  = $req->get('totalItems');
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'docBlank.htm';
        require_once SGL_ENT_DIR . '/Contact.php';
        if (is_array($input->deleteArray)) {
            foreach ($input->deleteArray as $userID) {
                $user = & new DataObjects_Contact();
                $user->whereAdd("usr_id = $userID");
                $user->whereAdd("originator_id  = " . SGL_HTTP_Session::getUid());
                $user->delete(true);
                unset($user);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' . __FUNCTION__, 
                SGL_ERROR_INVALIDARGS);
        }
        SGL::raiseMsg('contacts successfully deleted');
    }

    function _insert(&$input, &$output)
    {
        if (SGL_HTTP_Session::getUserType() != SGL_ADMIN) {
            require_once SGL_ENT_DIR . '/Contact.php';
            $savedUser = & new DataObjects_Contact();
            $dbh = $savedUser->getDatabaseConnection();

            //  skip if user already exists
            $savedUser->usr_id = $input->userID;
            $savedUser->originator_id  = SGL_HTTP_Session::getUid();
            $numRows = $savedUser->find();
            if ($numRows < 1) {
                $savedUser->contact_id       = $dbh->nextId('contact');
                $savedUser->date_created     = SGL::getTime();
                $savedUser->originator_id    = SGL_HTTP_Session::getUid();
                $savedUser->usr_id           = $input->userID;
                $res = $savedUser->insert();
            }
            $this->redirectTarget = 'contactMgr.php';
            $this->aRedirectParams = array(
                'moduleName'    => 'messaging',
                'managerName'   => 'contact',
                );

            $message = 'contacts successfully added';
        } else {
            //  admins not allowed to save contacts
            $this->redirectTarget = 'articles.php';
            $this->aRedirectParams = array(
                'moduleName'    => 'default',
                'managerName'   => 'default',
                );
            $message = 'admin cannot save contacts';
        }
        SGL::raiseMsg($message);
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->sectionTitle = 'Contacts';
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $dbh = & SGL_DB::singleton();
        $query = '
            SELECT  u.usr_id, u.username, u.first_name, u.last_name
            FROM    ' . $conf['table']['user'] . ' u, ' . $conf['table']['contact'] . ' c 
            WHERE   c.originator_id = ' . SGL_HTTP_Session::getUid() . '
            AND     u.usr_id = c.usr_id
            ORDER BY u.last_updated DESC
        ';

        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit || $input->moduleId) ? false : true;
        }
    }

    function _redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!(count($GLOBALS['_SGL']['ERRORS']))) {
            SGL_HTTP::redirect($this->aRedirectParams);

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
        }
    }
}
?>
