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
// | AdminCommentMgr.php                                                       |
// +---------------------------------------------------------------------------+
// | Author: Steven Stremciuc  <steve@freeslacker.net>                         |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';
require_once SGL_MOD_DIR . '/comment/classes/CommentDAO.php';

class AdminCommentMgr extends SGL_Manager
{
    function AdminCommentMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->da           = & CommentDAO::singleton();
        $this->pageTitle    = 'Comment Manager';

        $this->_aActionsMapping =  array(
            'edit'          => array('edit'),
            'update'        => array('update', 'redirectToCaller'),
            'changeStatus'  => array('changeStatus', 'redirectToCaller'),
            'delete'        => array('delete', 'redirectToCaller'),
        );
    }

    function validate($req, &$input)
    {
        $this->validated    = true;
        $input->commentId   = $req->get('commentId');
        $input->statusId    = $req->get('statusId');
        $input->comment     = (object) $req->get('comment');

        $input->action = ($req->get('action')) ? $req->get('action') : 'edit';
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->aStatus = array(
                            SGL_COMMENT_FOR_APPROVAL    => 'Awaiting Approval',
                            SGL_COMMENT_APPROVED        => 'Approved',
                            SGL_COMMENT_AKISMET_FAILED  => 'Spam',
                            );
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->comment = $this->da->getCommentById($input->commentId);
        $output->pageTitle = 'Edit Comment';
        $output->template = 'commentEdit.html';
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($_SERVER['HTTP_REFERER'])) {
            SGL_Session::set('caller', $_SERVER['HTTP_REFERER']);
        }
        $comment = DB_DataObject::factory($this->conf['table']['comment']);
        $comment->get($input->commentId);
        $comment->setFrom($input->comment);
        $comment->update();

        SGL::raiseMsg('The comment has successfully been updated', true, SGL_MESSAGE_INFO);
    }

    function _cmd_changeStatus(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($_SERVER['HTTP_REFERER'])) {
            SGL_Session::set('caller', $_SERVER['HTTP_REFERER']);
        }
        $oComment = DB_Dataobject::factory($this->conf['table']['comment']);
        $oComment->get($input->commentId);
        $original = clone($oComment);
        $oComment->status_id = $input->statusId;
        $result = $oComment->update($original);

        if ($result === false) {
            SGL::raiseError('problem changing the comment status');
        } else {
            SGL::raiseMsg('comment status changed successfully', false, SGL_MESSAGE_INFO);
        }
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($_SERVER['HTTP_REFERER'])) {
            SGL_Session::set('caller', $_SERVER['HTTP_REFERER']);
        }
        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        $oComment->get($input->commentId);
        $result = $oComment->delete();

        if ($result === false) {
            SGL::raiseError('There was a problem deleting the record');
        } else {
            SGL::raiseMsg('comment deleted successfully', false, SGL_MESSAGE_INFO);
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
