<?php
require_once dirname(__FILE__) . '/../AppController.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */  
class RendererStrategyTest extends UnitTestCase {

    function RendererStrategyTest()
    {
        $this->UnitTestCase('Renderer Strategy Test');
    }
    
    function testHtmlFlexyRendererStrategySetupPlugins()
    {

    }
    
    function testHtmlFlexyRendererStrategyInitEngine()
    {

    }
    
    function testHtmlFlexyRendererStrategyRender()
    {

    }
    
    function xtestHtmlViewPostProcess()
    {
        $output = new SGL_Output();
        $output->masterTemplate = 'home.html';
        $output->module = 'default';
        $view = new SGL_HtmlView($output, new SGL_HtmlFlexyRendererStrategy());
        #$view->postProcess();
        $process = new SGL_PrepareBlocks(
                   new SGL_PrepareNavigation (
                   new SGL_MainProcess()
                   ));
        $process->process($output);
    }
    
    function testProcessRun()
    {
        $process = new SGL_Init(
                   new SGL_MainProcess());
        $process->process($req = & SGL_Request::singleton());
    }
}

if (!function_exists('getSystemTime')) {
    function getSystemTime()
    {
        $time = gettimeofday();    
        $resultTime = $time['sec'] * 1000;
        $resultTime += floor($time['usec'] / 1000);
        return $resultTime;
    }
}
?>