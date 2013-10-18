<?php

class SGL_ImageTransformStrategyTest extends PHPUnit_Framework_TestCase
{
    var $imageSampleFile;

    function setUp()
    {
        $this->markTestSkipped(
            'problem loading config file ...'
        );
//        $this->imageSampleFile = dirname(__FILE__) . '/chicago.jpg';
    }

    function tearDown()
    {
    }

    function testLoad()
    {
        require 'Image/Transform/Driver/GD/SGL.php';
        $driver = $this->getMock('Image_Transform_Driver_GD_SGL');
        $driver->expects($this->imageSampleFile
            ->method('load')
            ->will(null));

        $strategy = new SGL_ImageTransform_FooStrategy1($driver);
        $ret = $strategy->load('/path/to/file_not_found.jpg');
        $this->assertInstanceOf('PEAR_Error', $ret);

        $strategy->load($this->imageSampleFile);
    }

    function testSave()
    {
        $argSaveFormat  = '';
        $argSaveQuality = '75';

        $driver = $this->getMock('Image_Transform_Driver_GD_SGL');
        $driver->expects($this->foo()
            ->method('free')
            ->will(null));

//        $driver->expectOnce('save', array($this->imageSampleFile,
//            $argSaveFormat, $argSaveQuality));

        $strategy = new SGL_ImageTransform_FooStrategy1($driver);
        $strategy->load($this->imageSampleFile);
        $strategy->save($argSaveQuality, $argSaveFormat);
    }
}

class SGL_ImageTransform_FooStrategy1
{
    function transform()
    {
    }
}

?>