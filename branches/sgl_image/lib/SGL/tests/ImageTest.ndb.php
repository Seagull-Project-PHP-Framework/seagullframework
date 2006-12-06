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
    var $imageConfFile;
    var $imageSampleFile;

    function ImageTest()
    {
        $this->UnitTestCase('Image Test');
    }

    function setUp()
    {
        $this->imageConfFile   = dirname(__FILE__) . '/image.ini';
        $this->imageSampleFile = dirname(__FILE__) . '/chicago.jpg';
    }

    function tearDown()
    {
    }

    function _clearDir($dir, $includeParent = false)
    {
        require_once 'System.php';

        $aFiles = $this->_getFiles($dir);
        foreach ($aFiles as $fileName) {
            System::rm(array('-r', $dir . '/' . $fileName));
        }
        if ($includeParent) {
            System::rm(array($dir));
        }
    }

    function _getFiles($dir)
    {
        require_once 'File/Util.php';
        require_once SGL_CORE_DIR . '/Util.php';

        $aDirFiles = SGL_Util::listDir($dir);
        $aRet      = array();
        foreach ($aDirFiles as $fileName) {
            if ($fileName != '..' && $fileName != '.') {
                $aRet[] = $fileName;
            }
        }
        return $aRet;
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

    /*
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

    function testCreate()
    {
        // we could simply call $image->init($this->imageConfFile),
        // but we need to correct options a bit before initializing
        // SGL_Image instance
        $params  = SGL_Image::getParamsFromFile($this->imageConfFile);
        // take default section for modification
        $section = SGL_IMAGE_DEFAULT_SECTION;
        $params  = $params[SGL_IMAGE_DEFAULT_SECTION];

        // dropping medium thumb
        unset($params['thumbnails']['medium']);

        // create and init an instance
        $image = & new SGL_Image();
        $image->init($params);

        // copy image to keep sample file at place
        $ok = $image->create($this->imageSampleFile, $replace = false, 'copy');
        // image copied and thumbnails created
        $this->assertTrue($ok);

        unset($image);
        $params['thumbDir'] = 'my_thumb_dir';

        // init with filename
        $image = & new SGL_Image('chicago_copy.jpg');
        $image->init($params);

        $ok = $image->create($this->imageSampleFile, $replace = false, 'copy');
        $this->assertTrue($ok);

        // empty directory from created files
        $this->_clearDir(SGL_UPLOAD_DIR);

        // create file
        $ok = copy($this->imageSampleFile, SGL_UPLOAD_DIR . '/chicago_copy.jpg');

        // try to copy file, but failed, 'cos file allready exists and
        // we can't override it in 'upload' mode
        $ok = $image->create($this->imageSampleFile, $replace = false, 'copy');
        $this->assertIsA($ok, 'PEAR_Error');

        // SGL_Image#replace($fileName, $callback) is an alias of
        // SGL_Image#create($fileName, $replace = true, $callback)
        $ok = $image->replace($this->imageConfFile, 'copy');
        $this->assertTrue($ok);

        $this->_clearDir(SGL_UPLOAD_DIR);
    }

    function testDelete()
    {
        $moduleName = 'testModule';
        $image = & new SGL_Image('riga.jpg', $moduleName);
        $image->init($this->imageConfFile);
        $image->_aParams['thumbDir'] = '';

        // success on upload
        $ok = $image->create($this->imageSampleFile, false, 'copy');
        $this->assertTrue($ok);

        // we specified module name => upload dir is as follows
        $uploadDir = SGL_MOD_DIR . '/' . $moduleName . '/www/images';

        // four elements
        //  - riga.jpg - file
        //  - large    - dir
        //  - small    - dir
        //  - medium   - dir
        $aFiles = $this->_getFiles($uploadDir);
        $this->assertEqual(4, count($aFiles));

        // list thumb dirs
        $aThumbs = array_keys($image->_aThumbnails);
        foreach ($aThumbs as $thumbName) {
            $aFiles = $this->_getFiles($uploadDir . '/' . $thumbName);
            $this->assertEqual(1, count($aFiles));
        }

        // delete image and all it's thumbnails
        $ok = $image->delete();
        $this->assertTrue($ok);

        // only folders remained
        $aFiles = $this->_getFiles($uploadDir);
        $this->assertEqual(3, count($aFiles));

        // list thumb dirs
        foreach ($aThumbs as $thumbName) {
            $aFiles = $this->_getFiles($uploadDir . '/' . $thumbName);
            $this->assertEqual(0, count($aFiles));
        }

        // cleanup
        $this->_clearDir(SGL_MOD_DIR . '/' . $moduleName, $includeParent = true);
    }
    */
}

?>