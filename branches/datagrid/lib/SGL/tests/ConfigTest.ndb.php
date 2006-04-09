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
        $this->assertEqual(array('foo' => 'bar'), $this->c->get('quux'));
    }
}
?>