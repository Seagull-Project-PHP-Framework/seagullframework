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
// | GuestbookAdminMgr.php                                                     |
// +---------------------------------------------------------------------------+
// | Author:   Rares Benea <rbenea@bluestardesign.ro>                          |
// +---------------------------------------------------------------------------+
// $Id: GuestbookAdminMgr.php,v 1.0 2006/07/01 00:26:16 demian Exp $

require_once 'GuestbookMgr.php';
require_once 'DB/DataObject.php';

/**
 * Allows users to leave guestbook entries.
 *
 * @package guestbook
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.0 $
 */
class AdminGuestbookMgr extends GuestbookMgr
{
    function AdminGuestbookMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Guestbook Manager';
        $this->template     = 'guestbookList.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'list'      => array('list'),
            'delete'    => array('delete','list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->aDelete     = $req->get('frmDelete');
        // Not elegant but works
        $this->conf['GuestbookMgr']['useCaptcha'] = false;
        parent::validate($req, $input);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = 'Welcome to our Guestbook';
        $output->mode = 'List';
        $output->template = 'guestbookList.html';
        $query = "
            SELECT
                guestbook_id, date_created, name, email, message
            FROM {$this->conf['table']['guestbook']}
            ORDER BY guestbook_id DESC";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
            foreach ($aPagedData['data'] as $key => $value) {
                $stripMessage = substr($aPagedData['data'][$key]['message'], 0, 20);
                $aPagedData['data'][$key]['message'] = $stripMessage.'...';
            }
        }
        $output->aPagedData = $aPagedData;
    }

    /**
    * Delete/unsubscribe a subscriber
    *
    * @access public
    *
    */
    function _cmd_delete (& $input, & $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $guestbook_id) {
                $oEntry = DB_DataObject::factory($this->conf['table']['guestbook']);
                $oEntry->get($guestbook_id);
                $oEntry->delete();
                unset ($oEntry);
            }
        } else {
            SGL :: raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__,
                SGL_ERROR_INVALIDARGS);
        }
        SGL :: raiseMsg('Entry deleted successfully', true, SGL_MESSAGE_INFO);
    }
}
?>
