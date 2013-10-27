<?php
/**
 * Returns systime in ms.
 *
 * @return string   Execution time in milliseconds
 */
function getSystemTime()
{
    $time = @gettimeofday();
    $resultTime = $time['sec'] * 1000;
    $resultTime += floor($time['usec'] / 1000);
    return $resultTime;
}

//  start timer
define('SGL_START_TIME', getSystemTime());

$rootDir = realpath(dirname(__FILE__) . '/..');
$varDir = realpath(dirname(__FILE__) . '/../var');

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

if (is_file($rootDir .'/lib/SGL/FrontController.php')) {
    require_once $rootDir .'/lib/SGL/FrontController.php';
}

// determine if setup needed
if (!is_file($varDir . '/INSTALL_COMPLETE.php')) {
    $protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
        ? 'https'
        : 'http';
    $webRoot = $protocol . '://'. $_SERVER['HTTP_HOST'] .
        str_replace('\\','/',(dirname($_SERVER['SCRIPT_NAME'])));
    $webRoot = $webRoot.((substr($webRoot,-1) !== '/')?'/':''). 'setup.php';
    header('Location: '.$webRoot);
    exit;
} else {
    define('SGL_INSTALLED', true);
}

SGL_FrontController::run();
?>
