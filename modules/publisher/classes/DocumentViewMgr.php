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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | DocumwntViewMgr.php                                                       |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: DocumentViewMgr.php,v 1.31 2005/06/13 21:34:17 demian Exp $

require_once SGL_CORE_DIR . '/Category.php';

/**
 * Class for browsing documents.
 *
 * @package publisher
 * @author  Werner Krauss <werner@seagullproject.org>
 */
class DocumentViewMgr extends SGL_Manager
{
    var $mostRecentArticleID = 0;

    function DocumentViewMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Document Browser';
        $this->template     = 'documentBrowser.html';
        $this->_aActionsMapping =  array(
            'summary'   => array('summary'),
        );
        $this->category     = new SGL_Category();
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
        $input->action          = ($req->get('action')) ? $req->get('action') : 'summary';
        $input->catId           = (int)$req->get('frmCatID');
        $input->totalItems      = $req->get('totalItems');

    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  get category info
        $output->path = $this->category->getBreadCrumbs($output->catId, true, 'linkCrumbsAlt1');
        $output->currentCat = $this->category->getLabel($output->catId);
    }

    function _cmd_summary(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  check if user has perms to view this category
        $this->category->load($input->catId);
        if (! $this->category->hasPerms()) {
            SGL::raiseError('You do not have read permissions for this category',
                SGL_ERROR_INVALIDAUTH); ;
            return false;
        }
        $limit = $_SESSION['aPrefs']['resPerPage'];

        $output->aPagedData = $aPagedData = $this->getAllDocuments($input);
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function _cmd_redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        SGL_HTTP::redirect(array(
            'frmCatID' => $this->conf['DocumentViewMgr']['defaultCategory']));
    }

    function getAllDocuments($input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $rangeWhereClause = ($input->catId) ? "WHERE c.category_id = $input->catId" : '';
        $query = "
            SELECT document_id, c.category_id, d.document_type_id,
                d.name, file_size, mime_type,
                d.date_created, description,
                dt.name AS document_type_name,
                u.username AS document_added_by
            FROM
                {$this->conf['table']['document']} d
            LEFT JOIN {$this->conf['table']['category']} c ON d.category_id = c.category_id
            LEFT JOIN {$this->conf['table']['document_type']} dt ON d.document_type_id = dt.document_type_id
            LEFT JOIN {$this->conf['table']['user']} u ON d.added_by = u.usr_id
            $rangeWhereClause
            ORDER BY d.date_created DESC";
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
            'totalItems'=> $input->totalItems,
            'spacesBeforeSeparator' => 0,
            'spacesAfterSeparator'  => 0,
            'curPageSpanPre'        => '<span class="currentPage">',
            'curPageSpanPost'       => '</span>',
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        return ($aPagedData['totalItems'] > 0) ? $aPagedData : false;
    }
}
?>
