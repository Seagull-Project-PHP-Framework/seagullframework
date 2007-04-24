<?php

$rootDir = dirname(__FILE__) . '/..';
$varDir  = dirname(__FILE__) . '/../var';

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

require_once $rootDir .'/lib/SGL/FrontController.php';

class AjaxInit extends SGL_FrontController
{
    function run()
    {
        define('SGL_TEST_MODE', true);

        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        //  assign request to registry
        $input = &SGL_Registry::singleton();
        $req = SGL_Request::singleton();

        if (PEAR::isError($req)) {
            //  stop with error page
            SGL::displayStaticPage($req->getMessage());
        }
        $input->setRequest($req);
        $output = &new SGL_Output();

        $process =  new SGL_Task_Init(
                    new SGL_Task_SetupORM(
                    new SGL_Task_CreateSession(
                    new SGL_Void()
                   )));

        $process->process($input, $output);
    }
}

AjaxInit::run();
require_once 'HTML/AJAX/Server.php';

class AutoServer extends HTML_AJAX_Server
{
    // this flag must be set for your init methods to be used
    var $initMethods = true;

    function initMediaAjaxProvider()
    {
        require_once SGL_MOD_DIR . '/media/classes/MediaAjaxProvider.php';
        $provider = & MediaAjaxProvider::singleton();
        $this->registerClass($provider);
    }

    function initEcommAjaxProvider()
    {
        require_once SGL_MOD_DIR . '/ecomm/classes/EcommAjaxProvider.php';
        $provider = & EcommAjaxProvider::singleton();
        $this->registerClass($provider);
    }

    function initCmsAjaxProvider()
    {
        require_once SGL_MOD_DIR . '/cms/classes/CmsAjaxProvider.php';
        $provider = & CmsAjaxProvider::singleton();
        $this->registerClass($provider);
    }
}

$server = new AutoServer();
$server->clientJsLocation = SGL_WEB_ROOT . '/js/html_ajax/';
$server->handleRequest();
?>
