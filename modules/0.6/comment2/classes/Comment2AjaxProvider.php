<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Comment2AjaxProvider.php                                                  |
// +---------------------------------------------------------------------------+
// | Authors:   Thomas Goetz  <thomas@getconfuse.net>                          |
// | Authors:   Demian Turner  <demian@phpkitchen.com>                         |
// +---------------------------------------------------------------------------+


/**
 * Wrapper for various ajax methods.
 *
 * @package seagull
 */

require_once SGL_CORE_DIR . '/Delegator.php';
require_once SGL_CORE_DIR . '/AjaxProvider2.php';
require_once SGL_MOD_DIR . '/comment2/classes/Comment2DAO.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';


class Comment2AjaxProvider extends SGL_AjaxProvider2
{

    public $req;
    public $da;
    public $responseFormat;
    public $aMsg;

    /**
     *
     */
    function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        parent::__construct();
        $daComment = Comment2DAO::singleton();
        $daUser = UserDAO::singleton();
        $this->da = new SGL_Delegator();
        $this->da->add($daComment);
        $this->da->add($daUser);
        $this->req = SGL_Registry::singleton()->getRequest();

        $this->responseFormat = SGL_RESPONSEFORMAT_JSON;
    }

    function _isOwner($requestedUsrId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return true;
    }

    function addComment($input, $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aComment = $this->req->get('comment');

        //  derive name
        $uid = SGL_Session::getUid();
        if ($uid) {
            $oUser = $this->da->getUserById($uid);
            $fullName = $oUser->first_name . ' ' . $oUser->last_name;
        } else {
            $fullName = 'anonymous';
        }
        $aComment['full_name'] = $fullName;

        $ok = $this->da->addComment($aComment);
        $aMsg = array(
            'message' => 'comment added successfully',
            'type'    => SGL_MESSAGE_INFO,
        );
        $output->lastCommentInsertId = $ok;
        $this->_raiseMsg($aMsg);
    }

    function getComments($input, $output)
    {
        $fk = $this->req->get('fk');
        $output->comments = $this->da->getCommentsByFk($fk);
        $output->html = $this->_renderTemplate($output, '_comment_list.html');
    }

    function getCommentById($input, $output)
    {
        $id = $this->req->get('commentId');
        $output->comments = array($this->da->getCommentById($id));
        $output->html = $this->_renderTemplate($output, '_comment_list.html');
    }

    public function deleteComment($input, $output)
    {
        $commentId = $this->req->get('commentId');
        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        $oComment->get($commentId);
        $ok = $oComment->delete();
    }
}

?>