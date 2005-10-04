<?php
require_once dirname(__FILE__) . '/../Url.php';
require_once dirname(__FILE__) . '/../Output.php';

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
        $this->url = new SGL_Url(null, false, new stdClass());
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
    
    function xtestGetSignificantSegments()
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
    
    function xtestMakeSearchEngineFriendlyBasic()
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
    function xtestMakeSearchEngineFriendlySimplified()
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
    
    //  simplified mod/mgr name, no action + params
    function xtestMakeSearchEngineFriendlySimplifiedWithParams()
    {
        $aUrlSegments = array (
          0 => 'index.php',
          1 => 'contactus',
          2 => 'foo',
          3 => 'bar',

        );
        $ret = $this->url->makeSearchEngineFriendly($aUrlSegments);
        
        //  assert expected keys present
        $this->assertTrue(array_key_exists('frontScriptName', $ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('foo', $ret));
        
        //  assert expected values present
        $this->assertEqual($ret['frontScriptName'], 'index.php');
        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['foo'], 'bar');
    }
    
    //  test Zend debug GET noise [position 1]
    function xtestMakeSearchEngineFriendlyWithZendDebugInfoInFrontScriptNamePosition()
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
    function xtestMakeSearchEngineFriendlyWithZendDebugInfoInModulePosition()
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
    function xtestMakeSearchEngineFriendlyWithZendDebugInfoInMgrPosition()
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
    
    function xtestMakeSearchEngineFriendlyWithSessionInfo()
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
    
    function xtestMakeSearchEngineFriendlyWithArrayParams()
    {
        $aUrlSegments = array (
            'index.php',
            'user',
            'action',
            'list',
            'foo[foo1]',
            'bar[bar1]',
            'baz[]',
            'quux',
            'baz[]',
            'quux2',
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
        $this->assertEqual($ret['baz'][1], 'quux2');
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
    }
    
    function testMakeLinkCollections()
    {
        $user1 = new Usr();
        $user1->usr_id = 1;
        $user1->username = 'foo';
        $user1->array_field = array('sub_element' => 'sub_foo');
        
        $user2 = new Usr();
        $user2->usr_id = 2;
        $user2->username = 'bar';
        $user2->array_field = array('sub_element' => 'sub_bar');
        
        $user3 = new Usr();
        $user3->usr_id = 3;
        $user3->username = 'baz';
        $user3->array_field = array('sub_element' => 'sub_baz');

        //  single k/v pair
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserID/3/
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserID/3/';
        
        $aCollection = array(
            (array)$user1, 
            (array)$user2, 
            (array)$user3, 
            );
        
        foreach ($aCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aCollection, 
                'frmUserID|usr_id', $k);
        }
        $this->assertEqual($target, $ret);
        
        
        //  multiple k/v pairs
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserID/3/frmUsername/baz/
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserID/3/frmUsername/baz/';
        
        foreach ($aCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aCollection, 
                'frmUserID|usr_id||frmUsername|username', $k);
        }
        $this->assertEqual($target, $ret);
        
        
        //  simple integer indexed array
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserType/2/
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserType/2/';
        
        $aSimpleCollection = array(
            'foo', 
            'bar', 
            'baz', 
            );
        
        foreach ($aSimpleCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aSimpleCollection, 
                'frmUserType', $k);
        }
        $this->assertEqual($target, $ret);
        
        
        //  simple integer indexed array with no action param
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/frmUserType/2/
        $target = $this->baseUrlString . 'baz/bar/frmUserType/2/';
        
        $aSimpleCollection = array(
            'foo', 
            'bar', 
            'baz', 
            );
        
        foreach ($aSimpleCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink('', $mgr = 'bar', $mod = 'baz', $aSimpleCollection, 
                'frmUserType', $k);
        }
        $this->assertEqual($target, $ret);
        
        
        //  simple integer indexed array with no action param, and mod name = mgr name
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/bar/frmUserType/2/
        $target = $this->baseUrlString . 'bar/frmUserType/2/';
        
        $aSimpleCollection = array(
            'foo', 
            'bar', 
            'baz', 
            );
        
        foreach ($aSimpleCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink('', $mgr = 'bar', $mod = 'bar', $aSimpleCollection, 
                'frmUserType', $k);
        }
        $this->assertEqual($target, $ret);


        //  random integer indexed array
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserType/916/
        $randIdx1 = rand(1, 999);
        $randIdx2 = rand(1, 999);
        $randIdx3 = rand(1, 999);
        
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserType/'.$randIdx3.'/';
        
        $aRandomCollection = array(
            $randIdx1 => 'foo', 
            $randIdx2 => 'bar', 
            $randIdx3 => 'baz', 
            );
        
        foreach ($aRandomCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aRandomCollection, 
                'frmUserType', $k);
        }
        $this->assertEqual($target, $ret);
        
        
        //  a collection of 3d arrays, eg:
        
        /*    [2] => Array
                (
                    [__table] => usr
                    [usr_id] => 3
                    [organisation_id] => 
                    [role_id] => 
                    [username] => baz
                    [passwd] => 
                    [first_name] => 
                    [last_name] => 
                    [telephone] => 
                    [mobile] => 
                    [email] => 
                    [addr_1] => 
                    [addr_2] => 
                    [addr_3] => 
                    [city] => 
                    [region] => 
                    [country] => 
                    [post_code] => 
                    [is_email_public] => 
                    [is_acct_active] => 
                    [security_question] => 
                    [security_answer] => 
                    [date_created] => 
                    [created_by] => 
                    [last_updated] => 
                    [updated_by] => 
                    [array_field] => Array
                        (
                            [sub_element] => sub_baz
                        )
        
                )*/
        
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserId/3/targetId/sub_baz/
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserId/3/targetId/sub_baz/';

        foreach ($aCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aCollection, 
                'frmUserId|usr_id||targetId|array_field[sub_element]', $k);
        }
        $this->assertEqual($target, $ret);
        
        //  an array of objects
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserId/3/
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserId/3/';

        $aCollection = array(
            $user1, 
            $user2, 
            $user3, 
            );
        foreach ($aCollection as $k => $user) {
            
            //  only interested in last element
            $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', $aCollection, 
                'frmUserId|usr_id', $k);
        }
        $this->assertEqual($target, $ret);
    }
        
        
    function testMakeLinkDirectFromManagers()
    {
        //  when method is invoked from a manager, ie, no $aList arg
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmNewsId/23/
        $obj = new stdClass();
        $obj->item_id = 23;
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmNewsId/23/';

        $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', array(), 
            "frmNewsId|$obj->item_id");
        $this->assertEqual($target, $ret);
    }
    
    function testMakeLinkUsingOutputObject()
    {
        //  when method is invoked from a template, but with no $aList arg, and a hash element
        //  in this case a category array has been assigned to the $output object
        //  see: categoryMgr.html
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmCatID/123/
        $output = new SGL_Output();
        $category = array('category_id' => 123);
        $output->category = $category;
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmCatID/123/';

        $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', array(), 
            "frmCatID|category[category_id]", 0, $output);
        $this->assertEqual($target, $ret);

        
        //  accessing an $output object property, no collection
        //  see banner.html
        //  http://localhost.localdomain/seagull/branches/0.4-bugfix/www/index.php/baz/bar/action/foo/frmUserID/456/
        $output = new SGL_Output();
        $output->loggedOnUserID = 456;
        $target = $this->baseUrlString . 'baz/bar/action/foo/frmUserID/456/';

        $ret = $this->url->makeLink($action = 'foo', $mgr = 'bar', $mod = 'baz', array(), 
            "frmUserID|loggedOnUserID", 0, $output);
        $this->assertEqual($target, $ret);
    }
}

class Usr
{
    var $usr_id;                          // int(11)  not_null primary_key
    var $organisation_id;                 // int(11)  not_null
    var $role_id;                         // int(11)  not_null
    var $username;                        // string(64)  multiple_key
    var $passwd;                          // string(32)  
    var $first_name;                      // string(128)  
    var $last_name;                       // string(128)  
    var $telephone;                       // string(16)  
    var $mobile;                          // string(16)  
    var $email;                           // string(128)  multiple_key
    var $addr_1;                          // string(128)  
    var $addr_2;                          // string(128)  
    var $addr_3;                          // string(128)  
    var $updated_by;                      // int(11) 
}
?>