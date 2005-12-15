<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// $Id: convertCategories.php,v 1.2 2005/05/09 23:33:39 demian Exp $

#WARNING: this way of initialising the app is deprecated, if you need to use this script pls contact the mailing list
require_once '../init.php';

error_reporting(E_ALL);

require_once SGL_CORE_DIR . '/NestedSet.php';
require_once SGL_CORE_DIR . '/Category.php';

require_once 'DB/DataObject.php';

// check if updated
$dbh = &SGL_DB::singleton();
$result = $dbh->getOne("SELECT COUNT(*) FROM category_nested");
if ($result) {
    echo 'already updated';
    exit;
}
$tree = &createFromSQL();

$catTree = new SGL_Category();
$nestedSet = new SGL_NestedSet($catTree->_params);

$root = $tree->nodes->nodes[0];

$values = array();
$values['category_id'] = $root->tag['id'];
$values['label'] = $root->tag['label'];
$values['perms'] = $root->tag['perms'];
$values['parent_id'] = 0;

// add root node
$nodeId = $nestedSet->createRootNode($values, false, $reinitTree = true);
updateCategoryId($values['category_id'], $nodeId);

// add subnodes
addToNestedSet($nestedSet, $root->nodes->nodes, $nodeId);

echo 'categories imported to category_nested';

/**
* recursion function which add all subnodes
*
*/
function addToNestedSet(&$nestedSet, &$nodes, $parentId)
{
    foreach($nodes as $node) {
        $values = array();
        $values['category_id'] = $node->tag['id'];
        $values['label'] = $node->tag['label'];
        $values['perms'] = $node->tag['perms'];
        $values['parent_id'] = $parentId;
        
        $nodeId = $nestedSet->createSubNode($values['parent_id'], $values);
        // update foreign category_id's
        updateCategoryId($values['category_id'], $nodeId);
        
        if (!empty($node->nodes->nodes)) {
            addToNestedSet($nestedSet, $node->nodes->nodes, $nodeId);
        }
    }
}

/**
* update category_ids in document and item tables
*
*/
function updateCategoryId($oldCategoryId, $newCategoryId)
{
    $dbh = &SGL_DB::singleton();

    $c = &SGL_Config::singleton();
    $conf = $c->getAll();
    
    // update documents
    $query = 'UPDATE ' . $conf['table']['document'] . ' SET category_id = '
        . $newCategoryId . ' WHERE category_id = ' . $oldCategoryId;
    $dbh->query($query);
    
    // update items
    $query = 'UPDATE item SET category_id = '
        . $newCategoryId . ' WHERE category_id = ' . $oldCategoryId;
    $dbh->query($query);
}

/**
* SGL_Category::createFromSQL doesn't load perms but this function does
*
*/
function &createFromSQL()
{
    require_once 'HTML/Tree.php';
    // get db
    $dbh = &SGL_DB::singleton();
    $c = &SGL_Config::singleton();
    $conf = $c->getAll();
    $query = 'SELECT  category_id as id, parent AS parent_id, label, perms
                        FROM category
                        ORDER BY parent_id, category_id';
    $tree     = &new Tree();
    $nodeList = array();

    // Perform query
    if ($result = $dbh->query($query)) {
        // Loop thru results
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            // Parent id is 0, thus root node.
            if (!$row['parent_id']) {
                unset($row['parent_id']);
                $nodeList[$row['id']] = &new Tree_Node($row);
                $tree->nodes->addNode($nodeList[$row['id']]);
             
            // Parent node has already been added to tree
            } elseif (!empty($nodeList[$row['parent_id']])) {
                $parentNode = &$nodeList[$row['parent_id']];
                unset($row['parent_id']);
                $nodeList[$row['id']] = &new Tree_Node($row);
                $parentNode->nodes->addNode($nodeList[$row['id']]);
                   
            } else {
                // Orphan node ?
            }
        }
    }
    return $tree;
}

?>
