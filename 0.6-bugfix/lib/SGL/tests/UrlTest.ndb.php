<?php
/*
FIXME: note from wiki, verify:
 about 5–10% of all URLs are not in makeUrl() format
    * one fix for some of these is the need to parse obj.method format vars in templates, currently only array[element] are dealt with
    * array[element] [subelement] are not dealt with either
*/

require_once dirname(__FILE__) . '/../Url.php';
require_once dirname(__FILE__) . '/../Output.php';
require_once dirname(__FILE__) . '/../UrlParser/AliasStrategy.php';
require_once dirname(__FILE__) . '/../UrlParser/ClassicStrategy.php';

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

        //  standard strategy array for subsequent URL objects
        $this->aStrats = array(
            new SGL_UrlParser_ClassicStrategy(),
            new SGL_UrlParser_SefStrategy(),
            new SGL_UrlParser_AliasStrategy()
            );
    }

    function testToPartialArray()
    {
        //  test random string
        $url = 'foo/bar/baz/quux';
        $ret = $this->url->toPartialArray($url, $frontScriptName = 'index.php');
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

    function testToPartialArrayWithEmptyUrlAndFrontScript()
    {
        $url = 'http://foo.com/';
        $ret = $this->url->toPartialArray($url, 'index.php');
        $this->assertFalse(count($ret));
    }

    function testToPartialArrayWithEmptyUrlAndWithoutFrontScript()
    {
        $url = 'http://foo.com/';
        $ret = $this->url->toPartialArray($url, '');
        $this->assertFalse(count($ret));
    }

    function testToPartialArrayWithEmptyUrlAndFalseFrontScript()
    {
        $url = 'http://foo.com/';
        $ret = $this->url->toPartialArray($url, false);
        $this->assertFalse(count($ret));
    }

    function xtestElementRepetionToPartialArrayWithFullUrlAndNoFrontScriptElement()
    {
        //  full URI object as path is needed for toPartialArray calculation
        $url = 'http://localhost/videovroom/video/action/list';
//        $conf = array('site' => array('frontScriptName' => false));
//        $url = new SGL_Url($str, true, $this->aStrats, $conf);
//        $ok = $url->init();
        $ret = $this->url->toPartialArray($url, false);
        $this->assertTrue(count($ret), 4);
    }

    function xtestToAbsolute()
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

    function xtestMakeLink()
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

    function xtestMakeLinkCollections()
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


    function xtestMakeLinkDirectFromManagers()
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

    function xtestMakeLinkUsingOutputObject()
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

    function xtestArrayOfStrategiesParam()
    {
        $url = new SGL_Url(null, true, $this->aStrats);
        $this->assertTrue(count($url->aStrategies), 3);
        foreach ($url->aStrategies as $strat) {
            $this->assertIsA($strat, 'SGL_UrlParser_Strategy');
        }
    }

    function testOverridingKeys()
    {
        $a = array('foo'=>'foo', 'bar' => 'bar', 'baz' => 'baz');
        $b = array('foo'=>'do', 'bar' => 'bar', 'baz' => 'mi');
        $ret = array_merge($a, $b);
        $this->assertEqual($ret, $b);
    }

    function testOverridingKeysWithBlanks()
    {
        $a = array('foo'=>'foo', 'bar' => 'bar', 'baz' => 'baz');
        $b = array();
        $ret = array_merge($a, $b);
        $this->assertEqual($ret, $a);
    }

    function testDynaMerge()
    {
        $a[] = array('foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz');
        $a[] = array('df' => 'df', 'er' => 'er', 'gh' => 'gh');
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
        $this->assertEqual($ret, $expected);
    }

    function xtestCorrerctClassicResultsWithMultipleStrats()
    {
        $uri = 'http://example.com?moduleName=user&managerName=account';

        $url = new SGL_Url($uri, true, $this->aStrats);
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'account');
    }

    function xtestCorrerctSefResultsWithMultipleStrats()
    {
        $uri = 'http://example.com/index.php/default/bug/';

        $url = new SGL_Url($uri, true, $this->aStrats);
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'default');
        $this->assertEqual($ret['managerName'], 'bug');
    }

    function xtestCorrerctAliasResultsWithMultipleStrats()
    {
        $uri = 'http://example.com/index.php/seagull-php-framework/';

        $url = new SGL_Url($uri, true, $this->aStrats);
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'default');
        $this->assertEqual($ret['managerName'], 'default');
    }


    function testGetStrategiesFingerprint()
    {
        $url = new SGL_Url('', true, $this->aStrats);
        $fingerprint = $url->getStrategiesFingerprint($url->aStrategies);
        $target = 'sgl_urlparser_classicstrategysgl_urlparser_sefstrategysgl_urlparser_aliasstrategy';
        $this->assertEqual(strtolower($fingerprint), $target); // added strtolower for php4 compat
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