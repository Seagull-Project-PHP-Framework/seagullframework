<?php
//  setup seagull environment
require_once '../constants.php';
$c = &SGL_Config::singleton();
$conf = $c->getAll();

//  disable logging and error handling
$conf['debug']['customErrorHandler'] = false;
require_once '../init.php';
require_once SGL_CORE_DIR . '/AppController.php';

class TestRunnerInit extends SGL_AppController
{
    function run()
    {
        $input = &SGL_Registry::singleton();
        $input->setRequest($req = SGL_Request::singleton());
        
        $process =  new SGL_Process_Init(
                    new SGL_Process_DiscoverClientOs(
                    new SGL_Process_ResolveManager(
                    new SGL_Process_CreateSession(
                    new SGL_Process_SetupLangSupport(
                    new SGL_Process_SetupPerms(
                    new SGL_Process_AuthenticateRequest(
                    new SGL_Process_BuildHeaders(
                    new SGL_Process_SetupLocale(
                    new SGL_Void()
                   )))))))));
                   
        $process->process($input);
    }
}

TestRunnerInit::run();
    
ini_set('include_path', ini_get('include_path') . ':' . '/usr/local/lib/php');

?>