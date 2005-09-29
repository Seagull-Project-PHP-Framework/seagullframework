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
// | constants.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: constants.php,v 1.31 2005/06/23 18:21:24 demian Exp $

    require_once dirname(__FILE__) . '/lib/SGL/Url.php';

    setupConstants();

    function setupConstants()
    {
        define('SGL_SERVER_NAME', hostnameToFilename());
        define('SGL_PATH', dirname(__FILE__));

        //  only IPs defined here can access debug sessions and delete config files
        $GLOBALS['_SGL']['TRUSTED_IPS'] = array(
            '127.0.0.1',
        );

		$configFile = SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.ini.php';

        //  test if a config delete is requested (see feature request 985089)
        if (isset( $_GET['deleteConfig']) 
                && $_GET['deleteConfig'] == 1 
                && in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['_SGL']['TRUSTED_IPS'])) {
            @unlink($configFile) or
            die('it was not possible to delete the config file, either it doesn\'t exist'.
                ' or you don\'t have sufficient file perms to delete it');
            @unlink(SGL_PATH . '/var/INSTALL_COMPLETE');
        }

        if (    !file_exists($configFile)
            &&  !file_exists(SGL_PATH . '/var/INSTALL_COMPLETE')) {
            
            $success = @copy(SGL_PATH . '/etc/default.conf.ini.dist', $configFile);
            if (!$success) {
                die("<br />Your config file cannot be copied to the seagull/var directory, " .
                    "please give the webserver write permissions to this directory, eg:<br />" .
                    "<code>'chmod 777 seagull/var'</code>");
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

            $GLOBALS['_SGL']['executeDbBootstrap'] = 1;
        }
        
        $conf = @parse_ini_file($configFile, true);

        //  set protocol correctly, build base url
        //  allows for various possibilities:
        //  - http://localhost/seagull/www
        //  - http://localhost:8080
        //  - http://www.example.com
        $serverName = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);
        
        $tmp  = new SGL_URL($_SERVER['PHP_SELF']);
        
        //  set baseUrl
        if (!(isset($conf['site']['baseUrl']))) {
            $conf['site']['baseUrl'] = getBaseUrl($conf, $serverName);
        }

        //  store in Seagull namespace
        $GLOBALS['_SGL']['CONF'] = $conf;

        // framework file structure
        define('SGL_BASE_URL',                  $conf['site']['baseUrl']);
        define('SGL_WEB_ROOT',                  SGL_PATH . '/www');
        define('SGL_LOG_DIR',                   SGL_PATH . '/var/log');
        define('SGL_TMP_DIR',                   SGL_PATH . '/var/tmp');
        define('SGL_CACHE_DIR',                 SGL_PATH . '/var/cache');
        define('SGL_UPLOAD_DIR',                SGL_PATH . '/var/uploads');
        define('SGL_LIB_PEAR_DIR',              SGL_PATH . '/lib/pear');
#        define('SGL_LIB_PEAR_DIR',              '@PEAR-DIR@');
        define('SGL_LIB_DIR',                   SGL_PATH . '/lib');
        define('SGL_ENT_DIR',                   SGL_CACHE_DIR . '/entities');
        define('SGL_MOD_DIR',                   SGL_PATH . '/modules');
        define('SGL_BLK_DIR',                   SGL_MOD_DIR . '/block/classes/blocks');
        define('SGL_DAT_DIR',                   SGL_PATH . '/lib/data');
        define('SGL_CORE_DIR',                  SGL_PATH . '/lib/SGL');
        define('SGL_THEME_DIR',                 SGL_WEB_ROOT . '/themes');

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

        // set php.ini directives
        $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
        $allowed = @ini_set('include_path',      '.' . $includeSeparator . SGL_LIB_PEAR_DIR);
        @ini_set('session.auto_start',          0); //  sessions will fail fail if enabled
        @ini_set('allow_url_fopen',             0); //  this can be quite dangerous if enabled
        @ini_set('error_log',                   SGL_PATH . '/' . $conf['log']['name']);

        if (!$allowed) {
            //  depends on PHP version being >= 4.3.0
            if (function_exists('set_include_path')) {
                set_include_path('.' . $includeSeparator . SGL_LIB_PEAR_DIR);
            } else {
                die('You need at least PHP 4.3.0 if you want to run Seagull
                with safe mode enabled.');
            }
        }

        //  set constant to represent profiling mode so it can be used in Controller
        define('SGL_PROFILING_ENABLED',         ($conf['debug']['profiling']) ? true : false);

        //  automate sorting
        define('SGL_SORTBY_GRP',                1);
        define('SGL_SORTBY_USER',               2);
        define('SGL_SORTBY_ORG',                3);
        
        //  Seagull user types
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
        
        //  with logging, you can optionally show the file + line no. where 
        //  SGL::logMessage was called from
        define('SGL_DEBUG_SHOW_LINE_NUMBERS',   false);

        //  set globals
        $GLOBALS['_SGL']['BANNED_IPS'] =        array();
        $GLOBALS['_SGL']['ERRORS'] =            array();
        $GLOBALS['_SGL']['CONNECTIONS'] =       array();
        $GLOBALS['_SGL']['QUERY_COUNT'] =       0;
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] =    false;
        $GLOBALS['_SGL']['VERSION'] =           $conf['tuples']['version'];
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
        if (php_sapi_name() != 'cli') {

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

    function getProtocol()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  == 'on') ? 'https' : 'http';
    }

    function getBaseUrl($conf, $serverName)
    {
        $baseFolder = dirname($_SERVER['PHP_SELF']);

        //  remove all elements after frontscript name
        $aUriParts = array_reverse(explode('/', $baseFolder));

        //  step through array and strip until fc element is reached
        if (in_array($conf['site']['frontScriptName'], $aUriParts)) {
            foreach ($aUriParts as $elem) {
                array_shift($aUriParts);
                if ($elem == $conf['site']['frontScriptName']) {
                    break;
                }
            }
        }
        $baseFolder = implode('/', array_reverse($aUriParts));

        //  handle case for user's homedir, ie, presence of tilda: example.com/~seagull
        if (preg_match('/~/', $baseFolder)) {
            $baseFolder = str_replace('~', '%7E', $baseFolder);
        }
        $baseUrl = getProtocol() . '://' . $serverName . $baseFolder;

        //  chop relevant final slash
        $search = (substr(PHP_OS, 0, 3) == 'WIN') ? "/\$/" : "/\/$/";
        if (preg_match($search, $baseUrl)) {
            $baseUrl = rtrim($baseUrl, DIRECTORY_SEPARATOR);
        }
        return $baseUrl;
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