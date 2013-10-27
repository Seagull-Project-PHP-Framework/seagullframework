<?php

require 'autoload.php';

define('SGL_INSTALLED', true);

class TestRunnerInit extends SGL_FrontController
{
    public static function run()
    {
        define('SGL_TEST_MODE', true);

        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        //  assign request to registry
        $input = SGL_Registry::singleton();
        $req = SGL_Request::singleton();

        if (PEAR::isError($req)) {
            //  stop with error page
            SGL::displayStaticPage($req->getMessage());
        }
        $input->setRequest($req);
        $output = new SGL_Output();

        $process =  new SGL_Task_Init(
            new SGL_Task_DiscoverClientOs(
                        new SGL_Task_MinimalSession(
                            new SGL_Task_SetupLangSupport(
                                new SGL_Void()
                            ))));

        $process->process($input, $output);
    }
}

class SGL_Task_MinimalSession extends SGL_DecorateProcess
{
    function process($input, $output)
    {
        session_start();
        $_SESSION['uid'] = 1;
        $_SESSION['rid'] = 1;
        $_SESSION['aPrefs'] = array();

        $this->processRequest->process($input, $output);
    }
}

TestRunnerInit::run();

?>