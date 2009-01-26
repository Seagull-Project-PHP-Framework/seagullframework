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
        $ret = $this->c->load($file, $force = true);
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
        $ret = $this->c->load($file, $force = true);
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
        $ret = $this->c->load($file, $force = true);
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
        $conf = $this->c->load($file, $force = true);
        $this->c->set('foo', 'bar');
        $aRes = $this->c->getAll();
        $this->assertTrue(array_key_exists('foo', $aRes));
    }

    function testSetArrayProperty()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $conf = $this->c->load($file, $force = true);
        $this->c->set('site', array('showLogo' => 'quux'));
        $var = $this->c->get(array('site' => 'showLogo'));
        $this->assertEqual('quux', $var);
    }

    function testSetStaticArrayProperty()
    {
        $file = dirname(__FILE__) . '/test.conf.ini';
        $conf = $this->c->load($file, $force = true);
        $ok = SGL_Config::set('river.boat', 'green');
        $aRes = $this->c->getAll();
        $this->assertTrue(array_key_exists('river', $aRes));
        $this->assertTrue($ok);
        $this->assertEqual(SGL_Config::get('river.boat'), 'green');
    }

    function testGetArrayProperty()
    {
        $var = $this->c->get(array('cache' => 'lifetime'));
        $this->assertEqual($var, 86400);
    }

    function testConfigGetScalarProperty()
    {
        $var = $this->c->get('cache');
        $expected = array (
          'enabled' => 0,
          'libCacheEnabled' => 0,
          'lifetime' => '86400',
          'cleaningFactor' => '0',
          'readControl' => '1',
          'writeControl' => '1',
          'javascript' => '0',
        );
        $this->assertEqual($var, $expected);
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

    function test_getCommandTarget()
    {
        $str = 'module^manager^action';
        $aExpected = array(
            'moduleName'    => 'module',
            'managerName'   => 'manager',
            'action'        => 'action',
            );
        $aRet = SGL_Config::getCommandTarget($str);
        $this->assertEqual($aExpected, $aRet);
    }

    function testGetCachedFileName()
    {
        $fileName = SGL_MOD_DIR . '/default/conf.ini';
        $ret = SGL_Config::getCachedFileName($fileName);
        $this->assertEqual(SGL_VAR_DIR . '/config/default.ini', $ret);

        $fileName = SGL_MOD_DIR . '/default/other.ini';
        $ret = SGL_Config::getCachedFileName($fileName);
        $this->assertEqual(SGL_VAR_DIR . '/default/other.ini', $ret);
    }

    function testCreateCachedFile()
    {
        $aTest = array(
            SGL_VAR_DIR . '/config/user.ini',
            SGL_VAR_DIR . '/media2/image.ini'
        );

        foreach ($aTest as $cachedFile) {
            SGL_Config::ensureDirExists(dirname($cachedFile));

            // copy cached file if exists
            if (file_exists($cachedFile)) {
                $ok = copy($cachedFile, $cachedFile . '.copy');
                $ok = unlink($cachedFile);
            }

            $ok = SGL_Config::createCachedFile($cachedFile);
            $this->assertTrue(file_exists($cachedFile));

            // cleanup
            unlink($cachedFile);

            // restore
            if (file_exists($cachedFile . '.copy')) {
                $ok = copy($cachedFile . '.copy', $cachedFile);
                $ok = unlink($cachedFile . '.copy');
            }
        }
    }
}
?>