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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | FaqMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: FaqMgr.php,v 1.26 2005/06/12 17:57:57 demian Exp $

require_once SGL_ENT_DIR . '/Faq.php';

/**
 * To allow users to contact site admins.
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
        $this->module       = 'faq';
        $this->pageTitle    = 'FAQ Manager';
        $this->template     = 'faqList.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'reorder'   => array('reorder'),
            'reorderUpdate' => array('reorderUpdate', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
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
        $input->faqId       = $req->get('frmFaqId');
        $input->items       = $req->get('_items');
        $input->submit      = $req->get('submitted');
        $input->faq         = (object)$req->get('faq');

        if ($input->submit) {
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
            $input->error = $aErrors;
            $input->template = 'faqEdit.html';
            $this->validated = false;
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'faqEdit.html';
        $output->action   = 'insert';
        $output->pageTitle = $this->pageTitle . ' :: Add';
        //  build ordering select object
        $output->faq = & new DataObjects_Faq();
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get new order number
        $faq = & new DataObjects_Faq();
        $faq->selectAdd();
        $faq->selectAdd('MAX(item_order) AS new_order');
        $faq->groupBy('item_order');
        $maxItemOrder = $faq->find(true);
        unset($faq);

        //  insert record
        $faq = & new DataObjects_Faq();
        $faq->setFrom($input->faq);
        $dbh = $faq->getDatabaseConnection();
        $faq->faq_id = $dbh->nextId('faq');
        $faq->last_updated = $faq->date_created = SGL::getTime(true);
        $faq->item_order = $maxItemOrder + 1;
        $success = $faq->insert();
        if ($success) {
            SGL::raiseMsg('faq saved successfully');
        } else {
           SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'faqEdit.html';
        $output->action   = 'update';
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $faq = & new DataObjects_Faq();

        //  get faq data
        $faq->get($input->faqId);
        $output->faq = $faq;
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $faq = & new DataObjects_Faq();
        $faq->get($input->faq->faq_id);
        $faq->setFrom($input->faq);
        $faq->last_updated = SGL::getTime(true);
        $success = $faq->update();
        if ($success) {
            SGL::raiseMsg('faq updated successfully');
        } else {
            SGL::raiseError('There was a problem updating the record', SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $faqId) {
                $faq = & new DataObjects_Faq();
                $faq->get($faqId);
                $faq->delete();
                unset($faq);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        SGL::raiseMsg('faq deleted successfully');
    }

    function _reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = $this->pageTitle . ' :: Reorder';
        $output->template = 'faqReorder.html';
        $faqList = & new DataObjects_Faq();
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

    function _reorderUpdate(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aNewOrder = explode(',', $input->items);

        //  reorder elements
        $pos = 1;
        foreach ($aNewOrder as $faqId) {
            $faq = & new DataObjects_Faq();
            $faq->get($faqId);
            $faq->item_order = $pos;
            $success = $faq->update();
            unset($faq);
            $pos++;
        }
        SGL::raiseMsg('faqs reordered successfully');
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (SGL_HTTP_Session::getUserType() == SGL_ADMIN) {
            $output->template = 'faqListAdmin.html';
            $output->pageTitle = $this->pageTitle . ' :: Browse';
        } else {
            $output->pageTitle = 'FAQs';
        }
        $faqList = & new DataObjects_Faq();
        $faqList->orderBy('item_order');
        $result = $faqList->find();
        $aFaqs = array();
        if ($result > 0) {
            while ($faqList->fetch()) {
                $faqList->question = $faqList->question;
                $faqList->answer = nl2br($faqList->answer);
                $aFaqs[] = clone($faqList);
            }
        }
        $output->results = $aFaqs;
    }
}
?>