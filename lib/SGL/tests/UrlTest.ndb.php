<?php
/*
FIXME: note from wiki, verify:
 about 5–10% of all URLs are not in makeUrl() format
    * one fix for some of these is the need to parse obj.method format vars in templates, currently only array[element] are dealt with
    * array[element] [subelement] are not dealt with either
*/

require_once dirname(__FILE__) . '/../Url.php';
require_once dirname(__FILE__) . '/../Output.php';
require_once dirname(__FILE__) . '/../UrlParserAliasStrategy.php';
require_once dirname(__FILE__) . '/../UrlParserClassicStrategy.php';
require_once dirname(__FILE__) . '/../UrlParserSimpleStrategy.php';

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
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
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

    function testToPartialArray()
    {
        //  test random string
        $url = 'foo/bar/baz/quux';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertEqual($ret, array());

        //  test with valid frontScriptName, should return 4 elements
        $url = 'index.php/bar/baz/quux';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertTrue(count($ret), 4);

        //  test with valid frontScriptName + leading slash, should return 4 elements
        $url = '/index.php/bar/baz/quux';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertTrue(count($ret), 4);

        //  test with valid frontScriptName + trailing slash, should return 4 elements
        $url = '/index.php/bar/baz/quux/';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertTrue(count($ret), 4);

        //  test with valid frontScriptName, should return 3 elements
        $url = '/bar/index.php/baz/quux/';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertTrue(count($ret), 3);

        //  test with valid frontScriptName, should return 1 element
        $url = '/foo/bar/baz/index.php/';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertTrue(count($ret), 1);
    }

    function testToPartialArrayWithFullUrl()
    {
        //  test with valid frontScriptName + leading slash, should return 4 elements
        $url = 'http://foo.com/index.php/bar/baz/quux/';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertTrue(count($ret), 4);
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

    function testArrayOfStrategiesParam()
    {
        $aStrats = array(
            new SGL_UrlParserClassicStrategy(),
            new SGL_UrlParserSefStrategy(),
            new SGL_UrlParserAliasStrategy()
            );

        $url = new SGL_Url(null, true, $aStrats);
        $this->assertTrue(count($url->parserStrategy), 3);
        foreach ($url->parserStrategy as $strat) {
            $this->assertIsA($strat, 'SGL_UrlParserStrategy');
        }
    }

    function testOverridingKeys()
    {
        $a = array('foo'=>'foo', 'bar' => 'bar', 'baz' => 'baz');
        $b = array('foo'=>'do', 'bar' => 'bar', 'baz' => 'mi');
        $ret = array_merge($a, $b);
        $this->assertTrue($ret, $b);
    }

    function testOverridingKeysWithBlanks()
    {
        $a = array('foo'=>'foo', 'bar' => 'bar', 'baz' => 'baz');
        $b = array();
        $ret = array_merge($a, $b);
        $this->assertTrue($ret, $a);
    }

    function testDynaMerge()
    {
        $a[] = array('foo'=>'foo', 'bar' => 'bar', 'baz' => 'baz');
        $a[] = array('df'=>'df', 'er' => 'er', 'gh' => 'gh');
        $a[] = array();

        $expected = array (
          'foo' => 'foo',
          'bar' => 'bar',
          'baz' => 'baz',
          'df' => 'df',
          'er' => 'er',
          'gh' => 'gh',
        );
        $ret = call_user_func_array('array_merge', $a);
        $this->assertTrue($ret, $expected);
    }

    function testClassicParserSimpleWithMultipleStrats()
    {
        $uri = 'http://example.com?moduleName=user&managerName=account';

        $aStrats = array(
            new SGL_UrlParserClassicStrategy(),
            new SGL_UrlParserSefStrategy(),
            new SGL_UrlParserAliasStrategy()
            );

        $url = new SGL_Url($uri, true, $aStrats);
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'account');
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