<?php

require_once dirname(__FILE__) . '/../Sql.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class VariousTest extends UnitTestCase {

    function VariousTest()
    {
        $this->UnitTestCase('Various Test');
    }

    function testParseAndExecute()
    {
        $sql = <<< EOF
INSERT INTO item_type VALUES (5,'Static Html Article');

#
# Dumping data for table `item_type_mapping`
#


INSERT INTO item_type_mapping VALUES (3,2,'title',0);
INSERT INTO item_type_mapping VALUES (4,2,'bodyHtml',2);
EOF;
        $aLines = explode("\n", $sql);
        $ret = $this->parse1($aLines);
//        print '<pre>'; print_r($ret);

        $ret = $this->parse2($aLines);
//        print '<pre>'; print_r($ret);
    }

    function parse1($aLines)
    {
        $sql = '';
        foreach ($aLines as $line) {
            $line = trim($line);
            $cmt  = substr($line, 0, 2);
            if ($cmt == '--' || trim($cmt) == '#') {
                continue;
            }
            $sql .= $line;
            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }
        }
        return $sql;
    }

    function parse2($aLines)
    {
        $sql = '';
        foreach ($aLines as $line) {
            if (preg_match("/^\s*(--)|^\s*#/", $line)) {
                continue;
            }
            $sql .= $line;
            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }
        }
        return $sql;
    }

    function testExtractTablenameFromCreateStatement()
    {
        $sql = <<< EOF
/*==============================================================*/
/* Table: block                                                 */
/*==============================================================*/
create table if not exists block
(
   block_id                       int                            not null,
   name                           varchar(64),
   title                          varchar(32),
   title_class                    varchar(32),
   body_class                     varchar(32),
   blk_order                      smallint,
   position                       varchar(16),
   is_enabled                     smallint,
   is_cached                      smallint,
   params                         longtext,
   primary key (block_id)
);

/*==============================================================*/
/* Table: block_assignment                                      */
/*==============================================================*/
create table if not exists block_assignment
(
   block_id                       int                            not null,
   section_id                     int                            not null,
   primary key (block_id, section_id)
);

/*==============================================================*/
/* Index: block_assignment_fk                                   */
/*==============================================================*/
create index block_assignment_fk on block_assignment
(
   block_id
EOF;
        $aLines = explode("\n", $sql);
        foreach ($aLines as $line) {
            $aTableNames[] = SGL_Sql::extractTableNameFromCreateStatement($line);
        }
        $aTableNames = array_filter($aTableNames); //remove blanks
        $this->assertEqual(count($aTableNames), 2); // doesn't catch index
    }

    function testExtractTableNameFromCreateStatement1()
    {
        $str = 'create table block';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement2()
    {
        $str = 'create table `block';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement3()
    {
        $str = 'create table if not exists block';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement4()
    {
        $str = 'create table if not exists `block`';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractExecuteFromSqlParse()
    {
        $schemaFile =  SGL_MOD_DIR . '/default/data/schema.my.sql';
        $res = SGL_Sql::parse($schemaFile, E_ALL, array('SGL_Sql', 'execute'));
        $this->assertTrue(strlen($res) > 2000); // a parsed ~ 2k file is returned
    }

    function testRemoveNonAlphaChars()
    {
        $foo = 'this is (foo - )';
        $pattern = "/[^\sa-z]/i";
        $replace = "";
        $ret = preg_replace($pattern, $replace, $foo);
        $this->assertEqual($ret, 'this is foo  ');
    }

    function testIsSetAndEmpty()
    {
        $this->assertFalse(@$foo);
        $this->assertNull(@$foo);
        $this->assertFalse(isset($foo));
        $this->assertTrue(empty($foo));

        //  test for null and non-null values
        $foo = null;
        $this->assertFalse(isset($foo));
        $this->assertFalse(!empty($foo));
        $foo = 'up';
        $this->assertTrue(!empty($foo));
    }

    function testObjectHasState()
    {
        $foo = new stdClass();
        $foo->bar = "0";
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = "";
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = 0;
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = array();
        $this->assertFalse(SGL::objectHasState($foo));
        $foo->bar = 1;
        $this->assertTrue(SGL::objectHasState($foo));
    }

    function testBuildFilterChain()
    {
        $aFilters = array('Foo1', 'Bar1', 'Baz');
        $code = '$process = ';
        $closeParens = '';
        $filters = '';
        foreach ($aFilters as $filter) {
            $filters .= "new $filter(\n";
            $closeParens .= ')';
        }
        $code = $filters . $closeParens;
        eval("\$process = $code;");
    }

    function testAutoLoad()
    {
        $className = 'Foo1_Bar1_Baz';
        $searchPath = preg_replace('/_/', '/', $className) . '.php';
        $expected = 'Foo1/Bar1/Baz.php';
        $this->assertEqual($expected, $searchPath);
    }

    function testDbVersionParsing()
    {
        $version = '4.1.16';
        $this->assertFalse(version_compare($version, '5', '>='));

        $version = '4.0.24_Debian-10sarge1-log';
        $this->assertFalse(version_compare($version, '5', '>='));

        $version = '5.0.1';
        $this->assertTrue(version_compare($version, '5', '>='));
    }

    function testIsImage()
    {
        $mimeType = 'image/x-png';
        $this->assertTrue(preg_match("/^image/", $mimeType));
    }

    function testApacheTypes()
    {
        $searchString = 'cgi';
        $this->assertTrue(preg_match("/cgi|apache2filter/i", $searchString));
        $searchString = 'apache2filter';
        $this->assertTrue(preg_match("/cgi|apache2filter/i", $searchString));
        $searchString = '';
        $this->assertFalse(preg_match("/cgi|apache2filter/i", $searchString));
    }

    function testArrayFilterForDisallowedMethods()
    {
        $test = array (
          'username' => '',
          'first_name' => 'Demian',
          'last_name' => 'Turner',
          'passwd' => '',
          'password_confirm' => '',
          'addr_1' => '39c Grange Park',
          'addr_2' => '',
          'addr_3' => '39c Grange Park',
          'city' => 'Ealing',
          'region' => '',
          'post_code' => 'W5 3PP',
          'country' => 'GB',
          'email' => 'demian@phpkitchen.com',
          'telephone' => '555555',
          'mobile' => '',
          'security_question' => '0',
          'security_answer' => '',
        );
        // returns no count, no disallowed keys
        $this->assertFalse(count(array_filter(array_flip($test), array($this, 'containsDisallowedKeys'))));

        $test = array (
          'username' => '',
          'first_name' => 'Demian',
          'last_name' => 'Turner',
          'passwd' => '',
          'password_confirm' => '',
          'addr_1' => '39c Grange Park',
          'addr_2' => '',
          'addr_3' => '39c Grange Park',
          'city' => 'Ealing',
          'region' => '',
          'post_code' => 'W5 3PP',
          'country' => 'GB',
          'email' => 'demian@phpkitchen.com',
          'telephone' => '555555',
          'mobile' => '',
          'security_question' => '0',
          'security_answer' => '',
          'role_id' => '', // forbidden key
        );
        //  returns count, disallowed key present
        $this->assertTrue(count(array_filter(array_flip($test), array($this, 'containsDisallowedKeys'))));

        $test = array('non-existant' => 'foo');
        $this->assertFalse(count(array_filter(array_flip($test), array($this, 'containsDisallowedKeys'))));
    }

    function containsDisallowedKeys($var)
    {
        $disAllowedKeys = array('role_id', 'organisation_id', 'is_acct_active');
        $ret = in_array($var, $disAllowedKeys);
        return $ret;
    }

    function testOringActions()
    {
        $action = 'insert';
        $this->assertTrue($action == ('update' || 'insert'));

        $action = 'bar';
        //  fails
        //$this->assertFalse($action == ('update' || 'insert'));
    }
}



class Foo1{}
class Bar1{}
class Baz{}

?>