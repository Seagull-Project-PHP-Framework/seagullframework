<?php

//  start timer
$GLOBALS['_SGL']['START_TIME'] = getSystemTime();

require_once dirname(__FILE__)  . '/../lib/SGL/AppController.php';
require_once dirname(__FILE__)  . '/../lib/other/dumpr/dumpr.php';

// determine if setup needed
if (!file_exists(dirname(__FILE__)  . '/../var/INSTALL_COMPLETE.php')) {
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
