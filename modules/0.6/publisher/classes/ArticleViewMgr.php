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
// | ArticleViewMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: ArticleViewMgr.php,v 1.31 2005/06/13 21:34:17 demian Exp $

require_once SGL_MOD_DIR . '/publisher/classes/PublisherBase.php';
require_once SGL_CORE_DIR . '/Item.php';
require_once SGL_CORE_DIR . '/Category.php';

/**
 * Class for browsing articles.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.31 $
 * @since   PHP 4.1
 */
class ArticleViewMgr extends SGL_Manager
{
    var $mostRecentArticleID = 0;

    function ArticleViewMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Article Browser';
        $this->template     = 'articleBrowser.html';
        $this->_aActionsMapping =  array(
            'view'   => array('view'),
            'summary'   => array('summary'),
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
        $input->error           = array();
        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = $this->masterTemplate;
        $input->template        = $this->template;

        //  form vars
        $input->action          = ($req->get('action')) ? $req->get('action') : 'view';
        $input->articleID       = ($req->get('frmArticleID'))
                                    ? (int)$req->get('frmArticleID')
                                    : (int)SGL_Session::get('articleID');
        $input->catID           = (int)$req->get('frmCatID');
        $input->from            = ($req->get('frmFrom')) ? (int)$req->get('frmFrom'):0;
        $input->dataTypeID      = ($req->get('frmDataTypeID'))
                                      ? $req->get('frmDataTypeID')
                                      : $this->conf['site']['defaultArticleViewType'];
        $input->articleLang     = str_replace('-', '_', $req->get('frmArticleLang'));

        //  if article id passed from 'Articles in this Category' list
        //  make it available for lead story
        if ($input->articleID) {
            $this->mostRecentArticleID = $input->articleID;
        }
        PublisherBase::maintainState($input);
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  get category info
        $cat = & new SGL_Category();
        $output->path = $cat->getBreadCrumbs($output->catID, true, 'linkCrumbsAlt1');
        $output->currentCat = $cat->getLabel($output->catID);
    }

    /**
     * The view 'action' method returns details for requested article ID.
     *
     * @return void
     */
    function _cmd_view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'articleView.html';
        $ret = SGL_Item::getItemDetail($input->articleID, null, $input->articleLang);
        //  Encode current url for redirecting purposes
        $url = $input->getCurrentUrl();
        $output->redir = urlencode(urlencode($url->toString()));

        if (PEAR::isError($ret) || !$ret) {
            return false;
        }

        // Set current category id to the category id for this article
        $input->catID = $ret['category_id'];
        $output->leadArticle = $ret;

        if ($output->leadArticle['type'] != 'Static Html Article') {

            // Retrieving a list of related articles for $input->catID
            $output->articleList = SGL_Item::getItemListByCatID(
                $input->catID, $input->dataTypeID, $this->mostRecentArticleID);

            // and related documents
            $output->documentList = PublisherBase::getDocumentListByCatID($input->catID);
        } else {
            $output->staticArticle = true;
        }
        //  display comments?
        if (SGL::moduleIsEnabled('comment') && !empty($this->conf['ArticleViewMgr']['commentsEnabled'])) {
            $aComments = (SGL_Session::getUid() == 1) ?
                $this->da->getCommentsByEntityId('articleView', $input->articleID, -1) :
                $this->da->getCommentsByEntityId('articleView', $input->articleID);
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
            $output->frmRefererUrl = isset($_SERVER['HTTP_REFERER'])
                ? $_SERVER['HTTP_REFERER']
                : null;

            //  with akismet
            if ($this->conf['ArticleViewMgr']['useAkismet']) {
                $output->useAkismet = true;
            }

            //  with captcha?
            if ($this->conf['ArticleViewMgr']['useCaptcha']) {
                //  check days before using Captcha
                $daysBeforeUsingCaptcha = (int) $this->conf['ArticleViewMgr']['daysBeforeUsingCaptcha'];
                if ((strtotime($output->leadArticle['startDate']) + ($daysBeforeUsingCaptcha * 86400)) <= strtotime(SGL_Date::getTime())) {
                    require_once SGL_CORE_DIR . '/Captcha.php';
                    $captcha = new SGL_Captcha();
                    $output->captcha = $captcha->generateCaptcha();
                    $output->useCaptcha = true;
                }
            }

            //  comments require approval?
            if ($this->conf['ArticleViewMgr']['moderationEnabled']) {
                $output->moderationEnabled = true;
            }
        }
    }

    function _cmd_summary(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aResult = SGL_Item::retrievePaginated(
            $input->catID,
            $bPublish = true,
            $input->dataTypeID,
            '',
            $input->from,
            'start_date');

        if (is_array($aResult['data']) && count($aResult['data'])) {
            $limit = $_SESSION['aPrefs']['resPerPage'];
            $output->pager = ($aResult['totalItems'] <= $limit) ? false : true;
        }
        $output->aPagedData = $aResult;

        foreach ($aResult['data'] as $key => $aValues) {
            $output->articleList[$key] = array_merge(
                SGL_Item::getItemDetail($aValues['item_id'], null, $input->articleLang),
                    $aResult['data'][$key]);

            // summarises article content
            foreach ($output->articleList[$key] as $k => $cValues) {
                switch ($k) {

                case 'bodyHtml':
                    $content = $output->articleList[$key]['bodyHtml'];
                    $output->articleList[$key]['bodyHtml'] =
                        SGL_String::summariseHtml($content);
                    break;

                case 'newsHtml':
                    $content = $output->articleList[$key]['newsHtml'];
                    $output->articleList[$key]['newsHtml'] =
                        SGL_String::summariseHtml($content);
                    break;
                }
            }
        }
    }
}
?>
