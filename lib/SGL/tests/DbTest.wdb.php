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
        $this->testconf = parse_ini_file(dirname(__FILE__) . '/../../../var/test.conf.ini.php',true);
        if ($this->testconf['db']['type'] == 'pgsql'){
            $excludeDbName = false;
        } else {
            $excludeDbName = true;
        }
        $dsn = SGL_DB::_getDsnAsString($this->testconf,$excludeDbName);
        $this->dsn = $dsn;
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
          'phptype' => $this->testconf['db']['type'],
          'username' => $this->testconf['db']['user'],
          'password' => $this->testconf['db']['pass'],
          'protocol' => $this->testconf['db']['protocol'],
          'socket' => false,
          'hostspec' => $this->testconf['db']['host'],
          'port' => $this->testconf['db']['port'],
          'database' => $this->testconf['db']['name'],
        );
        $this->assertEqual($dsn, $expected);
    }

    function testGetDsnArrayWithoutDb()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_ARRAY, true);
        $expected = array (
          'phptype' => $this->testconf['db']['type'],
          'username' => $this->testconf['db']['user'],
          'password' => $this->testconf['db']['pass'],
          'protocol' => $this->testconf['db']['protocol'],
          'socket' => false,
          'hostspec' => $this->testconf['db']['host'],
          'port' => $this->testconf['db']['port'],
        );
        $this->assertEqual($dsn, $expected);
    }

    function testGetDsnString()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_STRING);
        $expected = $this->testconf['db']['type'].'://'.$this->testconf['db']['user'].':'.$this->testconf['db']['pass'].'@'.$this->testconf['db']['protocol'].'+'.$this->testconf['db']['host'].':'.$this->testconf['db']['port'].'/'.$this->testconf['db']['name'];
        $this->assertEqual($dsn, $expected);
    }

    function testGetDsnStringWithoutDb()
    {
        $dbh = & SGL_DB::singleton($this->dsn);
        $dsn = SGL_DB::getDsn(SGL_DSN_STRING, true);
        $expected = $this->testconf['db']['type'].'://'.$this->testconf['db']['user'].':'.$this->testconf['db']['pass'].'@'.$this->testconf['db']['protocol'].'+'.$this->testconf['db']['host'].':'.$this->testconf['db']['port'];
        $this->assertEqual($dsn, $expected);
    }
}

?>