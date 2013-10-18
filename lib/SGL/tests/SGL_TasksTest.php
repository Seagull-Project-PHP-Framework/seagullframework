<?php

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class SGL_TasksTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $this->markTestSkipped(
            'problem with empty methods'
        );
    }
    function testGetLoadedModules()
    {
        $task = new SGL_Task_GetLoadedModules();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGettEnv()
    {
        $task = new SGL_Task_GetPhpEnv();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetIniValues()
    {
        $task = new SGL_Task_GetPhpIniValues();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetFilesystemInfo()
    {
        $task = new SGL_Task_GetFilesystemInfo();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetPearInfo()
    {
        $task = new SGL_Task_GetPearInfo();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}

?>