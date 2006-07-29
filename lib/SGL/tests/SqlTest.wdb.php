<?php
require_once dirname(__FILE__) . '/../Sql.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class SqlTest extends UnitTestCase {

    function ConfigTest()
    {
        $this->UnitTestCase('SQL Test');
    }

    function setup()
    {
        $this->sql = new SGL_Sql();
    }

    function tearDown()
    {
        unset($this->sql);
    }

    function testParseData()
    {
        $file = dirname(__FILE__) . '/test.data.sql';
        $ret = $this->sql->parse($file, E_ALL, array('SGL_Sql', 'execute'));
    }

    function testParseOutTablename()
    {
        $data = "INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'default', 'Default', 'The Default module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', NULL, 'default.png');";
        $pattern = '/^(INSERT INTO )(\w+)(.*);/i';
        preg_match($pattern, $data, $matches);
        $tableName = $matches[2];
        $this->assertEqual($tableName, 'module');
    }

    function testRewriteWithAutoIncrement()
    {
        $data = "INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'default', 'Default', 'The foo bar.', NULL, 'default.png');";
        $res = $this->sql->rewriteWithAutoIncrement($data, 23);
        $this->assertEqual($res, "INSERT INTO module VALUES (23, 0, 'default', 'Default', 'The foo bar.', NULL, 'default.png');");
    }

    function testextractTableNamesWithFuzz()
    {
        $partialSql = "INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',";
        $res = $this->sql->extractTableNameFromInsertStatement($partialSql);
        $this->assertEqual($res, 'module');

        $partialSql = "INSERT INTO `module VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',";
        $res = $this->sql->extractTableNameFromInsertStatement($partialSql);
        $this->assertEqual($res, 'module');

        $partialSql = "INSERT INTO `module` VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',";
        $res = $this->sql->extractTableNameFromInsertStatement($partialSql);
        $this->assertEqual($res, 'module');

        $partialSql = "INSERT INTO ` module` VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',";
        $res = $this->sql->extractTableNameFromInsertStatement($partialSql);
        $this->assertEqual($res, 'module');

        $partialSql = "INSERT INTO 'module' VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',";
        $res = $this->sql->extractTableNameFromInsertStatement($partialSql);
        $this->assertEqual($res, 'module');
    }

    function xtestParseSchema()
    {
        $file = dirname(__FILE__) . '/test.schema.sql';
        $ret = $this->sql->parse($file, E_ALL, array('SGL_Sql', 'execute'));
    }
}
?>