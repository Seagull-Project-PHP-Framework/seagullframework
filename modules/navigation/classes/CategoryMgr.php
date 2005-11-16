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
    var $_redirectCatId = 0;
    var $_category = null;

    function CategoryMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
                
        $this->aggregateOutput = true;
        $this->module           = 'navigation';
        $this->pageTitle        = 'Category Manager';
        $this->template         = 'categoryMgr.html';
        $this->da               = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'list'),
            'update'    => array('update', 'list'),
            'delete'    => array('delete', 'list'),
            'edit'      => array('edit'),
            'list'      => array('list'),
            'reorder'   => array('reorder', 'list'),
            'reorderUpdate'   => array('reorderUpdate', 'list'),
            'export'    => array('export'),
            'genCsvLine'=> array('genCsvLine'),
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
        $input->javascriptSrc   = array('TreeMenu.js');

        //  form vars
        $input->submit          = $req->get('submitted');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->category        = $req->get('category');
        $input->move            = $req->get('move');
        $input->targetId        = $req->get('targetId');
        $input->aDelete         = (array)$req->get('frmDelete');
        $input->fromPublisher   = $req->get('frmFromPublisher');
        $input->add             = $req->get('add');
        $input->mode            = $req->get('mode');

        $input->category_id = $req->get('frmCatID');

        if ($input->action == 'update') {
            $input->category_id           = $input->category['category_id'];
            $input->label                 = $input->category['label'];
            $input->parent_id             = $input->category['parent_id'];
            $input->perms                 = $input->category['perms'];
            $input->orginial_parent_id    = $input->category['original_parent_id'];
            $input->parent_id             = $input->category['parent_id'];

        }
    }

    function display(&$output)
    {
        //  prepare subnav
        $output->addOnLoadEvent("document.getElementById('frmResourceChooser').categories.disabled = true");
        
        if($output->action == 'export') {
        // export headers
           header("Content-type: application/ofx");
           header("Content-Disposition: attachment; filename=category.csv");
           echo $output->export;
           exit();
        }
    }

    function _export(&$input, &$output){
        $categoryTree = $this->_category->getTree();

        if (empty($input->mode)) {
            $header = array('category_id', 'root_id', 'level_id', 'parent_id', 'label');
            $export = $this->_genCsvLine($header);

            // export column names
            foreach ($categoryTree as $v){
               unset ($v['images']);
               unset ($v['perms']);
               unset ($v['left_id']);
               unset ($v['right_id']);
               unset ($v['order_id']);

               $export .= $this->_genCsvLine($v);
           }
        } else {
            $export = '';
            //TODO: optimize this...
            foreach ($categoryTree as $v){
               unset ($v['images']);
               unset ($v['perms']);
               unset ($v['left_id']);
               unset ($v['right_id']);
               unset ($v['order_id']);

               $spaces = ',';
               for($i = 1; $i < (int)$v['level_id']; $i++){
                echo $spaces .= '"",';
               }
               $export .= "\"{$v['category_id']}\",\"{$v['root_id']}\",\"{$v['level_id']}\",\"{$v['parent_id']}\"";
               $export .= "{$spaces}\"{$v['label']}\"\n";
           }
        }
        $output->export = $export;
        $output->action = 'export';
    }

    /**
    * Generates a CSV row filled with data from one product
    * Taken from PHP help file.
    *
    * @access public
    *
    */
    function _genCsvLine($inArray, $deliminator = ",")
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $line = "";
        foreach ($inArray as $val) {
            # remove any windows new lines,
            # as they interfere with the parsing at the other end
            $val = str_replace("\r\n", "\n", $val);

            # if a deliminator char, a double quote char or a newline
            # are in the field, add quotes
            if (ereg("[$deliminator\"\n\r]", $val)) {
                $val = '"'.str_replace('"', '""', $val).'"';
            } #end if

            $line .= $val.$deliminator;

        } #end foreach

        # strip the last deliminator
        $line = substr($line, 0, (strlen($deliminator) * -1));
        # add the newline
        $line .= "\n";
        return $line;
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->add = $input->add;
        $output->action = 'insert';

        if(isset($input->category_id) && is_numeric($input->category_id)){
            //load category
            if (!$this->_category->load($input->category_id)) {
               //$input->category_id == 0 and we are adding root category.
               $this->_category->create($input->category_id);
               $output->template = 'categoryAdd.html';
            } else {
               $output->categoryTree = $this->_category->getTree();
               $output->categoryTree[$input->category_id]['open'] = true;
            }
            $output->category = $this->_category->getValues();
            $output->breadCrumbs = $this->_category->getBreadCrumbs($input->category_id);
            $output->perms = $this->_category->getPerms();

            //$options = array('exclude' => $output->add);
            $menu = & new MenuBuilder('SelectBox', '');
            $aCategories = $menu->toHtml();
            $output->aCategories = $aCategories;
        } 
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'categoryMgr.html';
        $output->action = 'update';
        $output->categoryTree = $this->_category->getTree();
        if(!empty($input->category_id) && is_numeric($input->category_id)){
            $output->categoryTree[$input->category_id]['open'] = true;

            //load category
            if (!$this->_category->load($input->category_id)) {
               echo "cant load category";
            } else {
               $output->category = $this->_category->getValues();
               $output->breadCrumbs = $this->_category->getBreadCrumbs($input->category_id);
               $output->perms = $this->_category->getPerms();

               //  categories select box
               //$options = array('exclude' => $output->add);
               $menu = & new MenuBuilder('SelectBox', '');
               $aCategories = $menu->toHtml();
               $output->aCategories = $aCategories;
            }
        }
    }
    
    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $values = (array) $input->category;
        //dumpr($input->category);
        //die();
        if ($values['original_parent_id'] = 0) {
            $values['parent_id'] = 0;
        }
        $this->_redirectCatId = $this->_category->create($values);
    }

    function _reorder(&$input, &$output)
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

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $values = (array) $input->category;
        $message = $this->_category->update($input->category_id, $values);

        if ($message != '') {
            SGL::raiseMsg($message);
            $this->_redirectCatId = $input->category_id;
        } else {
            SGL::raiseError('Problem updating category', SGL_ERROR_NOAFFECTEDROWS);
        }
       
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  do not allow deletion of root category
        if ($input->category_id == 1) {
          //^^^ here we should have not deleteable categories - PubRoot, shopRoot
            SGL::raiseMsg('do not delete root category');

            $aParams = array(
                'moduleName'    => 'navigation', 
                'managerName'   => 'category',
                'action'   => 'list',
                );
            SGL_HTTP::redirect($aParams);

        }
        
        //  delete categories
        $this->_category->delete($input->aDelete);
        $output->category_id = 0;

        SGL::raiseMsg('The category has successfully been deleted');
        
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'categoryMgr.html';
        $output->categoryTree = $this->_category->getTree();
    }
    
    function _reorderUpdate(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);        
        $aMoveTo = array('BE' => 'up',
                         'AF' => 'down');
        if (isset($input->category_id, $input->targetId) && ($pos = array_search($input->move, $aMoveTo))) {
            $this->_category->move($input->category_id, $input->targetId, $pos);
            SGL::raiseMsg('Categories reordered successfully');        
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }

    function _redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!(count($GLOBALS['_SGL']['ERRORS']))) {
            SGL_HTTP::redirect(array('frmCatID' => $this->_redirectCatId));

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
        }
    }
}
?>
