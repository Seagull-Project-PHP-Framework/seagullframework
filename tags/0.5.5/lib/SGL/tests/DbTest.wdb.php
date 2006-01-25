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

    function xtestDataObjectRef()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh1 = $locator->get('DB');
		SGL_DB::setConnection($dbh1);

    	require_once 'DB/DataObject.php';
    	$dbdo = DB_DataObject::factory($this->conf['table']['module']);
    	$dbh2 = $dbdo->getDatabaseConnection();

        $this->assertReference($dbh1, $dbh2);
    }
}

?>