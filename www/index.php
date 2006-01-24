<?php

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

if (file_exists($rootDir .'/lib/SGL/AppController.php')) {
    require_once $rootDir .'/lib/SGL/AppController.php';
}
//else {
//    die('You have a PEAR installable version and therefore must '.
//        ' have a file called "PEAR_INSTALLED.txt" in the www dir.');
//}

// determine if setup needed
if (!file_exists($varDir . '/INSTALL_COMPLETE.php')) {
    header('Location: setup.php');
    exit;
} else {
    define('SGL_INSTALLED', true);
}

SGL_AppController::run();

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
?>