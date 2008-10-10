<?php

/**
 * Test suite.
 *
 * @package    seagull
 * @subpackage test
 * @author     Demian Turner <demian@phpkitchen.net>
 * @version    $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        SGL2_Registry::set('request', new SGL2_Request());
    }

    function tearDown()
    {
        SGL2_Registry::reset();
    }

    public function testAdd()
    {
        $req = SGL2_Registry::get('request');
        $count = count($req->getClean());
        $aParams = array('foo' => 'fooValue', 'bar' => 'barValue');
        $req->add($aParams);
        $total = count($req->getClean());
        $this->assertEquals($total, $count + 2);
        $this->assertTrue(array_key_exists('foo', $req->getClean()));
        $this->assertTrue(array_key_exists('bar', $req->getClean()));
        $this->assertEquals($req->get('foo'), 'fooValue');
    }

    public function testForcingABrowserRequest()
    {
        $r = new SGL2_Request(SGL2_Request::BROWSER);
        $this->assertEquals($r->getType(), SGL2_Request::BROWSER);
    }

    public function testReset()
    {
        $req = SGL2_Registry::get('request');
        $aParams = array('foo' => 'fooValue', 'bar' => 'barValue');
        $req->add($aParams);
        $total = count($req->getClean());
        $this->assertEquals($total, 2);
        $req->reset();
        $this->assertNull($req->getClean());
    }

    /**
     * In >= php 5.2.4 it's not possible to override $_SERVER
     * conflict with params used to run test script
     *
     */
    function xtestCliArguments()
    {
        $_SERVER['argc'] = 1;
        $_SERVER['argv'] = array('index.php');
        $req = new SGL2_Request(SGL2_Request::CLI);
        // test no params
        $this->assertEquals(count($req->getClean()), 0);

        unset($req);
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = array('index.php', '--moduleName=default');
        $req = new SGL2_Request(SGL2_Request::CLI);

        // test module name is caught
        $this->assertEquals(count($req->getClean()), 1);
        $this->assertEquals($req->get('moduleName'), 'default');

        unset($req);
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = array('index.php', '--moduleName=default',
            '--managerName=translation', '--action=update');
        $req = new SGL2_Request(SGL2_Request::CLI);

        // test module name, manager and action are recognized
        $this->assertTrue(count($req->getClean()) == 3);
        $this->assertTrue($req->get('moduleName') == 'default');
        $this->assertTrue($req->get('managerName') == 'translation');
        $this->assertTrue($req->get('action') == 'update');

        unset($req);
        $_SERVER['argc'] = 6;
        $_SERVER['argv'] = array(
            'index.php',
            '--moduleName=default',
            '--managerName=translation',
            '--action=update',
            '--paramNumberOne=firstParameter',
            '--paramNumberTwo=secondParameter',
            '--paramNumberThree=thirdParameter'
        );
        $req = new SGL2_Request(SGL2_Request::CLI);

        // test optional params
        $this->assertTrue(count($req->getClean()) == 6);
        $this->assertTrue($req->get('moduleName') == 'default');
        $this->assertTrue($req->get('managerName') == 'translation');
        $this->assertTrue($req->get('action') == 'update');
        $this->assertTrue($req->get('paramNumberOne') == 'firstParameter');
        $this->assertTrue($req->get('paramNumberTwo') == 'secondParameter');
        $this->assertTrue($req->get('paramNumberThree') == 'thirdParameter');
    }
}

?>