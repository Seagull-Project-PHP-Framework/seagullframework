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
// | CommentSearchMgr.php                                                      |
// +---------------------------------------------------------------------------+
// | Author: Steven Stremciuc  <steve@freeslacker.net>                         |
// +---------------------------------------------------------------------------+

require_once 'DB/DataObject.php';
require_once SGL_MOD_DIR . '/comment/classes/CommentDAO.php';

class CommentSearchMgr extends SGL_Manager
{
    function CommentSearchMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->da           = & CommentDAO::singleton();
        $this->pageTitle    = 'Comment Search Manager';

        $this->_aActionsMapping =  array(
            'delete'        => array('delete', 'redirectToDefault'),
            'search'        => array('search'),
        );
    }

    function validate($req, &$input)
    {
        $this->validated    = true;
        $input->pageTitle   = $this->pageTitle;
        $input->search      = (object) $req->get('search');
        $input->aDelete     = $req->get('frmDelete');
        $input->aEntityNames = $this->da->getEntityNames();

        //  Pager's total items value (maintaining it saves a count(*) on each request)
        $input->totalItems = $req->get('totalItems');

        $input->action = ($req->get('action')) ? $req->get('action') : 'search';

        //  determine if we need to run the delete action
        if ($req->get('delete')) { $input->action = 'delete';}
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

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $results = array();
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $commentId) {
                $comment = DB_DataObject::factory($this->conf['table']['comment']);
                $comment->get($commentId);
                $results[$commentId] = ($comment->delete()) ? 1 : 0;
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' .
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  could eventually display a list of failed/succeeded download ids --
        //  just summarize for now
        $results = array_count_values($results);
        $succeeded = array_key_exists(1, $results) ? $results[1] : 0;
        $failed = array_key_exists(0, $results) ? $results[0] : 0;
        if ($succeeded && !$failed) {
            $errorType = SGL_MESSAGE_INFO;
        } elseif (!$succeeded && $failed) {
            $errorType = SGL_MESSAGE_ERROR;
        } else {
            $errorType = SGL_MESSAGE_WARNING;
        }
        //  redirect on success
        SGL::raiseMsg("$succeeded comment(s) successfully deleted. $failed comment(s) failed.",
            false, $errorType);
    }

    function _cmd_search(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'commentSearchManager.html';
        $criteria = '';

        //  if search form data present, built search criteria SQL
        if (!empty($input->search)) {
            $whereAnd = "WHERE";
            foreach ($input->search as $k => $v) {
                if (isset($v) && $v != '') {
                    $v = addslashes($v);
                    switch ($k) {

                    case 'email':
                        $criteria .= " $whereAnd email LIKE '%{$v}%' ";
                        break;
                    case 'author':
                        $criteria .= " $whereAnd full_name LIKE '%{$v}%' ";
                        break;
                    case 'ip':
                        $criteria .= " $whereAnd ip LIKE '%{$v}%' ";
                        break;
                    case 'entity_name':
                        $entity = $input->aEntityNames[$v];
                        $criteria .= " $whereAnd entity_name = '$entity' ";
                        break;
                    default:
                        $criteria .= " $whereAnd $k = '$v' ";
                    }
                    $whereAnd = "AND";
                }
            }
        }

        $allowedSortFields = array();
        if (  !empty($input->sortBy)
                && !empty($input->sortOrder)
                && in_array($input->sortBy, $allowedSortFields)) {
            $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY c.comment_id DESC ';
        }

        $query = "
            SELECT  c.*
            FROM    {$this->conf['table']['comment']} c
            $criteria " . $orderBy_query;
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
            'path'      => SGL_Output::makeUrl('search'),
            'append'    => true,
        );

        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);
        if (PEAR::isError($aPagedData)) {
            SGL::raiseMsg('There was a database problem');
            $aPagedData = array();
        }

        //  set flag for whether comment is approved or not
        foreach ($aPagedData['data'] as $k => $aComment) {
            $aComment['isApproved'] = ($aComment['status_id'] == SGL_COMMENT_APPROVED)
                ? true
                : false;
            $aPagedData['data'][$k] = $aComment;
        }

        $output->aPagedData = $aPagedData;
        if (isset($aPagedData['data']) && is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->totalItems = @$aPagedData['totalItems'];
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }
}
?>
