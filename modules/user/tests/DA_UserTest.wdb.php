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
        
        //  get DA_User object
        require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
        $this->da = & DA_User::singleton();
    }

    function xtestAddMasterPrefs()
    {
        $aPrefs = array('foo' => 'bar', 'baz' => 'fluux');
        $ret = $this->da->addMasterPrefs($aPrefs);
        $this->assertTrue($ret);
    }
    
    function xtestDeleteMasterPrefs()
    {
        $aPrefs = array('foo', 'baz');
        $ret = $this->da->deleteMasterPrefs($aPrefs);
        $this->assertTrue($ret);
    }
    
    function testAddMasterPerms()
    {
        $moduleId = 33;
        
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $aPerms = array($oPassword->create() => 'first description', $oPassword->create() => 'second description');
        $ret = $this->da->addMasterPerms($aPerms, $moduleId);
        $this->assertTrue($ret);
    }
}
?>