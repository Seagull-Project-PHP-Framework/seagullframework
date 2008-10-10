<?php
/*

to run test suite, cd to this dir and:
$ phpunit AllTests.php

*/

require_once dirname(__FILE__) . '/../../SGL2.php';
require_once 'ArrayTest.php';
require_once 'InflectorTest.php';
require_once 'RegistryTest.php';
require_once 'RequestTest.php';
require_once 'Config2Test.php';

class SGL_AllTests {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName('SGL libs');
        $suite->addTestSuite('ArrayTest');
        $suite->addTestSuite('InflectorTest');
        $suite->addTestSuite('RegistryTest');
        $suite->addTestSuite('RequestTest');
        $suite->addTestSuite('Config2Test');
        return $suite;
    }
}

if (PHP_SAPI != 'cli') {
    SGL_AllTests::main();
}

?>