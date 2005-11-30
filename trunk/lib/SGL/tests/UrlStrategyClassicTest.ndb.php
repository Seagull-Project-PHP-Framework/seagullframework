<?php

require_once dirname(__FILE__) . '/../UrlParserClassicStrategy.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class UrlStrategyClassicTest extends UnitTestCase
{

    function UrlStrategyClassicTest()
    {
        $this->UnitTestCase('classic strategy test');
    }

    function testClassicParser()
    {
        $uri = 'http://example.com?moduleName=user&managerName=account';
        $url = new SGL_Url($uri, true, new SGL_UrlParserClassicStrategy());
        $ret = $url->getQueryData();

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'user');
        $this->assertEqual($ret['managerName'], 'account');
    }
}
?>