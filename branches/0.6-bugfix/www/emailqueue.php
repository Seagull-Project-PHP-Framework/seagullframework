<?php

/**
 * Controller, which sends emails from queue.
 *
 * Example usage:
 *   php <path>/emailqueue.php
 *
 * @todo we need to remove this controller
 *       and use appropriate CLI call for still to be deleveloped
 *       Emailqueue manager.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */

/**
 * Returns systime in ms.
 *
 * @return string  Execution time in milliseconds
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

// dirs
$rootDir = dirname(__FILE__) . '/..';
$varDir = dirname(__FILE__) . '/../var';

define('SGL_INSTALLED', true);
define('SGL_CACHE_LIBS', is_file("$varDir/ENABLE_LIBCACHE.txt")
    ? true
    : false);

require_once $rootDir . '/lib/SGL/FrontController.php';

/**
 * Email queue controller.
 */
class SGL_EmailQueueController extends SGL_FrontController
{
    public function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        // assign request to registry
        $input = SGL_Registry::singleton();
        $req   = SGL_Request::singleton();

        $input->setRequest($req);
        $output = new stdClass();

        $process =
            new SGL_Task_Init(
            new SGL_Task_SetupORM(
            new SGL_Task_StripMagicQuotes(
            new SGL_Task_DiscoverClientOs(
            new SGL_Task_CreateSession(
            new SGL_Task_SetupLangSupport(
            new SGL_Task_SetupLocale(

            // target
            new SGL_Task_ProcessEmailQueue()
        )))))));
        $process->process($input, $output);

        echo $output->data;
    }
}

/**
 * Email queue target.
 */
class SGL_Task_ProcessEmailQueue extends SGL_ProcessRequest
{
    public function process($input, $output)
    {
        $aOptions = SGL_Config::get('emailQueue')
            ? SGL_Config::get('emailQueue')
            : array();

        require_once 'SGL/Emailer/Queue.php';
        $queue = new SGL_Emailer_Queue($aOptions);
        $queue->processQueue();

        $output->data = SGL_Error::count()
            ? SGL_Error::getLast()->getMessage()
            : 'Success';
    }
}

// run
SGL_EmailQueueController::run();

?>