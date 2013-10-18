<?php

/**
 * SGL_Cache test suite.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_CacheTest extends PHPUnit_Framework_TestCase
{
    function testSingletonBc()
    {
        // test BC mode
        $oCache1 = SGL_Cache::singleton();
        $oCache2 = SGL_Cache::singleton();

        // test reference
        $this->assertSame($oCache1, $oCache2);

        // in BC mode switch off "force cache"
        $oCache3 = SGL_Cache::singleton(false);

        // test reference
        $this->assertSame($oCache1, $oCache3);
        $this->assertSame($oCache2, $oCache3);

        // force new cache instance in BC mode
        $oCache4 = SGL_Cache::singleton(true);
        $this->assertEquals($oCache4, $oCache1);
        $this->assertEquals($oCache4, $oCache2);
        $this->assertEquals($oCache4, $oCache3);
    }

    function testSingleton()
    {
        $aOptions = array(
            'readControl'  => true,
            'writeControl' => false
        );
        $oCache1 = SGL_Cache::singleton('default', $aOptions);
        $oCache2 = SGL_Cache::singleton('default', $aOptions);

        // test reference
        $this->assertSame($oCache1, $oCache2);

        // switch off "force cache"
        $oCache3 = SGL_Cache::singleton('default', $aOptions, false);

        // test reference
        $this->assertSame($oCache1, $oCache3);
        $this->assertSame($oCache2, $oCache3);

        // force new cache instance
        $oCache4 = SGL_Cache::singleton('default', $aOptions, true);
        $this->assertEquals($oCache4, $oCache1);
        $this->assertEquals($oCache4, $oCache2);
        $this->assertEquals($oCache4, $oCache3);

        // change options
        $aOptions['readControl'] = false;
        $oCache5 = SGL_Cache::singleton('default', $aOptions);
        $oCache6 = SGL_Cache::singleton('default', $aOptions);

        // test reference
        $this->assertSame($oCache5, $oCache6);
        // not equal 2 previous 2 instances
        $this->assertEquals($oCache5, $oCache1);
        $this->assertEquals($oCache5, $oCache4);

        // test "function" container
        $oCache7 = SGL_Cache::singleton('function');
        $oCache8 = SGL_Cache::singleton('function');

        // test reference
        $this->assertSame($oCache7, $oCache8);
    }
}
?>