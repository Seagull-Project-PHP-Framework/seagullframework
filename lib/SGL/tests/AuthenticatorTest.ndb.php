<?php
require_once dirname(__FILE__) . '/../Authenticator.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class AuthenticatorTest extends UnitTestCase {

    function AuthenticatorTest()
    {
        $this->UnitTestCase('Authenticator Test');
    }

    function test1()
    {
        $driver = 'File';
        $aOptions = array('filename' => dirname(__FILE__) .'/../tests/testPasswdFile.php');
        $a = new SGL_Authenticator($driver, $aOptions);
        $this->assertTrue($a->authenticate('testUser', 'passwd'));
    }

    function xtest()
    {

    }
}

?>