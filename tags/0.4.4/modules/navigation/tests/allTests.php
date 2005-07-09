<?php
    $GLOBALS['chrono']['start'] = microtime();
    
    // SimpleTest
    if (!defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', '../../../lib/simpletest');
    }

    require_once SIMPLE_TEST .'/unit_tester.php';
    require_once SIMPLE_TEST .'/mock_objects.php';
    require_once SIMPLE_TEST .'/reporter.php';
    require_once SIMPLE_TEST .'/web_tester.php';

    //  setup seagull environment
    require_once '../../../constants.php';
    $conf = &$GLOBALS['_SGL']['CONF'];
#    $conf['log']['enabled'] = false;

    //  disable logging and error handling
    $conf['debug']['customErrorHandler'] = false;
    require_once '../../../init.php';
    require_once SGL_CORE_DIR . '/Controller.php';
    $process = & new SGL_Controller();
    $process->init(new SGL_HTTP_Request());

    //  build test suite
    $test = & new GroupTest('Navigation module test suite');
    $test->addTestFile('TestSimpleNav.php');    


    //  To show passes, pass true to the constructor
    $test->run(new HtmlReporter(true));
    
    // Execution time
    list($endUsec, $endSec)     = explode(" ", microtime());
    $endTime                    = ((float)$endUsec + (float)$endSec);
    list($startUsec, $startSec) = explode(" ", $GLOBALS['chrono']['start']);
    $startTime                  = ((float)$startUsec + (float)$startSec);
    echo '<div align="right"><br/>Test Suite Execution Time ~ <b>'.substr(($endTime-$startTime), 0, 6).'</b> seconds.</div>';
?>
