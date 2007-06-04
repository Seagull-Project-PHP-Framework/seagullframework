<?php

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

    function testCamelise()
    {
        $aControl[] = 'Here is a string to camelise';
        $aControl[] = ' here IS a StrIng tO CameLise';
        $aControl[] = ' Here  is a  STRING To  CameliSE';
        $aControl[] = "Here is\na string\n\nto camelise";
        $expected   = 'hereIsAStringToCamelise';

        foreach ($aControl as $k => $control) {
            $ret = SGL_Inflector::camelise($control);
            $this->assertEqual($expected, $ret);
        }
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

    function testIsUrlSimplified1()
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

    function testIsUrlSimplified2()
    {
        $url = 'contactus/contactus';
        $sectionName = $url;
        $this->assertFalse(SGL_Inflector::isUrlSimplified($url, $sectionName));
    }

    function testGetManagerNameFromSimplifiedName()
    {
        $url = 'foobar';
        //  set key so caseFix can work
        $c = &SGL_Config::singleton();
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

    function testCaseFix()
    {
        $c = &SGL_Config::singleton();
        $c->set('DefaultMgr', array('requiresAuth' => false));
        $incorrect = 'defaultmgr';
        $ret = SGL_Inflector::caseFix($incorrect);
        $this->assertEqual($ret, 'DefaultMgr');
    }

    function testIsConstant()
    {
        $this->assertTrue(SGL_Inflector::isConstant('THIS_IS_A_CONSTANT'));
        $this->assertTrue(SGL_Inflector::isConstant('CONSTANT'));
        $this->assertTrue(SGL_Inflector::isConstant("'CONSTANT'"));
        $this->assertFalse(SGL_Inflector::isConstant('CONSTANTa'));
        $this->assertFalse(SGL_Inflector::isConstant('1'));
        $this->assertFalse(SGL_Inflector::isConstant(''));
        $this->assertFalse(SGL_Inflector::isConstant('127.0.0.1'));
        $this->assertFalse(SGL_Inflector::isConstant('/'));
        $this->assertFalse(SGL_Inflector::isConstant('SGLSESSID'));
        $this->assertFalse(SGL_Inflector::isConstant('CUR ADM OUR NOR STA NID'));
    }

    function test_isMgrNameOmitted()
    {
        $aParsedUri = array(
            'moduleName' => 'default',
            'managerName' => 'config',
            );
        $this->assertFalse(SGL_Inflector::isMgrNameOmitted($aParsedUri));

        $aParsedUri = array(
            'moduleName' => 'default',
            'managerName' => 'foo',
            );
        $this->assertTrue(SGL_Inflector::isMgrNameOmitted($aParsedUri));

        $aParsedUri = array(
            'moduleName' => 'foo',
            'managerName' => 'bar',
            );
        $this->assertFalse(SGL_Inflector::isMgrNameOmitted($aParsedUri));

        //  a case where a module does not have a default mgr
        $aParsedUri = array(
            'moduleName' => 'navigation',
            'managerName' => 'bar',
            );
        $this->assertFalse(SGL_Inflector::isMgrNameOmitted($aParsedUri));
    }
}

?>