<?php
require_once dirname(__FILE__). '/../classes/PermissionMgr.php';

class TestPermissionMgr extends UnitTestCase {

    function TestPermissionMgr()
    {
        $this->UnitTestCase('PermissionMgr Test');
    }

    function testRetrievePermsFromFile()
    {
        // copy fake files to default module
        require_once 'Text/Password.php';
        $randomizer =& new Text_Password();
        
        // create classname prefix to prevent possible overwriting
        $classPrefix = $randomizer->create(5, 'unpronouncable', 'alphabetic');
        $classPrefix = ucfirst($classPrefix);
        
        // copy fake files to user module
        copy(SGL_MOD_DIR . "/user/tests/files/TestFake.php",
            SGL_MOD_DIR . "/user/classes/{$classPrefix}TestFake.php");
        copy(SGL_MOD_DIR . "/user/tests/files/TestFakeMgr.php",
            SGL_MOD_DIR . "/user/classes/{$classPrefix}TestFakeMgr.php");
            
        $permMgr = new PermissionMgr();
        
        // find out user module id
        $query = "
            SELECT  module_id
            FROM    {$permMgr->conf['table']['module']}
            WHERE   name = 'user'";
        $moduleId = $permMgr->dbh->getOne($query);
        
        // get array of file perms
        $aFilePerms = $permMgr->retrievePermsFromFiles();
        
        // check if class perm of TestFakeMgr exists
        $reqClassName = strtolower($classPrefix . 'TestFakeMgr');
        $aReqFakeClassPerm = array(
            'perm' => $reqClassName,
            'module_id' => $moduleId,
            'module_name' => 'user');
        $this->assertTrue(in_array($aReqFakeClassPerm, $aFilePerms));
        
        // check if method perms exist
        $aReqFakeMethodPerm = array(
            'perm' => $reqClassName . '_cmd_requiredMethod',
            'module_id' => $moduleId,
            'module_name' => 'user');
        $this->assertTrue(in_array($aReqFakeMethodPerm, $aFilePerms));
        
        $aReqFakeMethodPerm = array(
            'perm' => $reqClassName . '_cmd_foo',
            'module_id' => $moduleId,
            'module_name' => 'user');
        $this->assertTrue(in_array($aReqFakeMethodPerm, $aFilePerms));
        
        // check if class perm of TestFake doesn't exist
        $forbClassName = strtolower($classPrefix . 'TestFake');
        $aForbFakeClassPerm = array(
            'perm' => $forbClassName,
            'module_id' => $moduleId,
            'module_name' => 'user');
        $this->assertFalse(in_array($aForbFakeClassPerm, $aFilePerms));
        
        // check if method perms don't exist
        $aForbFakeMethodPerm = array(
            'perm' => $forbClassName . '_cmd_disallowedMethod',
            'module_id' => $moduleId,
            'module_name' => 'user');
        $this->assertFalse(in_array($aForbFakeMethodPerm, $aFilePerms));
        
        $aForbFakeMethodPerm = array(
            'perm' => $forbClassName . '_cmd_disallowedFoo',
            'module_id' => $moduleId,
            'module_name' => 'user');
        $this->assertFalse(in_array($aForbFakeMethodPerm, $aFilePerms));
        
        // delete fake files
        unlink(SGL_MOD_DIR . "/user/classes/{$classPrefix}TestFake.php");
        unlink(SGL_MOD_DIR . "/user/classes/{$classPrefix}TestFakeMgr.php");
    }
}

?>