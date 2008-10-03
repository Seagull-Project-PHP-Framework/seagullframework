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

function __autoload($className)
{
    if (!class_exists($className)) {
        $path = str_replace('_', '/', $className);
        $file = $path . '.php';
        // man we have to get rid of Flexy ..
        if ($file == 'HTML/Template/Flexy/Token/Comment.php' ||
            $file == 'HTML/Template/Flexy/Token/Doctype.php' ||
            $file == 'HTML/Template/Flexy/Token/Literal.php' ||
            $file == 'HTML/Template/Flexy/Token/WhiteSpace.php' ||
            $file == 'HTML/Template/Flexy/Token/CloseTag.php' ||
            $file == 'HTML/Template/Flexy/Token/Name.php'
        ) {
            return;
        }
        require $file;
    }
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
    $rootDir = realpath(dirname(__FILE__) . '/..');
    $varDir = realpath(dirname(__FILE__) . '/../var');
}
//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

require_once $rootDir .'/lib/SGL/Task.php';
require_once $rootDir .'/lib/SGL/FrontController.php';
require_once $rootDir .'/lib/SGL/Task/SetupPaths.php';
SGL_Task_SetupPaths::run();

?>