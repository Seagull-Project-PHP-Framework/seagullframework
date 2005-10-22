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
    
    function testCheckingLoadedModules()
    {
        $task = new SGL_Task_CheckLoadedModules();
        print '<pre>'; print_r($task->run());
    }
    
}

?>