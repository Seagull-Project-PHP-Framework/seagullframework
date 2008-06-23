<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class UrlStrategySefTest extends UnitTestCase
{

    function UrlStrategySefTest()
    {
        $this->UnitTestCase('SEF strategy test');
    }

    function setup()
    {
        $this->strategy = new SGL_UrlParser_SefStrategy();
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->obj = new stdClass();
        $this->exampleUrl = 'http://example.com/';
    }

    function tearDown()
    {
        unset($this->strategy, $this->obj);
    }

    //  test urls like /
    function testSingleSlashUrls()
    {
        $this->obj->url = $this->exampleUrl;
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present, default module + mgr values
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        //  assert expected values present
        $this->assertEqual(count($ret), 2);
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
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('enquiry_type', $ret));

        //  assert expected values present
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

        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('enquiry_type', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['enquiry_type'], 'Hosting info');
    }

    //  simplified mod/mgr name, no action + params
    function testMakeSearchEngineFriendlySimplifiedWithParams()
    {
        $aUrlSegments = array (
          0 => 'index.php',
          1 => 'contactus',
          2 => 'foo',
          3 => 'bar',

        );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('foo', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['foo'], 'bar');
    }

    //  test Zend debug GET noise [position 1]
    function testMakeSearchEngineFriendlyWithZendDebugInfoInFrontScriptNamePosition()
    {
        $aUrlSegments = array (
            '?start_debug=1&debug_port=10000&debug_host=192.168.1.23,127.0.0.1&send_sess_end=1&debug_no_cache=1123518013790&debug_stop=1&debug_url=1&debug_start_session=1',
          );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present, default module + mgr values
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual(count($ret), 2);
    }

    //  test Zend debug GET noise [position 2]
    function testMakeSearchEngineFriendlyWithZendDebugInfoInModulePosition()
    {
        $aUrlSegments = array (
            'index.php',
            '?start_debug=1&debug_port=10000&debug_host=192.168.1.23,127.0.0.1&send_sess_end=1&debug_no_cache=1123518013790&debug_stop=1&debug_url=1&debug_start_session=1',
          );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(!array_key_exists('moduleName', $ret));
        $this->assertTrue(!array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret, array());
    }

    //  test Zend debug GET noise [position 3]
    function testMakeSearchEngineFriendlyWithZendDebugInfoInMgrPosition()
    {
        $aUrlSegments = array (
            'index.php',
            'user',
            '?start_debug=1&debug_port=10000&debug_host=192.168.1.23,127.0.0.1&send_sess_end=1&debug_no_cache=1123518013790&debug_stop=1&debug_url=1&debug_start_session=1',
          );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
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
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
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
            'quux',
            'baz[]',
            'quux2',
          );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('foo', $ret));
        $this->assertTrue(array_key_exists('foo1', $ret['foo']));
        $this->assertTrue(array_key_exists('baz', $ret));
        $this->assertTrue(is_array($ret['baz']));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'user');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['foo'], array('foo1' => 'bar[bar1]'));
        $this->assertEqual($ret['baz'][0], 'quux');
        $this->assertEqual($ret['baz'][1], 'quux2');
    }

    function testClassicParserSimpleWithMultipleStrats()
    {
        $uri = 'http://example.com?moduleName=user&managerName=account';

        $url = new SGL_Url($uri, true, new SGL_UrlParser_SefStrategy());
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(!array_key_exists('moduleName', $ret));
        $this->assertTrue(!array_key_exists('managerName', $ret));
        $this->assertEqual($ret, array());
    }

    function testQuerystringToHash1()
    {
        $str = 'frmArticleId/3/foo/bar';
        $res = SGL_Url::querystringArrayToHash(explode('/', $str));
        $expected = array ( 'frmArticleId' => '3', 'foo' => 'bar', );
        $this->assertEqual($res, $expected);
    }

    function testQuerystringToHash2()
    {

    }

}
?>