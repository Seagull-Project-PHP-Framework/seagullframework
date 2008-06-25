<?php

/**
 * @package Task
 */
class SGL_Task_SetupCustomErrorHandler extends SGL_Task
{
    function run($conf)
    {
        //  start custom PHP error handler
        if (isset( $conf['debug']['customErrorHandler'])
                && $conf['debug']['customErrorHandler'] == true
                && !defined('SGL_TEST_MODE')) {
            require_once SGL_CORE_DIR . '/ErrorHandler.php';
            $eh = & new SGL_ErrorHandler();
            $eh->startHandler();

            //  clean start for logs
            error_log(' ');
            error_log('##########   New request: '.trim($_SERVER['PHP_SELF']).'   ##########');
        } else {
            // otherwise setup standard PHP error handling
            if (!empty($conf['debug']['production'])) {
                ini_set('display_errors', false);
            }
            if (!empty($conf['log']['enabled'])) {
                ini_set('log_errors', true);
            }
        }
    }
}
?>