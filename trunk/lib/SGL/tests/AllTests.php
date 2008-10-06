<?php
/*

to run test suite, cd to this dir and:
$ phpunit AllTests.php

*/

require_once dirname(__FILE__) . '/../../../www/init.php';
require_once 'ArrayTest.php';
require_once 'InflectorTest.php';
class SGL_AllTests {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite(), array());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName('SGL libs');
        $suite->addTestSuite('ArrayTest');
        $suite->addTestSuite('InflectorTest');
        return $suite;
    }
}

SGL_AllTests::main();

?>