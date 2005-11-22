<?php
require_once dirname(__FILE__) . '/../DB.php';
require_once dirname(__FILE__) . '/../../../modules/user/classes/DA_User.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class DbTest extends UnitTestCase {

    function DbTest()
    {
        $this->UnitTestCase('DB Test');
        $this->dsn = 'mysql_SGL://root:@unix+localhost/seagull';
    }

    function testSingleton()
    {
		$dbh1 = & SGL_DB::singleton($this->dsn);
		$dbh2 = & SGL_DB::singleton($this->dsn);
        $this->assertReference($dbh1, $dbh2);
    }

    function testDataObjectRef()
    {
		$dbh1 = & SGL_DB::singleton($this->dsn);

    	require_once 'DB/DataObject.php';
    	$dbdo = DB_DataObject::factory('module');
    	$tmp = & $dbdo->getDatabaseConnection();
		$dbh2 = & SGL_DB::singleton($this->dsn, $tmp);

        $this->assertReference($dbh1, $dbh2);
    }

    function testDataAccessRef()
    {
        $dbh1 = & SGL_DB::singleton($this->dsn);

        $oUser = DB_DataObject::factory('Usr');
        $oUser->get(1);
        $tmp = $oUser->getDatabaseConnection();
        $dbh = &SGL_DB::singleton(null, $tmp);
        $this->assertReference($dbh1, $dbh);
    }

    function testSetConnection()
    {
        $dbh = & SGL_DB::singleton();

        $oUser = DB_DataObject::factory('Usr');
        $tmp = $oUser->getDatabaseConnection();
        SGL_DB::setConnection($tmp);


        $this->assertReference($tmp, $dbh);
    }
}

?>