<?php

/**
 * A script to generate the tree menu for the test suite.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */

require_once 'init.php';
require_once 'classes/Menu.php';

// Output the menu of tests
echo STR_Menu::buildTree();

?>