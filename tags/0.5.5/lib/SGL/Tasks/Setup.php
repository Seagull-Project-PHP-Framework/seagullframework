<?php
require_once dirname(__FILE__) . '/../Task.php';

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
            define('SGL_PATH', dirname(dirname(dirname((dirname(__FILE__))))));
            define('SGL_LIB_PEAR_DIR', SGL_PATH . '/lib/pear');
        }

        $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
        $allowed = @ini_set('include_path',      '.' . $includeSeparator
            . SGL_LIB_PEAR_DIR);
        if (!$allowed) {

            //  depends on PHP version being >= 4.3.0
            if (function_exists('set_include_path')) {
                set_include_path('.' . $includeSeparator . SGL_LIB_PEAR_DIR);
            } else {
                die('You need at least PHP 4.3.0 if you want to run Seagull
                with safe mode enabled.');
            }
        }
        require_once 'PEAR.php';
        require_once 'DB.php';
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
        define('SGL_TMP_DIR',                   SGL_VAR_DIR . '/tmp');
        define('SGL_CACHE_DIR',                 SGL_VAR_DIR . '/cache');
        define('SGL_UPLOAD_DIR',                SGL_VAR_DIR . '/uploads');
        define('SGL_LIB_DIR',                   SGL_APP_ROOT . '/lib');
        define('SGL_MOD_DIR',                   SGL_APP_ROOT . '/modules');
        define('SGL_ENT_DIR',                   SGL_CACHE_DIR . '/entities');
        define('SGL_BLK_DIR',                   SGL_MOD_DIR . '/block/classes/blocks');
        define('SGL_DAT_DIR',                   SGL_APP_ROOT . '/lib/data');
        define('SGL_CORE_DIR',                  SGL_APP_ROOT . '/lib/SGL');

        //  error codes to use with SGL::raiseError()
        //  start at -100 in order not to conflict with PEAR::DB error codes
        define('SGL_ERROR_INVALIDARGS',         -101);  // wrong args to function
        define('SGL_ERROR_INVALIDCONFIG',       -102);  // something wrong with the config
        define('SGL_ERROR_NODATA',              -103);  // no data available
        define('SGL_ERROR_NOCLASS',             -104);  // no class exists
        define('SGL_ERROR_NOMETHOD',            -105);  // no method exists
        define('SGL_ERROR_NOAFFECTEDROWS',      -106);  // no rows where affected by update/insert/delete
        define('SGL_ERROR_NOTSUPPORTED'  ,      -107);  // limit queries on unsuppored databases
        define('SGL_ERROR_INVALIDCALL',         -108);  // overload getter/setter failure
        define('SGL_ERROR_INVALIDAUTH',         -109);
        define('SGL_ERROR_EMAILFAILURE',        -110);
        define('SGL_ERROR_DBFAILURE',           -111);
        define('SGL_ERROR_DBTRANSACTIONFAILURE',-112);
        define('SGL_ERROR_BANNEDUSER',          -113);
        define('SGL_ERROR_NOFILE',              -114);
        define('SGL_ERROR_INVALIDFILEPERMS',    -115);
        define('SGL_ERROR_INVALIDSESSION',      -116);
        define('SGL_ERROR_INVALIDPOST',         -117);
        define('SGL_ERROR_INVALIDTRANSLATION',  -118);
        define('SGL_ERROR_FILEUNWRITABLE',      -119);
        define('SGL_ERROR_INVALIDMETHODPERMS',  -120);
        define('SGL_ERROR_INVALIDREQUEST',      -121);
        define('SGL_ERROR_INVALIDTYPE',         -122);
        define('SGL_ERROR_RECURSION',           -123);

        //  automate sorting
        define('SGL_SORTBY_GRP',                1);
        define('SGL_SORTBY_USER',               2);
        define('SGL_SORTBY_ORG',                3);

        //  Seagull user roles
        define('SGL_ANY_ROLE', 				    -2);
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

        //  define return types, k/v pairs, arrays, strings, etc
        define('SGL_RET_NAME_VALUE',            1);
        define('SGL_RET_ID_VALUE',              2);
        define('SGL_RET_ARRAY',                 3);
        define('SGL_RET_STRING',                4);

        //  various
        define('SGL_ANY_SECTION', 				0);
        define('SGL_NEXT_ID', 				    0);
        define('SGL_NOTICES_DISABLED',          0);
        define('SGL_NOTICES_ENABLED',           1);

        //  with logging, you can optionally show the file + line no. where
        //  SGL::logMessage was called from
        define('SGL_DEBUG_SHOW_LINE_NUMBERS',   false);

        //  to overcome overload problem
        define('DB_DATAOBJECT_NO_OVERLOAD', true);

        require_once dirname(__FILE__)  . '/../Url.php';
        require_once dirname(__FILE__)  . '/../UrlParserSefStrategy.php';
        require_once dirname(__FILE__)  . '/../Manager.php';
        require_once dirname(__FILE__)  . '/../Output.php';
        require_once dirname(__FILE__)  . '/../String.php';
        require_once dirname(__FILE__)  . '/../Tasks/Process.php';
        require_once dirname(__FILE__)  . '/../HTTP.php';
        require_once dirname(__FILE__)  . '/../ServiceLocator.php';
    }
}

