<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2010, Demian Turner                                         |
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
// | SGL.php                                                                   |
// +---------------------------------------------------------------------------+
// | Authors: Demian Turner <demian@phpkitchen.com>                            |
// |          Gilles Laborderie <gillesl@users.sourceforge.net>                |
// +---------------------------------------------------------------------------+

/**
 * Provides a set of static utility methods used by most modules.
 *
 * @package SGL
 * @author Demian Turner <demian@phpkitchen.com>
 */
class SGL
{
    /**
     * Returns the 2 letter language code, ie, de for German
     *
     * @static
     *
     * @access public
     *
     * @return string  langCode, ie zh-TW
     */
    function getCurrentLang()
    {
        $aLangs   = $GLOBALS['_SGL']['LANGUAGE'];
        $lang     = SGL_Translation::getLangID(SGL_LANG_ID_SGL);
        $langCode = $aLangs[$lang][2];
        return $langCode;
    }

    /**
     * Returns current encoding, ie, utf-8.
     *
     * @static
     *
     * @access public
     *
     * @return string  charset codepage
     */
    function getCurrentCharset()
    {
        return SGL_Translation::getCharset();
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
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        // Logging is not activated
        if (empty($conf['log']['enabled']) || $conf['log']['enabled'] == false) {
            return;
        }
        // Deal with the fact that logMessage may be called using the
        // deprecated method signature, or the new one
        if (is_int($file)) {
            $priority = $file;
        }
        // Priority is under logging threshold level
        if ($priority > SGL_String::pseudoConstantToInt($conf['log']['priority'])) {
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
        include_once 'Log.php';

        // Instantiate a logger object based on logging options
        $logger = & Log::singleton($conf['log']['type'],
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
        if ($priority == PEAR_LOG_DEBUG) {
            $message .= ' time: ' . (string)(getSystemTime() - @SGL_START_TIME) . 'ms';
        }

        // Log the message
        return $logger->log($message, $priority);
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
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  if fatal
        if ($behaviour > 0) {
            if (isset($conf['debug']['production']) && $conf['debug']['production']) {
                die ('Sorry your request can not be processed now. Try again later');
            }
            //  must log fatal msgs here as execution stops after
            //  PEAR::raiseError(arg, arg, PEAR_ERROR_DIE)
            $errorType = SGL_Error::constantToString($type);
            SGL::logMessage($errorType . ' :: ' . $msg, PEAR_LOG_EMERG);
        }
        $error = '';
        $message = SGL_String::translate($msg);

        //  catch error message that results for 'logout' where trans file is not loaded
        if ( (   isset($GLOBALS['_SGL']['ERRORS'][0])
                    && $GLOBALS['_SGL']['ERRORS'][0]->code == SGL_ERROR_INVALIDTRANSLATION)
                    || (!$getTranslation)) {
            $error = PEAR::raiseError($msg, $type, $behaviour);
        } else {
            $error = PEAR::raiseError($message, $type, $behaviour);
        }
        return $error;
    }

    function raiseMsg($messageKey, $getTranslation = true, $messageType = SGL_MESSAGE_ERROR)
    {
        //  must not log message here
        if (is_string($messageKey) && !empty($messageKey)) {

            $message = SGL_String::translate($messageKey);

            //  catch error message that results for 'logout' where trans file is not loaded
            if ( (   isset($GLOBALS['_SGL']['ERRORS'][0])
                        && $GLOBALS['_SGL']['ERRORS'][0]->code == SGL_ERROR_INVALIDTRANSLATION)
                        || (!$getTranslation)) {
                SGL_Session::set('message', $messageKey);
            } else {
                SGL_Session::set('message', $message);
            }
            SGL_Session::set('messageType', $messageType);
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
     * Returns false if no properties are set.
     *
     * Simplistic if ($prop) test is intentional, method will return false for
     * props such as 0, "0", "", null, array(), etc.
     *
     * @param object $obj
     * @return boolean
     */
    function objectHasState($obj)
    {
        $aProps = get_object_vars($obj);
        if (count($aProps)) {
            $ret = false;
            foreach ($aProps as $prop) {
                if ($prop) {
                    $ret = true;
                    break;
                }
            }
        } else {
            $ret = false;
        }
        return $ret;
    }

    //  to get around limitations of PHP4's aggregate_* methods
    function objectCopy($src, &$target)
    {
        $aProps = get_object_vars($src);
        foreach ($aProps as $attribName => $attribValue) {
            $target->{$attribName} = $attribValue;
        }
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
            if (!defined('STDIN')) {
                return false;
            } else {
                return @is_resource(STDIN);
            }
        } else {
            return in_array($sapi, array('cli', 'cgi')) && empty($_SERVER['REMOTE_ADDR']);
        }
    }

    function setNoticeBehaviour($mode = SGL_NOTICES_ENABLED)
    {
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = ($mode) ? false : true;
    }

    /**
     * Returns true on success, false if resource was not found.
     *
     * @param string $resource  File or lib name
     */
    function import($resource)
    {

    }

     /**
      * Loads region list for current language. If not found, loads region
      * list for default language (English). Put found data into $GLOBALS.
      *
      * All region lists should be UTF-8 encoded.
      *
      * @todo remove presence of $GLOBALS
      *
      * @static
      *
      * @param string $regionType
      *
      * @return mixed
      */
    function loadRegionList($regionType)
    {
        $aAllowedTypes = array('countries', 'states', 'counties');
        if (!in_array($regionType, $aAllowedTypes)) {
            return SGL::raiseError('Invalid argument', SGL_ERROR_INVALIDARGS);
        }
        if (!empty($GLOBALS['_SGL'][strtoupper($regionType)])) {
            return $GLOBALS['_SGL'][strtoupper($regionType)];
        }

        $lang = SGL::getCurrentLang();
        $file = SGL_DAT_DIR . "/ary.$regionType.$lang.php";
        if (!file_exists($file)) {
            // get data with default language
            $file = SGL_DAT_DIR . "/ary.$regionType.en.php";
        }

        // load data
        include_once $file;
        $list = ${$regionType};

        // sort arrays
        if (is_array($list)) {
            $aList = $list;

            // replace accents for utf-8 encoded string
            array_walk($aList, create_function('&$v',
                '$v = SGL_String::replaceAccents($v);'));

            // sort values
            asort($aList);

            // restore accents
            $aList = array_merge($aList, $list);
            $list = $aList;

            // decode list to current charset
            array_walk($list, array('SGL', '_toCurrentCharset'));
        }

        // remember region list in global array
        $GLOBALS['_SGL'][strtoupper($regionType)] = $list;

        return $list;
    }

    /**
     * Convert string to current charset from utf-8.
     *
     * @static
     *
     * @param string $v
     */
    function _toCurrentCharset(&$v)
    {
        $v = function_exists('iconv')
            ? iconv('UTF-8', SGL::getCurrentCharset(), $v)
            : $v;
    }

    function displayStaticPage($msg)
    {
        include_once SGL_CORE_DIR . '/Install/Common.php';
        SGL_Install_Common::printHeader('An error has occurred');
        if (SGL::runningFromCli()) {
            print $msg;
        } else {
            echo '  <div class="errorContainer">
                        <div class="errorHeader">Error</div>
                        <div class="errorContent">' . $msg . '</div>
                    </div>';
        }
        SGL_Install_Common::printFooter();
        exit();
    }

    function displayMaintenancePage(&$output)
    {
        $c      = &SGL_Config::singleton();
        $conf   = $c->getAll();
        $output->moduleName         = 'default';
        $output->theme              = !empty($conf['site']['defaultTheme'])
            ? $conf['site']['defaultTheme']
            : 'default';
        $output->masterTemplate     = 'masterBlank.html';
        $output->template           = 'maintenance.html';
        $output->charset            = $GLOBALS['_SGL']['CHARSET'];
        $output->webRoot            = SGL_BASE_URL;
        $output->imagesDir          = SGL_BASE_URL . '/themes/' . $output->theme . '/images';
        $output->versionAPI         = SGL_SEAGULL_VERSION;
        $output->sessID             = SGL_Session::getId();
        $output->scriptOpen         = "\n<script type='text/javascript'>\n//<![CDATA[\n";
        $output->scriptClose        = "\n//]]>\n</script>\n";
        $output->conf               = $conf;

        $view = new SGL_HtmlSimpleView($output);
        echo $view->render();

        exit();
    }

    /**
     * Display Seagull error page.
     *
     * @param SGL_Output $output
     */
    function displayErrorPage(&$output)
    {
        $c = &SGL_Config::singleton();

        // basics to be able to render
        $output->moduleName = SGL_Config::get('site.defaultModule');
        $output->theme      = SGL_Config::get('site.defaultTheme')
            ? SGL_Config::get('site.defaultTheme')
            : 'default';

        // templates
        $output->masterTemplate = 'masterBlank.html';
        $output->template       = 'error.html';

        // lang prefs
        $output->charset  = $GLOBALS['_SGL']['CHARSET'];
        $output->currLang = SGL::getCurrentLang()
            ? SGL::getCurrentLang()
            : 'en';
        $output->langDir  = in_array($output->currLang, array('ar', 'he'))
            ? 'rtl'
            : 'ltr';

        // paths
        $output->webRoot   = SGL_BASE_URL;
        $output->imagesDir = SGL_BASE_URL . '/themes/' . $output->theme . '/images';

        // other
        $output->versionAPI  = SGL_SEAGULL_VERSION;
        $output->sessID      = SGL_Session::getId();
        $output->scriptOpen  = "\n<script type='text/javascript'>\n//<![CDATA[\n";
        $output->scriptClose = "\n//]]>\n</script>\n";
        $output->conf        = $c->getAll();

        // output rendered page
        $view = new SGL_HtmlSimpleView($output);
        echo $view->render();

        exit;
    }

     /**
      * Returns true if a minimal version of Seagull has been installed.
      *
      * @static
      * @return boolean
      */
    function isMinimalInstall()
    {
        return is_file(SGL_PATH . '/MINIMAL_INSTALL.txt') ? true : false;
    }

     /**
      * Returns true if a module is installed, ie has a record in the module table.
      *
      * @static
      * @param string $moduleName
      * @return boolean
      */
    function moduleIsEnabled($moduleName)
    {
        static $aInstances;
        if (!isset($aInstances)) {
            $aInstances = array();
        }
        if (!isset($aInstances[$moduleName])) {

            $locator = &SGL_ServiceLocator::singleton();
            $dbh = $locator->get('DB');
            if (!$dbh) {
                $dbh = & SGL_DB::singleton();
                //  catch connection failure
                if (PEAR::isError($dbh)) {
                    return $dbh;
                }
                $locator->register('DB', $dbh);
            }
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            $query = "
                SELECT  module_id
                FROM    {$conf['table']['module']}
                WHERE   name = " .$dbh->quoteSmart($moduleName);
            $ret = $dbh->getOne($query);
            if (PEAR::isError($ret)) {
                return false;
            } else {
                $aInstances[$moduleName] = $ret;
            }
        }
        return ! is_null($aInstances[$moduleName]);
    }
}

if (!SGL::isPhp5() && !function_exists('clone')) {
    // emulate clone  - as per php_compact, slow but really the correct behaviour..
    eval('function clone($t) { $r = $t; if (method_exists($r,"__clone")) { $r->__clone(); } return $r; }');
}
?>