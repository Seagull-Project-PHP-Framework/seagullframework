<?php
require_once SGL_MOD_DIR . '/export/classes/Rss2Mgr.php';

/**
 * Test suite.
 *
 * @package seagull
 * @subpackage export
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UserDAOTest.wdb.php,v 1.1 2005/06/23 15:18:06 demian Exp $
 */
class RssTest extends UnitTestCase
{

    function RssTest()
    {
        $this->UnitTestCase('RSS2 Test');
    }

    function setup()
    {
        //$this->rssMgr = new Rss2Mgr();
    }

    function testBuildConfigKey()
    {
        //  simple content type
        $control = '/datasrc/cms/contenttype/5';
        $aArgs['datasrc'] = 'cms';
        $aArgs['contenttype'] = SGL_CMS_CONTENTTYPE_REVIEW_PRODUCT_USER;
        $ret = Rss2Mgr::buildConfigKey($aArgs);
        $this->assertEqual($control, $ret);

        //  data access object
        $control = '/datasrc/dao/module/user/method/foo/bar/baz';
        $aArgs['datasrc'] = 'dao';
        $aArgs['module'] = 'user';
        $aArgs['method'] = 'foo';
        $aArgs['aParams'] = array('bar', 'baz');
        $ret = Rss2Mgr::buildConfigKey($aArgs);
        $this->assertEqual($control, $ret);
    }

    function testGetContent()
    {
        $aArgs['datasrc'] = 'dao';
        $aArgs['module'] = 'user';
        $aArgs['method'] = 'getUserPrefsByOrgId';
        //$aArgs['aParams'] = array(0, SGL_RET_NAME_VALUE);
        $aArgs['aParams'] = array('orgId' => 0, 'type' => SGL_RET_NAME_VALUE);
        $ret = Rss2Mgr::getContent($aArgs);
    }
}
?>