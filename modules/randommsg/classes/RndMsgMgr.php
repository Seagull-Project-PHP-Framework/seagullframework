<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Michael Willemot                                      |
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
// | RndMsgMgr.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Michael Willemot <michael@sotto.be>                             |
// +---------------------------------------------------------------------------+
// $Id: RndMsgMgr.php,v 1.19 2005/01/23 13:47:24 demian Exp $

require_once 'DB/DataObject.php';

/**
* RndMsgMgr class, for managing random messages
*
* @package randommsg
* @author  Michael Willemot <michael@sotto.be>
* @copyright Michael Willemot 2004
* @version 0.4
*/
class RndMsgMgr extends SGL_Manager
{
    function RndMsgMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle         = 'RndMsg Manager :: Browse';
        $this->masterTemplate    = 'masterLeftCol.html';
        $this->template          = 'rndMsg.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
        );

        $this->_allowedFileTypes = array(
            'txt',
            'csv',
        );
        $this->crlf = SGL_String::getCrlf();
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->error          = array();
        $input->pageTitle      = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template       = $this->template;
        $input->action         = ($req->get('action')) ? $req->get('action') : 'list';
        $input->msgDelete      = $req->get('frmMsgDelete');
        $input->addMsgsText    = $req->get('addMsgsText');
        $input->submitted      = ($req->get('submitted') || $req->get('upload'));

        //  request values for upload
        $input->msgUpload               = $req->get('upload');
        $input->msgFileArray            = $req->get('msgFile');
        if (is_array($input->msgFileArray)) {
            $input->msgFileName         = $input->msgFileArray['name'];
            $input->msgFileType         = $input->msgFileArray['type'];
            $input->msgFileTmpName      = $input->msgFileArray['tmp_name'];
            $input->msgFileSize         = $input->msgFileArray['size'];
        }

        $aErrors = array();
        if ($input->submitted) {
            if ($input->msgUpload) {
                if ($input->msgFileSize < 1) {
                    $aErrors['noUpload'] = 'Please select a file to upload';
                } else {
                    $ext = end(explode('.', $input->msgFileName));

                    //  check uploaded file is of valid type
                    if (!in_array(strtolower($ext), $this->_allowedFileTypes)) {
                        $aErrors['invalidType'] = 'Error: Not a recognised file type';
                    }
                }
            } else {
                if ($input->addMsgsText == '') {
                    $aErrors['noMessage'] = 'Please enter one or more messages';
                }
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error    = $aErrors;
            $input->template = 'rndMsgAdd.html';
            $this->validated = false;

            // fix page title
            if ('insert' == $input->action) {
                $input->pageTitle = 'RndMsg Manager :: Add';
            }
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = 'RndMsg Manager :: Add';
        $output->template  = 'rndMsgAdd.html';
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_DB::setConnection();
        $output->template = 'rndMsg.html';
        if ($input->msgUpload) {
            $aLines = $this->file2($input->msgFileTmpName);
        } else {
            $aLines = split($this->crlf, $input->addMsgsText);
        }
        $success = true;
        foreach ($aLines as $rndmsg) {
            if (trim($rndmsg) != '') {
                $msg = DB_DataObject::factory($this->conf['table']['rndmsg_message']);
                $msg->msg = trim($rndmsg);
                $msg->rndmsg_message_id = $this->dbh->nextId($this->conf['table']['rndmsg_message']);
                $success = ($success && $msg->insert());
            }
        }
        if ($success) {
            SGL::raiseMsg('One or more messages successfully added.');
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->msgDelete)) {
            foreach ($input->msgDelete as $index => $msgId){
                $rm = DB_DataObject::factory($this->conf['table']['rndmsg_message']);
                $rm->get($msgId);
                $rm->delete();
                unset($rm);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        SGL::raiseMsg('Message(s) successfully removed.');
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'rndMsg.html';
        $query = "  SELECT
                         rndmsg_message_id, msg
                    FROM {$this->conf['table']['rndmsg_message']}";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
    }

    /**
     * Splits text files into an array of strings
     *
     * Can handle files whose line endings are whatever
     * <LF> (*nix), <CR><LF> (M$) or <CR> (Mac)
     */
    function file2($filename)
    {
        $fp = fopen($filename, "rb");
        $buffer = fread($fp, filesize($filename));
        fclose($fp);
        $lines = preg_split("/\r?\n|\r/", $buffer);
        return $lines;
    }
}
?>