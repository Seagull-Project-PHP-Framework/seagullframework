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
        $conf = & $GLOBALS['_SGL']['CONF'];
        $this->url = new SGL_Url();
        $this->baseUrlString = SGL_BASE_URL . '/' . $conf['site']['frontScriptName'] . '/';
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
        //  basic example
        $url = 'example.com/index.php/faq';
        $sectionName = 'example.com/index.php/faq/faq';
        $this->assertTrue($this->url->isSimplified($url, $sectionName));
        
        //  minimal
        $url = 'index.php/faq';
        $sectionName = 'index.php/faq/faq';
        $this->assertTrue($this->url->isSimplified($url, $sectionName));
    }
    
    function testGetManagerNameFromSimplifiedName()
    {
        $url = 'foobar';
        $ret = $this->url->getManagerNameFromSimplifiedName($url);
        $this->assertEqual($ret, 'FoobarMgr');
        
        //  test case sensitivity
        $this->assertNotEqual($ret, 'Foobarmgr');
        
        //  cannot deal with arbitrary bumpy caps
        $url = 'foobarbaz';
        $ret = $this->url->getManagerNameFromSimplifiedName($url);
        $this->assertNotEqual($ret, 'FooBarBazMgr'); //  returns FoobarbazMgr
        
        //  does not fix incorrect case
        $url = 'FoObArMGr';
        $ret = $this->url->getManagerNameFromSimplifiedName($url);
        $this->assertNotEqual($ret, 'FoobarMgr'); // returns FoObArMGr
        
        $url = 'FooBarMgr';
        $ret = $this->url->getManagerNameFromSimplifiedName($url);
        $this->assertEqual($ret, 'FooBarMgr');
    }
    
    function testGetSimplifiedNameFromManagerName()
    {
        $url = 'FooBarMgr';
        $ret = $this->url->getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');
        
        $url = 'FooBar';
        $ret = $this->url->getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');
        
        $url = 'FooBarMgr.php';
        $ret = $this->url->getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');
        
        $url = 'FooBar.php';
        $ret = $this->url->getSimplifiedNameFromManagerName($url);
        $this->assertEqual($ret, 'foobar');
    }
    
    function testToAbsolute()
    {
        $url = 'example.com/index.php/Foo/Bar';
        $this->url->toAbsolute($url);
        $this->assertTrue(preg_match('/^http[s]?/', $url));
        
        //  if you supply an FQDN, 'secure' will not be checked
        $url = 'https://example.com/index.php/Foo/Bar';
        $this->url->toAbsolute($url);
        $this->assertTrue(preg_match('/^https/', $url));
        
        //  otherwise, 'secure' will be checked
        $url = 'example.com/index.php/Foo/Bar';
        $this->url->toAbsolute($url);
        $this->assertFalse(preg_match('/^https/', $url));
    }
    
    function testParseResourceUri()
    {
        //  empty URL returns default values
        $url = '';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertEqual($ret['module'], 'default');
        $this->assertEqual($ret['manager'], 'default');
        
        //  less than 2 elements returns default values
        $url = 'foo';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertEqual($ret['module'], 'default');
        $this->assertEqual($ret['manager'], 'default');
        
        //  basic module/manager names
        $url = 'publisher/articleview';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertTrue(array_key_exists('actionMapping', $ret));
        $this->assertEqual($ret['module'], 'publisher');
        $this->assertEqual($ret['manager'], 'articleview');
        $this->assertNull($ret['actionMapping']);
        
        //  with one set of params
        $url = 'publisher/articleview/frmArticleID/1';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertTrue(array_key_exists('actionMapping', $ret));
        $this->assertEqual($ret['module'], 'publisher');
        $this->assertEqual($ret['manager'], 'articleview');
        $this->assertNull($ret['actionMapping']);
        $this->assertTrue(is_array($ret['parsed_params']));
        $this->assertTrue(array_key_exists('frmArticleID', $ret['parsed_params']));
        $this->assertEqual($ret['parsed_params']['frmArticleID'], 1);
        
        //  with action and params, returns following:
        
        //    Array
        //    (
        //        [module] => publisher
        //        [manager] => articleview
        //        [actionMapping] => foo
        //        [parsed_params] => Array
        //            (
        //                [bar] => baz
        //            )
        //    
        //    )
        $url = 'publisher/articleview/action/foo/bar/baz';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertTrue(array_key_exists('actionMapping', $ret));
        $this->assertEqual($ret['module'], 'publisher');
        $this->assertEqual($ret['manager'], 'articleview');
        $this->assertEqual($ret['actionMapping'], 'foo');
        $this->assertTrue(is_array($ret['parsed_params']));
        $this->assertTrue(array_key_exists('bar', $ret['parsed_params']));
        $this->assertEqual($ret['parsed_params']['bar'], 'baz');
       
        //  test removing URL encoding
        $url = 'contactus/contactus/action/list/enquiry_type/Get+a+quote';
        $ret = $this->url->parseResourceUri($url);
        $this->assertTrue(array_key_exists('module', $ret));
        $this->assertTrue(array_key_exists('manager', $ret));
        $this->assertTrue(array_key_exists('actionMapping', $ret));
        $this->assertEqual($ret['module'], 'contactus');
        $this->assertEqual($ret['manager'], 'contactus');
        $this->assertEqual($ret['actionMapping'], 'list');
        $this->assertTrue(is_array($ret['parsed_params']));
        $this->assertTrue(array_key_exists('enquiry_type', $ret['parsed_params']));
        $this->assertEqual($ret['parsed_params']['enquiry_type'], 'Get a quote');
    }
    
    function testMakeSearchEngineFriendlyBasic()
    {
        $aUrlSegments = array (
          0 => 'index.php',
          1 => 'contactus',
          2 => 'contactus',
          3 => 'action',
          4 => 'list',
          5 => 'enquiry_type',
          6 => 'Hosting+info',
        );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('enquiry_type', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['enquiry_type'], 'Hosting info');
    }
    
    //  remove explicit contactus/contactus module/mgr mapping, see if FC can deduce
    function testMakeSearchEngineFriendlySimplified()
    {
        $aUrlSegments = array (
          0 => 'index.php',
          1 => 'contactus',
          2 => 'action',
          3 => 'list',
          4 => 'enquiry_type',
          5 => 'Hosting+info',
        );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('enquiry_type', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['enquiry_type'], 'Hosting info');
    }
    
    //  test Zend debug GET noise [position 1]
    function testMakeSearchEngineFriendlyWithZendDebugInfoInFrontScriptNamePosition()
    {
        $aUrlSegments = array (
            '?start_debug=1&debug_port=10000&debug_host=192.168.1.23,127.0.0.1&send_sess_end=1&debug_no_cache=1123518013790&debug_stop=1&debug_url=1&debug_start_session=1',
          );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'default');
        $this->assertEqual($ret['managerName'], 'default');
    }
    
    //  test Zend debug GET noise [position 2]
    function testMakeSearchEngineFriendlyWithZendDebugInfoInModulePosition()
    {
        $aUrlSegments = array (
            'index.php',
            '?start_debug=1&debug_port=10000&debug_host=192.168.1.23,127.0.0.1&send_sess_end=1&debug_no_cache=1123518013790&debug_stop=1&debug_url=1&debug_start_session=1',
          );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'default');
        $this->assertEqual($ret['managerName'], 'default');
    }
    
    //  test Zend debug GET noise [position 3]
    function testMakeSearchEngineFriendlyWithZendDebugInfoInMgrPosition()
    {
        $aUrlSegments = array (
            'index.php',
            'user',
            '?start_debug=1&debug_port=10000&debug_host=192.168.1.23,127.0.0.1&send_sess_end=1&debug_no_cache=1123518013790&debug_stop=1&debug_url=1&debug_start_session=1',
          );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'user');
    }
    
    function testMakeSearchEngineFriendlyWithSessionInfo()
    {
        $aUrlSegments = array (
            'index.php',
            'user',
            '?SGLSESSID=4294a4bf7ac84738a60a85dafa70ae33&',
            '1',
          );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'user');
    }
    
    function testMakeSearchEngineFriendlyWithArrayParams()
    {
        $aUrlSegments = array (
            'index.php',
            'user',
            'action',
            'list',
            'foo[foo1]',
            'bar[bar1]',
            'baz[]',
            'quux'
          );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('foo', $ret));
        $this->assertTrue(array_key_exists('foo1', $ret['foo']));
        $this->assertTrue(array_key_exists('baz', $ret));
        $this->assertTrue(is_array($ret['baz']));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'user');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['foo'], array('foo1' => 'bar[bar1]'));
        $this->assertEqual($ret['baz'][0], 'quux');
    }
    
    function testMakeLink()
    {
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/default/
        $target = $this->baseUrlString . 'default/';
        $ret = $this->url->makeLink();
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/default/action/foo/
        $target = $this->baseUrlString . 'default/action/foo/';
        $ret = $this->url->makeLink($action = 'foo');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/default/bar/
        $target = $this->baseUrlString . 'default/bar/';
        $ret = $this->url->makeLink($action = '', $mgr = 'bar');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/default/
        $target = $this->baseUrlString . 'baz/default/';
        $ret = $this->url->makeLink($action = '', $mgr = '', $mod = 'baz');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/
        $target = $this->baseUrlString . 'baz/';
        $ret = $this->url->makeLink($action = '', $mgr = 'baz', $mod = 'baz');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/
        $target = $this->baseUrlString . 'baz/bar/action/foo/';
        $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/default/action/foo/
        $target = $this->baseUrlString . 'baz/default/action/foo/';
        $ret = $this->url->makeLink($action = 'foo', $mgr = '', $mod = 'baz');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/
        $target = $this->baseUrlString . 'baz/bar/action/foo/';
        $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aList = array(), 
            $params = '', $idx = 0, $output = '');
        $this->assertEqual($target, $ret);
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/
        $target = $this->baseUrlString . 'baz/bar/action/foo/';
        $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aList = array(), 
            $params = '', $idx = 0, $output = '');
        $this->assertEqual($target, $ret);

print '<pre>'; print_r($ret);
    }
}
?>