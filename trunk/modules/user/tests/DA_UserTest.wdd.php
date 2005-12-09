<?php
require_once dirname(__FILE__). '/../classes/UserMgr.php';
require_once dirname(__FILE__). '/../classes/LoginMgr.php';

/**
 * Test suite.
 *
 * @package user
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: DA_UserTest.wdb.php,v 1.1 2005/06/23 15:18:06 demian Exp $
 */  
class DA_UserTest extends UnitTestCase {

    function DA_UserTest()
    {
        $this->UnitTestCase('DA_User Test');
    }
    
    function setup()
    {
        //  get DA_User object
        require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
        $this->da = & DA_User::singleton();   
    }
    
    
    //  //////////////////////////////////////////////////
    //  /////////////////   PERMS   //////////////////////
    //  //////////////////////////////////////////////////    
    
    function testAddMasterPerms()
    {
        $moduleId = 33;
        
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $aPerms = array(
        	$oPassword->create() => 'first description', 
        	$oPassword->create() => 'second description');
		$countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM permission');        	
        $ret = $this->da->addMasterPerms($aPerms, $moduleId);
        $this->assertTrue($ret);
		$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM permission');
		$this->assertEqual($countPre + 2, $countPost);
    }
    
    function testDeleteOrphanedPerms()
    { 
    	$ret = $this->da->addMasterPerms(array('perm_name' => 'desc'), 87);
    	$countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM permission'); 		
		$ret = $this->da->deleteOrphanedPerms(array(1 => 'perm_name^87'));	
		$this->assertTrue($ret); 				
		$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM permission');   	
		$this->assertEqual($countPre - 1, $countPost);			
		
    }
    
    function testDeleteMasterPerms()
    {
    	$countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM permission'); 
    	$permName = $this->da->dbh->getOne('SELECT MAX(name) FROM permission');
    	$ret = $this->da->deleteMasterPerms(array($permName));
        $this->assertTrue($ret);    	
    	$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM permission');
    	$this->assertEqual($countPre - 1, $countPost);
    }
    
    function testAddPermsByUserId()
    {
    	$countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM user_permission'); 
    	$aPerms = range(0, 42);
    	$ret = $this->da->addPermsByUserId($aPerms, 2);
        $this->assertTrue($ret);    	
    	$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM user_permission');
    	$this->assertEqual($countPre + 43, $countPost);    	
    }
    
    function testGetPermsByUserId()
    {
    	$ret = $this->da->getPermsByUserId(1);
    	$this->assertEqual(array(), $ret); //	admin has no individual perms
    }
    
    function testGetPermNamesByRoleId()
    {
    	$ret = $this->da->getPermNamesByRoleId(2);	
    	$expected = array (
		  14 => 'bugmgr',
		  13 => 'defaultmgr_list',
		  80 => 'accountmgr',
		  29 => 'accountmgr_edit',
		  32 => 'accountmgr_summary',
		  30 => 'accountmgr_update',
		  31 => 'accountmgr_viewProfile',
		  81 => 'loginmgr',
		  34 => 'loginmgr_list',
		  33 => 'loginmgr_login',
		  82 => 'loginmgr_logout',
		  41 => 'passwordmgr_edit',
		  103 => 'passwordmgr_redirectToEdit',
		  42 => 'passwordmgr_update',
		  53 => 'preferencemgr_edit',
		  54 => 'preferencemgr_update',
		  57 => 'profilemgr_view',
		  58 => 'registermgr_add',
		  59 => 'registermgr_insert',
		  78 => 'userpreferencemgr_editAll',
		  79 => 'userpreferencemgr_updateAll',
		);
    	
    	$this->assertEqual($ret, $expected);
    }
    
    function testgetPermsByRoleId()
    {
    	$ret = $this->da->getPermsByRoleId();
    	$expected = array(
		    0 => 14,
		    1 => 13,
		    2 => 34,
		    3 => 33,
		    4 => 44,
		    5 => 43,
		    6 => 57,
		    7 => 58,
		    8 => 59,
		    9 => 74,
		);	
    	$this->assertEqual($ret, $expected);		
    }
    
