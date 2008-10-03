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
    public static function getCurrentLang()
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
    public static function getCurrentCharset()
    {
        return 'utf-8';
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $messageKey
     * @param unknown_type $getTranslation
     * @param unknown_type $messageType
     * @todo move to SGL_Controller_Page#_raiseMsg()
     */
    public static function raiseMsg($messageKey, $getTranslation = true, $messageType = SGL_MESSAGE_ERROR)
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

    /**
     * Determines current server API, ie, are we running from commandline or webserver.
     *
     * @return boolean
     */
    public static function runningFromCLI()
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

    /**
     * Very useful static method when dealing with PEAR libs ;-)
     *
     * @param unknown_type $mode
     */
    public static function setNoticeBehaviour($mode = SGL_NOTICES_ENABLED)
    {
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = ($mode) ? false : true;
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
                $locator->register('DB', $dbh);
            }
            $c = SGL_Config::singleton();
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

    /**
     * Returns TRUE if the $filename is readable, or FALSE otherwise.
     * This function uses the PHP include_path, where PHP's is_readable()
     * does not.
     *
     * @param string   $filename
     * @return boolean
     */
    public static function isReadable($filename)
    {
        if (! @fopen($filename, 'r', true)) {
            return false;
        }
        return true;
    }
}
?>