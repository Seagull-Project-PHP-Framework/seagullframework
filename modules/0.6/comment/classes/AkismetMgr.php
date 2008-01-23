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
// | AkismetAPIKeyMgr.php                                                      |
// +---------------------------------------------------------------------------+
// | Author: Steven Stremciuc  <steve@freeslacker.net>                         |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';
require_once SGL_MOD_DIR . '/comment/classes/Akismet.php';

class AkismetMgr extends SGL_Manager
{
    function AkismetMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Akismet Manager';

        $this->_aActionsMapping =  array(
            'list'                      => array('list'),
            'reportHam'                 => array('reportHam', 'redirectToCaller'),
            'reportSpam'                => array('reportSpam', 'redirectToCaller'),
            'testAkismetAPIKey'         => array('testAkismetAPIKey'),
        );
    }

    function validate($req, &$input)
    {
        $this->validated    = true;
        $input->template    = 'akismetManager.html';
        $input->pageTitle   = $this->pageTitle;
        $input->submitted   = (bool) $req->get('submitted');
        $input->akismetKey  = $req->get('akismetKey');
        $input->commentId   = $req->get('commentId');

        if ($input->submitted) {
            if (empty($input->akismetKey)) {
                $aErrors['akismetKey'] = 'Please fill in a Akismet API Key';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error    = $aErrors;
            $this->validated = false;
        }

        $input->action = ($req->get('action')) ? $req->get('action') : 'list';
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_reportHam(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($_SERVER['HTTP_REFERER'])) {
            SGL_Session::set('caller', $_SERVER['HTTP_REFERER']);
        }

        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        $oComment->get($input->commentId);
        if ($oComment) {
            //  get API key
            $key = $this->conf['AkismetMgr']['akismetAPIKey'];
            $akismet = new Akismet();
            $akismet->reportHam($oComment, $key);
        }

        //  change comment status to approved
        $original = clone($oComment);
        $oComment->status_id = SGL_COMMENT_APPROVED;
        $result = $oComment->update($original);

        if ($result) {
            SGL::raiseMsg('The comment has successfully been updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was an error updating the comment', true, SGL_MESSAGE_WARNING);
        }
    }

    function _cmd_reportSpam(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($_SERVER['HTTP_REFERER'])) {
            SGL_Session::set('caller', $_SERVER['HTTP_REFERER']);
        }

        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        $oComment->get($input->commentId);
        if ($oComment) {
            //  get API key
            $key = $this->conf['AkismetMgr']['akismetAPIKey'];
            $akismet = new Akismet();
            $akismet->reportSpam($oComment, $key);
        }

        //  change comment status to spam
        $original = clone($oComment);
        $oComment->status_id = SGL_COMMENT_AKISMET_FAILED;
        $result = $oComment->update($original);

        if ($result) {
            SGL::raiseMsg('The comment has successfully been updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('There was an error updating the comment', true, SGL_MESSAGE_WARNING);
        }
    }

    function _cmd_testAkismetAPIKey(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $akismet = new Akismet();
        $result = $akismet->verifyKey($input->akismetKey);
        if ($result) {
            SGL::raiseMsg('Valid Akismet API Key', false, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('Invalid Akismet API key entered', false);
        }
    }

    function _cmd_redirectToCaller()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $caller = SGL_Session::get('caller');
        if (!empty($caller)) {
            SGL_HTTP::redirect($caller);
        } elseif (isset($_SERVER['HTTP_REFERER'])) {
            SGL_Session::set('caller','');
            SGL_HTTP::redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->_cmd_redirectToDefault;
        }
    }
}
?>
