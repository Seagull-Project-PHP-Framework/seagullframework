<?php

require_once dirname(__FILE__) . '/../UrlParser/AliasStrategy.php';
require_once SGL_MOD_DIR . '/navigation/classes/NavigationDAO.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class UrlStrategyAliasTest extends UnitTestCase
{

    function UrlStrategyAliasTest()
    {
        $this->UnitTestCase('alias strategy test');
    }

    function setup()
    {
        $c = &SGL_Config::singleton();
        $this->da = & NavigationDAO::singleton($forceNew = true);
        $this->conf = $c->getAll();
        $this->obj = new stdClass();
        $this->dbh = & SGL_DB::singleton();
        $this->exampleUrl = 'http://example.com/';
    }

    // Testing the simplest alias in the end of uri
    // Simple alias: index.php/test/

    function testSimpleAlias() {
        $section = array (
          'title' => 'Alias Test Section',
          'parent_id' => '4',
          'uriType' => 'dynamic',
          'module' => 'block',
          'manager' => 'BlockMgr.php',
          'actionMapping' => 'list', // eg: edit
          'add_params' => '',    // eg: frmArticleID/23
          'is_enabled' => 1,
          'uri_alias_enable' => 1,
          'uri_alias' => 'test',
          'perms' => '1',        // role id, eg: 1 for admin
        );

        $ok = $this->da->addSection($section);

        $this->assertTrue($ok);

        $this->strategy = new SGL_UrlParser_AliasStrategy();

        $aUrlSegments = array (
            0 => 'index.php',
            1 => "test"
        );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj, $this->conf);


        //  assert expected keys present
        $this->assertEqual($ret['moduleName'],'block');
        $this->assertEqual($ret['managerName'],'block');
        $this->assertEqual($ret['action'],'list');

    }

    // Here we go: example.com/news/2/
    // Instead of http://example.com/publisher/articleview/action/view/frmArticleID/2/

    function testFlexibleAlias() {
        $section = array (
          'title' => 'Alias Test Section',
          'parent_id' => '4',
          'uriType' => 'dynamic',
          'module' => 'publisher',
          'manager' => 'ArticleViewMgr.php',
          'actionMapping' => '', // eg: edit
          'add_params' => 'frmArticleID',    // eg: frmArticleID/23
          'is_enabled' => 1,
          'uri_alias_enable' => 1,
          'uri_alias' => 'news',
          'perms' => '1',        // role id, eg: 1 for admin
        );

        $ok = $this->da->addSection($section);

        $this->assertTrue($ok);

        $this->strategy = new SGL_UrlParser_AliasStrategy();

        $aUrlSegments = array (
            0 => 'index.php',
            1 => 'news',
            2 => '2'
        );
        $this->obj->url = $this->exampleUrl . implode('/', $aUrlSegments);
        $ret = $this->strategy->parseQueryString($this->obj, $this->conf);

        //  assert expected keys present
        $this->assertEqual($ret['moduleName'],'publisher');
        $this->assertEqual($ret['managerName'],'articleview');
        $this->assertEqual($ret['frmArticleID'],'2');

    }

    function tearDown()
    {
        unset($this->strategy, $this->obj);
    }
}
?>