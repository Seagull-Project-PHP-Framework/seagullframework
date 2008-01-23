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
// | CategoryMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: CategoryMgr.php,v 1.27 2005/05/17 23:54:51 demian Exp $

require_once SGL_CORE_DIR . '/Category.php';
require_once SGL_MOD_DIR . '/navigation/classes/MenuBuilder.php';

/**
 * For performing operations on Category objects.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.27 $
 */
class CategoryMgr extends SGL_Manager
{
    var $_redirectCatId = 1;
    var $_category = null;

    function CategoryMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->aggregateOutput = true;
        $this->pageTitle        = 'Category Manager';
        $this->template         = 'categoryMgr.html';

        $this->_aActionsMapping =  array(
            'insert'    => array('insert', 'redirectToDefault'),
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
            'reorder'   => array('reorder'),
            'reorderUpdate'   => array('reorderUpdate', 'reorder'),
        );

        $this->_category = & new SGL_Category();
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->error           = array();
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterLeftCol.html';
        $input->template        = $this->template;

        //  form vars
        $input->submitted       = $req->get('submitted');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->category        = $req->get('category');
        $input->move            = $req->get('move');
        $input->targetId        = $req->get('targetId');
        $input->aDelete         = $req->get('frmDelete');
        $input->fromPublisher   = $req->get('frmFromPublisher');

        if ($input->action == 'update') {
            $input->category_id         = $input->category['category_id'];
            $input->label               = $input->category['label'];
            $input->parent_id           = $input->category['parent_id'];
            $input->perms               = $input->category['perms'];
            $input->orginial_parent_id  = $input->category['original_parent_id'];
        } elseif ($input->action =='insert') {
            $input->category['parent_id'] = $req->get('frmCatID');
        } else {
            $input->category_id = ($req->get('frmCatID') == '') ? 1 : $req->get('frmCatID');
        }

    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $values = (array) $input->category;
        $this->_redirectCatId = $this->_category->create($values);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  load category
        if (!$this->_category->load($input->category_id)) {
            $output->noEditForm = 1;
            return;
        }
        $output->category = $this->_category->getValues();
        $output->breadCrumbs = $this->_category->getBreadCrumbs($output->category['category_id']);
        $output->perms = $this->_category->getPerms();

        //  categories select box
        $options = array('exclude' => $output->category['category_id']);
        $menu = & new MenuBuilder('SelectBox', $options);
        $aCategories = $menu->toHtml();
        $output->aCategories = $aCategories;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $values = (array) $input->category;
        $message = $this->_category->update($input->category_id, $values);

        if ($message != '') {
            SGL::raiseMsg($message, true, SGL_MESSAGE_INFO);
            $this->_redirectCatId = $input->category_id;
        } else {
            SGL::raiseError('Problem updating category', SGL_ERROR_NOAFFECTEDROWS);
        }

    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  do not allow deletion of root category
        if ($input->category_id == 1) {
            SGL::raiseMsg('do not delete root category', true, SGL_MESSAGE_WARNING);

            $aParams = array(
                'moduleName'    => 'navigation',
                'managerName'   => 'category',
                'action'        => 'list',
                );
            SGL_HTTP::redirect($aParams);

        }

        //  delete categories
        $this->_category->delete($input->aDelete);
        $output->category_id = 0;

        SGL::raiseMsg('The category has successfully been deleted', true, SGL_MESSAGE_INFO);
    }

    function _cmd_reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'categoryReorder.html';
        $output->categoryTree = $this->_category->getTree();

        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function _cmd_reorderUpdate(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aMoveTo = array('BE' => 'up',
                         'AF' => 'down');
        if (isset($input->category_id, $input->targetId) && ($pos = array_search($input->move, $aMoveTo))) {
            $this->_category->move($input->category_id, $input->targetId, $pos);
            SGL::raiseMsg('Categories reordered successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _cmd_redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            SGL_HTTP::redirect(array('frmCatID' => $this->_redirectCatId));

        //  else display error with blank template
        } else {
            $output->template = 'error.html';
        }
    }
}
?>
