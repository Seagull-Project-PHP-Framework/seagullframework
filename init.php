<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | init.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: init.php,v 1.136 2005/06/25 08:41:51 demian Exp $

    //  fire off init tasks, run as fn to keep global namespace clean
    //  - set constants
    //  - config loading
    //  - basic PHP config with ini_set()
    //  - check if installed
    //  - init error handler
    //  - init DB_DataObject

    //  load BC functions depending on PHP version detected
    if (!function_exists('version_compare') || version_compare(phpversion(), "4.3.0", 'lt')) {
        require_once 'etc/bc.php';
    }

    require_once 'constants.php';

    SGL_init();

    function SGL_init()
    {
        $conf = $GLOBALS['_SGL']['CONF'];

        // load Base and init DB_DataObject
        require_once SGL_LIB_DIR . '/SGL.php';

        //  to overcome overload problem
        define('DB_DATAOBJECT_NO_OVERLOAD', true);

        // If we just copied conf.ini, execute bootstrap
        if (isset($GLOBALS['_SGL']['executeDbBootstrap']) || @$conf['db']['bootstrap'] == '1') {
            require_once SGL_CORE_DIR . '/SetupWizard.php';
            $wizard = new SGL_SetupWizard($conf);
            $wizard->run();
        }

        $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        $options = array(
            'database'              => SGL_DB::getDsn(SGL_DSN_STRING),
            'schema_location'       => SGL_ENT_DIR,
            'class_location'        => SGL_ENT_DIR,
            'require_prefix'        => SGL_ENT_DIR . '/',
            'class_prefix'          => 'DataObjects_',
            'debug'                 => 0,
            'production'            => 0,
            'ignore_sequence_keys'  => 'ALL',
            'generator_strip_schema' => 1,
        );

        //  include Log.php if logging enabled
        if ($conf['log']['enabled']) {
            require_once 'Log.php';
            
        } else {
            //  define log levels to avoid notices, since Log.php not included
            define('PEAR_LOG_EMERG',    0);     /** System is unusable */
            define('PEAR_LOG_ALERT',    1);     /** Immediately action */
            define('PEAR_LOG_CRIT',     2);     /** Critical conditions */
            define('PEAR_LOG_ERR',      3);     /** Error conditions */
            define('PEAR_LOG_WARNING',  4);     /** Warning conditions */
            define('PEAR_LOG_NOTICE',   5);     /** Normal but significant */
            define('PEAR_LOG_INFO',     6);     /** Informational */
            define('PEAR_LOG_DEBUG',    7);     /** Debug-level messages */
        }

        //  which degree of error severity before emailing admin
        define('SGL_EMAIL_ADMIN_THRESHOLD',     constant($conf['debug']['emailAdminThreshold']));

        //  start PHP error handler
        if ($conf['debug']['customErrorHandler']) {
	        require_once SGL_CORE_DIR . '/ErrorHandler.php';
	        $eh = & new SGL_ErrorHandler();
	        $eh->startHandler();
        }

        //  set PEAR error handler
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pearErrorHandler');
    }
    
    /**
     * A callback fn that sets the default PEAR error behaviour.
     *
     * @access   public
     * @static    
     * @param    object $oError the PEAR error object
     * @return   void
     */
    function pearErrorHandler($oError)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  log message
        $message = $oError->getMessage();
        $debugInfo = $oError->getDebugInfo();
        SGL::logMessage('PEAR' . " :: $message : $debugInfo", PEAR_LOG_ERR);

        //  if sesssion debug, send error info to screen
        if (!$conf['debug']['production'] || SGL_HTTP_Session::get('debug')) {
            $GLOBALS['_SGL']['ERRORS'][] = $oError;
            if ($conf['debug']['showBacktrace']) {
                echo '<pre>'; print_r($oError->getBacktrace()); print '</pre>';
            }
        }
    }
    
if (!(function_exists('file_put_contents'))) {
    function file_put_contents($location, $data)
    {
        if (file_exists($location)) {
            unlink($location);
        }
        $fileHandler = fopen ($location, "w");
        fwrite ($fileHandler, $data);
        fclose ($fileHandler);
        return true;
    }
}
?>
