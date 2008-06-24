<?php

// this require the AMFPHP 1.9 Beta to work
// located there : http://www.5etdemi.com/blog/archives/2007/01/amfphp-19-beta-2-ridiculously-faster/

require_once SGL_LIB_DIR . '/amfphp/core/amf/app/Gateway.php';
//You can set this constant appropriately to disable traces and debugging headers
//You will also have the constant available in your classes, for changing
//the mysql server info for example
if (!defined('PRODUCTION_SERVER')) {
    define('PRODUCTION_SERVER', false);
}

/**
 * @package Task
 */
class SGL_Task_ExecuteAmfAction extends SGL_ProcessRequest
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $req = $input->getRequest();
        $moduleName = $req->getModuleName();
        $method = $req->getActionName();


        $gateway = new Gateway();

        //Set where the services classes are loaded from, *with trailing slash*
        $gateway->setClassPath(SGL_MOD_DIR . '/' .($moduleName) . '/classes/');


        //Set where class mappings are loaded from (ie: for VOs)
        $gateway->setClassMappingsPath(SGL_MOD_DIR . '/' .($moduleName) . '/classes/vo/');

        //$gateway->setCharsetHandler("utf8_decode", "ISO-8859-1", "ISO-8859-1");

        //Error types that will be rooted to the NetConnection debugger
        $gateway->setErrorHandling(E_ALL ^ E_NOTICE);

        if (PRODUCTION_SERVER)
        {
            //Disable profiling, remote tracing, and service browser
            $gateway->disableDebug();
        }

        //If you are running into low-level issues with corrupt messages and
        //the like, you can add $gateway->logIncomingMessages('path/to/incoming/messages/');
        //and $gateway->logOutgoingMessages('path/to/outgoing/messages/'); here
        //$gateway->logIncomingMessages('in/');
        //$gateway->logOutgoingMessages('out/');

        //Explicitly disable the native extension if it is installed
        //$gateway->disableNativeExtension();

        //Enable gzip compression of output if zlib is available,
        //beyond a certain byte size threshold
        $gateway->enableGzipCompression(25*1024);

        //Service now
        ob_start();
        $gateway->service();
        $output->data = ob_get_contents();
        ob_end_clean();
        SGL::logMessage('------ query count:'.SGL_Output::getQueryCount(), PEAR_LOG_DEBUG);
    }
}

?>