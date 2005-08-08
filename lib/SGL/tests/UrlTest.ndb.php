<?php
require_once dirname(__FILE__) . '/../Url.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class UrlTest extends UnitTestCase {

    function UrlTest()
    {
        $this->UnitTestCase('Url Test');
    }
    
    function setup()
    {
        $this->url = new SGL_Url();
    }

    function testParseResourceUriFullString()
    {
        $url = 'contactus/contactus/action/list/enquiry_type/Hosting info';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertTrue(array_key_exists('actionMapping', $ret));        
        $this->assertTrue(is_array($ret['parsed_params']));
        $this->assertTrue(array_key_exists('enquiry_type', $ret['parsed_params']));        
    }
    
    function testParseResourceUriSlash()
    {
        $url = '/';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
    }

    function testParseResourceUriEmpty()
    {
        $url = '';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
    }
    
    function testGetSignificantSegments()
    {
        //  test random string
        $url = 'foo/bar/baz/quux';
        $ret = $this->url->getSignificantSegments($url);
        $this->assertEqual($ret, array());
        
        //  test with valid frontScriptName, should return 4 elements
        $url = 'index.php/bar/baz/quux';
        $ret = $this->url->getSignificantSegments($url);
        $this->assertTrue(count($ret), 4);
        
        //  test with valid frontScriptName + leading slash, should return 4 elements
        $url = '/index.php/bar/baz/quux';
        $ret = $this->url->getSignificantSegments($url);
        $this->assertTrue(count($ret), 4);
        
        //  test with valid frontScriptName + trailing slash, should return 4 elements
        $url = '/index.php/bar/baz/quux/';
        $ret = $this->url->getSignificantSegments($url);
        $this->assertTrue(count($ret), 4);
        
        //  test with valid frontScriptName, should return 3 elements
        $url = '/bar/index.php/baz/quux/';
        $ret = $this->url->getSignificantSegments($url);
        $this->assertTrue(count($ret), 3);
        
        //  test with valid frontScriptName, should return 1 element
        $url = '/foo/bar/baz/index.php/';
        $ret = $this->url->getSignificantSegments($url);
        $this->assertTrue(count($ret), 1);
    }
    
    function testContainsDuplicates()
    {
        $url = '/index.php/faq/faq/';
        $this->assertTrue($this->url->containsDuplicates($url));
        
        $url = 'http://example.com/index.php/foo/foo';
        $this->assertTrue($this->url->containsDuplicates($url));
        
        //  ignores whitespace
        $url = 'http://example.com/index.php/foo/foo /';
        $this->assertTrue($this->url->containsDuplicates($url));
        
        $url = 'http://example.com/index.php/foo/fooo';
        $this->assertFalse($this->url->containsDuplicates($url));
        
        //  case sensitive
        $url = 'FOO/foo';
        $this->assertFalse($this->url->containsDuplicates($url));
        
        //  minimal
        $url = 'baz/baz';
        $this->assertTrue($this->url->containsDuplicates($url));
    }
    
    function testIsSimplified()
    {
        
    }
}
?>