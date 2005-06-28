<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | SGL.php                                                                   |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// |            Gilles Laborderie <gillesl@users.sourceforge.net>              |
// +---------------------------------------------------------------------------+
// $Id: SGL.php,v 1.20 2005/05/17 22:53:29 demian Exp $

require_once 'PEAR.php';
require_once 'DB.php';
require_once SGL_CORE_DIR . '/DB.php';
require_once SGL_CORE_DIR . '/HTTP.php';
require_once SGL_CORE_DIR . '/String.php';

/**
 * Base class the Seagull framework.
 *
 * A set of utility methods used by most modules.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.20 $
 * @since   PHP 4.1
 */
class SGL
{
    /**
     * Returns current time in YYYY-MM-DD HH:MM:SS format.
     * 
     * GMT format is best for logging system events, otherwise locale offset
     * will be most helpful to users.
     * 
     * @access public
     * @static
     * @param boolean $gmt       is time GMT or locale offset
     * @return string $instance  formatted current time
     * @todo factor out Cache and Lang methods into their own objects
     */
    function getTime($gmt = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        static $instance;
        if (!isset($instance)) {
            $instance = ($gmt)  ? gmstrftime("%Y-%m-%d %H:%M:%S", time())
                                : strftime("%Y-%m-%d %H:%M:%S", time());
        }
        return $instance;
    }

    /**
     * Returns the 2 letter language code, ie, de for German.
     *
     * @access  public
     * @static
     * @return string    language abbreviation
     */
    function getCurrentLang()
    {
        $aLangs = $GLOBALS['_SGL']['LANGUAGE'];
        $sessLang = $_SESSION['aPrefs']['language'];
        return $aLangs[$sessLang][2];
    }

    /**
     * Log a message to the global Seagull log backend.
     *
     * Note that the method can be safely called by simply omitting the deprecated
     * parameters (but doesn't have to be).
     *
     * @access public
     * @static
     * @param mixed $message     Either a string or a PEAR_Error object.
     * @param string $file       Deprecated.
     * @param integer $line      Deprecated.
     * @param integer $priority  The priority of the message. One of:
     *                           PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT
     *                           PEAR_LOG_ERR, PEAR_LOG_WARNING, PEAR_LOG_NOTICE
     *                           PEAR_LOG_INFO, PEAR_LOG_DEBUG
     * @return boolean           True on success or false on failure.
     * @author Andrew Hill <andrew@awarez.net>
     * @author Gilles Laborderie <gillesl@users.sourceforge.net>
     * @author Horde Group <http://www.horde.org>
     */
    function logMessage($message, $file = null, $line = null, $priority = PEAR_LOG_INFO)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        // Logging is not activated
        if ($conf['log']['enabled'] == false) {
            return;
        }
        // Deal with the fact that logMessage may be called using the
        // deprecated method signature, or the new one
        if (is_int($file)) {
            $priority =& $file;
        }
        // Priority is under logging threshold level
        if ($priority > @constant($conf['log']['priority'])) {
            return;
        }
        // Grab DSN if we are logging to a database
        $dsn = ($conf['log']['type'] == 'sql') ? SGL_DB::getDsn() : '';

        // if log type is file, determine if log path is absolute, ie: /tmp/ or c:\
        if ($conf['log']['type'] == 'file') {
            if ($conf['log']['name']{0} == '/' || $conf['log']['name']{1} == ':') {
                $logName = $conf['log']['name'];
            } else {
                $logName = SGL_PATH . '/' . $conf['log']['name'];
            }        
        } else {
            $logName = $conf['log']['name'];
        }
        
