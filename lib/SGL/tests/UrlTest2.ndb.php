<?php
require_once dirname(__FILE__) . '/../Url.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class UrlTest2 extends UnitTestCase {

    function UrlTest()
    {
        $this->UnitTestCase('Url Test2');
    }

    function setup()
    {
        $qs = 'http://localhost/seagull/branches/0.4-bugfix/www/index.php/user/login/foo/bar/';
        $this->url = new SGL_Url($qs, true, new SGL_UrlParser_SefStrategy());
        $err = $this->url->init();
        if (PEAR::isError($err)) {
            return $err;
        }
    }

    function testGetRequestData()
    {
        $aExpected = array(
            'foo' => 'bar',
            'moduleName' => 'user',
            'managerName' => 'login',
            );
        $aData = $this->url->getQueryData();
        $this->assertEqual($aData, $aExpected);
    }

    function testToString()
    {
        $url = $this->url->toString();
        $str = 'http://localhost/seagull/branches/0.4-bugfix/www/index.php/user/login/foo/bar/';
        $this->assertEqual($url, $str);
    }

    function testGetBase()
    {
        $url = $this->url->getBase();
        $str = 'http://localhost/seagull/branches/0.4-bugfix/www';
        $this->assertEqual($url, $str);
    }
}
?>