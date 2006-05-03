<?php

$rootDir = dirname(__FILE__) . '/..';
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
$server->handleRequest();
?>
