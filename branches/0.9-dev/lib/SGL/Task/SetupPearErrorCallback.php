<?php

/**
 * @package Task
 */
class SGL_Task_SetupPearErrorCallback extends SGL_Task
{
    function run($conf)
    {
        //  set PEAR error handler
        #$old_error_handler = set_error_handler("myErrorHandler");
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'pearErrorHandler'));
        if (!SGL_Config::get('debug.showBacktrace')) {
           $options = &PEAR::getStaticProperty('PEAR_Error', 'skiptrace');
           $options = true;
        }
    }

    /**
     * A callback method that sets the default PEAR error behaviour.
     *
     * @access   public
     * @static
     * @param    object $oError the PEAR error object
     * @return   void
     */
    function pearErrorHandler($oError)
    {
        //  log message
        $message = $oError->getMessage();
        $debugInfo = $oError->getDebugInfo();
        SGL::logMessage('PEAR' . " :: $message : $debugInfo", PEAR_LOG_ERR);

        //  send error info to screen
        SGL_Error::push($oError);
        if (SGL_Config::get('debug.showBacktrace')) {
            echo '<pre>'; print_r($oError->getBacktrace()); print '</pre>';
        }
    }
}
?>