    function testGetPermsByModuleIdRetArray()
    {
    	$ret = $this->da->getPermsByModuleId(3, SGL_RET_ARRAY);
		$expected = array (
		  0 => 
		  array (
		    'permission_id' => '119',
		    'name' => 'blockmgr',
		    'module_name' => 'block',
		    'module_id' => '3',
		  ),
		  1 => 
		  array (
		    'permission_id' => '120',
		    'name' => 'blockmgr_add',
		    'module_name' => 'block',
		    'module_id' => '3',
		  ),
		  2 => 
		  array (
		    'permission_id' => '122',
		    'name' => 'blockmgr_delete',
		    'module_name' => 'block',
		    'module_id' => '3',
		  ),
		  3 => 
		  array (
		    'permission_id' => '121',
		    'name' => 'blockmgr_edit',
		    'module_name' => 'block',
		    'module_id' => '3',
		  ),
		  4 => 
		  array (
		    'permission_id' => '124',
		    'name' => 'blockmgr_list',
		    'module_name' => 'block',
		    'module_id' => '3',
		  ),
		  5 => 
		  array (
		    'permission_id' => '123',
		    'name' => 'blockmgr_reorder',
		    'module_name' => 'block',
		    'module_id' => '3',
		  ),
		);
    	$this->assertEqual($expected, $ret);    	
    }
    
    function testGetPermsByModuleIdRetIdValue()
    {
    	$ret = $this->da->getPermsByModuleId(3);
    	$expected = array (
		  119 => 'blockmgr',
		  120 => 'blockmgr_add',
		  122 => 'blockmgr_delete',
		  121 => 'blockmgr_edit',
		  124 => 'blockmgr_list',
		  123 => 'blockmgr_reorder',
		);
    	$this->assertEqual($expected, $ret);    	
    }    
    
    function testDeletePermByUserIdAndPermId()
    {
    	$countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM user_permission'); 		
		$ret = $this->da->deletePermByUserIdAndPermId(2, 42);	
		$this->assertTrue($ret); 				
		$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM user_permission');   	
		$this->assertEqual($countPre - 1, $countPost);		    	
    }
        
    function testDeletePermsByUserId()
    {
    	$countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM user_permission'); 		
		$ret = $this->da->deletePermsByUserId(2);	
		$this->assertTrue($ret); 				
		$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM user_permission');   	
		$this->assertEqual($countPre - 42, $countPost);		    	
    }
    
    function testGetRemainingPerms()
    {
        $aRolePerms = $this->da->getPermNamesByRoleId(2);
        $aRemainingPerms = $this->da->getPermsNotInRole($aRolePerms);    
		$this->assertEqual(count($aRemainingPerms), 104);
    }
    
    //  //////////////////////////////////////////////////
    //  /////////////////   PREFS   //////////////////////
    //  //////////////////////////////////////////////////  
    
    function testAddMasterPrefs()
    {
        $aPrefs = array('foo' => 'bar', 'baz' => 'fluux');
        $countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM preference');  
        $ret = $this->da->addMasterPrefs($aPrefs);
        $this->assertTrue($ret);
		$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM preference');
		$this->assertEqual($countPre + 2, $countPost);        
    }
    
    function testDeleteMasterPrefs()
    {
        $aPrefs = array('foo', 'baz');
        $countPre = $this->da->dbh->getOne('SELECT COUNT(*) FROM preference'); 
        $ret = $this->da->deleteMasterPrefs($aPrefs);
        $this->assertTrue($ret);
		$countPost = $this->da->dbh->getOne('SELECT COUNT(*) FROM preference');
		$this->assertEqual($countPre - 2, $countPost);          
    }

    function testGetUserPrefsByOrgIdRetIdValue()
    {
    	//	no org data
    }   
    
    function testGetUserPrefsByOrgIdRetNameValue()
    {
    	
    	//	no org data
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
    
    function testGetPrefsMapping()
    {
    	
    }
}
















?>