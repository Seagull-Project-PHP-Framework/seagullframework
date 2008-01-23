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
require_once SGL_MOD_DIR . '/faq/classes/FaqMgr.php';

/**
 * Allow admins to manage FAQs.
 *
 * @package faq
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.26 $
 * @since   PHP 4.1
 */
class AdminFaqMgr extends FaqMgr
{
    function AdminFaqMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::FaqMgr();

        $this->pageTitle = 'FAQ Manager';
        $this->template  = 'faqList.html';

        $this->_aActionsMapping =  array(
            'add'           => array('add'),
            'insert'        => array('insert', 'redirectToDefault'),
            'edit'          => array('edit'),
            'update'        => array('update', 'redirectToDefault'),
            'reorder'       => array('reorder'),
            'reorderUpdate' => array('reorderUpdate', 'redirectToDefault'),
            'delete'        => array('delete', 'redirectToDefault'),
            'list'          => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        parent::validate($req, $input);
        $input->faqId     = $req->get('frmFaqId');
        $input->items     = $req->get('_items');
        $input->faq       = (object)$req->get('faq');

        // Misc.
        $input->submitted   = $req->get('submitted');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->isAdd       = $req->get('isadd');

        if ($input->submitted && in_array($input->action, array('insert', 'update'))) {
            if (empty($input->faq->question)) {
                $aErrors['question'] = 'Please fill in a question';
            }
            if (empty($input->faq->answer)) {
                $aErrors['answer'] = 'Please fill in an answer';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error    = $aErrors;
            $input->template = 'faqEdit.html';
            $this->validated = false;

            // save currect title
            if ($input->action == 'insert') {
                $input->pageTitle .= ' :: Add';
            } elseif ($input->action == 'update') {
                $input->pageTitle .= ' :: Edit';
            }
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template  = 'faqEdit.html';
        $output->action    = 'insert';
        $output->pageTitle = $this->pageTitle . ' :: Add';
        $output->isAdd     = true;
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_DB::setConnection();
        //  get new order number
        $faq = DB_DataObject::factory($this->conf['table']['faq']);
        $faq->selectAdd();
        $faq->selectAdd('MAX(item_order) AS new_order');
        $faq->groupBy('item_order');
        $maxItemOrder = $faq->find(true);
        unset($faq);

        //  insert record
        $faq = DB_DataObject::factory($this->conf['table']['faq']);
        $faq->setFrom($input->faq);
        $faq->faq_id = $this->dbh->nextId('faq');
        $faq->last_updated = $faq->date_created = SGL_Date::getTime(true);
        $faq->item_order = $maxItemOrder + 1;
        $success = $faq->insert();
        if ($success) {
            SGL::raiseMsg('faq saved successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template  = 'faqEdit.html';
        $output->action    = 'update';
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $faq = DB_DataObject::factory($this->conf['table']['faq']);

        //  get faq data
        $faq->get($input->faqId);
        $output->faq = $faq;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $faq = DB_DataObject::factory($this->conf['table']['faq']);
        $faq->get($input->faq->faq_id);
        $faq->setFrom($input->faq);
        $faq->last_updated = SGL_Date::getTime(true);
        $success = $faq->update();
        if ($success) {
            SGL::raiseMsg('faq updated successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $faqId) {
                $faq = DB_DataObject::factory($this->conf['table']['faq']);
                $faq->get($faqId);
                $faq->delete();
                unset($faq);
            }
            SGL::raiseMsg('faq deleted successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _cmd_reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Reorder';
        $output->template  = 'faqReorder.html';
        $output->action    = 'reorderUpdate';
        $faqList = DB_DataObject::factory($this->conf['table']['faq']);
        $faqList->orderBy('item_order');
        $result = $faqList->find();
        if ($result > 0) {
            $aFaqs = array();
            while ($faqList->fetch()) {
                $aFaqs[$faqList->faq_id] = SGL_String::summarise($faqList->question, 40);
            }
            $output->aFaqs = $aFaqs;
        }
    }

    function _cmd_reorderUpdate(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!empty($input->items)) {
            $aNewOrder = explode(',', $input->items);
            //  reorder elements
            $pos = 1;
            foreach ($aNewOrder as $faqId) {
                $faq = DB_DataObject::factory($this->conf['table']['faq']);
                $faq->get($faqId);
                $faq->item_order = $pos;
                $success = $faq->update();
                unset($faq);
                $pos++;
            }
            SGL::raiseMsg('faqs reordered successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'faqList.html';
        $query = "SELECT * FROM {$this->conf['table']['faq']} ORDER BY item_order";

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
            'spacesBeforeSeparator' => 0,
            'spacesAfterSeparator'  => 0,
            'curPageSpanPre'        => '<span class="currentPage">',
            'curPageSpanPost'       => '</span>',
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        //  determine if pagination is required
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }

        $output->addOnLoadEvent("switchRowColorOnHover()");
    }
}

?>
