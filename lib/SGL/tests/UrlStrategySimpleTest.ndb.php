<?php

require_once dirname(__FILE__) . '/../UrlParserSimpleStrategy.php';

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

    function testSimpleParserTwoParams()
    {
        $qs = 'user/account';
        $url = new SGL_Url($qs, true, new SGL_UrlParserSimpleStrategy());
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'account');
    }

    function testSimpleParserNoParams()
    {
        $qs = '';
        $url = new SGL_Url($qs, true, new SGL_UrlParserSimpleStrategy());
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(!array_key_exists('moduleName', $ret));
        $this->assertTrue(!array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret, array());

    }
}
?>