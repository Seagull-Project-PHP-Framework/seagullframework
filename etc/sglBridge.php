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
        $input = &SGL_RequestRegistry::singleton();
        $input->setRequest($req = SGL_Request::singleton());
        
        $process =  new SGL_Init(
                    new SGL_DiscoverClientOs(
                    new SGL_ManagerResolver(
                    new SGL_InitSession(
                    new SGL_InitLangSupport(
                    new SGL_InitPerms(
                    new SGL_AuthenticateRequest(
                    new SGL_BuildHeaders(
                    new SGL_SetLocale(
                    new SGL_Void()
                   )))))))));
                   
        $process->process($input);
    }
}

TestRunnerInit::run();
    
ini_set('include_path', ini_get('include_path') . ':' . '/usr/local/lib/php');

?>