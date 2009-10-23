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
// | NestedSet.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Andy Crain <andy@newslogic.com>                                 |
// +---------------------------------------------------------------------------+
// $Id: NestedSet.php,v 1.10 2005/04/27 23:32:41 demian Exp $

/**
 * A lightweight wrapper to PEAR DB_NestedSet that bypasses NestedSet for most
 * methods, querying the specified nested set table directly via PEAR DB; more
 * complex, write-heavy methods are left to DB_NestedSet.
 *
 * @package SGL
 * @author  Andy Crain <andy@newslogic.com>
 * @version $Revision: 1.10 $
 */
class SGL_NestedSet
{
   /**
    * Array of images for addImages() to use to decorate results, relative to root.
    *
    * @var array
    * @access private
    */
    var $_images = array(
        'upArrow'       => 'images/16/move_up.gif',
        'upArrowDead'   => 'images/16/move_up_dead.gif',
        'downArrow'     => 'images/16/move_down.gif',
        'downArrowDead' => 'images/16/move_down_dead.gif',
        'folder'        => 'images/treeNav/foldericon.png',
        'file'          => 'images/treeNav/file.png',
        'blank'         => 'images/treeNav/blank.png',
        't'             => 'images/treeNav/T.png',
        'l'             => 'images/treeNav/L.png',
        'i'             => 'images/treeNav/I.png');

    var $_protectedFields = array('id', 'rootid', 'l', 'r', 'norder', 'level', 'parent');

    var $params          = array('tableStructure' =>
                                array(
                                    'id'     => 'id',
                                    'rootid' => 'rootid',
                                    'l'      => 'l',
                                    'r'      => 'r',
                                    'norder' => 'norder',
                                    'level'  => 'level',
                                    'parent' => 'parent'
                                ),
                                'tableName'      => '',
                                'lockTableName'  => '',
                                'sequenceName'   => '');

    var $_aNodes = array();

    function &singleton($params)
    {
        static $aInstances;

        $signature = md5(serialize($params));
        if (!isset($aInstances[$signature])) {
            $aInstances[$signature] = new SGL_NestedSet($params);
        }

        return $aInstances[$signature];
    }

