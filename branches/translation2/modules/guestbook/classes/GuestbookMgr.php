<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | GuestbookMgr.php                                                          |
// +---------------------------------------------------------------------------+
// | Author:   Boris Kerbikov <boris@techdatasolutions.com>                    |
// +---------------------------------------------------------------------------+
// $Id: GuestbookMgr.php,v 1.22 2005/01/21 00:26:16 demian Exp $

require_once SGL_CORE_DIR . '/Manager.php';
require_once SGL_ENT_DIR . '/Guestbook.php';

/**
 * To allow users to contact site admins.
 *
 * @package guestbook
 * @author  Boris Kerbikov <boris@techdatasolutions.com>
 * @version $Revision: 1.22 $
 * @since   PHP 4.1
 */
class GuestbookMgr extends SGL_Manager
{
    function GuestbookMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module       = 'guestbook';
        $this->pageTitle    = 'Guestbook Manager';
        $this->template     = 'guestbookList.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'), 
            'insert'    => array('insert', 'redirectToDefault'),
            'list'      => array('list'), 
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
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submit      = $req->get('submitted');
        $input->guestbook   = (object)$req->get('guestbook');
        $input->from        = ($req->get('frmFrom')) ?
                               $req->get('frmFrom') : 0;
        $input->totalItems  = $req->get('totalItems');

        if ($input->submit) {
            if (empty($input->guestbook->name)) {
                $aErrors['name'] = 'Please, specify your name';
            }
            if (empty($input->guestbook->email)) {
                $aErrors['email'] = 'Please, specify your email';
            }
            if (empty($input->guestbook->message)) {
                $aErrors['message'] = 'Please, fill in the message text';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'guestbookAdd.html';
            $this->validated = false;
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = 'Guestbook Manager :: Add';
        $output->template = 'guestbookAdd.html';

        //  build ordering select object
        $output->guestbook = & new DataObjects_Guestbook();
    }
    
    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        //  insert record
        $newEntry = & new DataObjects_Guestbook();
        $newEntry->setFrom($input->guestbook);
        $dbh = $newEntry->getDatabaseConnection();
        $newEntry->guestbook_id = $dbh->nextId($this->conf['table']['guestbook']);
        $newEntry->date_created = SGL_Date::getTime(true);
        $success = $newEntry->insert();
        if ($success) {
            SGL::raiseMsg('new guestbook entry saved successfully');
        } else {
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
                
        $output->pageTitle = 'Welcome to our Guestbook';
        $query = "  SELECT
                        guestbook_id, date_created, name, email, message
                    FROM {$this->conf['table']['guestbook']}
                    ORDER BY guestbook_id DESC";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
    }
}
?>