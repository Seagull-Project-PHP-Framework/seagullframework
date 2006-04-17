<?php
require_once dirname(__FILE__) . '/../Task/DetectEnv.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class TasksTest extends UnitTestCase {

    function TasksTest()
    {
        $this->UnitTestCase('Tasks Test');
    }

    function testGetLoadedModules()
    {
        $task = new SGL_Task_GetLoadedModules();
    }

    function testGettEnv()
    {
        $task = new SGL_Task_GetPhpEnv();
    }

    function testGetIniValues()
    {
        $task = new SGL_Task_GetPhpIniValues();
    }

    function testGetFilesystemInfo()
    {
        $task = new SGL_Task_GetFilesystemInfo();
    }

    function testGetPearInfo()
    {
        $task = new SGL_Task_GetPearInfo();
    }
}

?>