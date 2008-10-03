<?php
/**
 * Returns systime in ms.
 *
 * @return string   Execution time in milliseconds
 */
function getSystemTime()
{
    $time = gettimeofday();
    $resultTime = $time['sec'] * 1000;
    $resultTime += floor($time['usec'] / 1000);
    return $resultTime;
}


//  start timer
define('SGL_START_TIME', getSystemTime());
$pearTest = '@PHP-DIR@';

//  set initial paths according to install type
if ($pearTest != '@' . 'PHP-DIR'. '@') {
    define('SGL_PEAR_INSTALLED', true);
    $rootDir = '@PHP-DIR@/Seagull';
    $varDir = '@DATA-DIR@/Seagull/var';
} else {
    $rootDir = dirname(__FILE__) . '/..';
    $varDir = dirname(__FILE__) . '/../var';
}
//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

if (is_file($rootDir .'/lib/SGL/FrontController.php')) {
    require_once $rootDir .'/lib/SGL/FrontController.php';
}

// determine if setup needed
if (!is_file($varDir . '/INSTALL_COMPLETE.php')) {
    header('Location: setup.php');
    exit;
} else {
    define('SGL_INSTALLED', true);
}

SGL_FrontController::run();
?>