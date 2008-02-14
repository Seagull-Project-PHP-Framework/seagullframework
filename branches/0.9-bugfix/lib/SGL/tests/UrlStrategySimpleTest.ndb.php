<?php

require_once dirname(__FILE__) . '/../UrlParser/AliasStrategy.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class UrlStrategySimpleTest extends UnitTestCase
{

    function UrlStrategySimpleTest()
    {
        $this->UnitTestCase('simple strategy test');
    }

    function testSimpleParserNoParams()
    {
        $qs = '';
        $url = new SGL_Url($qs, true, new SGL_UrlParser_SimpleStrategy());
        $url->init();
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(!array_key_exists('moduleName', $ret));
        $this->assertTrue(!array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret, array());

    }

    function testParseResourceUriSlash()
    {
        $qs = '/';
        $url = new SGL_Url($qs, true, new SGL_UrlParser_SimpleStrategy());
        $url->init();
        $ret = $url->getQueryData();

        $this->assertTrue(!array_key_exists('module', $ret));
        $this->assertTrue(!array_key_exists('manager', $ret));
        //  assert expected values present
        $this->assertEqual($ret, array());
    }

    function testSimpleParserOneParam()
    {
        $qs = 'faq';
        $url = new SGL_Url($qs, true, new SGL_UrlParser_SimpleStrategy());
        $url->init();
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'faq');
        $this->assertEqual($ret['managerName'], 'faq');
    }

    function testSimpleParserTwoParams()
    {
        $qs = 'user/account';
        $url = new SGL_Url($qs, true, new SGL_UrlParser_SimpleStrategy());
        $url->init();
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'account');
    }

    function testParseResourceUriFullString()
    {
        $qs = 'contactus/contactus/action/list/enquiry_type/Hosting info';
        $url = new SGL_Url($qs, true, new SGL_UrlParser_SimpleStrategy());
        $url->init();
        $ret = $url->getQueryData();

        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('enquiry_type', $ret));

        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['enquiry_type'], 'Hosting info');
    }

    function testParseResourceUriFullStringWithEncoding()
    {
        $qs = 'contactus/contactus/action/list/enquiry_type/Get+a+quote';
        $url = new SGL_Url($qs, true, new SGL_UrlParser_SimpleStrategy());
        $url->init();
        $ret = $url->getQueryData();

        $this->assertTrue(is_array($ret));
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        $this->assertTrue(array_key_exists('action', $ret));
        $this->assertTrue(array_key_exists('enquiry_type', $ret));

        $this->assertEqual($ret['moduleName'], 'contactus');
        $this->assertEqual($ret['managerName'], 'contactus');
        $this->assertEqual($ret['action'], 'list');
        $this->assertEqual($ret['enquiry_type'], 'Get a quote');
    }
}
?>