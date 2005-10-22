<?php
require_once dirname(__FILE__) . '/../Tasks/All.php';

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
        print '<pre>'; print_r($task->run());
    }
    
    function testGettEnv()
    {
        $task = new SGL_Task_GetPhpEnv();
        print '<pre>'; print_r($task->run());
    }
    
    function testGetIniValues()
    {
        $task = new SGL_Task_GetPhpIniValues();
        print '<pre>'; print_r($task->run());
    }
    
    function testGetFilesystemInfo()
    {
        $task = new SGL_Task_GetFilesystemInfo();
        print '<pre>'; print_r($task->run());
    }
    
    function testGetPearInfo()
    {
        $task = new SGL_Task_GetPearInfo();
        print '<pre>'; print_r($task->run());
    }
}

?>