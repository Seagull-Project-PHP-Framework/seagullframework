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
// | CommentMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: ManagerTemplate.html,v 1.2 2005/04/17 02:15:02 demian Exp $

require_once 'Validate.php';

/**
 * Associate comments with any content type.
 *
 * @package comment
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class CommentMgr extends SGL_Manager
{
    function CommentMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Comment Manager';
        $this->_aActionsMapping =  array(
            'insert'      => array('insert', 'redirectToCaller'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submitted   = $req->get('submitted');
        $input->comment     = (object)$req->get('comment');
        $input->callerMod   = $req->get('frmCallerMod');
        $input->callerMgr   = $req->get('frmCallerMgr');
        $input->callerId    = $req->get('frmCallerId');
        $input->callerTmpl  = $req->get('frmCallerTmpl');

        // if receiving post
        if ($input->submitted) {
            $v = & new Validate();
            if (empty($input->comment->full_name)) {
                $input->comment->full_name = 'anonymous';
            }
            if (empty($input->comment->email)) {
                $aErrors['email'] = 'You must enter a valid email address';
            } elseif (!$v->email($input->comment->email)) {
                $aErrors['email'] = 'Your email is not correctly formatted';
            }
            if (empty($input->comment->body)) {
                $aErrors['body'] = 'You must fill in your comment';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = $input->callerTmpl;
            $input->moduleName = $input->callerMod;
            $mgrName = SGL_Inflector::getManagerNameFromSimplifiedName($input->callerMgr);
            $c = &SGL_Config::singleton();
            $c->set($mgrName, array('commentsEnabled' => true));
            $this->validated = false;
        }
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  insert record
        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        $oComment->setFrom($input->comment);
        $oComment->comment_id = $this->dbh->nextId('comment');
        $oComment->date_created = SGL_Date::getTime(true);
        if (!empty($input->callerId)) {
            $oComment->entity_id = $input->callerId;
        }
        $oComment->type = 'comment';
        $oComment->ip = $_SERVER['REMOTE_ADDR'];;
        $success = $oComment->insert();

        if ($success) {
            SGL::raiseMsg('comment saved successfully', false, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_redirectToCaller(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aRedirect = array(
            'moduleName'  => $input->callerMod,
            'managerName' => $input->callerMgr);
        if (!empty($input->callerId)) {
            $aRedirect['frmArticleID'] = $input->callerId;
        }

        SGL_HTTP::redirect($aRedirect);
    }
}
?>