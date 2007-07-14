<?php
require_once dirname(__FILE__). '/../classes/UserMgr.php';
require_once dirname(__FILE__). '/../classes/LoginMgr.php';

/**
 * Test suite.
 *
 * @package user
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: TestLoginMgr.php,v 1.3 2005/05/08 21:29:06 demian Exp $
 */
class TestLoginMgr extends UnitTestCase {

    function TestLoginMgr()
    {
        $this->UnitTestCase('LoginMgr Test');
    }

    function testLogin()
    {
        //  create random username/password, add test user, test login

        //  create unique username and passwd
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $username = $oPassword->create();
        $passwd = $oPassword->create();

        $oInput = new stdClass();
        $oOutput = new stdClass();

        //  build user object
        $oUser = new stdClass();
        $oUser->organisation_id = 1;
        $oUser->role_id = 2;
        $oUser->username = $username;
        $oUser->passwd = $passwd;
        $oUser->first_name = 'test';
        $oUser->last_name = 'user';
        $oUser->email = 'test@example.com';
        $oUser->is_acct_active = 1;

        //  assign to input object
        $oInput->user = $oUser;

        $userMgr = new UserMgr();
        $userMgr->_cmd_insert($oInput, $oOutput);

        //  test login with new user details
        $oInput = new stdClass();
        $oOutput = new stdClass();
        $doLogin = new User_DoLogin($oInput, $oOutput);
        $doLogin->dbh = $doLogin->_getDb();
        $c = &SGL_Config::singleton();
        $doLogin->conf = $c->getAll();
        $res = $doLogin->_doLogin($username, $passwd, false);
        $this->assertTrue(is_array($res));
        $this->assertTrue(count($res));
    }
}
?>