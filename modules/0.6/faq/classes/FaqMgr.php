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
// | FaqMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: FaqMgr.php,v 1.26 2005/06/12 17:57:57 demian Exp $

require_once 'DB/DataObject.php';

/**
 * Allow users to see FAQs.
 *
 * @package faq
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.26 $
 * @since   PHP 4.1
 */
class FaqMgr extends SGL_Manager
{
    function FaqMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'FAQs';
        $this->template  = 'faqList.html';

        $this->_aActionsMapping = array(
            'list' => array('list'),
        );
        //  enable comments if configured
        if (SGL::moduleIsEnabled('comment')) {
            require_once SGL_MOD_DIR  . '/comment/classes/CommentDAO.php';
            require_once SGL_CORE_DIR . '/Delegator.php';
            $dao = &CommentDAO::singleton();
            $this->da  = new SGL_Delegator();
            $this->da->add($dao);
        }
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
    }


    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $faqList = DB_DataObject::factory($this->conf['table']['faq']);
        $faqList->orderBy('item_order');
        $result = $faqList->find();
        $aFaqs  = array();
        if ($result > 0) {
            while ($faqList->fetch()) {
                $faqList->question = $faqList->question;
                $faqList->answer   = nl2br($faqList->answer);
                $aFaqs[]           = clone($faqList);
            }
        }
        $output->results = $aFaqs;
        //  display comments?
        if (SGL::moduleIsEnabled('comment') && !empty($this->conf['FaqMgr']['commentsEnabled'])) {
            $aComments = (SGL_Session::getUid() == 1) ?
                $this->da->getCommentsByEntityId('faq', null, -1) :
                $this->da->getCommentsByEntityId('faq');
            foreach ($aComments as $key => $oComment) {
                $oComment->isApproved = ($oComment->status_id == SGL_COMMENT_APPROVED)
                    ? true
                    : false;
                $oComment->isSpam = ($oComment->status_id == SGL_COMMENT_AKISMET_FAILED)
                    ? true
                    : false;
                $aComments[$key] = $oComment;
            }
            $output->aComments = $aComments;
            $output->frmRefererUrl = $_SERVER['HTTP_REFERER'];

            //  with akismet
            if ($this->conf['FaqMgr']['useAkismet']) {
                $output->useAkismet = true;
            }

            //  with captcha?
            if ($this->conf['FaqMgr']['useCaptcha']) {
                require_once SGL_CORE_DIR . '/Captcha.php';
                $captcha = new SGL_Captcha();
                $output->captcha = $captcha->generateCaptcha();
                $output->useCaptcha = true;
            }

            //  comments require approval?
            if ($this->conf['FaqMgr']['moderationEnabled']) {
                $output->moderationEnabled = true;
            }
        }
    }
}

?>