class SGL_Task_SetupConstantsFinish extends SGL_Task
{
    function run($conf)
    {
        if (isset($conf['path']['webRoot'])) {
            define('SGL_WEB_ROOT', $conf['path']['webRoot']);
        } elseif (defined('SGL_PEAR_INSTALLED')) {
            define('SGL_WEB_ROOT', '@WEB-DIR@/Seagull/www');
        } else {
            define('SGL_WEB_ROOT', SGL_PATH . '/www');
        }

        define('SGL_THEME_DIR', SGL_WEB_ROOT . '/themes');

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

        if (count($conf)) {

            //  set constant to represent profiling mode so it can be used in Controller
            define('SGL_PROFILING_ENABLED', ($conf['debug']['profiling']) ? true : false);
            define('SGL_SEAGULL_VERSION', $conf['tuples']['version']);

            //  which degree of error severity before emailing admin
            $const = str_replace("'", "", $conf['debug']['emailAdminThreshold']);
            define('SGL_EMAIL_ADMIN_THRESHOLD', constant($const));
            define('SGL_BASE_URL', $conf['site']['baseUrl']);
        }
    }
}

/**
 * Routine to discover the base url of the installation.
 *
 * Only gets invoked if user deletes URL in config, or if we're setting up.
 */
class SGL_Task_SetBaseUrl extends SGL_Task
{
    function run($conf)
    {
        if (!(isset($conf['site']['baseUrl']))) {

            //  defines SGL_BASE_URL constant
            require_once dirname(__FILE__)  . '/../Tasks/Install.php';
            SGL_Task_SetBaseUrlMinimal::run();
        }
    }
}


class SGL_Task_SetGlobals extends SGL_Task
{
    function run($data)
    {
        $GLOBALS['_SGL']['BANNED_IPS'] =        array();
        $GLOBALS['_SGL']['ERRORS'] =            array();
        $GLOBALS['_SGL']['QUERY_COUNT'] =       0;
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] =    false;
    }
}

//			$userInfo = posix_getpwuid(fileowner($configFile));
//			$fileOwnerName = $userInfo['name'];
//			$allowedFileOwners = array('nobody', 'apache');
//
//			if (!in_array($fileOwnerName, $allowedFileOwners)) {
//                die("<br />Your config file in the seagull/var directory has the wrong " .
//					"owner (currently set as: $fileOwnerName). " .
//                    "Please set the correct file owner to this directory and it's contents, eg:<br/>" .
//                    "<code>'chmod -R 777 seagull/var'</code>");
//			}

class SGL_Task_ModifyIniSettings extends SGL_Task
{
    function run($conf)
    {
        // set php.ini directives
        @ini_set('session.auto_start',          0); //  sessions will fail fail if enabled
        @ini_set('allow_url_fopen',             0); //  this can be quite dangerous if enabled
        @ini_set('error_log',                   SGL_PATH . '/' . $conf['log']['name']);
    }
}

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
                if (file_exists($location)) {
                    unlink($location);
                }
                $fileHandler = fopen ($location, "w");
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

?>