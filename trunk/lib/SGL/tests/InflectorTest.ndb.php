<?php
require_once dirname(__FILE__) . '/../Request.php'; // for now inflector is in here to minimise file loading

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class InflectorTest extends UnitTestCase {

    function InflectorTest()
    {
        $this->UnitTestCase('Inflector Test');
    }

    function testGetTitleFromCamelCase()
    {
        $camelWord = 'thisIsAnotherCamelWord';
        $ret = SGL_Inflector::getTitleFromCamelCase($camelWord);
        $this->assertEqual($ret, 'This Is Another Camel Word');
    }

    function testIsCamelCase()
    {
        $str = 'thisIsCamel';
        $this->assertTrue(SGL_Inflector::isCamelCase($str));

        $str = 'ThisIsCamel';
        $this->assertTrue(SGL_Inflector::isCamelCase($str));

        $str = 'this_Is_not_Camel';
        $this->assertFalse(SGL_Inflector::isCamelCase($str));

        $str = 'thisisnotcamel';
        $this->assertFalse(SGL_Inflector::isCamelCase($str));

        $str = 'Thisisnotcamel';
        $this->assertFalse(SGL_Inflector::isCamelCase($str));

        $str = 'thisisnotcameL';
        $this->assertFalse(SGL_Inflector::isCamelCase($str));
    }

    function testUrlContainsDuplicates()
    {
        $url = '/index.php/faq/faq/';
        $this->assertTrue(SGL_Inflector::urlContainsDuplicates($url));

        $url = 'http://example.com/index.php/foo/foo';
        $this->assertTrue(SGL_Inflector::urlContainsDuplicates($url));

        //  ignores whitespace
        $url = 'http://example.com/index.php/foo/foo /';
        $this->assertTrue(SGL_Inflector::urlContainsDuplicates($url));

        $url = 'http://example.com/index.php/foo/fooo';
        $this->assertFalse(SGL_Inflector::urlContainsDuplicates($url));

        //  case sensitive
        $url = 'FOO/foo';
        $this->assertFalse(SGL_Inflector::urlContainsDuplicates($url));

        //  minimal
        $url = 'baz/baz';
        $this->assertTrue(SGL_Inflector::urlContainsDuplicates($url));
    }

    function testIsUrlSimplified()
    {
        //  basic example
        $url = 'example.com/index.php/faq';
        $sectionName = 'example.com/index.php/faq/faq';
        $this->assertTrue(SGL_Inflector::isUrlSimplified($url, $sectionName));

        //  minimal
        $url = 'index.php/faq';
        $sectionName = 'index.php/faq/faq';
        $this->assertTrue(SGL_Inflector::isUrlSimplified($url, $sectionName));
    }

    function testGetManagerNameFromSimplifiedName()
    {
        $url = 'foobar';
        $ret = SGL_Inflector::getManagerNameFromSimplifiedName($url);
        $this->assertEqual($ret, 'FoobarMgr');

        //  test case sensitivity
        $this->assertNotEqual($ret, 'Foobarmgr');

        //  cannot deal with arbitrary bumpy caps
        $url = 'foobarbaz';
        $ret = SGL_Inflector::getManagerNameFromSimplifiedName($url);
        $this->assertNotEqual($ret, 'FooBarBazMgr'); //  returns FoobarbazMgr

        //  does not fix incorrect case
        $url = 'FoObArMGr';
        $ret = SGL_Inflector::getManagerNameFromSimplifiedName($url);
        $this->assertNotEqual($ret, 'FoobarMgr'); // returns FoObArMGr

        $url = 'FooBarMgr';
        $ret = SGL_Inflector::getManagerNameFromSimplifiedName($url);
        $this->assertEqual($ret, 'FooBarMgr');
    }

    function testGetSimplifiedNameFromManagerName()
    {
        $url = 'FooBarMgr';
        $ret = SGL_Inflector::getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');

        $url = 'FooBar';
        $ret = SGL_Inflector::getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');

        $url = 'FooBarMgr.php';
        $ret = SGL_Inflector::getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');

        $url = 'FooBar.php';
        $ret = SGL_Inflector::getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');
    }
}

?>