    function SGL_NestedSet($params)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->dbh                = $this->_getDb();
        $this->_params            = $params;
        $this->_tableName         = $params['tableName'];
        $this->_fieldsInternal    = array_flip($params['tableStructure']);
        $this->_fieldListExternal = $this->_tableName . '.' . implode(",$this->_tableName.",$this->_fieldsInternal);
    }

    function &_getDb()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    function setImage($key, $value)
    {
        $this->_images[$key] = $value;
    }


    function setImages($imagesArray)
    {
        $this->_images = $imagesArray;
    }

    /**
     * Fetches all root nodes (rootid = id) from the entire table/tree.
     *
     * @access public
     * @param void
     * @return array of node arrays
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getRoots($addSQL = array())
    {
        $cols     = $this->_addSQL($addSQL, 'cols');
        $join     = $this->_addSQL($addSQL, 'join');
        $groupBy  = $this->_addSQL($addSQL, 'groupBy');
        $where    = $this->_addSQL($addSQL, 'where', 'AND');

        //  Get root nodes (id = rootid).
        $sql = "SELECT $this->_fieldListExternal $cols
                FROM $this->_tableName $join
                WHERE {$this->_tableName}.{$this->_fieldsInternal['id']} = {$this->_tableName}.{$this->_fieldsInternal['rootid']}
                $where $groupBy
                ORDER BY {$this->_tableName}.{$this->_fieldsInternal['norder']}";
        $result =& $this->dbh->query($sql);
        if (PEAR::isError($result)) {
            return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
        }
        $r = '';
        while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)){
            $r[$row[$this->_fieldsInternal['id']]] = $row;
        }
        return $r;
    }


    /**
     * Fetches all nodes from all roots, i.e., the entire table/tree.
     *
     * @access public
     * @param void
     * @return array of node arrays
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getTree($addSQL = array())
    {
        $cols     = $this->_addSQL($addSQL, 'cols');
        $join     = $this->_addSQL($addSQL, 'join');
        $groupBy  = $this->_addSQL($addSQL, 'groupBy');
        $where    = $this->_addSQL($addSQL, 'where', 'AND');

        $roots = $this->getRoots($addSQL);
        //  Get all descendants of each node (rootid = $rootid).
        $r = '';
        if (is_array($roots) && count($roots)) {
            foreach($roots as $id => $root) {
                $sql = "SELECT $this->_fieldListExternal $cols
                        FROM $this->_tableName $join
                        WHERE {$this->_tableName}.{$this->_fieldsInternal['rootid']} = {$root[$this->_fieldsInternal['rootid']]}
                        $where $groupBy
                        ORDER BY {$this->_tableName}.{$this->_fieldsInternal['l']}";
                $result =& $this->dbh->query($sql);
                if (PEAR::isError($result)) {
                    return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
                }
                while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)){
                    $r[$row[$this->_fieldsInternal['id']]] = $row;
                }
            }
        }
        return $r;
    }

    /**
     * For any node whose id is supplied, fetches branch for that node (all
     * nodes with same root_id), including that node, and those above and below it.
     *
     * @access public
     * @param int $node_id
     * @return array of node arrays
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getBranch($node_id, $addSQL = array())
    {
        $cols     = $this->_addSQL($addSQL, 'cols');
        $join     = $this->_addSQL($addSQL, 'join');
        $groupBy  = $this->_addSQL($addSQL, 'groupBy');
        $where    = $this->_addSQL($addSQL, 'where', 'AND');

        //  Get this node's rootnode, so we can then get nodes with matching rootid.
        $node = $this->getNode($node_id);
        $sql = "SELECT $this->_fieldListExternal $cols
                FROM $this->_tableName $join
                WHERE {$this->_tableName}.{$this->_fieldsInternal['rootid']} = {$node[$this->_fieldsInternal['rootid']]}
                $where $groupBy
                ORDER BY {$this->_tableName}.{$this->_fieldsInternal['l']}";
        $result =& $this->dbh->query($sql);
        if (PEAR::isError($result)) {
            return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
        }
        while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)){
            $r[$row[$this->_fieldsInternal['id']]] = $row;
        }
        return $r;
    }

    /**
     * For any node, fetches all descendents (kids, grandkids, etc.)of that node (nodes with same
     * rootid and with left ids bounded by this node's left and right id, and which aren't this node).
     *
     * @access public
     * @param int $node_id
     * @return array of node arrays
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getSubBranch($node_id, $addSQL = array())
    {
        $cols     = $this->_addSQL($addSQL, 'cols');
        $join     = $this->_addSQL($addSQL, 'join');
        $groupBy  = $this->_addSQL($addSQL, 'groupBy');
        $where    = $this->_addSQL($addSQL, 'where', 'AND');

        //  Get this node's rootnode, left and right nodes and use to fetch children.
        $node = $this->getNode($node_id);
        $sql = "SELECT $this->_fieldListExternal $cols
                FROM $this->_tableName $join
                WHERE {$this->_tableName}.{$this->_fieldsInternal['rootid']} = {$node[$this->_fieldsInternal['rootid']]}
                AND {$this->_tableName}.{$this->_fieldsInternal['l']} BETWEEN {$node[$this->_fieldsInternal['l']]} AND {$node[$this->_fieldsInternal['r']]}
                AND {$this->_tableName}.{$this->_fieldsInternal['id']} <> $node_id
                $where $groupBy
                ORDER BY {$this->_tableName}.{$this->_fieldsInternal['l']}";
        $result =& $this->dbh->query($sql);
        if (PEAR::isError($result)) {
            return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
        }
        $r = '';
        while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)){
            $r[$row[$this->_fieldsInternal['id']]] = $row;
        }
        return $r;
    }

    function getParent($id)
    {
        $ns = $this->_getNestedSet();
        return $ns->getParent($id);
    }

    /**
     * For any node, fetches child nodes (nodes with same rootid, with level = parent
     * level + 1, with l between parent's l and r).
     *
     * @access public
     * @param int $node_id
     * @return array of node arrays
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getChildren($node_id, $addSQL = array())
    {
        //  Get this node's rootnode, left and right nodes and use to fetch children.
        $node = $this->getNode($node_id);
        $cols     = $this->_addSQL($addSQL, 'cols');
        $join     = $this->_addSQL($addSQL, 'join');
        $groupBy  = $this->_addSQL($addSQL, 'groupBy');
        $where    = $this->_addSQL($addSQL, 'where', 'AND');
        $sql  = "SELECT $this->_fieldListExternal $cols
                FROM $this->_tableName $join
                WHERE {$this->_tableName}.{$this->_fieldsInternal['rootid']} = {$node[$this->_fieldsInternal['rootid']]}
                AND {$this->_tableName}.{$this->_fieldsInternal['l']} BETWEEN {$node[$this->_fieldsInternal['l']]} AND {$node[$this->_fieldsInternal['r']]}
                AND {$this->_tableName}.{$this->_fieldsInternal['level']} = {$node[$this->_fieldsInternal['level']]} + 1
                $where $groupBy
                ORDER BY {$this->_tableName}.{$this->_fieldsInternal['l']}";
        $result =& $this->dbh->query($sql);
        if (PEAR::isError($result)) {
            return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
        }
        $r = array();
        while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $r[$row[$this->_fieldsInternal['id']]] = $row;
        }
        return $r;
    }

    /**
     * Borrowed from PEAR DB_NestedSet. Formats various sql clauses.
     *
     * @access private
     * @param
     * @return
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function _addSQL($addSQL, $type, $prefix = false) {
        if (!isset($addSQL[$type]) || $addSQL[$type] == '') {
            return '';
        }
        switch ($type) {
            case 'cols':
                return ', ' . $addSQL[$type];
            case 'where':
                return $prefix . ' (' . $addSQL[$type] . ')';
            default:
                return $addSQL[$type];
        }
    }


    /**
     * Returns the node indentified by supplied id, or false.
     *
     * @access private
     * @param int $node_id
     * @return array | false
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getNode($node_id)
    {
        if (!isset($this->_aNodes[$node_id])) {
            $sql = "SELECT $this->_fieldListExternal
                    FROM $this->_tableName
                    WHERE {$this->_fieldsInternal['id']} = $node_id";
            $result =& $this->dbh->query($sql);
            if (PEAR::isError($result)) {
                return $result;
            }
            $result->fetchInto($this->_aNodes[$node_id], DB_FETCHMODE_ASSOC);
            if (is_null($this->_aNodes[$node_id])) {
                $this->_aNodes[$node_id] = false;
            }
        }
        return $this->_aNodes[$node_id];
    }

    /**
     * Returns "breadcrumbs," a node's ancestral path through the tree: same rootid,
     * and a level less than this node's, and a left node less than this node's, and
     * a right node greater than this node's.
     *
     * @access public
     * @param int $node_id
     * @return array of node arrays
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function getBreadcrumbs($node_id, $addSQL = array(), $includeCurrentNode = false)
    {
        $cols     = $this->_addSQL($addSQL, 'cols');
        $join     = $this->_addSQL($addSQL, 'join');
        $groupBy  = $this->_addSQL($addSQL, 'groupBy');
        $where    = $this->_addSQL($addSQL, 'where', 'AND');

        $node = $this->getNode($node_id);
        if ($includeCurrentNode) {
            $sql = "SELECT $this->_fieldListExternal $cols
                    FROM $this->_tableName $join
                    WHERE {$this->_tableName}.{$this->_fieldsInternal['level']} <= {$node[$this->_fieldsInternal['level']]}
                    AND {$this->_tableName}.{$this->_fieldsInternal['l']} <= {$node[$this->_fieldsInternal['l']]}
                    AND {$this->_tableName}.{$this->_fieldsInternal['r']} >= {$node[$this->_fieldsInternal['r']]}
                    AND {$this->_tableName}.{$this->_fieldsInternal['rootid']} = {$node[$this->_fieldsInternal['rootid']]}
                    $where $groupBy
                    ORDER BY {$this->_tableName}.{$this->_fieldsInternal['l']}";
        } else {
            $sql = "SELECT $this->_fieldListExternal $cols
                    FROM $this->_tableName $join
                    WHERE {$this->_tableName}.{$this->_fieldsInternal['level']} < {$node[$this->_fieldsInternal['level']]}
                    AND {$this->_tableName}.{$this->_fieldsInternal['l']} < {$node[$this->_fieldsInternal['l']]}
                    AND {$this->_tableName}.{$this->_fieldsInternal['r']} > {$node[$this->_fieldsInternal['r']]}
                    AND {$this->_tableName}.{$this->_fieldsInternal['rootid']} = {$node[$this->_fieldsInternal['rootid']]}
                    $where $groupBy
                    ORDER BY {$this->_fieldsInternal['l']}";
        }
        $result =& $this->dbh->query($sql);
        if (PEAR::isError($result)) {
            return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
        }
        $r = array();
        while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)){
            $r[$row[$this->_fieldsInternal['id']]] = $row;
        }
        return $r;
    }

    /**
     * Takes a reference to a nodes array such as returned by getTree() et. al. and adds to
     * each node array an array of images that can be used to build a MS Windows Explorer-like
     * tree view, with images for file, folder, blank, and I, L, and T shapes, as well as up
     * and down arrows that are grayed out when the node cannot be moved up or down (has no
     * siblings above or below it).
     *
     * @access public
     * @param array $aNodes
     * @return void
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function addImages(&$aNodes)
    {
        //  Build sorted array $nodeMap so we can easily test whether a node has
        //  siblings (same parent, same level) above or below it (order-1, order+1).
        if (is_array($aNodes) && count($aNodes)) {
            if (!isset($nodeMap)) {
                $level  = $this->_fieldsInternal['level'];
                $parent = $this->_fieldsInternal['parent'];
                $order  = $this->_fieldsInternal['norder'];
                foreach($aNodes as $k => $n){
                    $nodeMap[$n[$level]][$n[$parent]][$n[$order]] = $k;
                }
            }
            reset($aNodes);
            while (list($id,$node) = each($aNodes)){
                //  If inside subtree, use I shape, else blank (for level 2 and above only,
                //  since rightmost 2 images are the L/T shape then folder/file).
                for ($i=2; $i < $node[$this->_fieldsInternal['level']]; $i++){
                    if (isset($insideNest[$i]) && $insideNest[$i] == true) {
                        $aNodes[$id]['images']['treePad'][] = $this->_images['i'];
                    } else {
                        $aNodes[$id]['images']['treePad'][] = $this->_images['blank'];
                    }
                }
                //  Only nodes with parents get T and L shapes
                if ($node[$this->_fieldsInternal['l']] > 1){
                    //  use L shape for terminal node, T shape for intermediate node
                    if ($node[$this->_fieldsInternal['r']] == $aNodes[$node[$this->_fieldsInternal['parent']]][$this->_fieldsInternal['r']] - 1){//terminal node
                        $aNodes[$id]['images']['treePad'][] = $this->_images['l'];
                        $insideNest[$node[$this->_fieldsInternal['level']]] = false;
                    } else {
                        $aNodes[$id]['images']['treePad'][] = $this->_images['t'];
                        $insideNest[$node[$this->_fieldsInternal['level']]] = true;
                    }
                }
                //  Finally, add folder icon.
                $aNodes[$id]['images']['treePad'][] = $this->_images['folder'];

                //  Using $nodeMap, assign up/down arrows and the target node id above or below
                //  which to move the node.
                if (isset($nodeMap[$node[$this->_fieldsInternal['level']]][$node[$this->_fieldsInternal['parent']]][$node[$this->_fieldsInternal['norder']] - 1]) ) {
                    $aNodes[$id]['images']['moveUpImg']    = $this->_images['upArrow'];
                    $aNodes[$id]['images']['moveUpTarget'] = $nodeMap[$node[$this->_fieldsInternal['level']]][$node[$this->_fieldsInternal['parent']]][$node[$this->_fieldsInternal['norder']] - 1];
                } else {
                    $aNodes[$id]['images']['moveUpImg']    = $this->_images['upArrowDead'];
                    $aNodes[$id]['images']['moveUpTarget'] = 0;
                }
                if (isset($nodeMap[$node[$this->_fieldsInternal['level']]][$node[$this->_fieldsInternal['parent']]][$node[$this->_fieldsInternal['norder']] + 1]) ) {
                    $aNodes[$id]['images']['moveDownImg']    = $this->_images['downArrow'];
                    $aNodes[$id]['images']['moveDownTarget'] = $nodeMap[$node[$this->_fieldsInternal['level']]][$node[$this->_fieldsInternal['parent']]][$node[$this->_fieldsInternal['norder']] + 1];
                } else {
                    $aNodes[$id]['images']['moveDownImg']    = $this->_images['downArrowDead'];
                    $aNodes[$id]['images']['moveDownTarget'] = 0;
                }
            }
        }
    }

    /**
     * Updates defined fields (any field defined in params['tableStructure'] that is not
     * a NestedSet field such as l, r, rootid, norder, etc. (i.e. not in _protectedFields).
     *
     * @access public
     * @param int $id
     * @param array $values (field name => field value)
     * @return DB_OK | PEAR Error
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function updateNode($id, $values)
    {
        $this->_cleanMemoryCache($id);

        $set = implode(', ', $this->prepareValues($values, true));
        $sql = "UPDATE $this->_tableName
                SET $set
                WHERE {$this->_fieldsInternal['id']} = $id";
        $result =& $this->dbh->query($sql);
        if (PEAR::isError($result)) {
            return SGL::raiseError('SQL problem', SGL_ERROR_DBFAILURE);
        }
        return $result;
    }

    /**
     * Prepares $values array for insert or update into table. Called
     * with $quoteValues=true to build quoted key=value strings for update sql.
     * Called externally with $quoteValues=false to simply weed out of an
     * array any fields that either are not in the table or that are but are internal.
     *
     * @access private
     * @param
     * @return object DB_NestedSet_DB
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @version 1.0
     * @since   PHP 4.0
     */
    function prepareValues($values, $quoteValues = false)
    {
        $r = array();
        if ($quoteValues) {
            foreach ($values as $k => $value) {
                $internalField = array_search($k,$this->_fieldsInternal);
                if ($internalField && !in_array($internalField,$this->_protectedFields)) {
                    $k     = $this->dbh->quoteIdentifier($k);
                    $value = $this->dbh->quoteSmart($value);
                    $r[] = "$k = $value";
                }
            }
        } else {
            foreach ($values as $k => $value) {
                $internalField = array_search($k,$this->_fieldsInternal);
                if ($internalField && !in_array($internalField,$this->_protectedFields)) {
                    $r[$k] = $value;
                }
            }
        }
        return $r;
    }

    /**
     * DB_NestedSet_DB factory. Called only by wrapper methods.
     * Several methods (createRootNode() and moveTree()) when called from
     * DB_NestedSet directly take constants as parameters, but since DB_NestedSet
     * isn't instantiated at call time, we pass the constants' values instead.
     * Values for $pos are: 'BE' (move node before target norder),
     * 'AF' (move node after target norder), or 'SUB' (move node beneath, as a child
     * to target). See PEAR DB_NestedSet docs for api info on wrapper methods.
     *
     * @access private
     * @param
     * @return object DB_NestedSet_DB
     *
     * @author  Andy Crain <andy@newslogic.com>
     */
    function _getNestedSet()
    {
        static $ns;
        if (is_null($ns)) {
            require_once 'DB/NestedSet.php';

            // we copy connection object instead of passing it
            // to DB_NestedSet::factory(), because DB_NestedSet
            // changes DB_Common::setFetchMode()
            $db = SGL_DB::singleton();
            if (SGL::isPhp5()) {
                // use `clone($db)` instead of `clone $db`
                // to aviod php4 parse error
                $dbh = clone($db);
            } else {
                $dbh = $db;
            }

            //  create an instance of DB_NestedSet_DB
            $ns = & DB_NestedSet::factory('DB', $dbh, $this->_params['tableStructure']);
            if (is_a($ns, 'PEAR_Error')) {
                echo $ns->getCode() . ': ' . $ns->getMessage();
            }
            $ns->setAttr(array(
                    'node_table' => $this->_tableName,
                    'lock_table' => $this->_params['lockTableName'],
                    'sequence_table' => $this->_params['sequenceName'])
            );
        }
        return $ns;
    }

    /**
     * The following are all just wrappers to DB_NestedSet, an instance of which is
     * returned by _getNestedSet(). See PEAR/DB/NestedSet.php for API docs.
     */

    function createRootNode($values, $id = false, $first = false, $pos = 'AF')
    {
        $ns = $this->_getNestedSet();
        return $ns->createRootNode($values, $id, $first, $pos);
    }

    function createSubNode($id, $values)
    {
        $this->_cleanMemoryCache($id);

        $ns = $this->_getNestedSet();
        return $ns->createSubNode($id, $values);
    }

    function createLeftNode($id, $values)
    {
        $this->_cleanMemoryCache($id);

        $ns = $this->_getNestedSet();
        return $ns->createLeftNode($id, $values);
    }

    function createRightNode($id, $values)
    {
        $this->_cleanMemoryCache($id);

        $ns = $this->_getNestedSet();
        return $ns->createRightNode($id, $values);
    }

    function moveTree($id, $targetid, $pos, $copy = false)
    {
        $this->_cleanMemoryCache();

        $ns = $this->_getNestedSet();
        return $ns->moveTree($id, $targetid, $pos, $copy);
    }

    function deleteNode($id)
    {
        $this->_cleanMemoryCache($id);

        $ns = $this->_getNestedSet();
        return $ns->deleteNode($id);
    }

    /**
     * Clean nodes stored in memory
     * @param integer node id [optional]
     * @access private
     */
    function _cleanMemoryCache($id = null)
    {
        if (is_null($id)) {
            //clear all nodes
            $this->_aNodes = array();
        } elseif (array_key_exists($id, $this->_aNodes)) {
            //clear the given node
            unset($this->_aNodes[$id]);
        }
    }
}
?>