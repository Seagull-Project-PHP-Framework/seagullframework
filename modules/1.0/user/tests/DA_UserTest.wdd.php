<?php
require_once dirname(__FILE__). '/../classes/UserMgr.php';
require_once dirname(__FILE__). '/../classes/LoginMgr.php';

/**
 * Test suite.
 *
 * @package user
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UserDAOTest.wdb.php,v 1.1 2005/06/23 15:18:06 demian Exp $
 */
class UserDAOTest extends UnitTestCase {

    function UserDAOTest()
    {
        $this->UnitTestCase('UserDAO Test');
    }

    function setup()
    {
        //  get UserDAO object
        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $this->da = & UserDAO::singleton();
    }


    //  //////////////////////////////////////////////////
    //  /////////////////   PERMS   //////////////////////
    //  //////////////////////////////////////////////////

    function testAddMasterPerms()
    {
        $conf     = $this->da->conf;
        $moduleId = 33;

        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $aPerms = array(
            $oPassword->create() => 'first description',
            $oPassword->create() => 'second description');
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['permission'];
        $countPre = $this->da->dbh->getOne($query);
        $ret = $this->da->addMasterPerms($aPerms, $moduleId);
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre + 2, $countPost);
    }

    function testDeleteOrphanedPerms()
    {
        $conf  = $this->da->conf;
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['permission'];

        $ret = $this->da->addMasterPerms(array('perm_name' => 'desc'), 87);
        $countPre = $this->da->dbh->getOne($query);
        $ret = $this->da->deleteOrphanedPerms(array(1 => 'perm_name^87'));
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre - 1, $countPost);

    }

    function testDeleteMasterPerms()
    {
        $conf       = $this->da->conf;
        $countQuery = 'SELECT COUNT(*) FROM ' . $conf['table']['permission'];
        $maxQuery   = 'SELECT MAX(name) FROM ' . $conf['table']['permission'];

        $countPre = $this->da->dbh->getOne($countQuery);
        $permName = $this->da->dbh->getOne($maxQuery);
        $ret = $this->da->deleteMasterPerms(array($permName));
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($countQuery);
        $this->assertEqual($countPre - 1, $countPost);
    }

    function testAddPermsByUserId()
    {
        $conf  = $this->da->conf;
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['user_permission'];

        $countPre = $this->da->dbh->getOne($query);
        $aPerms = range(0, 42);
        $ret = $this->da->addPermsByUserId($aPerms, 2);
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre + 43, $countPost);
    }

    function testGetPermsByUserId()
    {
        $ret = $this->da->getPermsByUserId(1);
        $this->assertEqual(array(), $ret); //   admin has no individual perms
    }

    function testGetPermNamesByRoleId()
    {
        $ret = $this->da->getPermNamesByRoleId(2);
        $expected = array (
          14 => 'bugmgr',
          13 => 'defaultmgr_cmd_list',
          85 => 'accountmgr',
          33 => 'accountmgr_cmd_edit',
          36 => 'accountmgr_cmd_summary',
          34 => 'accountmgr_cmd_update',
          35 => 'accountmgr_cmd_viewProfile',
          86 => 'loginmgr',
          38 => 'loginmgr_cmd_list',
          37 => 'loginmgr_cmd_login',
          87 => 'loginmgr_cmd_logout',
          48 => 'userpasswordmgr_cmd_edit',
          49 => 'userpasswordmgr_cmd_update',
          58 => 'preferencemgr_cmd_edit',
          59 => 'preferencemgr_cmd_update',
          62 => 'profilemgr_cmd_view',
          63 => 'registermgr_cmd_add',
          64 => 'registermgr_cmd_insert',
          83 => 'userpreferencemgr_cmd_editAll',
          84 => 'userpreferencemgr_cmd_updateAll',
        );

        $this->assertEqual($ret, $expected);
    }

    function testgetPermsByRoleId()
    {
        $ret = $this->da->getPermsByRoleId(2);
        $expected = array(
          0 => '14',
          1 => '13',
          2 => '85',
          3 => '33',
          4 => '36',
          5 => '34',
          6 => '35',
          7 => '86',
          8 => '38',
          9 => '37',
          10 => '87',
          11 => '48',
          12 => '49',
          13 => '58',
          14 => '59',
          15 => '62',
          16 => '63',
          17 => '64',
          18 => '83',
          19 => '84',
        );
        $this->assertEqual($ret, $expected);
    }

    function testGetPermsByModuleIdRetArray()
    {
        $ret = $this->da->getPermsByModuleId(4, SGL_RET_ARRAY);
        $expected = array (
         0 =>
          array (
            'permission_id' => '133',
            'name' => 'blockmgr',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          1 =>
          array (
            'permission_id' => '134',
            'name' => 'blockmgr_cmd_add',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          2 =>
          array (
            'permission_id' => '136',
            'name' => 'blockmgr_cmd_delete',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          3 =>
          array (
            'permission_id' => '135',
            'name' => 'blockmgr_cmd_edit',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          4 =>
          array (
            'permission_id' => '139',
            'name' => 'blockmgr_cmd_insert',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          5 =>
          array (
            'permission_id' => '138',
            'name' => 'blockmgr_cmd_list',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          6 =>
          array (
            'permission_id' => '137',
            'name' => 'blockmgr_cmd_reorder',
            'module_name' => 'block',
            'module_id' => '4',
          ),
          7 =>
          array (
            'permission_id' => '140',
            'name' => 'blockmgr_cmd_update',
            'module_name' => 'block',
            'module_id' => '4',
          ),
        );
        $this->assertEqual($expected, $ret);
    }

    function testGetPermsByModuleIdRetIdValue()
    {
        $ret = $this->da->getPermsByModuleId(3);
        $expected = array (
          122 => 'navstylemgr',
          123 => 'navstylemgr_cmd_changeStyle',
          124 => 'navstylemgr_cmd_list',
          125 => 'sectionmgr',
          126 => 'sectionmgr_cmd_add',
          130 => 'sectionmgr_cmd_delete',
          128 => 'sectionmgr_cmd_edit',
          127 => 'sectionmgr_cmd_insert',
          132 => 'sectionmgr_cmd_list',
          131 => 'sectionmgr_cmd_reorder',
          129 => 'sectionmgr_cmd_update',
        );
        $this->assertEqual($expected, $ret);
    }

    function testDeletePermByUserIdAndPermId()
    {
        $conf  = $this->da->conf;
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['user_permission'];

        $countPre = $this->da->dbh->getOne($query);
        $ret = $this->da->deletePermByUserIdAndPermId(2, 42);
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre - 1, $countPost);
    }

    function testDeletePermsByUserId()
    {
        $conf  = $this->da->conf;
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['user_permission'];

        $countPre = $this->da->dbh->getOne($query);
        $ret = $this->da->deletePermsByUserId(2);
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre - 42, $countPost);
    }

    function testGetRemainingPerms()
    {
        $aRolePerms = $this->da->getPermNamesByRoleId(SGL_MEMBER);
        $aRemainingPerms = $this->da->getPermsNotInRole($aRolePerms);
        $this->assertEqual(count($aRemainingPerms), 123);
    }

    //  //////////////////////////////////////////////////
    //  /////////////////   PREFS   //////////////////////
    //  //////////////////////////////////////////////////

    function testAddMasterPrefs()
    {
        $conf  = $this->da->conf;
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['preference'];

        $aPrefs = array('foo' => 'bar', 'baz' => 'fluux');
        $countPre = $this->da->dbh->getOne($query);
        $ret = $this->da->addMasterPrefs($aPrefs);
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre + 2, $countPost);
    }

    function testDeleteMasterPrefs()
    {
        $conf  = $this->da->conf;
        $query = 'SELECT COUNT(*) FROM ' . $conf['table']['preference'];

        $aPrefs = array('foo', 'baz');
        $countPre = $this->da->dbh->getOne($query);
        $ret = $this->da->deleteMasterPrefs($aPrefs);
        $this->assertTrue($ret);
        $countPost = $this->da->dbh->getOne($query);
        $this->assertEqual($countPre - 2, $countPost);
    }

    function testGetUserPrefsByOrgIdRetIdValue()
    {
        //  no org data
    }

    function testGetUserPrefsByOrgIdRetNameValue()
    {

        //  no org data
        #$ret = $this->da->getUserPrefsByOrgId();

    }

    function testGetPrefsByUserId()
    {
        $ret = $this->da->getPrefsByUserId(1);
        $expected = array (
          'sessionTimeout' => '1800',
          'timezone' => 'UTC',
          'theme' => 'default',
          'dateFormat' => 'UK',
          'language' => 'en-iso-8859-15',
          'resPerPage' => '10',
          'showExecutionTimes' => '1',
          'locale' => 'en_GB',
        );
        $this->assertEqual($ret, $expected);
    }

    function xtestGetPrefsMapping()
    {

    }

    function testGetMasterPrefsReturnByNameValue()
    {
        $ret = $this->da->getMasterPrefs(SGL_RET_NAME_VALUE);
        $expected = array (
          'sessionTimeout' => '1800',
          'timezone' => 'UTC',
          'theme' => 'default',
          'dateFormat' => 'UK',
          'language' => 'en-iso-8859-15',
          'resPerPage' => '10',
          'showExecutionTimes' => '1',
          'locale' => 'en_GB',
        );
        $this->assertEqual($ret, $expected);
    }

    function testGetMasterPrefsReturnByIdValue()
    {
        $ret = $this->da->getMasterPrefs(SGL_RET_ID_VALUE);
        $expected = array (
          1 => '1800',
          2 => 'UTC',
          3 => 'default',
          4 => 'UK',
          5 => 'en-iso-8859-15',
          6 => '10',
          7 => '1',
          8 => 'en_GB',
        );
        $this->assertEqual($ret, $expected);
    }
}
















?>