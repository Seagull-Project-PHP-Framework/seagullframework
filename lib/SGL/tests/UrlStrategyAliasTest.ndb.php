<?php

require_once dirname(__FILE__) . '/../UrlParserAliasStrategy.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class UrlStrategyAliasTest extends UnitTestCase
{

    function UrlStrategySefTest()
    {
        $this->UnitTestCase('alias strategy test');
    }

    function setup()
    {
        $this->strategy = new SGL_UrlParserAliasStrategy();
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->obj = new stdClass();
        $this->exampleUrl = 'http://example.com/';
    }

    function tearDown()
    {
        unset($this->strategy, $this->obj);
    }

    function testMakeSearchEngineFriendlyBasic()
    {
        $aUrlSegments = array (
          0 => 'index.php',
          1 => 'seagull-php-framework',
        );
        $obj = new SGL_Url();
        $obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($obj, $this->conf);

        //  assert expected keys present
        $this->assertTrue(array_key_exists('moduleName', $ret));
        $this->assertTrue(array_key_exists('managerName', $ret));
        #$this->assertTrue(array_key_exists('action', $ret));

        //  assert expected values present
        $this->assertEqual($ret['moduleName'], 'default');
        $this->assertEqual($ret['managerName'], 'default');
        #$this->assertEqual($ret['action'], 'list');
    }
}
?>