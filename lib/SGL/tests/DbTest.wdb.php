<?php
require_once dirname(__FILE__) . '/../DB.php';
require_once dirname(__FILE__) . '/../../../modules/user/classes/UserDAO.php';

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

    function xtestDataObjectRef()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh1 = $locator->get('DB');
        SGL_DB::setConnection();

        require_once 'DB/DataObject.php';
        $dbdo = DB_DataObject::factory($this->conf['table']['module']);
        $dbh2 = $dbdo->getDatabaseConnection();

        $this->assertReference($dbh1, $dbh2);
    }

    function testGetDsnArray()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_ARRAY);
        $expected = array (
          'phptype' => 'mysql_SGL',
          'username' => 'root',
          'password' => '',
          'protocol' => 'tcp',
          'socket' => false,
          'hostspec' => 'localhost',
          'port' => false,
          'database' => 'simpletest',
        );
        $this->assertEqual($dsn, $expected);
    }

    function testGetDsnArrayWithoutDb()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_ARRAY, true);
        $expected = array (
          'phptype' => 'mysql_SGL',
          'username' => 'root',
          'password' => '',
          'protocol' => 'tcp',
          'socket' => false,
          'hostspec' => 'localhost',
          'port' => false,
        );
        $this->assertEqual($dsn, $expected);
    }

    function testGetDsnString()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_STRING);
        $expected = 'mysql_SGL://root:@tcp+localhost/simpletest';
        $this->assertEqual($dsn, $expected);
    }

    function testGetDsnStringWithoutDb()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_STRING, true);
        $expected = 'mysql_SGL://root:@tcp+localhost';
        $this->assertEqual($dsn, $expected);
    }
}

?>