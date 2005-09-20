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
// | CategoryMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: CategoryMgr.php,v 1.27 2005/05/17 23:54:51 demian Exp $

require_once SGL_CORE_DIR. '/NestedSet.php';
require_once SGL_ENT_DIR . '/Category.php';
require_once SGL_MOD_DIR . '/navigation/classes/MenuBuilder.php';
require_once SGL_MOD_DIR . '/user/classes/DA_User.php';

define('SGL_MAX_RECURSION', 100);

/**
 * For performing operations on Category objects.
 *
 * @package publisher
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.27 $
 * @since   PHP 4.1
 * @todo    at some point change this for PEAR::Tree
 */
class CategoryMgr extends SGL_Manager
{
    var $_bFirstTimeIn  = false;
    var $_cacheFile     = '';
    var $_ancestors     = array();
    var $_params        = array();
    var $navCache       = array();
    var $redirectCatId  = 0;

    function CategoryMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
                
        $this->aggregateOutput = true;
        $this->module           = 'navigation';
        $this->pageTitle        = 'Category Manager';
        $this->template         = 'categoryMgr.html';
        $this->da               = & DA_User::singleton();
        $this->_bFirstTimeIn    = true;

        $this->_aActionsMapping =  array(
            'insert'    => array('insert', 'redirectToDefault'), 
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'), 
            'list'      => array('list'),
            'reorder'   => array('reorder'), 
            'reorderUpdate'   => array('reorderUpdate', 'reorder'),            
        );
        //  Nested Set Params
        $this->_params = array(
            'tableStructure' => array(
                'category_id'    => 'id',
                'root_id'       => 'rootid',
                'left_id'       => 'l',
                'right_id'      => 'r',
                'order_id'      => 'norder',
                'level_id'      => 'level',
                'parent_id'     => 'parent',
                'label'         => 'label',
                'perms'         => 'perms',
            ),
            'tableName'      => $conf['table']['category'],
            'lockTableName'  => 'table_lock',
            'sequenceName'   => $conf['table']['category']);
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
            $input->category_id = $req->get('frmCatID');
        }

    }

    function display(&$output)
    {
        //  prepare subnav
        $output->addOnLoadEvent("document.getElementById('frmResourceChooser').categories.disabled = true");
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $values = (array) $input->category;
        $values['label'] = 'New Category';
        $nestedSet = new SGL_NestedSet($this->_params);
        //  create new set with first rootnode
        if ($values['parent_id'] == 0) {    //  they want a root node
            $node = $nestedSet->createRootNode($values);
        } elseif ((int)$values['parent_id'] > 0) {//    they want a sub node
            $node = $nestedSet->createSubNode($values['parent_id'], $values);
        } else {//  error
            SGL::raiseError('Incorrect parent node id passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear block & category caches
        SGL::clearCache('blocks');
        SGL::clearCache('categorySelect');
        $this->redirectCatId = $node;
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
             
        // if category_id not set or 0
        if (!isset($input->category_id) || ($input->category_id == '0')) {
            $output->noEditForm = 1;
            return;
        }
        $nestedSet = new SGL_NestedSet($this->_params);
        $category = $nestedSet->getNode($input->category_id);       

        // if category_id does not exist
        if (!isset($category) && empty($category)) {
            SGL::raiseError('Invalid category ID passed', SGL_ERROR_INVALIDARGS);
            $output->noEditForm = 1;
            return;
        }
        $output->category = $category;
        $output->breadCrumbs = $this->getBreadCrumbs($category['category_id']);

        //  Perms
        //  get assoc array of all roles
        $aRoles = $this->da->getRoles();
        $aRoles[0] = 'guest';

        //  if no perms in category table for current category_id, set to empty array
        $aPerms = (isset($output->category['perms']) && count($output->category['perms']))
            ? explode(',', $output->category['perms']) 
            : array();
            
        foreach ($aRoles as $roleId => $roleName) {
            $tmp['category_id'] = $roleId;
            $tmp['name'] = $roleName;
            $tmp['isAllowed'] = (!in_array($roleId, $aPerms)) ? 1 : 0;
            $perms[] = (object)$tmp;
        }
        $output->perms = $perms;

        //  Move
        $options = array('exclude' => $output->category['category_id']);
        $menu = & new MenuBuilder('SelectBox', $options);       
        $aCategories = $menu->toHtml();
        $output->aCategories = $aCategories;
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);       
        $nestedSet = new SGL_NestedSet($this->_params);
        $values = (array) $input->category;

        //  attempt to update section values
        if (!$nestedSet->updateNode($input->category_id, $values)) {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
        //  move node if needed
        switch ($input->category['parent_id']) {
        
        case $input->category['original_parent_id']:
            //  usual case, no change => do nothing
            $message = 'Category details successfully updated';
            $success_cat = true;
            break;
            
        case $input->category['category_id']:
            //  cannot be parent to self => display user error
            $message = 'Category details updated, no data changed';
            $success_cat = true;
            break;
            
        case 0:
            //  move the category, make it into a root node, just above it's own root
            $thisNode = $nestedSet->getNode($input->category['category_id']);
            $moveNode = $nestedSet->moveTree($input->category['category_id'], $thisNode['root_id'], 'BE');
            $message = 'Category details successfully updated';
            if (!is_a($thisNode, 'PEAR_Error') || !is_a($moveNode, 'PEAR_Error')) {
                $success_cat = true;
            } else {
                $success_cat = false;
            }
            break;
            
        default:
            //  move the category under the new parent
            $moveNode = $nestedSet->moveTree($input->category['category_id'], $input->category['parent_id'], 'SUB');
            $message = 'Category details successfully updated';
            if (!is_a($moveNode, 'PEAR_Error') || !is_a($moveNode, 'PEAR_Error')) {
                $success_cat = true;
            } else {
                $success_cat = false;
            }
        }
        
		// Update perms
        require_once SGL_MOD_DIR . '/navigation/classes/Permissions.php';
		$perms = & new Permissions($input->category_id);
        $perms->set('aPerms', $input->category['perms']);
        $success_perms = $perms->update(); 

		//  clear block & category caches
        SGL::clearCache('categorySelect');
		SGL::clearCache('blocks');

        if ($success_cat !== false and $success_perms !== false) {
            //  redirect on success
            SGL::raiseMsg($message);
            $this->redirectCatId = $input->category_id;
        } else {
            SGL::raiseError('Problem updating category', SGL_ERROR_NOAFFECTEDROWS);
        }
       
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  do not allow deletion of root category
        if ($input->category_id == 1) {
            SGL::raiseMsg('do not delete root category');

            $aParams = array(
                'moduleName'    => 'navigation', 
                'managerName'   => 'category',
                'action'   => 'list',
                );
            SGL_HTTP::redirect($aParams);

        }

        if (is_array($input->aDelete)) {
            $nestedSet = new SGL_NestedSet($this->_params);
            //  deleting parent nodes automatically deletes chilren nodes, but user
            //  might have checked child nodes for deletion, in which case deleteNode()
            //  would try to delete nodes that no longer exist, after parent deletion,
            //  and therefore error, so test first to make sure they're still around
            foreach ($input->aDelete as $categoryId) {
                if ($nestedSet->getNode($categoryId)){
                    $nestedSet->deleteNode($categoryId);
                }
            }
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }

        //  clear block & category caches
        SGL::clearCache('categorySelect');
        SGL::clearCache('blocks');
        $output->category_id = 0;

        SGL::raiseMsg('The category has successfully been deleted');
    }

    function _reorder(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'categoryReorder.html';
        $nestedSet = new SGL_NestedSet($this->_params);
        $nestedSet->setImage('folder', 'images/imagesAlt2/file.png');       
        $output->categoryTree = $nestedSet->getTree();
        $nestedSet->addImages($output->categoryTree);                
    }
    
    function _reorderUpdate(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);        
        $nestedSet = new SGL_NestedSet($this->_params);
        $aMoveTo = array('BE' => 'up',
                         'AF' => 'down');
        if (isset($input->category_id, $input->targetId) && ($pos = array_search($input->move, $aMoveTo))) {
            $nestedSet->moveTree($input->category_id, $input->targetId, $pos);
            SGL::raiseMsg('Categories reordered successfully');        
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear cache so a new cache file is built reflecting changes
        SGL::clearCache('categorySelect');
        SGL::clearCache('blocks');
    }

    function _redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!(count($GLOBALS['_SGL']['ERRORS']))) {
            SGL_HTTP::redirect(array('frmCatID' => $this->redirectCatId));

        //  else display error with blank template
        } else {
            $output->template = 'docBlank.html';
        }
    }

    /**
     * Retrieve children
     * 
     * @access	public
     * @param	int		$id
     * @return	array	categories children
     */
    function getChildren($id)
    {
        if (!is_numeric($id)) {
            SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' . 
                __FUNCTION__, SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        }
        $conf = & $GLOBALS['_SGL']['CONF'];
        $query = "  SELECT category_id, label 
                    FROM " . $conf['table']['category'] . "
                    WHERE parent_id = $id
                    ORDER BY parent_id, order_id";
                    
        $dbh = & SGL_DB::singleton();
        $result = $dbh->query($query);
        $count = 0;
        $aChildren = array();
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $aChildren[$count]['category_id'] = $row['category_id'];   
            $aChildren[$count]['label'] = $row['label'];
            $count++;
        }

        return $aChildren;
    }

    /**
     * Checks if an category is a branch
     * 
     * @access 	public
     * @param 	int		$id
     * @return	boolean
     */
    function isBranch($id)
    {
        $nestedSet = new SGL_NestedSet($this->_params);
        $ns = $nestedSet->_getNestedSet();
        $node = $ns->pickNode($id, $keepAsArray = true, $alias = true);
        if ($node) {
            if (($node['r'] - $node['l']) > 1) {
                return true;
            }
        }
        return false;
    }
    
    /**
     *  Generates breadcrumbs for category
     * 
     * @access 	public
     * @param 	integer	$category_id	
     * @param 	boolean $links			build links
     * @param	string	$style			CSS Class
     * @param 	boolean $links			add link to the current CatID
     * @return 	string  $finalHtmlString
     */
    function getBreadCrumbs($category_id, $links = true, $style = '', $lastLink = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_numeric($category_id)) {
            SGL::raiseError("Invalid category ID, '$category_id', passed to " . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $nestedSet = new SGL_NestedSet($this->_params);
        $node = $nestedSet->getNode($category_id);

        if (empty($node) || is_a($node, 'PEAR_Error')) {
            return false;
        }
        $crumbs = $nestedSet->getBreadcrumbs($category_id);        
        $htmlString = '';

        //  build url for current page
        $req = & SGL_HTTP_Request::singleton();
        $url = SGL_Url::makeLink(   $req->get('action'), 
                                    $req->get('managerName'),
                                    $req->get('moduleName')                             
                                    );
        $url .= 'frmCatID/';
        
        foreach ($crumbs as $crumb) {
            if ($links) {
                $htmlString .= "<a class='$style' href='$url".$crumb['category_id']."/'>" . 
                    stripslashes($crumb['label']) . "</a> > ";
            } else {
                $htmlString .= stripslashes($crumb['label']) . " > ";
            }
        }
        $finalHtmlString = ($lastLink) 
            ? $htmlString . "<a class='$style' href='$url".$category_id."/'>" . $node['label'] ."</a>"
            : $htmlString . $node['label'];
        return $finalHtmlString;
    }

    /**
     * Retrives category label
     * 
     * @access 	public
     * @param	int		$id
     * @return	string
     */
     
    function getLabel($id)
    {
        $nestedSet = new SGL_NestedSet($this->_params);
        $node = $nestedSet->getNode($id);
        if ($node) {
            return $node['label'];
        } else {
            return false;
        }
    }

    function debug($id = 0)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $result = $this->getChildren($id);
        $listString .= '<ul>';
        for ($x = 0; $x < count($result); $x++) {
            $listString .= "<li>" . $result[$x]["label"] . "[" . $result[$x]['category_id'] . "]";

            // if branch then recurse
            if ($this->isBranch($result[$x]['category_id'])) {
                $listString .= $this->debug($result[$x]['category_id']);
            }
        }
        $listString .=  '</ul>';
        return $listString;
    }

    //  abstract methods
    function render()
    {
        //  abstract
    }
   
}
?>
