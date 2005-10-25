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
}

?>