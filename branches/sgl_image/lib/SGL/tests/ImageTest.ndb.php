<?php

require_once dirname(__FILE__) . '/../Image.php';

/**
 * Test suite.
 *
 * @package    seagull
 * @subpackage test
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class ImageTest extends UnitTestCase
{
    function ImageTest()
    {
        $this->UnitTestCase('Image Test');
        $this->imageConfFile = dirname(__FILE__) . '/image.ini';

        // removeme
        SGL_Image_Test::init();
    }

    function setUp()
    {
    }

    function tearDown()
    {
    }

    function testIsClassMethod()
    {
        $image = & new SGL_Image();
        $this->assertTrue(SGL_Image::_isClassMethod());
        $this->assertFalse($image->_isClassMethod());
        $this->assertTrue($image->_isInstanceMethod());
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
        $aRet = SGL_Image::_getUniqueSectionNames($aSectionNames);
        $this->assertEqual(3, count($aRet));
        $this->assertEqual('default', reset($aRet));

        $aParsedData = parse_ini_file($this->imageConfFile, true);
        $aRet = SGL_Image::_getUniqueSectionNames($aParsedData);
        $this->assertEqual(3, count($aRet));
        $this->assertEqual('default', reset($aRet));
    }

    function testConfigParamCleanup()
    {
        $params = array(
            'inherit'           => 1,
            'resize'            => 1,
            'thumbnails'        => 'small,large',
            'inheritThumbnails' => 1,
            'border'            => 1
        );
        $copy = $params;
        SGL_Image::_configParamCleanup($params);

        $this->assertFalse(array_key_exists('inherit', $params));
        $this->assertFalse(array_key_exists('inheritThumbnails', $params));
        $this->assertFalse(array_key_exists('thumbnails', $params));
        $this->assertEqual(2, count($params));

        $params = $copy;
        $params['thumbnails'] = array(
            'small' => 1,
            'large' => 1,
        );
        $copy = $params;
        SGL_Image::_configParamCleanup($params);

        $this->assertTrue(array_key_exists('thumbnails', $params));
        $this->assertEqual(3, count($params));

        $params = $copy;
        $params = array(
            'media'   => $copy,
            'default' => $copy,
            'test'    => array()
        );
        SGL_Image::_configParamCleanup($params);

        $this->assertFalse(array_key_exists('inherit', $params['media']));
        $this->assertFalse(array_key_exists('inherit', $params['default']));
        $this->assertFalse(array_key_exists('inheritThumbnails', $params['media']));
        $this->assertFalse(array_key_exists('inheritThumbnails', $params['default']));
        $this->assertTrue(array_key_exists('thumbnails', $params['media']));
        $this->assertTrue(array_key_exists('thumbnails', $params['default']));
        $this->assertEqual(count($params['media']), count($params['default']));
        $this->assertEqual(3, count($params['media']));
    }

    function testGetParamsFromFile()
    {
        $aParams = SGL_Image::getParamsFromFile($this->imageConfFile);

        // three containers parsed
        $this->assertEqual(array('default', 'media', 'test'),
            array_keys($aParams));

        // default's thumbnails found
        $default = $aParams['default'];
        $this->assertTrue(isset($default['thumbnails'])
            && is_array($default['thumbnails']));

        // super must be ignored
        $defaultThumbs = $default['thumbnails'];
        $this->assertFalse(isset($defaultThumbs['super']));

        // total number of thumbs
        $this->assertEqual(3, count($defaultThumbs));

        // thumbnails' names
        // don't care about the order - sort arrays first
        $this->assertEqual(sort($names = array('small', 'medium', 'large')),
            sort(array_keys($defaultThumbs)));

        // testing small thumb
        $small = $defaultThumbs['small'];
        $this->assertEqual($small['driver'], $default['driver']);
        $this->assertEqual($small['saveQuality'], $default['saveQuality']);
        $this->assertNotEqual($small['resize'], $default['resize']);

        // testing large thumb
        $large = $defaultThumbs['large'];
        $this->assertEqual($large['driver'], $default['driver']);
        $this->assertEqual($large['saveQuality'], $default['saveQuality']);
        $this->assertNotEqual($large['resize'], $default['resize']);

        // testing medium thumb
        $medium = $defaultThumbs['medium'];
        $this->assertNotEqual($medium['driver'], $default['driver']);
        $this->assertNotEqual($medium['saveQuality'], $default['saveQuality']);
        $this->assertNotEqual($medium['thumbDir'], $default['saveQuality']);

        // testing another parent section,
        // which inherited some options from default
        $test = $aParams['test'];
        $this->assertEqual($test['driver'], $default['driver']);
        $this->assertNotEqual($test['thumbDir'], $default['thumbDir']);
        $this->assertNotEqual($test['resize'], $default['resize']);

        // thumbnails found for test
        $this->assertTrue(isset($test['thumbnails'])
            && is_array($test['thumbnails']));
        $testThumbs = $test['thumbnails'];

        // only 'extra' thumbnail exists
        $this->assertEqual(array('extra'), array_keys($testThumbs));
        $extra = $testThumbs['extra'];

        // 'extra' thumbnail has same data as it's parent and as default section
        $this->assertEqual($extra['driver'], $default['driver']);
        $this->assertEqual($extra['saveQuality'], $default['saveQuality']);
        // following are inherited from parent section instead
        $this->assertNotEqual($extra['thumbDir'], $default['thumbDir']);
        $this->assertNotEqual($extra['resize'], $default['resize']);

        // testing media section
        // it doesn't have thumbnails specified, but inherits ones from default
        $media = $aParams['media'];
        // just ensure that media inherited some options form default section
        $this->assertTrue(count($media) > 3);
        // let's see if they are equal
        $this->assertEqual($media['driver'], $default['driver']);
        $this->assertEqual($media['thumbDir'], $default['thumbDir']);
        $this->assertEqual($media['resize'], $default['resize']);
        $this->assertNotEqual($media['saveQuality'], $default['saveQuality']);

        // now the trickiest part
        // media section inherited thumbnails from default
        $this->assertTrue(isset($media['thumbnails'])
            && is_array($media['thumbnails']));
        $mediaThumbs = $media['thumbnails'];

        // thumbnails' names equal, of course
        $this->assertEqual(array_keys($mediaThumbs), array_keys($defaultThumbs));
        $this->assertEqual(count($mediaThumbs), count($defaultThumbs));

        // media section has special option - 'border'
        $this->assertFalse(isset($default['border']));
        $this->assertTrue(isset($media['border']));

        // that's why thumbnails can't have equal params
        $this->assertNotEqual($mediaThumbs, $defaultThumbs);
    }

    function testInit()
    {
        $image = & new SGL_Image();

        // config file not found
        $ok = $image->init('file_not_exists.ini');
        $this->assertIsA($ok, 'PEAR_Error');

        // only array or filename can be specified as first argument
        $ok = $image->init(null);
        $this->assertIsA($ok, 'PEAR_Error');

        // at least mandatory options must be specified
        $ok = $image->init(array());
        $this->assertIsA($ok, 'PEAR_Error');

        // specify main options for image
        $aOptions = array(
            'driver'      => 'GD_SGL',
            'saveQuality' => '70',
            'thumbDir'    => false,
            'thumbnails'  => 'small,large' // will be stripped
        );
        $ok = $image->init($aOptions);

        $this->assertTrue($ok); // success
        // ensure class' vars are populated
        $this->assertEqual(count($image->_aParams), 3);
        $this->assertFalse(array_key_exists('thumbnails', $image->_aParams));
        $this->assertTrue(array_key_exists('driver', $image->_aParams));

        // adding thumbnails
        $aOptions['thumbnails'] = array(
            'small' => array(),
            'large' => array(),
        );
        $ok = $image->init($aOptions);

        // we didn't specified mandatory options for thumbnails
        $this->assertIsA($ok, 'PEAR_Error');

        // adding options for thumbnails
        $aOptions['thumbnails']['small'] = array(
            'driver'      => 'GD_SGL',
            'saveQuality' => '50',
            'thumbDir'    => false,
        );
        $aOptions['thumbnails']['large'] = array(
            'inherit'     => true, // will be stripped
            'driver'      => 'GD',
            'saveQuality' => '90',
            'thumbDir'    => false,
        );
        $ok = $image->init($aOptions);

        $this->assertTrue($ok); // success
        // ensure class' vars are populated
        $this->assertEqual(array_keys($image->_aThumbnails),
            array_keys($aOptions['thumbnails']));
        $this->assertEqual($image->_aThumbnails['small'],
            $aOptions['thumbnails']['small']);

        // autoload parameters from file
        $ok = $image->init($this->imageConfFile);
        $this->assertTrue($ok);

        // check that strategies are loaded for thumbnails
        foreach (array_keys($image->_aThumbnails) as $thumbName) {
            if (isset($image->_aStrats[$thumbName])) {
                foreach ($image->_aStrats[$thumbName] as $stratName => $oStrat) {
                    $this->assertIsA($oStrat, 'SGL_ImageTransformStrategy');
                }
            }
        }
    }

    function testEnsureDirIsWritable()
    {
        require_once 'System.php';
        $tmpdir = session_save_path();

        // create dir
        $dir = '/thumbs/small';
        $ok = SGL_Image::_ensureDirIsWritable($tmpdir . $dir);
        $this->assertTrue($ok);
        // remove dir
        $ok = System::rm(array('-r', dirname($tmpdir . $dir)));
        $this->assertTrue($ok);

        // create dir
        $dir = '/thumbs/large';
        $ok = SGL_Image::_ensureDirIsWritable($tmpdir . $dir);
        $this->assertTrue($ok);

        // try to re-create dir
        $ok = SGL_Image::_ensureDirIsWritable($tmpdir . $dir);
        $this->assertTrue($ok);
        // remove dir
        $ok = System::rm(array('-r', dirname($tmpdir . $dir)));
        $this->assertTrue($ok);
    }

    function testGetImagePath()
    {
        // direct call without the params
        $ok = SGL_Image::_getImagePath();
        // you should not call SGL_Image::_getImagePath() directly,
        // use wrappers instead:
        //  - SGL_Image::getPath() or
        //  - SGL_Image::getUrl()
        $this->assertIsA($ok, 'PEAR_Error');

        // static call without specified module result in default uploadir
        $path = SGL_Image::getPath();
        $this->assertEqual(SGL_UPLOAD_DIR, $path);

        // static call with specified module
        $path = SGL_Image::getPath('media');
        $this->assertEqual(SGL_MOD_DIR . '/media/www/images', $path);

        // we can't get URL for static call if module is not specified
        $url = SGL_Image::getUrl();
        $this->assertIsA($url, 'PEAR_Error');

        // static call for an URL with specified module name
        $url = SGL_Image::getUrl('media');
        $this->assertEqual(SGL_BASE_URL . '/media/images', $url);

        // init SGL_Image instance with module name supplied
        $image = & new SGL_Image(null, $moduleName = 'media');

        // with instance call we know a module name
        $url = $image->getUrl();
        $this->assertEqual(SGL_BASE_URL . '/media/images', $url);

        // instance call for a path gives save results
        // as SGL_Image::getPath('media');
        $path = $image->getPath();
        $this->assertEqual(SGL_MOD_DIR . '/media/www/images', $path);
    }
}

?>