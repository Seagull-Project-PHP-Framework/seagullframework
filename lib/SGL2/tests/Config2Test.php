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
        $this->c = SGL2_Config::singleton();
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
        $conf = new SGL2_Config($autoLoad = true);
        $this->assertEquals(SGL2_Config::get('site.name'), 'Seagull Framework');
    }

    public function testSettingValue()
    {

    }

    public function testMergingConfigs()
    {
        //  initial config object
        //  new SGL2_Config(); // autoloads global config array

        //  config object ready for static calls
        //  $val = SGL2_Config::get('site.name');
        //  SGL2_Config::set('site.name', 'my site name');

        //  load additional config (data not loaded in config object)
        //  $data = SGL2_Config::load('path/to/config2.php');

        //  load module config compared to global config

        //  merge loaded data with existing config object
        //  SGL2_Config::merge($data);

        //  saving config data
        //  $str = var_export(SGL2_Config::getAll(), true);
        //  file_put_contents($str, '/path/to/file.php');
        //  SGL2_Config::save('/path/to/file.php');

        //  config caches



    }

    public function testSavingConfigFiles()
    {

    }
}
?>