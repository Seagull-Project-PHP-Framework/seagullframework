<?php

$rootDir = dirname(__FILE__) . '/..';
$varDir = dirname(__FILE__) . '/../var';

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

require_once $rootDir .'/lib/SGL/FrontController.php';
SGL_FrontController::init();
require_once 'HTML/AJAX/Server.php';

class AutoServer extends HTML_AJAX_Server
{
    // this flag must be set for your init methods to be used
    var $initMethods = true;

    function initMediaDAO()
    {
        require_once SGL_MOD_DIR . '/media/classes/MediaDAO.php';
        $da = & MediaDAO::singleton();
        $this->registerClass($da);
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
