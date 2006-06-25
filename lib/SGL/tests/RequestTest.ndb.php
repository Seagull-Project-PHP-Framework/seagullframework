<?php
require_once dirname(__FILE__) . '/../Request.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class RequestTest extends UnitTestCase {

    function RequestTest()
    {
        $this->UnitTestCase('Request Test');
    }

    function setup()
    {

    }

    function teardown()
    {
        $_REQUEST = array();
    }

    function testAdd()
    {
        $req = &SGL_Request::singleton($forceNew = true);
        $count = count($req->getAll());
        $aParams = array('foo' => 'fooValue', 'bar' => 'barValue');
        $req->add($aParams);
        $total = count($req->getAll());

        $this->assertEqual($total, $count + 2);
        $this->assertTrue(array_key_exists('foo', $req->getAll()));
        $this->assertTrue(array_key_exists('bar', $req->getAll()));
        $this->assertEqual($req->get('foo'), 'fooValue');
    }
}

?>