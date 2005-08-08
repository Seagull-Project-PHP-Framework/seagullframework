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

    function testParseResourceUriFullString()
    {
        $url = 'contactus/contactus/action/list/enquiry_type/Hosting info';
        $obj = new SGL_Url();
        $ret = $obj->parseResourceUri($url);
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
        $obj = new SGL_Url();
        $ret = $obj->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
    }

    function testParseResourceUriEmpty()
    {
        $url = '';
        $obj = new SGL_Url();
        $ret = $obj->parseResourceUri($url);
        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
    }
}
?>