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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | ArticleViewMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: ArticleViewMgr.php,v 1.31 2005/06/13 21:34:17 demian Exp $

require_once SGL_MOD_DIR . '/publisher/classes/PublisherBase.php';
require_once SGL_CORE_DIR . '/Item.php';
require_once SGL_MOD_DIR . '/navigation/classes/CategoryMgr.php';

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

        $this->module       = 'publisher';
        $this->pageTitle    = 'Article Browser';
        $this->template     = 'articleBrowser.html';

        $this->_aActionsMapping =  array(
            'view'   => array('view'),
            'summary'   => array('summary'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $input->error           = array();
        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = $this->masterTemplate;
        $input->template        = $this->template;
        $input->javascriptSrc   = array('TreeMenu.js');

        //  form vars
        $input->action          = ($req->get('action')) ? $req->get('action') : 'summary';
        $input->articleID       = ($req->get('frmArticleID'))
                                    ? (int)$req->get('frmArticleID')
                                    : (int)SGL_HTTP_Session::get('articleID');
        $input->catID           = (int)$req->get('frmCatID');
        $input->staticArticle   = ($req->get('staticId')) ? (int)$req->get('staticId') : 0;
        $input->from            = ($req->get('frmFrom')) ? (int)$req->get('frmFrom'):0;
        $input->dataTypeID      = ($req->get('frmDataTypeID'))
                                      ? $req->get('frmDataTypeID')
                                      : $GLOBALS['_SGL']['CONF']['site']['defaultArticleViewType'];
        //  catch static article flag, route to view
        if ($input->staticArticle) {
            $input->action = 'view';
        }
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
        $catMgr = & new CategoryMgr();
	// If $output->articleID is set (as on the article detail page, but not not the article summary page),
	// set $output->catID from the relevant Item object
	// This ensures breadcrumbs and current category are displayed correctly
	if (! empty($output->articleID)) {
    	$ret = SGL_Item::getItemDetail($output->articleID);
    	$output->catID = $ret['categoryID'];
	}
	$output->path = $catMgr->getBreadCrumbs($output->catID, true, 'linkCrumbsAlt1');
        $output->currentCat = $catMgr->getLabel($output->catID);
    }

    function _view(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'articleView.html';
        $ret = SGL_Item::getItemDetail($input->articleID);

        if (PEAR::isError($ret)) {
            return false;
        }
        $output->leadArticle = $ret;

	//Ensure $input->catID corressponds to this article's category
	$input->catID = $ret['categoryID'];
        if ($output->leadArticle['type'] != 'Static Html Article') {
            $output->articleList = SGL_Item::getItemListByCatID(
                $input->catID, $input->dataTypeID, $this->mostRecentArticleID);
            $output->documentList = PublisherBase::getDocumentListByCatID($input->catID);
        } else {
            $output->staticArticle = true;

            //show inline edit link
            $output->showInlineEdit = (SGL_HTTP_Session::getUserType() == SGL_ADMIN) ? true : false;
        }
    }

    function _summary(&$input, &$output)
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
            $output->articleList[$key] = array_merge(SGL_Item::getItemDetail($aValues['item_id']),
                                            $aResult['data'][$key]);

            // summarises article content
            foreach ($output->articleList[$key] as $cKey => $cValues) {
                switch ($cKey) {

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
