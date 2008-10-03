<?php
require_once dirname(__FILE__) . '/../Request.php';
require_once dirname(__FILE__) . '/../Request/Cli.php';

/**
 * Test suite.
 *
 * @package    seagull
 * @subpackage test
 * @author     Demian Turner <demian@phpkitchen.net>
 * @version    $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class RequestTest extends UnitTestCase
{
    function RequestTest()
    {
        $this->UnitTestCase('Request Test');
    }

    function setUp()
    {
    }

    function tearDown()
    {
        $_REQUEST = array();
        if (isset($_SERVER['argc'])) {
            unset($_SERVER['argc']);
        }
        if (isset($_SERVER['argv'])) {
            unset($_SERVER['argv']);
        }
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

    /**
     * In >= php 5.2.4 it's not possible to override $_SERVER
     *
     */
    function xtestCliArguments()
    {
        $_SERVER['argc'] = 1;
        $_SERVER['argv'] = array('index.php');
        $req = new SGL_Request_Cli();
        $req->init();
        // test no params
        $this->assertFalse(count($req->getAll()));

        unset($req);
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = array('index.php', '--moduleName=default');
        $req = new SGL_Request_Cli();
        $req->init();

        // test module name is caught
        $this->assertTrue(count($req->getAll()) == 1);
        $this->assertTrue($req->get('moduleName') == 'default');

        unset($req);
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = array('index.php', '--moduleName=default',
            '--managerName=translation', '--action=update');
        $req = new SGL_Request_Cli();
        $req->init();

        // test module name, manager and action are recognized
        $this->assertTrue(count($req->getAll()) == 3);
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
        $req = new SGL_Request_Cli();
        $req->init();

        // test optional params
        $this->assertTrue(count($req->getAll()) == 6);
        $this->assertTrue($req->get('moduleName') == 'default');
        $this->assertTrue($req->get('managerName') == 'translation');
        $this->assertTrue($req->get('action') == 'update');
        $this->assertTrue($req->get('paramNumberOne') == 'firstParameter');
        $this->assertTrue($req->get('paramNumberTwo') == 'secondParameter');
        $this->assertTrue($req->get('paramNumberThree') == 'thirdParameter');
    }
}

?>