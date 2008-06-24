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

    function setUp()
    {
        $this->sql = new SGL_Sql();

        // save config
        $c = &SGL_Config::singleton();
        $this->startUpConf = $c->getAll();
    }

    function tearDown()
    {
        unset($this->sql);

        // restore config
        $c = &SGL_Config::singleton();
        foreach ($this->startUpConf as $container => $param) {
            foreach ($param as $key => $value) {
                $c->set($container, array($key => $value));
            }
        }
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
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

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

    /**
     * This test is focused on the regexs used for parsing the
     * data files in the data directories
     */
    function testSqlParse()
    {
    /** preg_match() returns the number of times pattern matches.
     * That will be either 0 times (no match) or 1 time because preg_match()
     * will stop searching after the first match. preg_match_all() on the
     * contrary will continue until it reaches the end of subject.
     * preg_match() returns FALSE if an error occurred.
     */
        $target1 = "INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'default/maintenance', '48/module_default.png', '', NULL, NULL, NULL);";
        $target2 = "INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'default/maintenance', '48/module_default.png', '', NULL, NULL, NULL);";
        $target3 = "-- Comment for test a double dash regex";
        $target4 = "   -- Comment for test a double dash regex with spaces";
        $target5 = "# Comment for test a hash regex";
        $target6 = "   # Comment for test a hash regex with spaces";
        /* FALSE != 0 as a total number of matches */
        /* We should know if preg_match is failing for some reason */
        $this->assertNotIdentical(false, preg_match("/insert/i", $target1),'preg_match returned error');
        $this->assertNotIdentical(false, preg_match("/\{SGL_NEXT_ID\}/", $target2),'preg_match returned error');
        $this->assertNotIdentical(false, preg_match("/^\s*(--)|^\s*#/", $target3),'preg_match returned error');
        $this->assertNotIdentical(false, preg_match("/^\s*(--)|^\s*#/", $target4),'preg_match returned error');
        $this->assertNotIdentical(false, preg_match("/^\s*(--)|^\s*#/", $target5),'preg_match returned error');
        $this->assertNotIdentical(false, preg_match("/^\s*(--)|^\s*#/", $target6),'preg_match returned error');
        /* Each of these should return (int) 1,
         * but since we're using them as Bools,
         * we should check for (bool) TRUE to pass the test
         *
         * This is the loose type checking test
         */
        $this->assertEqual(true, preg_match("/insert/i", $target1),'Did not find \'insert\' in: '.$target1);
        $this->assertEqual(true, preg_match("/\{SGL_NEXT_ID\}/", $target2),'Did not find {SGL_NEXT_ID} in: '.$target2);
        $this->assertEqual(true, preg_match("/^\s*(--)/", $target3),'Did not find -- in: '.$target3);
        $this->assertEqual(true, preg_match("/^\s*(--)/", $target4),'Did not find -- in: '.$target4);
        $this->assertEqual(true, preg_match("/^\s*#/", $target5),'Did not find -- in: '.$target5);
        $this->assertEqual(true, preg_match("/^\s*#/", $target6),'Did not find -- in: '.$target6);
        /* Let's try to combine some of the conditionals */
        $compound = preg_match("/insert/i", $target1) && preg_match("/\{SGL_NEXT_ID\}/", $target1);
        $this->assertEqual(true,  $compound,"compound conditional fails with");
        $this->assertEqual(true, preg_match("/insert/i", $target1) && preg_match("/\{SGL_NEXT_ID\}/", $target1),"compound conditional fails");
        $this->assertEqual(true, preg_match("/^\s*(--)|^\s*#/", $target3),"compound conditional fails");
        $this->assertEqual(true, preg_match("/^\s*(--)|^\s*#/", $target6),"compound conditional fails");

        /*
         * This is the type casted checking test
         * Type casting to (bool) looking for them to be identical matches
         */
        $this->assertIdentical(true, (bool) preg_match("/insert/i", $target1),'Did not find \'insert\' in: '.$target1);
        $this->assertIdentical(true, (bool) preg_match("/\{SGL_NEXT_ID\}/", $target2),'Did not find {SGL_NEXT_ID} in: '.$target2);
        $this->assertIdentical(true, (bool) preg_match("/^\s*(--)/", $target3),'Did not find -- in: '.$target3);
        $this->assertIdentical(true, (bool) preg_match("/^\s*(--)/", $target4),'Did not find -- in: '.$target4);
        $this->assertIdentical(true, (bool) preg_match("/^\s*#/", $target5),'Did not find -- in: '.$target5);
        $this->assertIdentical(true, (bool) preg_match("/^\s*#/", $target6),'Did not find -- in: '.$target6);
        /* Let's try to combine some of the conditionals */
        $compound = preg_match("/insert/i", $target1) && preg_match("/\{SGL_NEXT_ID\}/", $target1);
        $this->assertIdentical(true,  $compound,"compound conditional fails with");
        $this->assertIdentical(true, (bool) preg_match("/insert/i", $target1) && preg_match("/\{SGL_NEXT_ID\}/", $target1),"compound conditional fails");
        $this->assertIdentical(true, (bool) preg_match("/^\s*(--)|^\s*#/", $target3),"compound conditional fails");
        $this->assertIdentical(true, (bool) preg_match("/^\s*(--)|^\s*#/", $target6),"compound conditional fails");
    }


    function testExtractTablenameFromCreateStatement()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

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
        $aRes[] = SGL_Sql::extractTableNamesFromSchema($sql);

        $aTableNames = $aRes[0]; // get first key
        $this->assertEqual(count($aTableNames), 2);
        $this->assertTrue(in_array('block', $aTableNames));
        $this->assertTrue(in_array('block_assignment', $aTableNames));
    }

    function testExtractTableNameFromCreateStatement1()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

        $str = 'create table block';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement2()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

        $str = 'create table `block';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement3()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

        $str = 'create table if not exists block';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement4()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

        $str = 'create table if not exists `block`';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement5()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

        $str = 'create table `block`';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'block');
    }

    function testExtractTableNameFromCreateStatement6()
    {
        // reset prefix
        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => ''));

        $str = 'CREATE TABLE `event-media` (';
        $tableName = SGL_Sql::extractTableNameFromCreateStatement($str);
        $this->assertEqual($tableName, 'event-media');
    }

    function testExtractExecuteFromSqlParse()
    {
        $schemaFile =  SGL_MOD_DIR . '/default/data/schema.my.sql';
        $res = SGL_Sql::parse($schemaFile);
        $this->assertTrue(strlen($res) > 2000); // a parsed ~ 2k file is returned
    }

    function testAddTablePrefix()
    {
        require_once 'Text/Password.php';
        $prefix = Text_Password::create();
        $table  = 'module';

        $c = &SGL_Config::singleton();
        $c->set('db', array('prefix' => $prefix));

        $ret = SGL_Sql::addTablePrefix($table);
        $this->assertEqual($ret, $prefix . $table);
    }

    function testPrefixTableNameInCreateTableStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            'create table block',
            'create table `block',
            'create table if not exists block',
            'create table if not exists `block`',
            'create table `block`',
            'CREATE TABLE `event-media` ('
        );
        $aExpected = array(
            "create table {$prefix}block",
            "create table `{$prefix}block",
            "create table if not exists {$prefix}block",
            "create table if not exists `{$prefix}block`",
            "create table `{$prefix}block`",
            "CREATE TABLE `{$prefix}event-media` ("
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'createTable');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }

    function testPrefixTableNameInInsertStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            "INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO `module VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO `module` VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO ` module` VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO 'module' VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
        );
        $aExpected = array(
            "INSERT INTO {$prefix}module VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO `{$prefix}module VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO `{$prefix}module` VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO ` {$prefix}module` VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
            "INSERT INTO '{$prefix}module' VALUES ({SGL_NEXT_ID}, 1, 'asset', 'Asset Manager',",
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'insert');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }

    function testPrefixTableNameInSelectStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            "SELECT @langSwitcher2BlockId := block_id FROM block WHERE name = 'Default_Block_LangSwitcher2'",
            "SELECT @moduleId := MAX(module_id) FROM module",
            "SELECT @moduleId := MAX(module_id) FROM `module`",
            "SELECT @moduleId := MAX(module_id) FROM `module` WHERE id = 325"
        );
        $aExpected = array(
            "SELECT @langSwitcher2BlockId := block_id FROM {$prefix}block WHERE name = 'Default_Block_LangSwitcher2'",
            "SELECT @moduleId := MAX(module_id) FROM {$prefix}module",
            "SELECT @moduleId := MAX(module_id) FROM `{$prefix}module`",
            "SELECT @moduleId := MAX(module_id) FROM `{$prefix}module` WHERE id = 325"
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'select');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }

    function testPrefixTableNameInCreateIndexStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            'create index block_assignment_fk on block_assignment',
            'create index `block_assignment_fk` on `block_assignment` ',
            'create unique index `block_assignment_fk` using btree on `block_assignment` ',
            'create fulltext index `block_assignment_fk` on `block_assignment` ',
        );
        $aExpected = array(
            "create index {$prefix}block_assignment_fk on {$prefix}block_assignment",
            "create index `{$prefix}block_assignment_fk` on `{$prefix}block_assignment` ",
            "create unique index `{$prefix}block_assignment_fk` using btree on `{$prefix}block_assignment` ",
            "create fulltext index `{$prefix}block_assignment_fk` on `{$prefix}block_assignment` ",
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'createIndex');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }

    function testPrefixTableNameInAlterTableStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            'alter table block_assignment',
            'alter table `block_assignment`',
            'alter table block_assignment add constraint FK_block_assignment foreign key (block_id)',
            'alter table `block_assignment` add constraint `FK_block_assignment` foreign key (block_id)'
        );
        $aExpected = array(
            "alter table {$prefix}block_assignment",
            "alter table `{$prefix}block_assignment`",
            "alter table {$prefix}block_assignment add constraint {$prefix}FK_block_assignment foreign key (block_id)",
            "alter table `{$prefix}block_assignment` add constraint `{$prefix}FK_block_assignment` foreign key (block_id)"
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'alterTable');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }

    function testPrefixTableNameInReferencesStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            'references block (block_id) on delete restrict on update restrict',
            'references `block` (block_id) on delete restrict on update restrict',
        );
        $aExpected = array(
            "references {$prefix}block (block_id) on delete restrict on update restrict",
            "references `{$prefix}block` (block_id) on delete restrict on update restrict",
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'ref');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }

    function testPrefixTableNameInDeleteStatement()
    {
        $c = &SGL_Config::singleton();

        // set prefix
        $prefix = 'prefix_';
        $c->set('db', array('prefix' => $prefix));

        $aInitials = array(
            'DELETE FROM block',
            'DELETE FROM `block` WHERE block_id = @catNavBlockId',
        );
        $aExpected = array(
            "DELETE FROM {$prefix}block",
            "DELETE FROM `{$prefix}block` WHERE block_id = @catNavBlockId",
        );

        for ($i = 0, $cnt = count($aInitials); $i < $cnt; $i++) {
            $str = $aInitials[$i];
            $ret = SGL_Sql::prefixTableNameInStatement($str, 'delete');
            $this->assertEqual($ret, $aExpected[$i]);
        }
    }
}
?>