<?php
//  setup seagull environment
require_once dirname(__FILE__)  . '/../lib/SGL/AppController.php';

class TestRunnerInit extends SGL_AppController
{
    function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_AppController::init();
        }

        //  get config singleton
        $c = &SGL_Config::singleton();
        $c->set('debug', array('customErrorHandler' => false));
        $conf = $c->getAll();

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        //  get current url object
        $urlHandler = $conf['site']['urlHandler'];
        $url = new SGL_URL($_SERVER['PHP_SELF'], true, new $urlHandler());

        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($url);
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

$includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
ini_set('include_path', ini_get('include_path') . $includeSeparator . '/usr/local/lib/php');
?>