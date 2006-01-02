<?php

//  start timer
define('SGL_START_TIME', getSystemTime());

//  set initial paths according to install type
if (file_exists('PEAR_INSTALLED.txt')) {
    define('SGL_PEAR_INSTALLED', true);
    require_once '@PHP-DIR@/Seagull/lib/SGL/AppController.php';
    $varDir = '@DATA-DIR@/Seagull/var';
} else {
    require_once dirname(__FILE__)  . '/../lib/SGL/AppController.php';
    $varDir = dirname(__FILE__)  . '/../var';
}

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