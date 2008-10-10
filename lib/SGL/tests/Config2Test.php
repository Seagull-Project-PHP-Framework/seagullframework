<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class Config2Test extends PHPUnit_Framework_TestCase
{

    function xsetup()
    {
        $this->c = SGL_Config::singleton();
    }

    function xtearDown()
    {
        $this->c = null;
    }

    public function testLoadingFile()
    {

    }

    public function testGettingValue()
    {
        //$path = realpath(dirname(__FILE__) . '/../../../var/default.conf.php');
        $conf = new SGL_Config2($autoLoad = true);
        $this->assertEquals(SGL_Config2::get('site.name'), 'Seagull Framework');
    }

    public function testSettingValue()
    {

    }

    public function testMergingConfigs()
    {
        //  initial config object
        //  new SGL_Config2(); // autoloads global config array

        //  config object ready for static calls
        //  $val = SGL_Config2::get('site.name');
        //  SGL_Config2::set('site.name', 'my site name');

        //  load additional config (data not loaded in config object)
        //  $data = SGL_Config2::load('path/to/config2.php');

        //  load module config compared to global config

        //  merge loaded data with existing config object
        //  SGL_Config2::merge($data);

        //  saving config data
        //  $str = var_export(SGL_Config2::getAll(), true);
        //  file_put_contents($str, '/path/to/file.php');
        //  SGL_Config2::save('/path/to/file.php');

        //  config caches



    }

    public function testSavingConfigFiles()
    {

    }
}
?>