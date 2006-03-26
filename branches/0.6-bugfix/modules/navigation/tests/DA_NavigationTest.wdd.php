<?php
require_once SGL_MOD_DIR . '/navigation/classes/DA_Navigation.php';

/**
 * Test suite.
 *
 * @package user
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: DA_UserTest.wdb.php,v 1.1 2005/06/23 15:18:06 demian Exp $
 */
class DA_NavigationTest extends UnitTestCase {

    function DA_NavigationTest()
    {
        $this->UnitTestCase('DA_Navigation Test');
    }

    function setup()
    {
        $this->da = & DA_Navigation::singleton();
    }

    function xtestAddSection()
    {
        $section = array();
        $ok = $this->da->addSection($section);
    }
}
?>