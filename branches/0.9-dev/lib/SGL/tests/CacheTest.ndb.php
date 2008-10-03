<?php

require_once SGL_CORE_DIR . '/Cache.php';

/**
 * SGL_Cache test suite.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class CacheTest extends UnitTestCase
{
    function CacheTest()
    {
        $this->UnitTestCase('Cache Test');
    }

    function testSingletonBc()
    {
        // test BC mode
        $oCache1 = &SGL_Cache::singleton();
        $oCache2 = &SGL_Cache::singleton();

        // test reference
        $this->assertReference($oCache1, $oCache2);

        // in BC mode switche off "force cache"
        $oCache3 = &SGL_Cache::singleton(false);

        // test reference
        $this->assertReference($oCache1, $oCache3);
        $this->assertReference($oCache2, $oCache3);

        // force new cache instance in BC mode
        $oCache4 = &SGL_Cache::singleton(true);
        $this->assertCopy($oCache4, $oCache1);
        $this->assertCopy($oCache4, $oCache2);
        $this->assertCopy($oCache4, $oCache3);
    }

    function testSingleton()
    {
        $aOptions = array(
            'readControl'  => true,
            'writeControl' => false
        );
        $oCache1 = &SGL_Cache::singleton('default', $aOptions);
        $oCache2 = &SGL_Cache::singleton('default', $aOptions);

        // test reference
        $this->assertReference($oCache1, $oCache2);

        // switche off "force cache"
        $oCache3 = &SGL_Cache::singleton('default', $aOptions, false);

        // test reference
        $this->assertReference($oCache1, $oCache3);
        $this->assertReference($oCache2, $oCache3);

        // force new cache instance
        $oCache4 = &SGL_Cache::singleton('default', $aOptions, true);
        $this->assertCopy($oCache4, $oCache1);
        $this->assertCopy($oCache4, $oCache2);
        $this->assertCopy($oCache4, $oCache3);

        // change options
        $aOptions['readControl'] = false;
        $oCache5 = &SGL_Cache::singleton('default', $aOptions);
        $oCache6 = &SGL_Cache::singleton('default', $aOptions);

        // test reference
        $this->assertReference($oCache5, $oCache6);
        // not equal 2 previous 2 instances
        $this->assertCopy($oCache5, $oCache1);
        $this->assertCopy($oCache5, $oCache4);

        // test "function" container
        $oCache7 = &SGL_Cache::singleton('function');
        $oCache8 = &SGL_Cache::singleton('function');

        // test reference
        $this->assertReference($oCache7, $oCache8);
    }
}
?>