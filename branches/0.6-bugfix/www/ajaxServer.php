<?php

$rootDir = dirname(__FILE__) . '/..';
$varDir = dirname(__FILE__) . '/../var';

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

require_once $rootDir .'/lib/SGL/FrontController.php';
SGL_FrontController::init();
require_once SGL_MOD_DIR . '/cms/classes/CmsDAO.php';
require_once 'HTML/AJAX/Server.php';

class AutoServer extends HTML_AJAX_Server
{
	// this flag must be set for your init methods to be used
	var $initMethods = true;

	function initCmsDAO()
	{
		$da = & CmsDAO::singleton();
		$this->registerClass($da);
	}
}

$server = new AutoServer();
$server->clientJsLocation = SGL_WEB_ROOT . '/js/html_ajax/';
$server->handleRequest();
?>
