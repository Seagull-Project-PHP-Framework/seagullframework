<?php

require_once SGL_CORE_DIR . '/Cache2.php';

/**
 * SGL_Cache2 test suite.
 *
 * @package test
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class TestCache2 extends UnitTestCase
{
    function TestCache2()
    {
        $this->UnitTestCase('Cache2 Test');
    }

    function setUp()
    {
        $ps  = PATH_SEPARATOR;
        $dir = dirname(dirname(dirname(__FILE__)));
        $this->includePath = ini_get('include_path');
        ini_set('include_path', $this->includePath . $ps . $dir);
    }

    function tearDown()
    {
        ini_set('include_path', $this->includePath);
    }

    function testZend()
    {
        $oCache1 = SGL_Cache2::singleton();
        $oCache2 = SGL_Cache2::singleton();

        // by default we get Zend_Cache_Core
        // i.e. Zend_Cache library with Core frontend
        $this->assertTrue($oCache1 instanceof Zend_Cache_Core);

        // check singleton
        $this->assertReference($oCache1, $oCache2);

        // set 'Function' frontend
        $aOpts = array('frontend' => 'Function');
        $oCacheFunction = SGL_Cache2::singleton($aOpts);

        // check frontend
        $this->assertTrue($oCacheFunction instanceof Zend_Cache_Frontend_Function);

        $aOpts = array(
            'frontend' => 'Function',
            'backend'  => 'File',
            'backendOptions' => array('incorrect option name' => 'my value')
        );
        $oCache = SGL_Cache2::singleton($aOpts);

        // test Pear_Error when wrong params are passed.
        $msg = 'SGL_Cache2: Incorrect option name : incorrect option name';
        $this->assertTrue($oCache instanceof PEAR_Error);
        $this->assertEqual($oCache->getMessage(), $msg);
    }

    function testCacheLite()
    {
        $oCache1 = SGL_Cache2::singleton(array(), 'Cache_Lite');
        $oCache2 = SGL_Cache2::singleton(array(), 'Cache_Lite');

        // by default we get SGL_Cache_Lite
        $this->assertTrue($oCache1 instanceof SGL_Cache_Lite);

        // check singleton
        $this->assertReference($oCache1, $oCache2);

        // set 'Function' frontend
        $aOpts = array('frontend' => 'Function');
        $oCacheFunction = SGL_Cache2::singleton($aOpts, 'Cache_Lite');

        // check frontend
        $this->assertTrue($oCacheFunction instanceof Cache_Lite_Function);

        // set 'Output' frontend
        $aOpts = array('frontend' => 'Output');
        $oCacheOutput = SGL_Cache2::singleton($aOpts, 'Cache_Lite');

        // check frontend
        $this->assertTrue($oCacheOutput instanceof Cache_Lite_Output);

        // check default options
        $aDefaultOpts = array(
            'caching'      => SGL_Config::get('cache.enabled'),
            'lifetime'     => SGL_Config::get('cache.lifetime'),
            'writeControl' => SGL_Config::get('cache.writeControl'),
            'readControl'  => SGL_Config::get('cache.readControl'),
            'cacheDir'     => str_replace('\\', '/', SGL_TMP_DIR . '/')
        );
        $aOptions = array(
            'caching'      => $oCacheOutput->_caching,
            'lifetime'     => $oCacheOutput->_lifeTime,
            'writeControl' => $oCacheOutput->_writeControl,
            'readControl'  => $oCacheOutput->_readControl,
            'cacheDir'     => str_replace('\\', '/', $oCacheOutput->_cacheDir)
        );
        $this->assertEqual($aDefaultOpts, $aOptions);

        // check custom options
        $aOpts = array(
            'frontendOptions' => array(
                'caching'        => true,
                'lifetime'       => 100,
                'writeControl'   => false,
                'cleaningFactor' => 5
            ),
            'backendOptions' => array(
                'readControl' => false,
                'cacheDir'    => '/my/dir/'
            )
        );
        $oCache = SGL_Cache2::singleton($aOpts, 'Cache_Lite');

        $aOptions = array(
            'caching'        => $oCache->_caching,
            'lifetime'       => $oCache->_lifeTime,
            'writeControl'   => $oCache->_writeControl,
            'cleaningFactor' => $oCache->_automaticCleaningFactor,
            'readControl'    => $oCache->_readControl,
            'cacheDir'       => str_replace('\\', '/', $oCache->_cacheDir)
        );
        $this->assertEqual(
            array_merge($aOpts['frontendOptions'], $aOpts['backendOptions']),
            $aOptions
        );
    }
}
?>