<?php

/**
 * A script to generate the tree menu for the test suite.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */

require_once 'init.php';
require_once 'classes/Menu.php';


//  replace test db resource link with live link to enabled modules can be detected
$locator = SGL_ServiceLocator::singleton();
$locator->remove('MDB2');
$dbh = SGL_DB::singleton();
$locator->register('MDB2', $dbh);

// Output the menu of tests
echo STR_Menu::buildTree();

//  reinstate test db resource link
$locator = SGL_ServiceLocator::singleton();
$locator->remove('MDB2');
$dbh = STR_DB::singleton();
$locator->register('MDB2', $dbh);

?>