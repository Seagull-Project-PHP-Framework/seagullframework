<?php

/**
 * Page controller for Sitemap manager. See 'robots.txt' for details.
 *
 * @author Laszlo Horvath <pentarim@gmail.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */

/**
 * Returns systime in ms.
 *
 * @return string  execution time in milliseconds
 */
function getSystemTime()
{
    $time = gettimeofday();
    $resultTime = $time['sec'] * 1000;
    $resultTime += floor($time['usec'] / 1000);
    return $resultTime;
}

// start timer
define('SGL_START_TIME', getSystemTime());

$rootDir = dirname(__FILE__) . '/../../..';
$varDir  = dirname(__FILE__) . '/../../../var';

// check for lib cache
define('SGL_CACHE_LIBS', is_file($varDir . '/ENABLE_LIBCACHE.txt'));
define('SGL_INSTALLED', true);

require_once $rootDir . '/lib/SGL/FrontController.php';

SGL_FrontController::init();

// load modules config
SGL_Config::singleton()->ensureModuleConfigLoaded('sitemap');

SGL_Request::singleton()->set('moduleName', 'sitemap');
SGL_Request::singleton()->set('managerName','sitemap');
SGL_FrontController::run();

?>