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
// | Category.php                                                              |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// |           Aymerick Jehanne <aymerick@jehanne.org>                         |
// +---------------------------------------------------------------------------+
// $Id: Category.php,v 1.10 2005/04/27 23:32:41 demian Exp $

require_once SGL_CORE_DIR. '/NestedSet.php';

define('SGL_MAX_RECURSION', 100);

/**
 * Wrapper to SGL_NestedSet, to manipulate Categories.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Aymerick Jehanne <aymerick@jehanne.org>
 * @version $Revision: 1.10 $
 */
class SGL_Category
{
    var $_params = array();
    var $_nestedSetNode = array();
    var $_da = null;

    /**
     * Constructor.
     *
     * @return void
     */
    function SGL_Category()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

        $this->da = & UserDAO::singleton();
        $this->dbh = & SGL_DB::singleton();

        //  Nested Set Params
        $this->_params = array(
            'tableStructure' => array(
                'category_id' => 'id',
                'root_id'     => 'rootid',
                'left_id'     => 'l',
                'right_id'    => 'r',
                'order_id'    => 'norder',
                'level_id'    => 'level',
                'parent_id'   => 'parent',
                'label'       => 'label',
                'perms'       => 'perms',
            ),
            'tableName'     => SGL_Config::get('table.category'),
            /** @todo Use $this->conf['table']['table_lock'] */
            'lockTableName' => SGL_Config::get('db.prefix') . 'table_lock',
            'sequenceName'  => SGL_Config::get('table.category'));
    }

    /**
     * Create a Category with given values.
     *
     * @access  public
     * @param   array  $values Category values
     * @return  array          NestedSet node created
     */
    function create(&$values)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set default category label if none provided
        if (!isset($values['label']))
            $values['label'] = 'New Category';

        //  use a NestedSet
        $nestedSet = &SGL_NestedSet::singleton($this->_params);

        //  create new set with first rootnode
        if ($values['parent_id'] == 0) {
            //  we want a root node
            $this->_nestedSetNode = $nestedSet->createRootNode($values);
        } elseif ((int)$values['parent_id'] > 0) {
            //  we want a sub node
            $this->_nestedSetNode = $nestedSet->createSubNode($values['parent_id'], $values);
        } else {
            //  error
            SGL::raiseError('Incorrect parent node id passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear block & category caches
        SGL_Cache::clear('blocks');
        SGL_Cache::clear('categorySelect');

        return $this->_nestedSetNode;
    }

    /**
     * Update a Category with given values.
     *
     * @access  public
     * @param   int    $category_id Category ID to update
     * @param   array  $values      Values to set
     * @return  string              An empty string if error while updating, else
     *                              a message to display.
     */
    function update($category_id, &$values)
    {
        $message = '';

        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $nestedSet = &SGL_NestedSet::singleton($this->_params);

        //  attempt to update section values
        if (!$nestedSet->updateNode($category_id, $values)) {
            SGL::raiseError('There was a problem updating the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }

        //  move node if needed
        switch ($values['parent_id']) {
        case $values['original_parent_id']:
            //  usual case, no change => do nothing
            $message = 'Category details successfully updated';
            break;

        case $values['category_id']:
            //  cannot be parent to self => display user error
            $message = 'Category details updated, no data changed';
            break;

        case 0:
            //  move the category, make it into a root node, just above it's own root
            $thisNode = $nestedSet->getNode( $values['category_id']);
            $moveNode = $nestedSet->moveTree($values['category_id'], $thisNode['root_id'], 'BE');

            if (!is_a($thisNode, 'PEAR_Error') && !is_a($moveNode, 'PEAR_Error')) {
                $message = 'Category details successfully updated';
            }
            break;

        default:
            //  move the category under the new parent
            $moveNode = $nestedSet->moveTree($values['category_id'], $values['parent_id'], 'SUB');

            if (!is_a($moveNode, 'PEAR_Error') && !is_a($moveNode, 'PEAR_Error')) {
                $message = 'Category details successfully updated';
            }
            break;
        }

        // Update perms
        require_once SGL_CORE_DIR . '/CategoryPerms.php';
        $perms = new SGL_CategoryPerms($category_id);
        $perms->set('aPerms', $values['perms']);
        $perms->update();

        //  clear block & category caches
        SGL_Cache::clear('categorySelect');
        SGL_Cache::clear('blocks');

        /** @todo Return a constant instead of a message ! */
        return $message;
    }

    /**
     * Delete several Categories.
     *
     * @access public
     * @param  array  $aDelete Array of Category IDs to delete
     * @return void
     */
    function delete(&$aDelete)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($aDelete)) {
            $nestedSet = &SGL_NestedSet::singleton($this->_params);
            //  deleting parent nodes automatically deletes children nodes, but user
            //  might have checked child nodes for deletion, in which case deleteNode()
            //  would try to delete nodes that no longer exist, after parent deletion,
            //  and therefore error, so test first to make sure they're still around
            foreach ($aDelete as $index => $categoryId) {
                if ($nestedSet->getNode($categoryId)) {
                    $nestedSet->deleteNode($categoryId);
                }
            }
        } else {
            SGL::raiseError("Incorrect parameter passed to " . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  clear block & category caches
        SGL_Cache::clear('categorySelect');
        SGL_Cache::clear('blocks');
    }

    /**
     * Move a Category.
     *
     * @access  public
     * @param   int    $category_id Category ID to move
     * @param   int    $target_id   New parent
     * @param   int    $pos         New position
     */
    function move($category_id, $target_id, $pos)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $nestedSet = &SGL_NestedSet::singleton($this->_params);

        //  move tree
        $nestedSet->moveTree($category_id, $target_id, $pos);

        //  clear block & category caches
        SGL_Cache::clear('categorySelect');
        SGL_Cache::clear('blocks');
    }

    /**
     * Load a Category, given its ID.
     *
     * @access  public
     * @param   int    $category_id Category ID to load
     * @return  bool                TRUE if loaded, FALSE if error
     */
    function load($category_id)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  check if category_id not set or 0
        if (!isset($category_id) || ($category_id == '0')) {
            return false;
        }

        //  get NestedSet node
        $nestedSet = &SGL_NestedSet::singleton($this->_params);
        $this->_nestedSetNode = $nestedSet->getNode($category_id);

        //  check if category_id does not exist
        if (!isset($this->_nestedSetNode) || empty($this->_nestedSetNode)) {
            SGL::raiseError('Invalid category ID passed', SGL_ERROR_INVALIDARGS);
            return false;
        }
        return true;
    }

    /**
     * Get current Category values.
     *
     * Category must be loaded before using this function.
     *
     * @access  public
     * @return  array  NestedSet node representing the current Category
     */
    function getValues()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  check if Category is not loaded
        if (!isset($this->_nestedSetNode) || empty($this->_nestedSetNode)) {
            SGL::raiseError('Category not loaded', SGL_ERROR_INVALIDCALL);
            return null;
        }

        return $this->_nestedSetNode;
    }

    /**
     * Get current Category permissions.
     *
     * Category must be loaded before using this function.
     *
     * @access  public
     * @return  array  Current Category permissions
     */
    function getPerms()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  check if Category is not loaded
        if (!isset($this->_nestedSetNode) || empty($this->_nestedSetNode)) {
            SGL::raiseError('Category not loaded', SGL_ERROR_INVALIDCALL);
            return null;
        }

        //  get assoc array of all roles
        $aRoles = $this->da->getRoles();
        $aRoles[0] = 'guest';

        //  if no perms in category table for current category_id, set to empty array
        $aPerms = (isset($this->_nestedSetNode['perms']) && count($this->_nestedSetNode['perms']))
            ? explode(',', $this->_nestedSetNode['perms'])
            : array();

        foreach ($aRoles as $roleId => $roleName) {
            $tmp['role_id'] = $roleId;
            $tmp['name'] = $roleName;
            $tmp['isAllowed'] = (!in_array($roleId, $aPerms)) ? 1 : 0;
            $perms[] = (object)$tmp;
        }

        return $perms;
    }

    /**
     * Returns true if current user has perms to view a category.
     *
     * Category must be loaded before using this function.
     *
     * @access  public
     * @return bool
     */
    function hasPerms()
    {
        //  check if Category is not loaded
        if (!isset($this->_nestedSetNode) || empty($this->_nestedSetNode)) {
            return false;
        }
        $aPerms = $this->getPerms();
        $roleId = SGL_Session::get('rid');

        foreach ($aPerms as $perm) {
            if ($perm->role_id == $roleId) {
                return $perm->isAllowed;
            }
        }
        return false;
    }

    /**
     * Get a tree representing all Categories.
     *
     * @access  public
     * @return  array  Categories tree
     */
    function getTree()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $nestedSet = &SGL_NestedSet::singleton($this->_params);
        $nestedSet->setImage('folder', 'images/treeNav/file.png');
        $categoryTree = $nestedSet->getTree();
        $nestedSet->addImages($categoryTree);

        return $categoryTree;
    }

    /**
     * Retrieve children
     *
     * @access  public
     * @param   int     $id
     * @return  array   categories children
     */
    function getChildren($id)
    {
        if (!is_numeric($id)) {
            SGL::raiseError('Wrong datatype passed to '  . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
        }
        $query = "
            SELECT category_id, label
            FROM " . SGL_Config::get('table.category') . "
            WHERE parent_id = $id
            ORDER BY parent_id, order_id";

        $result = $this->dbh->query($query);
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
     * @access  public
     * @param   int     $id
     * @return  boolean
     */
    function isBranch($id)
    {
        $nestedSet = &SGL_NestedSet::singleton($this->_params);
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
     * @access  public
     * @param   integer $category_id
     * @param   boolean $links          build links
     * @param   string  $style          CSS Class
     * @param   boolean $links          add link to the current CatID
     * @return  string  $finalHtmlString
     */
    function getBreadCrumbs($category_id, $links = true, $style = '', $lastLink = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (!is_numeric($category_id)) {
            SGL::raiseError("Invalid category ID, '$category_id', passed to " .
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }
        $nestedSet = &SGL_NestedSet::singleton($this->_params);
        $node = $nestedSet->getNode($category_id);

        if (empty($node) || is_a($node, 'PEAR_Error')) {
            return false;
        }
        $crumbs = $nestedSet->getBreadcrumbs($category_id);
        $htmlString = '';

        $req = & SGL_Request::singleton();

        //  logical case for publisher->articleview->view
        $managerName = $req->get('managerName');
        $action = $req->get('action');

        //  Make sure the articleview->view page shows the right breadcrumbs
        if (strtolower($managerName) == 'articleview' &&
            strtolower($action) == 'view') {
            // summary is the correct action when browsing categories
            $action = "summary";
        }

        //  build url for current page
        $url = SGL_Url::makeLink(  $action,
                                   $managerName,
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
     * Retrieves category label
     *
     * @access  public
     * @param   int     $id
     * @return  string
     */
    function getLabel($id)
    {
        $nestedSet = &SGL_NestedSet::singleton($this->_params);
        $node = $nestedSet->getNode($id);
        if (!PEAR::isError($node)) {
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
