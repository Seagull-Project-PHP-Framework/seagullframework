<?php

require_once dirname(__FILE__) . '/../Image.php';
require_once SGL_LIB_PEAR_DIR . '/Image/Transform/Driver/GD_SGL.php';

class ImageTransformStrategyTest extends UnitTestCase
{
    var $imageSampleFile;

    function ImageTransformStrategyTest()
    {
        $this->UnitTestCase('ImageTransformStragegy Test');

        Mock::generate('Image_Transform_Driver_GD_SGL');
    }

    function setUp()
    {
        $this->imageSampleFile = dirname(__FILE__) . '/chicago.jpg';
    }

    function tearDown()
    {
    }

    function testLoad()
    {
        $driver = & new MockImage_Transform_Driver_GD_SGL();
        $driver->expectOnce('load', array($this->imageSampleFile));

        $strategy = & new SGL_ImageTransform_FooStrategy1($driver);
        $ret = $strategy->load('/path/to/file_not_found.jpg');
        $this->assertIsA($ret, 'PEAR_Error');

        $strategy->load($this->imageSampleFile);
    }

    function testSave()
    {
        $argSaveFormat  = '';
        $argSaveQuality = '75';

        $driver = & new MockImage_Transform_Driver_GD_SGL();
        $driver->expectOnce('free', array());
        $driver->expectOnce('save', array($this->imageSampleFile,
            $argSaveFormat, $argSaveQuality));

        $strategy = & new SGL_ImageTransform_FooStrategy1($driver);
        $strategy->load($this->imageSampleFile);
        $strategy->save($argSaveQuality, $argSaveFormat);
    }
}

class SGL_ImageTransform_FooStrategy1 extends SGL_ImageTransformStrategy
{
    function transform()
    {
    }
}

?>