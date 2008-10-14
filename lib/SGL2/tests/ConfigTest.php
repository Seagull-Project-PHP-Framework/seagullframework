<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{

    function setup()
    {
        new SGL2_Config();
    }

    function tearDown()
    {
        SGL2_Config::reset();
    }

    public function testLoadingFile()
    {

    }

    public function testGettingValue()
    {
        $this->assertEquals(SGL2_Config::get('site.name'), 'Seagull Framework');
    }

    function testGettingNonExistentValue()
    {
        $res = SGL2_Config::get('foo.bar');
        $this->assertFalse($res);
    }

    function testConfigGetEmptyValue()
    {
        $res = SGL2_Config::get('site.compression');
        $this->assertTrue(empty($res));
    }

    function testEmptyValueIsFalse()
    {
        $res = SGL2_Config::get('site.compression');
        $this->assertFalse($res);
    }

    function testGetValueWithMissingDimension()
    {
        $res = SGL2_Config::get('foo.');
        $this->assertFalse($res);
    }

    function testGetValueWithMissingDimensionNoSeparator()
    {
        $res = SGL2_Config::get('foo');
        $this->assertFalse($res);
    }

    public function testSettingValue()
    {
        $this->assertEquals(SGL2_Config::get('site.name'), 'Seagull Framework');
        SGL2_Config::set('site.name', 'my site');
        $this->assertEquals(SGL2_Config::get('site.name'), 'my site');

    }

    function testGetWithVars()
    {
        $d = 'cookie';
        $cookieName = SGL2_Config::get("$d.name");
        $this->assertEquals($cookieName, 'SGLSESSID');
    }

    function testGetWithVars2()
    {
        $mgr = 'default';
        $ret = SGL2_Config::get("$mgr.filterChain");
        $this->assertFalse(SGL2_Config::get("$mgr.filterChain"));
    }
        //  initialise config object
        //  new SGL2_Config(); // autoloads global config array

        //  config object ready for static calls
        //  $val = SGL2_Config::get('site.name');
        //  SGL2_Config::set('site.name', 'my site name');

        //  load additional config (data not loaded in config object)
        //  $data = SGL2_Config::load('path/to/config2.php');

        //  load module config compared to global config
        //  $data = SGL2_Config::load('modules/$module/conf.ini');

        //  merge loaded data with existing config object
        //  SGL2_Config::merge($data);

        //  saving config data
        //  $str = var_export(SGL2_Config::getAll(), true);
        //  file_put_contents($str, '/path/to/file.php');
        //  SGL2_Config::save('/path/to/file.php', SGL2_Config::SAVE_ALL_KEYS);
        //  SGL2_Config::save('/path/to/file.php', SGL2_Config::SAVE_MODULE_KEYS);
        //  SGL2_Config::save('/path/to/file.php', SGL2_Config::SAVE_GLOBAL_KEYS);

        //  config caches

    public function testLoadGlobalConfigSeparately()
    {
        $path = realpath(dirname(__FILE__) . '/../../../var/default.conf.php');
        $aConf = SGL2_Config::load($path);
        SGL2_Config::reset();
        $this->assertTrue(SGL2_Config::isEmpty());
        SGL2_Config::replace($aConf);
        $this->assertTrue(is_array(SGL2_Config::getAll()));
        $this->assertFalse(SGL2_Config::isEmpty());
    }

    /**
     * Tests the loading of a PHP array in a .php file
     *
     */
    public function testConfigMergeAndCount()
    {
        SGL2_Config::reset();
        $path = realpath(dirname(__FILE__) . '/../../../var/default.conf.php');
        $aConf = SGL2_Config::load($path);
        $c1 = count($aConf);
        $this->assertEquals(SGL2_Config::count(), 0);
        SGL2_Config::replace($aConf);
        $this->assertEquals(SGL2_Config::count(), $c1);
        $a = array('foo', 'bar', 'bar');
        $c2 = count($a);
        SGL2_Config::merge($a);
        $c3 = $c1 + $c2;
        $this->assertEquals(SGL2_Config::count(), $c3);
    }

    public function testGetDefaultValue()
    {
        $default = SGL2_Config::get('doesnt.exist', 'foo');
        $this->assertEquals($default, 'foo');
    }

    /**
     * Tests loading of an ini file
     *
     */
    public function testLoadingModuleConfig()
    {
        $path = realpath(dirname(__FILE__).'/config/conf.ini');
        $aConf = SGL2_Config::load($path);
        $this->assertEquals(count($aConf), 1);
    }

    public function testSavingConfigFiles()
    {
    }
}
?>