<?php
require_once dirname(__FILE__) . '/../DB.php';

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
    }
    
    function testSingleton()
    {
    	$dsn = 'mysql_SGL://root:@tcp+127.0.0.1:3306/seagull';
		$dbh1 = & SGL_DB::singleton($dsn);
		$dbh2 = & SGL_DB::singleton($dsn);
        $this->assertReference($dbh1, $dbh2);
    }
    
    function testDataObjectCopy()
    {
    	$dsn = 'mysql_SGL://root:@tcp+127.0.0.1:3306/seagull';
		$dbh1 = & SGL_DB::singleton($dsn);
		$this->assertIsA($dbh1, 'db_mysql_sgl');
		
    	require_once 'DB/DataObject.php';
    	$dbdo = DB_DataObject::factory('module');
    	$dbh2 = & $dbdo->getDatabaseConnection();
    	$dbh2->setFetchMode(DB_FETCHMODE_OBJECT);
    	$this->assertIsA($dbh2, 'db_mysql_sgl');
    }   
    
    function testDataObjectRef()
    {
    	$dsn = 'mysql_SGL://root:@tcp+127.0.0.1:3306/seagull';
		$dbh1 = & SGL_DB::singleton($dsn);
		
    	require_once 'DB/DataObject.php';
    	$dbdo = DB_DataObject::factory('module');
    	$tmp = & $dbdo->getDatabaseConnection();
		$dbh2 = & SGL_DB::singleton($dsn, $tmp);
    	
        $this->assertReference($dbh1, $dbh2);
    }      
}

?>