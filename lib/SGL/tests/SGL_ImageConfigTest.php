<?php

/**
 * Test suite.
 *
 * @package    seagull
 * @subpackage test
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_ImageConfigTest extends PHPUnit_Framework_TestCase
{
    var $imageConfFile;

    function setUp()
    {
        $this->markTestSkipped(
            'problem loading config file ...'
        );
//        $this->imageConfFile = dirname(__FILE__) . '/image.ini';
    }

    function tearDown()
    {
    }

    function testGetUniqueSectionNames()
    {
        $aSectionNames = array(
            'default_small' => 1,
            'media'         => 1,
            'image'         => 1,
            'media_small'   => 1,
            'default'       => 1,
            'media_large'   => 1,
            'image_medium'  => 1,
            'default_large' => 1
        );
        $aRet = SGL_ImageConfig::getUniqueSectionNames($aSectionNames);
        $this->assertEquals(3, count($aRet));
        $this->assertEquals('default', reset($aRet));

        $aParsedData = parse_ini_file($this->imageConfFile, true);
        $aRet = SGL_ImageConfig::getUniqueSectionNames($aParsedData);
        $this->assertEquals(3, count($aRet));
        $this->assertEquals('default', reset($aRet));
    }

    function testParamsCheck()
    {
        // no params -> result in PEAR_Error
        $aParams = array();
        $ret = SGL_ImageConfig::paramsCheck($aParams);
        $this->assertInstanceOf('PEAR_Error', $ret);

        // no mandatory params
        $aParams['strategy_1'] = 1;
        $aParams['strategy_2'] = 1;
        $ret = SGL_ImageConfig::paramsCheck($aParams);
        $this->assertInstanceOf('PEAR_Error', $ret);

        // some mandatory params missing
        $aParams['saveQuality'] = 1;
        $aParams['driver']      = 1;
        $ret = SGL_ImageConfig::paramsCheck($aParams);
        $this->assertInstanceOf('PEAR_Error', $ret);

        // all mandatory params are at place -> check: ok
        $aParams['thumbDir'] = 1;
        $ret = SGL_ImageConfig::paramsCheck($aParams);
        $this->assertTrue($ret);
    }

    function testCleanup()
    {
        $params = array(
            'inherit'           => 1,
            'resize'            => 1,
            'thumbnails'        => 'small,large',
            'inheritThumbnails' => 1,
            'border'            => 1
        );
        $copy = $params;
        SGL_ImageConfig::cleanup($params);

        $this->assertFalse(array_key_exists('inherit', $params));
        $this->assertFalse(array_key_exists('inheritThumbnails', $params));
        $this->assertFalse(array_key_exists('thumbnails', $params));
        $this->assertEquals(2, count($params));

        $params = $copy;
        $params['thumbnails'] = array(
            'small' => 1,
            'large' => 1,
        );
        $copy = $params;
        SGL_ImageConfig::cleanup($params);

        $this->assertTrue(array_key_exists('thumbnails', $params));
        $this->assertEquals(3, count($params));

        $params = $copy;
        $params = array(
            'media'   => $copy,
            'default' => $copy,
            'test'    => array()
        );
        SGL_ImageConfig::cleanup($params);

        $this->assertFalse(array_key_exists('inherit', $params['media']));
        $this->assertFalse(array_key_exists('inherit', $params['default']));
        $this->assertFalse(array_key_exists('inheritThumbnails', $params['media']));
        $this->assertFalse(array_key_exists('inheritThumbnails', $params['default']));
        $this->assertTrue(array_key_exists('thumbnails', $params['media']));
        $this->assertTrue(array_key_exists('thumbnails', $params['default']));
        $this->assertEquals(count($params['media']), count($params['default']));
        $this->assertEquals(3, count($params['media']));
    }

    function testGetParamsFromFile()
    {
        $aParams = SGL_ImageConfig::getParamsFromFile($this->imageConfFile);

        // three containers parsed
        $this->assertEquals(array('default', 'media', 'test'),
            array_keys($aParams));

        // default's thumbnails found
        $default = $aParams['default'];
        $this->assertTrue(isset($default['thumbnails'])
            && is_array($default['thumbnails']));

        // super must be ignored
        $defaultThumbs = $default['thumbnails'];
        $this->assertFalse(isset($defaultThumbs['super']));

        // total number of thumbs
        $this->assertEquals(3, count($defaultThumbs));

        // thumbnails' names
        // don't care about the order - sort arrays first
        $this->assertEquals(sort($names = array('small', 'medium', 'large')),
            sort(array_keys($defaultThumbs)));

        // testing small thumb
        $small = $defaultThumbs['small'];
        $this->assertEquals($small['driver'], $default['driver']);
        $this->assertEquals($small['saveQuality'], $default['saveQuality']);
        $this->assertNotEquals($small['resize'], $default['resize']);

        // testing large thumb
        $large = $defaultThumbs['large'];
        $this->assertEquals($large['driver'], $default['driver']);
        $this->assertEquals($large['saveQuality'], $default['saveQuality']);
        $this->assertNotEquals($large['resize'], $default['resize']);

        // testing medium thumb
        $medium = $defaultThumbs['medium'];
        $this->assertNotEquals($medium['driver'], $default['driver']);
        $this->assertNotEquals($medium['saveQuality'], $default['saveQuality']);
        $this->assertNotEquals($medium['thumbDir'], $default['saveQuality']);

        // testing another parent section,
        // which inherited some options from default
        $test = $aParams['test'];
        $this->assertEquals($test['driver'], $default['driver']);
        $this->assertNotEquals($test['thumbDir'], $default['thumbDir']);
        $this->assertNotEquals($test['resize'], $default['resize']);

        // thumbnails found for test
        $this->assertTrue(isset($test['thumbnails'])
            && is_array($test['thumbnails']));
        $testThumbs = $test['thumbnails'];

        // only 'extra' thumbnail exists
        $this->assertEquals(array('extra'), array_keys($testThumbs));
        $extra = $testThumbs['extra'];

        // 'extra' thumbnail has same data as it's parent and as default section
        $this->assertEquals($extra['driver'], $default['driver']);
        $this->assertEquals($extra['saveQuality'], $default['saveQuality']);
        // following are inherited from parent section instead
        $this->assertNotEquals($extra['thumbDir'], $default['thumbDir']);
        $this->assertNotEquals($extra['resize'], $default['resize']);

        // testing media section
        // it doesn't have thumbnails specified, but inherits ones from default
        $media = $aParams['media'];
        // just ensure that media inherited some options form default section
        $this->assertTrue(count($media) > 3);
        // let's see if they are equal
        $this->assertEquals($media['driver'], $default['driver']);
        $this->assertEquals($media['thumbDir'], $default['thumbDir']);
        $this->assertEquals($media['resize'], $default['resize']);
        $this->assertNotEquals($media['saveQuality'], $default['saveQuality']);

        // now the trickiest part
        // media section inherited thumbnails from default
        $this->assertTrue(isset($media['thumbnails'])
            && is_array($media['thumbnails']));
        $mediaThumbs = $media['thumbnails'];

        // thumbnails' names equal, of course
        $this->assertEquals(array_keys($mediaThumbs), array_keys($defaultThumbs));
        $this->assertEquals(count($mediaThumbs), count($defaultThumbs));

        // media section has special option - 'border'
        $this->assertFalse(isset($default['border']));
        $this->assertTrue(isset($media['border']));

        // that's why thumbnails can't have equal params
        $this->assertNotEquals($mediaThumbs, $defaultThumbs);
    }

    function testGetParamsFromString()
    {
        // empty string -> empty array
        $string = '';
        $ret = SGL_ImageConfig::getParamsFromString($string);
        $this->assertEquals($ret, array());

        // one param without explicit name
        $string = 'www/images/seagull.png';
        $ret = SGL_ImageConfig::getParamsFromString($string);
        $this->assertEquals($ret, array($string));

        // many params without names
        $string = '#ffffff,#cccccc';
        $ret = SGL_ImageConfig::getParamsFromString($string);
        $this->assertEquals($ret, explode(',', $string));

        // complete params string
        $string = 'color:#eeeeee,align:bottom,paddingLeft:10,paddingBottom:10,size:27';
        $ret = SGL_ImageConfig::getParamsFromString($string);
        $expected = array(
            'color'         => '#eeeeee',
            'align'         => 'bottom',
            'paddingLeft'   => 10,
            'paddingBottom' => 10,
            'size'          => 27
        );
        $this->assertEquals($ret, $expected);
    }
}

?>