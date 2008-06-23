<?php

require_once dirname(__FILE__) . '/../Image.php';
require_once SGL_LIB_PEAR_DIR . '/Image/Transform/Driver/GD_SGL.php';

/**
 * Test suite.
 *
 * @package    seagull
 * @subpackage test
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class ImageTest extends UnitTestCase
{
    var $imageConfFile;
    var $imageSampleFile;

    function ImageTest()
    {
        $this->UnitTestCase('Image Test');

        Mock::generate('Image_Transform_Driver_GD_SGL');
    }

    function setUp()
    {
        $this->imageConfFile   = dirname(__FILE__) . '/image.ini';
        $this->imageSampleFile = dirname(__FILE__) . '/chicago.jpg';

        $driver   = & new MockImage_Transform_Driver_GD_SGL();
        $strategy = & new SGL_ImageTransform_FooStrategy($driver);

        $conf = array();
        $conf['driver']      = &$driver;
        $conf['thumbDir']    = 'thumbs';
        $conf['saveQuality'] = 100;
        $conf['foo']         = &$strategy;

        // add thumbs
        $conf['thumbnails']['small'] = array();
        $conf['thumbnails']['large'] = array();
        $small = &$conf['thumbnails']['small'];
        $large = &$conf['thumbnails']['large'];

        $small['driver']      = &$driver;
        $small['thumbDir']    = 'thumbs';
        $small['saveQuality'] = 75;
        $small['foo']         = &$strategy;
        $small['foo2']        = &$strategy;
        $large['driver']      = &$driver;
        $large['thumbDir']    = 'thumbs';
        $large['saveQuality'] = 90;
        $large['foo3']        = &$strategy;

        $this->conf   = $conf;
        $this->driver = &$driver;
        $this->strat  = &$strategy;
    }

    function tearDown()
    {
        unset($this->conf);
        unset($this->driver);
        unset($this->strat);
    }

    function testIsStaticMethod()
    {
        $image = & new SGL_Image();
        $this->assertTrue(SGL_Image::_isStaticMethod());
        $this->assertFalse($image->_isStaticMethod());
        $this->assertTrue($image->_isInstanceMethod());
    }

    function testEnsureDirIsWritable()
    {
        require_once 'System.php';
        $tmpdir = SGL_Util::getTmpDir();

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

        // instance call for a path gives same results
        // as SGL_Image::getPath('media');
        $path = $image->getPath();
        $this->assertEqual(SGL_MOD_DIR . '/media/www/images', $path);
    }

    function testSetParams()
    {
        $image = & new SGL_Image();

        $conf = SGL_ImageConfig::getParamsFromFile($this->imageConfFile);
        $ret = $image->_setParams($conf[SGL_IMAGE_DEFAULT_SECTION]);
        $this->assertTrue($ret);

        $conf = array();
        $ret = $image->_setParams($conf);
        $this->assertIsA($ret, 'PEAR_Error');

        $conf = array('thumbDir' => 1, 'driver' => 1);
        $ret = $image->_setParams($conf);
        $this->assertTrue($ret);
    }

    function testGetThumbnailNames()
    {
        $conf = SGL_ImageConfig::getParamsFromFile($this->imageConfFile);
        $image = & new SGL_Image();
        $image->_setParams($conf[SGL_IMAGE_DEFAULT_SECTION]);

        $ret = $image->getThumbnailNames();
        $this->assertEqual(sort($ret), sort($expected = array('small', 'large', 'medium')));
    }

    function testInit()
    {
        $driver   = &$this->driver;
        $conf     = &$this->conf;
        $strategy = &$this->strat;

        // init image without thumbs
        $confcopy = $conf;
        unset($confcopy['thumbnails']);

        $image = & new SGL_Image();
        $ret = $image->init($confcopy);
        $this->assertTrue($ret);

        unset($image);

        // init image with thumbs
        $image = & new SGL_Image();
        $ret = $image->init($conf);
        $this->assertTrue($ret);

        $this->assertReference($image->_aParams['driver'], $driver);
        $this->assertReference($image->_aThumbnails['small']['driver'], $driver);
        $this->assertReference($image->_aThumbnails['large']['driver'], $driver);

        $this->assertReference($image->_aThumbnails['small']['foo'], $strategy);
        $this->assertReference($image->_aThumbnails['small']['foo2'], $strategy);
        $this->assertReference($image->_aThumbnails['large']['foo3'], $strategy);

        $ret = $image->getThumbnailNames();
        $this->assertEqual(sort($ret), sort($expected = array('small', 'large')));
    }

    function testTransform()
    {
        $driver = &$this->driver;
        $conf   = &$this->conf;

        // init instance
        $fileName = 'chicago.jpg';
        $image = & new SGL_Image($fileName);
        $ret = $image->init($conf);

        // make sure target dir is writable
        $dir = $image->getPath();
        $ret = SGL_Image::_ensureDirIsWritable($dir);

        // is there a possibility to add custom methods?
        $driver->expectOnce('resize', array());

        $driver->expectOnce('load', array($dir . '/' . $fileName));
        $driver->expectOnce('free', array());
        $driver->expectOnce('save', array($dir . '/' . $fileName, '', $conf['saveQuality']));

        // place file which will be transformed
        copy($this->imageSampleFile, $dir . '/' . $fileName);

        $ret = $image->transform();
        $this->assertTrue($ret);

        unlink($dir . '/' . $fileName);
    }

    function testCreate()
    {
        $driver = &$this->driver;
        $conf   = &$this->conf;

        // initial data for image
        $fileName   = 'chicago.jpg';
        //$moduleName = 'testModule';

        // init image
        $image = & new SGL_Image($fileName);
        $image->init($conf);

        $driver->expectCallCount('resize', 8);
        $driver->expectCallCount('load', 8);
        $driver->expectCallCount('free', 8);
        $driver->expectCallCount('save', 8);

        $ret = $image->create($this->imageSampleFile, 'copy');
        if (PEAR::isError($ret)) {
            die($ret->getMessage());
        }
        $this->assertTrue($ret && (is_string($ret) || is_bool($ret)));

        // can't create file, 'cos it already exists
        $ret = $image->create($this->imageSampleFile, 'copy');
        $this->assertIsA($ret, 'PEAR_Error');

        // SGL_Image#replace($fileName, $callback)
        // is an alias of
        // SGL_Image#create($fileName, $callback, $replace = true)
        $ret = $image->replace($this->imageSampleFile, 'copy');
        $this->assertTrue($ret && (is_string($ret) || is_bool($ret)));

        // cleanup
        require_once SGL_CORE_DIR . '/File.php';
        SGL_File::rmDir(SGL_UPLOAD_DIR, '-r');
        //SGL_File::rmDir(SGL_MOD_DIR . '/' . $moduleName, '-r');
    }

    function testDelete()
    {
        // libs to read dir data
        require_once SGL_LIB_PEAR_DIR . '/File/Util.php';
        require_once SGL_CORE_DIR . '/Util.php';

        // image config
        $conf = &$this->conf;

        // files in upload dir
        $aFiles = SGL_Util::listDir(SGL_UPLOAD_DIR, FILE_LIST_FILES);
        $filesCntBefore = count($aFiles);

        // files in thumbs dir
        $thumbDir = SGL_UPLOAD_DIR . '/' . $conf['thumbDir'];
        $aThumbFiles = SGL_Util::listDir($thumbDir , FILE_LIST_FILES);
        $thumbCntBefore = count($aThumbFiles);

        // create images in dirs
        $image = & new SGL_Image();
        $image->init($conf);
        $ret = $image->create($this->imageSampleFile, 'copy', $replace = true);

        $aFiles        = SGL_Util::listDir(SGL_UPLOAD_DIR, FILE_LIST_FILES);
        $filesCntAfter = count($aFiles);
        $aThumbFiles   = SGL_Util::listDir($thumbDir , FILE_LIST_FILES);
        $thumbCntAfter = count($aThumbFiles);
        $thumbCnt      = count($image->getThumbnailNames());

        // ensure image was created
        $this->assertEqual($filesCntBefore + 1, $filesCntAfter);
        $this->assertEqual($thumbCntBefore + $thumbCnt, $thumbCntAfter);

        // drop files
        $ret = $image->delete();
        $this->assertTrue($ret);

        // how many left after deletion
        $aFiles         = SGL_Util::listDir(SGL_UPLOAD_DIR, FILE_LIST_FILES);
        $filesCntResult = count($aFiles);
        $aThumbFiles    = SGL_Util::listDir($thumbDir , FILE_LIST_FILES);
        $thumbCntResult = count($aThumbFiles);

        $this->assertEqual($filesCntResult, $filesCntBefore);
        $this->assertEqual($thumbCntResult, $thumbCntBefore);
    }
}

class SGL_ImageTransform_FooStrategy extends SGL_ImageTransformStrategy
{
    function transform()
    {
        // do some dummy action, which exists in original driver
        return $this->driver->resize();
    }
}

?>