<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Init.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Init.php,v 1.85 2005/06/22 00:40:44 demian Exp $

/**
 * Basic init tasks: sets up paths, contstants, include_path, etc.
 *
 * @author  Demian Turner <demian@phpkitchen.com>
 */
require_once dirname(__FILE__) . '/../Task.php';

// if SGL_FrontController::init() called without index.php
if (!isset($GLOBALS['varDir'])) {
    $GLOBALS['varDir']  = realpath(dirname(__FILE__) . '/../../../var');
    $GLOBALS['rootDir'] = realpath(dirname(__FILE__) . '/../../../');
}

/**
 * @package Task
 */
class SGL_Task_SetupPaths extends SGL_Task
{
    /**
     * Sets up the minimum paths required for framework execution.
     *
     * - SGL_SERVER_NAME must always be known in order to rewrite config file
     * - SGL_PATH is the filesystem root path
     * - pear include path is setup
     * - PEAR.php included for errors, etc
     *
     * @param array $data
     */
    function run($conf)
    {
        define('SGL_SERVER_NAME', $this->hostnameToFilename());
        if (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_PATH', '@PHP-DIR@/Seagull');
            define('SGL_LIB_PEAR_DIR', '@PHP-DIR@');
        } else {
            $path = $GLOBALS['varDir']  . '/INSTALL_COMPLETE.php';
            if (is_file($path)) {
                $configFile = $GLOBALS['varDir']  . '/'
                    . SGL_Task_SetupPaths::hostnameToFilename() . '.conf.php';
                require_once $configFile;
                if (!empty($conf['path']['installRoot'])) {
                    define('SGL_PATH', $conf['path']['installRoot']);
                }
            } else {
                define('SGL_PATH', $GLOBALS['rootDir']);
            }
            define('SGL_LIB_PEAR_DIR', SGL_PATH . '/lib/pear');
            //  put sgl lib dir in include path
            $sglLibDir =  SGL_PATH . '/lib';
        }

        if (!defined('PATH_SEPARATOR')) { // defined in >= PHP 4.3.4
            define('PATH_SEPARATOR', (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':');
        }
        $sglPath = '.' . PATH_SEPARATOR . SGL_LIB_PEAR_DIR . PATH_SEPARATOR . $sglLibDir .
            PATH_SEPARATOR . get_include_path();
        $allowed = @ini_set('include_path', $sglPath);
        if (!$allowed) {
            //  depends on PHP version being >= 4.3.0
            if (function_exists('set_include_path')) {
                set_include_path($sglPath);
            } else {
                die('You need at least PHP 4.3.0 if you want to run Seagull
                with safe mode enabled.');
            }
        }
    }

    /**
     * Determines the name of the INI file, based on the host name.
     *
     * If PHP is being run interactively (CLI) where no $_SERVER vars
     * are available, a default 'localhost' is supplied.
     *
     * @return  string  the name of the host
     */
    function hostnameToFilename()
    {
        //  start with a default
        $hostName = 'localhost';
        if (!SGL::runningFromCLI()) {

            // Determine the host name
            if (!empty($_SERVER['SERVER_NAME'])) {
                $hostName = $_SERVER['SERVER_NAME'];

            } elseif (!empty($_SERVER['HTTP_HOST'])) {
                //  do some spoof checking here, like
                //  if (gethostbyname($_SERVER['HTTP_HOST']) != $_SERVER['SERVER_ADDR'])
                $hostName = $_SERVER['HTTP_HOST'];
            } else {
                //  if neither of these variables are set
                //  we're going to have a hard time setting up
                die('Could not determine your server name');
            }
            // Determine if the port number needs to be added onto the end
            if (!empty($_SERVER['SERVER_PORT'])
                    && $_SERVER['SERVER_PORT'] != 80
                    && $_SERVER['SERVER_PORT'] != 443) {
                $hostName .= '_' . $_SERVER['SERVER_PORT'];
            }
        }
        return $hostName;
    }
}

/**
 * @package Task
 */
class SGL_Task_SetupConstantsStart extends SGL_Task
{
    function run($conf)
    {
        // framework file structure
        if (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_VAR_DIR',              '@DATA-DIR@/Seagull/var');
            define('SGL_ETC_DIR',              '@DATA-DIR@/Seagull/etc');
            define('SGL_APP_ROOT',             '@PHP-DIR@/Seagull');
        } else {
            define('SGL_VAR_DIR',               SGL_PATH . '/var');
            define('SGL_ETC_DIR',               SGL_PATH . '/etc');
            define('SGL_APP_ROOT',              SGL_PATH);
        }
        define('SGL_LOG_DIR',                   SGL_VAR_DIR . '/log');
        define('SGL_CACHE_DIR',                 SGL_VAR_DIR . '/cache');
        define('SGL_LIB_DIR',                   SGL_APP_ROOT . '/lib');
        define('SGL_ENT_DIR',                   SGL_CACHE_DIR . '/entities');
        define('SGL_DAT_DIR',                   SGL_APP_ROOT . '/lib/data');
        define('SGL_CORE_DIR',                  SGL_APP_ROOT . '/lib/SGL');

        //  error codes to use with SGL::raiseError()
        //  start at -100 in order not to conflict with PEAR::DB error codes

        /**
         * Wrong args to function.
         */
        define('SGL_ERROR_INVALIDARGS',         -101);
        /**
         * Something wrong with the config.
         */
        define('SGL_ERROR_INVALIDCONFIG',       -102);
        /**
         * No data available.
         */
        define('SGL_ERROR_NODATA',              -103);
        /**
         * No class exists.
         */
        define('SGL_ERROR_NOCLASS',             -104);
        /**
         * No method exists.
         */
        define('SGL_ERROR_NOMETHOD',            -105);
        /**
         * No rows were affected by query.
         */
        define('SGL_ERROR_NOAFFECTEDROWS',      -106);
        /**
         * Limit queries on unsuppored databases.
         */
        define('SGL_ERROR_NOTSUPPORTED'  ,      -107);
        /**
         * Invalid call.
         */
        define('SGL_ERROR_INVALIDCALL',         -108);
        /**
         * Authentication failure.
         */
        define('SGL_ERROR_INVALIDAUTH',         -109);
        /**
         * Failed to send email.
         */
        define('SGL_ERROR_EMAILFAILURE',        -110);
        /**
         * Failed to connect to DB.
         */
        define('SGL_ERROR_DBFAILURE',           -111);
        /**
         * A DB transaction failed.
         */
        define('SGL_ERROR_DBTRANSACTIONFAILURE',-112);
        /**
         * User not allow to access site.
         */
        define('SGL_ERROR_BANNEDUSER',          -113);
        /**
         * File not found.
         */
        define('SGL_ERROR_NOFILE',              -114);
        /**
         * Perms were invalid.
         */
        define('SGL_ERROR_INVALIDFILEPERMS',    -115);
        /**
         * Session was invalid.
         */
        define('SGL_ERROR_INVALIDSESSION',      -116);
        /**
         * Posted data was invalid.
         */
        define('SGL_ERROR_INVALIDPOST',         -117);
        /**
         * Translation invalid.
         */
        define('SGL_ERROR_INVALIDTRANSLATION',  -118);
        /**
         * Could not write to the file.
         */
        define('SGL_ERROR_FILEUNWRITABLE',      -119);
        /**
         * Method perms were invalid.
         */
        define('SGL_ERROR_INVALIDMETHODPERMS',  -120);
        /**
         * Authorisation is invalid.
         */
        define('SGL_ERROR_INVALIDAUTHORISATION',  -121);
        /**
         * Request was invalid.
         */
        define('SGL_ERROR_INVALIDREQUEST',      -122);
        /**
         * Type invalid.
         */
        define('SGL_ERROR_INVALIDTYPE',         -123);
        /**
         * Excessive recursion occured.
         */
        define('SGL_ERROR_RECURSION',           -124);
        /**
         * Resource could not be found.
         */
        define('SGL_ERROR_RESOURCENOTFOUND',    -404);

        //  message types to use with SGL:raiseMsg($msg, $translation, $msgType)
        define('SGL_MESSAGE_ERROR',             0);  // by default
        define('SGL_MESSAGE_INFO',              1);
        define('SGL_MESSAGE_WARNING',           2);

        //  automate sorting
        define('SGL_SORTBY_GRP',                1);
        define('SGL_SORTBY_USER',               2);
        define('SGL_SORTBY_ORG',                3);

        //  Seagull user roles
        define('SGL_ANY_ROLE',                  -2);
        define('SGL_UNASSIGNED',                -1);
        define('SGL_GUEST',                     0);
        define('SGL_ADMIN',                     1);
        define('SGL_MEMBER',                    2);

        define('SGL_STATUS_DELETED',            0);
        define('SGL_STATUS_FOR_APPROVAL',       1);
        define('SGL_STATUS_BEING_EDITED',       2);
        define('SGL_STATUS_APPROVED',           3);
        define('SGL_STATUS_PUBLISHED',          4);
        define('SGL_STATUS_ARCHIVED',           5);

        //  comment status types
        define('SGL_COMMENT_FOR_APPROVAL',      0);
        define('SGL_COMMENT_APPROVED',          1);
        define('SGL_COMMENT_AKISMET_PASSED',    2);
        define('SGL_COMMENT_AKISMET_FAILED',    3);

        //  define return types, k/v pairs, arrays, strings, etc
        define('SGL_RET_NAME_VALUE',            1);
        define('SGL_RET_ID_VALUE',              2);
        define('SGL_RET_ARRAY',                 3);
        define('SGL_RET_STRING',                4);

        //  define string element
        define('SGL_CHAR',                      1);
        define('SGL_WORD',                      2);

        //  define language id types
        define('SGL_LANG_ID_SGL',               1);
        define('SGL_LANG_ID_TRANS2',            2);

        //  various
        define('SGL_ANY_SECTION',               0);
        define('SGL_NEXT_ID',                   0);
        define('SGL_NOTICES_DISABLED',          0);
        define('SGL_NOTICES_ENABLED',           1);

        //  with logging, you can optionally show the file + line no. where
        //  SGL::logMessage was called from
        define('SGL_DEBUG_SHOW_LINE_NUMBERS',   false);

        //  to overcome overload problem
        define('DB_DATAOBJECT_NO_OVERLOAD',     true);
    }
}

/**
 * @package Task
 */
class SGL_Task_SetupConstantsFinish extends SGL_Task
{
    function run($conf)
    {
        //  include Log.php if logging enabled
        if (isset($conf['log']['enabled']) && $conf['log']['enabled']) {
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
        // On install, $conf is empty let's load it
        if (empty($conf) && file_exists(SGL_ETC_DIR . '/customInstallDefaults.ini')) {
            $c = &SGL_Config::singleton();
            $conf1 = $c->load(SGL_ETC_DIR . '/customInstallDefaults.ini');
            if (isset($conf1['path']['moduleDirOverride'])) {
                $conf['path']['moduleDirOverride'] = $conf1['path']['moduleDirOverride'];
            }
        // On re-install or INSTALL_COMPLETE
        } elseif (count($conf)) {
            //  set constant to represent profiling mode so it can be used in Controller
            define('SGL_PROFILING_ENABLED', ($conf['debug']['profiling']) ? true : false);
            define('SGL_SEAGULL_VERSION', $conf['tuples']['version']);

            //  which degree of error severity before emailing admin
            define('SGL_EMAIL_ADMIN_THRESHOLD',
                SGL_String::pseudoConstantToInt($conf['debug']['emailAdminThreshold']));
            define('SGL_BASE_URL', $conf['site']['baseUrl']);

            //  add additional search paths
            if (!empty($conf['path']['additionalIncludePath'])) {
                $ok = ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR
                    . $conf['path']['additionalIncludePath']);
            }
        }

        if (isset($conf['path']['webRoot'])) {
            define('SGL_WEB_ROOT', $conf['path']['webRoot']);
        } elseif (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_WEB_ROOT', '@WEB-DIR@/Seagull/www');
        } else {
            define('SGL_WEB_ROOT', SGL_PATH . '/www');
        }

        define('SGL_THEME_DIR', SGL_WEB_ROOT . '/themes');
        if (!empty($conf['path']['moduleDirOverride'])) {
            define('SGL_MOD_DIR', SGL_APP_ROOT . '/' . $conf['path']['moduleDirOverride']);
        } else {
            define('SGL_MOD_DIR', SGL_APP_ROOT . '/modules');
        }
        if (!empty($conf['path']['uploadDirOverride'])) {
            define('SGL_UPLOAD_DIR', SGL_PATH . $conf['path']['uploadDirOverride']);
        } else {
            define('SGL_UPLOAD_DIR', SGL_VAR_DIR . '/uploads');
        }
        if (!empty($conf['path']['tmpDirOverride'])) {
            define('SGL_TMP_DIR', $conf['path']['tmpDirOverride']);
        } else {
            define('SGL_TMP_DIR', SGL_VAR_DIR . '/tmp');
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_InitialiseDbDataObject extends SGL_Task
{
    function run()
    {
        $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        $options = array(
            'database'              => SGL_DB::getDsn(SGL_DSN_STRING),
            'schema_location'       => SGL_ENT_DIR,
            'class_location'        => SGL_ENT_DIR,
            'require_prefix'        => SGL_ENT_DIR . '/',
            'class_prefix'          => 'DataObjects_',
            'debug'                 => SGL_Config::get('debug.dataObject'),
            'production'            => 0,
            'ignore_sequence_keys'  => 'ALL',
            'generator_strip_schema'=> 1,
            'quote_identifiers'     => 1,
        );
    }
}

/**
 * @package Task
 */
class SGL_Task_EnsurePlaceholderDbPrefixIsNull extends SGL_Task
{
    function run($conf)
    {
        // for 0.6.x versions
        if (!empty($conf['db']['prefix'])
                && $conf['db']['prefix'] == 'not implemented yet') {
            $config = &SGL_Config::singleton();
            $config->set('db', array('prefix' => ''));
            $config->save();
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_SetGlobals extends SGL_Task
{
    function run($data)
    {
        $GLOBALS['_SGL']['BANNED_IPS'] =        array();
        $GLOBALS['_SGL']['ERRORS'] =            array();
        $GLOBALS['_SGL']['QUERY_COUNT'] =       0;
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] =    false;
        $GLOBALS['_SGL']['CHARSET'] =           '';
    }
}

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

/**
 * @package Task
 */
class SGL_Task_SetupCustomErrorHandler extends SGL_Task
{
    function run($conf)
    {
        if (version_compare(phpversion(), "5.3.0", '>=')) {
            return;
        }
        //  start custom PHP error handler
        if (isset( $conf['debug']['customErrorHandler'])
                && $conf['debug']['customErrorHandler'] == true
                && !defined('SGL_TEST_MODE')) {
            require_once SGL_CORE_DIR . '/ErrorHandler.php';
            $eh = new SGL_ErrorHandler();
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

/**
 * Routine to discover the base url of the installation.
 *
 * Only gets invoked if user deletes URL in config, or if we're setting up.
 *
 * @package Task
 */
class SGL_Task_SetBaseUrl extends SGL_Task
{
    function run($conf)
    {
        if (!(isset($conf['site']['baseUrl']))) {

            //  defines SGL_BASE_URL constant
            require_once dirname(__FILE__)  . '/Install.php';
            SGL_Task_SetBaseUrlMinimal::run();
        }
    }
}

//          $userInfo = posix_getpwuid(fileowner($configFile));
//          $fileOwnerName = $userInfo['name'];
//          $allowedFileOwners = array('nobody', 'apache');
//
//          if (!in_array($fileOwnerName, $allowedFileOwners)) {
//                die("<br />Your config file in the seagull/var directory has the wrong " .
//                  "owner (currently set as: $fileOwnerName). " .
//                    "Please set the correct file owner to this directory and it's contents, eg:<br/>" .
//                    "<code>'chmod -R 777 seagull/var'</code>");
//          }

/**
 * @package Task
 */
class SGL_Task_ModifyIniSettings extends SGL_Task
{
    function run($conf)
    {
        // set php.ini directives
        @ini_set('session.auto_start',          0); //  sessions will fail fail if enabled
        @ini_set('allow_url_fopen',             0); //  this can be quite dangerous if enabled
        if (count($conf)) {
            @ini_set('error_log', SGL_PATH . '/' . $conf['log']['name']);
            if (!empty($conf['log']['ignoreRepeated'])) {
                ini_set('ignore_repeated_errors', true);
                ini_set('ignore_repeated_source', true);
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_RegisterTrustedIPs extends SGL_Task
{
    function run($data)
    {
        //  only IPs defined here can access debug sessions and delete config files
        $GLOBALS['_SGL']['TRUSTED_IPS'] = array(
            '127.0.0.1',
        );
    }
}

/**
 * @package Task
 */
class SGL_Task_LoadCustomConfig extends SGL_Task
{
    function run($conf)
    {
        if (!empty($conf['path']['pathToCustomConfigFile'])) {
            if (is_file($conf['path']['pathToCustomConfigFile'])) {
                require_once realpath($conf['path']['pathToCustomConfigFile']);
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_InitialiseModules extends SGL_Task
{
    function run()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  skip if we're in installer
        if (defined('SGL_INSTALLED')) {
            $locator = &SGL_ServiceLocator::singleton();
            $dbh = $locator->get('DB');
            if (!$dbh) {
                $dbh = & SGL_DB::singleton();
                $locator->register('DB', $dbh);
            }
            //  this task can be called when installing a new module
            if (!empty($conf['aModuleList'])) {
                $oMod = new stdClass();
                $oMod->name = $conf['aModuleList'][0];
                $aRet[] = $oMod;
            } else {
                $query = "
                    SELECT  name
                    FROM    {$conf['table']['module']}
                    ";
                $aRet = $dbh->getAll($query);
            }
            if (is_array($aRet) && count($aRet)) {
                foreach ($aRet as $oModule) {
                    $moduleInitFile = SGL_MOD_DIR . '/' . $oModule->name . '/init.php';
                    if (is_file($moduleInitFile)) {
                        require_once $moduleInitFile;
                    }
                }
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_EnsureBC extends SGL_Task
{
    function run($data)
    {
        //  load BC functions depending on PHP version detected
        if (!function_exists('version_compare') || version_compare(phpversion(), "4.3.0", 'lt')) {
            require_once SGL_ETC_DIR . '/bc.php';
        }

        if (!(function_exists('file_put_contents'))) {
            function file_put_contents($location, $data)
            {
                if (is_file($location)) {
                    unlink($location);
                }
                $fileHandler = fopen($location, "w");
                fwrite ($fileHandler, $data);
                fclose ($fileHandler);
                return true;
            }
        }

        if (!function_exists('getSystemTime')) {
            function getSystemTime()
            {
                $time = gettimeofday();
                $resultTime = $time['sec'] * 1000;
                $resultTime += floor($time['usec'] / 1000);
                return $resultTime;
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_EnsureFC extends SGL_Task
{
    function run($data)
    {
        //  load BC functions depending on PHP version detected
        if (version_compare(phpversion(), "5.3.0", '>=')) {
            date_default_timezone_set('UTC');
            error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
        }
    }
}

    // +---------------------------------------+
    // | Abstract classes                      |
    // +---------------------------------------+

/**
 * Abstract renderer strategy
 *
 * @abstract
 * @package SGL
 */
class SGL_OutputRendererStrategy
{
    /**
     * Prepare renderer options.
     *
     */
    function initEngine() {}

    /**
     * Abstract render method.
     *
     * @param SGL_View $view
     */
    function render($view) {}
}

/**
 * Container for output data and renderer strategy.
 *
 * @abstract
 * @package SGL
 */
class SGL_View
{
    /**
     * Output object.
     *
     * @var SGL_Output
     */
    var $data;

    /**
     * Reference to renderer strategy.
     *
     * @var SGL_OutputRendererStrategy
     */
    var $rendererStrategy;

    /**
     * Constructor.
     *
     * @param SGL_Output $data
     * @param SGL_OutputRendererStrategy $rendererStrategy
     * @return SGL_View
     */
    function SGL_View(&$data, $rendererStrategy)
    {
        $this->data = &$data;
        $this->rendererStrategy = $rendererStrategy;
    }

    /**
     * Post processing tasks specific to view type.
     *
     * @abstract
     * @return boolean
     */
    function postProcess() {}


    /**
     * Delegates rendering strategy based on view.
     *
     * @return string   Rendered output data
     */
    function render()
    {
        return $this->rendererStrategy->render($this);
    }
}

/**
 * Wrapper for simple HTML views.
 *
 * @package SGL
 */
class SGL_HtmlSimpleView extends SGL_View
{
    /**
     * HTML renderer decorator
     *
     * @param SGL_Output $data
     * @return string   Rendered output data
     */
    function SGL_HtmlSimpleView(&$data, $templateEngine = null)
    {
        //  prepare renderer class
        if (!$templateEngine) {
            $templateEngine = SGL_Config::get('site.templateEngine');
        }
        $templateEngine = ucfirst($templateEngine);
        $rendererClass  = 'SGL_HtmlRenderer_' . $templateEngine . 'Strategy';
        $rendererFile   = $templateEngine.'Strategy.php';

        if (is_file(SGL_LIB_DIR . '/SGL/HtmlRenderer/' . $rendererFile)) {
            require_once SGL_LIB_DIR . '/SGL/HtmlRenderer/' . $rendererFile;
        } else {
            PEAR::raiseError('Could not find renderer', SGL_ERROR_NOFILE,
                PEAR_ERROR_DIE);
        }
        parent::SGL_View($data, new $rendererClass);
    }
}

?>