        // Instantiate a logger object based on logging options
        $logger = & Log::singleton( $conf['log']['type'], 
                                    $logName,
                                    $conf['log']['ident'], 
                                    array(  $conf['log']['paramsUsername'],
                                            $conf['log']['paramsPassword'],
                                            'dsn' => $dsn
                                    ));
        // If log message is an error object, extract info
        if (is_a($message, 'PEAR_Error')) {
            $userinfo = $message->getUserInfo();
            $message = $message->getMessage();
            if (!empty($userinfo)) {
                if (is_array($userinfo)) {
                    $userinfo = implode(', ', $userinfo);
                }
            $message .= ' : ' . $userinfo;
            }
        }
        // Obtain backtrace information, if supported by PHP
        if (version_compare(phpversion(), '4.3.0') >= 0) {      
            $bt = debug_backtrace();
            if (isset($bt[1]['class']) && $bt[1]['type'] && isset($bt[1]['function'])) {
                $callInfo = $bt[1]['class'] . $bt[1]['type'] . $bt[1]['function'] . ': ';
                $message = $callInfo . $message;
            }
            if (SGL_DEBUG_SHOW_LINE_NUMBERS) {
                if (isset($bt[0]['file']) && isset($bt[0]['line'])) {
                    $message .=  "\n" . str_repeat(' ', 20 + strlen($conf['log']['ident']) + strlen($logger->priorityToString($priority)));
                    $message .= 'on line ' . $bt[0]['line'] . ' of "' . $bt[0]['file'] . '"';
                }
            }
        }
        // Log the message
        return $logger->log($message, $priority);
    }

    /**
     * Determines if a debug session is permittetd for the current authenticated user.
     *
     * @access  public
     * @static
     * @return boolean   true if debug session allowed
     */
    function debugAllowed()
    {
        $conf = & $GLOBALS['_SGL']['CONF'];
        return $conf['debug']['sessionDebugAllowed'] &&
            in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['_SGL']['TRUSTED_IPS']);
    }

    /**
     * Clear cache directory of a specific module's cache files. A simple wrapper to
     * PEAR::Cache_Lite's clean() method.
     *
     * @access public
     * @param  string $group name of the cache group (e.g. nav, blocks, etc.)
     * @return boolean true on success
     * @author  Andy Crain <apcrain@fuse.net>
     */
     function clearCache($group = false)
     {
        $cache = & SGL::cacheSingleton();
        return $cache->clean($group);
     }

    /**
     * Returns a singleton Cache_Lite instance.
     *
     * example usage: 
     * $cache = & SGL::cacheSingleton();
     * warning: in order to work correctly, the cache
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  mixed reference to Cache_Lite object
     */
    function &cacheSingleton()
    {
        static $instance;
        // If the instance is not there, create one
        if (!isset($instance)) {
            require_once 'Cache/Lite.php';
            $conf = & $GLOBALS['_SGL']['CONF'];
            $options = array(
                'cacheDir'  => SGL_TMP_DIR . '/', 
                'lifeTime'  => $conf['cache']['lifetime'],
                'caching'   => $conf['cache']['enabled']);
            $instance = new Cache_Lite($options);
        }
        return $instance;
    }

    /**
     * Converts error constants into equivalent strings.
     *
     * @access  public
     * @param   int     $errorCode  error code
     * @return  string              text representing error type
     */
    function errorConstantToString($errorCode)
    {
        $aErrorCodes = array(
            SGL_ERROR_INVALIDARGS       => 'invalid arguments',
            SGL_ERROR_INVALIDCONFIG     => 'invalid config',
            SGL_ERROR_NODATA            => 'no data',
            SGL_ERROR_NOCLASS           => 'no class',
            SGL_ERROR_NOMETHOD          => 'no method',
            SGL_ERROR_NOAFFECTEDROWS    => 'no affected rows',
            SGL_ERROR_NOTSUPPORTED      => 'not supported',
            SGL_ERROR_INVALIDCALL       => 'invalid call',
            SGL_ERROR_INVALIDAUTH       => 'invalid auth',
            SGL_ERROR_EMAILFAILURE      => 'email failure',
            SGL_ERROR_DBFAILURE         => 'db failure',
            SGL_ERROR_DBTRANSACTIONFAILURE => 'db transaction failure',
            SGL_ERROR_BANNEDUSER        => 'banned user',
            SGL_ERROR_NOFILE            => 'no file',
            SGL_ERROR_INVALIDFILEPERMS  => 'invalid file perms',
            SGL_ERROR_INVALIDSESSION    => 'invalid session',
            SGL_ERROR_INVALIDPOST       => 'invalid post',
            SGL_ERROR_INVALIDTRANSLATION => 'invalid translation',
            SGL_ERROR_FILEUNWRITABLE    => 'file unwritable',
            SGL_ERROR_INVALIDREQUEST    => 'invalid request',
            SGL_ERROR_INVALIDTYPE       => 'invalid type',
            SGL_ERROR_RECURSION         => 'recursion',
        );
        if (in_array($errorCode, array_keys($aErrorCodes))) {
            return strtoupper($aErrorCodes[$errorCode]);

        //  if not within this range, most likely a PEAR::DB error
        } else {
            return 'PEAR';
        }
    }

    function errorObjToString($oError)
    {
        $message = $oError->getMessage();
        $debugInfo = $oError->getDebugInfo();
        $level = $oError->getCode();
        $errorType = SGL::errorConstantToString($level);
        $output = <<<EOF
  <strong>MESSAGE</strong>: $message<br />
  <strong>TYPE:</strong> $errorType<br />
  <strong>DEBUG INFO:</strong> $debugInfo<br />
  <strong>CODE:</strong> $level<br />
EOF;
        return $output;
    }

    /**
     * A static method to invoke errors.
     *
     * @static
     * @access  public
     * @param   string  $msg        the error message
     * @param   int     $type       custom message code
     * @param   int     $behaviour  behaviour (die or continue!);
     * @return  object  $error      PEAR error
     */
    function raiseError($msg, $type = null, $behaviour = null, $getTranslation = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        //  if fatal
        if ($behaviour > 0) {
            if ($conf['debug']['production']) {
                die ('Sorry your request can not be processed now. Try again later');
            }
            //  must log fatal msgs here as execution stops after
            //  PEAR::raiseError(arg, arg, PEAR_ERROR_DIE)
            $errorType = SGL::errorConstantToString($type);
            SGL::logMessage($errorType . ' :: ' . $msg, PEAR_LOG_EMERG);
        }
        $error = '';
        $message = SGL_String::translate($msg);
        //  catch error message that results for 'logout' where trans file is not loaded
        if ( (   isset($GLOBALS['_SGL']['ERRORS'][0])
                    && $GLOBALS['_SGL']['ERRORS'][0]->code == SGL_ERROR_INVALIDTRANSLATION)
                    ||(!$getTranslation)) {
            $error = PEAR::raiseError($msg, $type, $behaviour);
        } else {
            $error = PEAR::raiseError($message, $type, $behaviour);
        }
        return $error;
    }

    function raiseMsg($msg, $getTranslation = true)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_string($msg) && !empty($msg)) {

            $message = SGL_String::translate($msg);

            //  catch error message that results for 'logout' where trans file is not loaded
            if ( (   isset($GLOBALS['_SGL']['ERRORS'][0])
                        && $GLOBALS['_SGL']['ERRORS'][0]->code == SGL_ERROR_INVALIDTRANSLATION)
                        ||(!$getTranslation)) {
                SGL_HTTP_Session::set('message', $msg);
            } else {
                SGL_HTTP_Session::set('message', $message);
            }
        } else {
            SGL::raiseError('supplied message not recognised', SGL_ERROR_INVALIDARGS);
        }
    }

    function isPhp5()
    {
        $phpVersion = PHP_VERSION;
        return ($phpVersion{0} == 5);
    }

    /**
     * Determines current server API, ie, are we running from commandline or webserver.
     *
     * @return boolean
     */
    function runningFromCLI()
    {
        // STDIN isn't a CLI constant before 4.3.0
        $sapi = php_sapi_name();
        if (version_compare(PHP_VERSION, '4.3.0') >= 0 && $sapi != 'cgi') {
            return @is_resource(STDIN);
        } else {
            return in_array($sapi, array('cli', 'cgi')) && empty($_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Makes up for case insensitive classnames in php4 with get_class().
     *
     * @access   public
     * @static    
     * @param    string $str classname  
     * @return   mixed       either correct case classname or false
     */
    function caseFix($str)
    {
        if (SGL::isPhp5()) {
            return $str;
        }
        static $aConfValues;
        if (!isset($aConfValues)) {
            $conf = & $GLOBALS['_SGL']['CONF'];
            $aConfValues = array_keys($conf);
        }
        $aConfValuesLowerCase = array_map('strtolower', $aConfValues);
        $isFound = array_search(strtolower($str), $aConfValuesLowerCase);
        return ($isFound !== false) ? $aConfValues[$isFound] : false;
    }
}
?>