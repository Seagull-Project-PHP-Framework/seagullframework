<?php
require_once SGL_MOD_DIR . '/navigation/classes/NavigationDAO.php';

/**
 * Test suite.
 *
 * @package user
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UserDAOTest.wdb.php,v 1.1 2005/06/23 15:18:06 demian Exp $
 */
class NavigationDAOTest extends UnitTestCase {

    function NavigationDAOTest()
    {
        $this->UnitTestCase('NavigationDAO Test');
    }

    function setup()
    {
        $this->da = & NavigationDAO::singleton($forceNew = true);
    }

    function testAddSection()
    {
        $section = array (
          'title' => 'test section',
          'parent_id' => '4',
          'uriType' => 'dynamic',
          'module' => 'block',
          'manager' => 'BlockMgr.php',
          'actionMapping' => '', // eg: edit
          'add_params' => '',    // eg: frmArticleID/23
          'is_enabled' => 1,
          'perms' => SGL_ADMIN,        // role id, eg: 1 for admin
            );
        $ok = $this->da->addSimpleSection($section);
    }

    function testGetAllAliases()
    {
        $aSection = array(
              'title' => 'test section',
              'parent_id' => SGL_NODE_ADMIN,
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => SGL_ADMIN,  // role id, eg: 1 for admin
                );
        $sectionId = $this->da->addSimpleSection($aSection);
        $id = $this->da->dbh->nextId('uri_alias');
        $this->assertTrue($this->da->addUriAlias($id, 'my_alias', $sectionId));
        $ret = $this->da->getAllAliases();
        $this->assertTrue(is_array($ret));
        $this->assertTrue(count($ret));
    }

    function testIsUriAliasDuplicated()
    {
        /* data from previous method
        Array
        (
            [my_alias] => stdClass Object
                (
                    [uri_alias] => my_alias
                    [resource_uri] => block/block
                    [section_id] => 69
                )

        )*/

        $this->assertTrue($this->da->isUriAliasDuplicated('my_alias', $sectionId = null));
        $this->assertTrue($this->da->isUriAliasDuplicated('my_alias', $sectionId = 32));//non-existant id
    }

    function xtestAddingSectionsWithSubs()
    {
        $aSections = array(
            array (
              'title' => 'test section',
              'parent_id' => '4',
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => '1',        // role id, eg: 1 for admin
                ),
            array (
              'title' => 'test child section',
              'parent_id' => 'CHILD',
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => '1',        // role id, eg: 1 for admin
                ),
            );
            $task = new SGL_Task_BuildNavigation();
            $ok = $task->run(array());

    }
}
//define('SGL_NODE_ADMIN', 4);
//define('SGL_NODE_GROUP', 1);

/*
builds
 - admin level, #1
    - child 1
    - child 2
 - admin level, #2
*/
class xSGL_Task_BuildNavigation extends SGL_Task
{
    var $groupId = null;
    var $childId = null;

    function run($data)
    {
        $aSections = array(
            array (
              'title' => 'test section',
              'parent_id' => SGL_NODE_ADMIN,
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => SGL_ADMIN,  // role id, eg: 1 for admin
                ),
            array (
              'title' => 'test child section',
              'parent_id' => SGL_NODE_GROUP,
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => SGL_ADMIN,  // role id, eg: 1 for admin
                ),
            array (
              'title' => 'test sibling section',
              'parent_id' => SGL_NODE_GROUP,
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => SGL_ADMIN,  // role id, eg: 1 for admin
                ),
            array (
              'title' => 'next test section',
              'parent_id' => SGL_NODE_ADMIN,
              'uriType' => 'dynamic',
              'module' => 'block',
              'manager' => 'BlockMgr.php',
              'actionMapping' => '', // eg: edit
              'add_params' => '',    // eg: frmArticleID/23
              'is_enabled' => 1,
              'perms' => SGL_ADMIN,  // role id, eg: 1 for admin
                ),
            );

#        require_once SGL_MOD_DIR . '/navigation/classes/NavigationDAO.php';
        $da = & NavigationDAO::singleton();
//        $aModuleList = SGL_Util::getAllModuleDirs($onlyRegistered = true);

        #foreach ($aModuleList as $module) {
        #    $navigationPath = SGL_MOD_DIR . '/' . $module  . '/data/navigation.php';
        #    if (file_exists($navigationPath)) {
        #        require_once $navigationPath;
                foreach ($aSections as $aSection) {

                    //  check if section is designated as child to last insert
                    if ($aSection['parent_id'] == SGL_NODE_GROUP) {
                        $aSection['parent_id'] = $this->groupId;
                    } else {
                        $aSection['parent_id'] = SGL_NODE_ADMIN;
                    }
                    $id = $da->addSimpleSection($aSection);
                    if (!PEAR::isError($id)) {
                        if ($aSection['parent_id'] == SGL_NODE_ADMIN) {
                            $this->groupId = $id;
                        } else {
                            $this->childId = $id;
                        }
                    }
                }
         #   }
        #}

//        if (PEAR::isError($ok)) {
//            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
//        }
    }
}
?>