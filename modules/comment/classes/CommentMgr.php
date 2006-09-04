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
// | CommentMgr.php                                                    |
// +---------------------------------------------------------------------------+
// | Author: Alouicious Bird  <matti@tahvonen.com>                                  |
// +---------------------------------------------------------------------------+
// $Id: ManagerTemplate.html,v 1.2 2005/04/17 02:15:02 demian Exp $

require_once SGL_MOD_DIR . '/comment/classes/CommentContainer.php';


/**
 * Type your class description here ...
 *
 * @package comment
 * @author  Alouicious Bird  <matti@tahvonen.com>
 */
class CommentMgr extends SGL_Manager
{
    function CommentMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Comment Manager';
        $this->template     = 'commentMgrList.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'list'      => array('list'),
            'delete'    => array('delete', 'redirectToDefault'),
            'view'      => array('view'),
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
        $input->aDelete     = $req->get('frmDelete');
        $input->submitted   = $req->get('submitted');
        $input->seagull_uri = $req->get('seagull_uri');
        $input->parent_id   = $req->get('parent_id');
        $input->comment_id  = $req->get('comment_id');
        $input->comment     = (object)$req->get('comment');

        // if receiving post
        if ($input->submitted) {
            if (empty($input->comment->name)) {
                $input->comment->name = SGL_String::translate('Guest');
            }
            if (empty($input->comment->subject)) {
                $aErrors['subject'] = 'Please, specify subject for your post';
            }
            if (empty($input->comment->comment)) {
                $aErrors['comment'] = 'Please, specify body for your post';
            }
            if (empty($input->comment->parent_id)) {
                // this is ok, posting a root message
                $input->comment->parent_id = null;
            }
            if (empty($input->seagull_uri)) {
                // "fatal" error not coming through valid form or page
                $aErrors['seagull_uri'] = 'Fatal error, go to homepage and try again';
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            print_r($aErrors);
            $input->template = 'commentMgrAdd.html';
            $this->validated = false;
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle    = 'Add a comment';
        $output->template = 'commentMgrAdd.html';

        if(isset($input->comment->seagull_uri)) {
            $output->seagull_uri = $input->comment->seagull_uri;
        } elseif (isset($input->parent_id)) {
            $parent = new Comment;
            $parent->get($input->parent_id);
            $output->seagull_uri = $parent->seagull_uri;
            $output->comment = new Comment();
            $output->comment->subject = "Re:" . $parent->subject;
            $output->comment->parent_id = $parent->comment_id;
        } else {
            $output->seagull_uri = $input->seagull_uri;
        }

        // only guest users have name field
        $output->showNameField = (SGL_GUEST == SGL_Session::getUserType()) ? true : false;
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $cc = new CommentContainer($input->seagull_uri);
        $cc->saveComment(
            $input->comment->subject,
            $input->comment->comment,
            $input->comment->parent_id,
            $input->comment->name);
        SGL::raiseMsg('new Comment added succesfully');
        // API currently sucks or I'm stupid :-(
        $redir = $this->conf['site']['baseUrl'];
        if ($this->conf['site']['frontScriptName']) $redir .= '/'.$this->conf['site']['frontScriptName'];
        $redir .= '/' . $input->seagull_uri;
        SGL_HTTP::redirect($redir);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $cc = new CommentContainer($input->seagull_uri);
        $output->aComments = $cc->getAllComments();
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template     = 'commentMgrView.html';
        $cc = new CommentContainer(null, $input->comment_id);
        $cc->buildTree();
        $output->comment = $cc->getComment($input->comment_id);
        $output->comment->comment = nl2br($output->comment->comment);
        $output->children =  $output->comment->getChildTreeAsHtmlSnippet();
        // API currently sucks or I'm stupid :-(
        $redir = $this->conf['site']['baseUrl'];
        if ($this->conf['site']['frontScriptName']) $redir .= '/'.$this->conf['site']['frontScriptName'];
        $redir .= '/' . $input->seagull_uri;
        $output->redir = $redir;
    }

}
?>