<?php
require_once dirname(__FILE__) . '/../Config.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class ConfigTest extends UnitTestCase {

    function ConfigTest()
    {
        $this->UnitTestCase('Config Test');
    }

    function setup()
    {
        $this->c = &SGL_Config::singleton();
    }

    function tearDown()
    {
        $this->c = null;
    }

    function testLoadIniFile()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $ret = $this->c->load($file);
        $this->assertTrue(is_array($ret));
        $this->assertEqual(count($ret), 14);
    }

    function testLoadPhpArrayFile()
    {
        $file = dirname(__FILE__) . '/test.conf.php';
        $ret = $this->c->load($file);
        $this->assertTrue(is_array($ret));
        $this->assertEqual(count($ret), 15);
    }

    function testWriteIniFile()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $ret = $this->c->load($file);
        $this->assertTrue(is_array($ret));
        $this->assertEqual(count($ret), 14);

        $tmpFileName = tempnam('/tmp', 'test');
        $iniTmpFileName = $tmpFileName . '.ini';
        $ok = $this->c->save($iniTmpFileName);
        $this->assertTrue(is_file($iniTmpFileName));
        $this->assertTrue(is_array(parse_ini_file($iniTmpFileName)));
    }


    function testWritePhpArrayFile()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $ret = $this->c->load($file);
        $this->assertTrue(is_array($ret));
        $this->assertEqual(count($ret), 14);

        $tmpFileName = tempnam('/tmp', 'test');
        $phpTmpFileName = $tmpFileName . '.php';

        //  replace config keys with those loaded
        $this->c->replace($ret);
        $ok = $this->c->save($phpTmpFileName);
        $this->assertTrue($ok);
        $this->assertTrue(is_file($phpTmpFileName));
        $aConf = $this->c->load($phpTmpFileName);
        $this->assertTrue(is_array($aConf));
        $this->assertEqual(count($aConf), 14);
    }

    function testSetScalarProperty()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $conf = $this->c->load($file);
        $this->c->set('foo', 'bar');
        $this->assertTrue(array_key_exists('foo', $this->c->getAll()));
    }

    function testGetProperty()
    {
        $var = $this->c->get(array('cache' => 'lifetime'));
        $this->assertEqual($var, 86400);
    }

    function testSetArrayProperty()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $conf = $this->c->load($file);
        $this->c->set('quux', array('foo' => 'bar'));
        $this->assertTrue(array_key_exists('quux', $this->c->getAll()));
    }

    function testImprovedConfigGet()
    {
        $lifetime = SGL_Config::get('cache.lifetime');
        $this->assertEqual($lifetime, 86400);
    }

    function testConfigGetEmptyValue()
    {
        $res = SGL_Config::get('db.collation');
        $this->assertTrue(empty($res));
    }

    function testConfigGetFalseValue()
    {
        $res = SGL_Config::get('db.collation');
        $this->assertTrue(!($res));
    }

    function testConfigGetStrictFalseValue()
    {
        $res = SGL_Config::get('db.mysqlCluster');
        $this->assertNotIdentical($res, false); // returns a string of zero length
    }

    function testConfigGetNonExistentValue()
    {
        $res = SGL_Config::get('foo.bar');
        $this->assertFalse($res);
    }

    function testConfigGetValueWithMissingDimension()
    {
        $res = SGL_Config::get('foo.');
        $this->assertFalse($res);
    }

    function testConfigGetValueWithMissingDimensionNoSeparator()
    {
        $res = SGL_Config::get('foo');
        $this->assertFalse($res);
    }

    function testImprovedConfigGetWithVars()
    {
        $d = 'cache';
        $lifetime = SGL_Config::get("$d.lifetime");
        $this->assertEqual($lifetime, 86400);
    }

    function testImprovedConfigGetWithVars2()
    {
        $mgr = 'default';
        $ret = SGL_Config::get("$mgr.filterChain");
        $this->assertFalse(SGL_Config::get("$mgr.filterChain"));
    }

}
?>