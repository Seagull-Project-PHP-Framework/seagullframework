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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// | SGL2.php                                                                   |
// +---------------------------------------------------------------------------+
// | Authors: Demian Turner <demian@phpkitchen.com>                            |
// +---------------------------------------------------------------------------+

/**
 * Register SGL2::autoload() with SPL.
 */
spl_autoload_register(array('SGL2', 'autoload'));

$sglPath = realpath(dirname(__FILE__).'/..');
$libPath = realpath(dirname(__FILE__).'/../lib');

define('SGL2_PATH', $sglPath);
set_include_path($libPath.PATH_SEPARATOR.get_include_path());
require_once 'SGL2/File.php';

/**
 * Provides a set of static utility methods used by most modules.
 *
 * @package SGL
 * @author Demian Turner <demian@phpkitchen.com>
 */
class SGL2
{
    /**
     *
     * Loads a class or interface file from the include_path.
     *
     * @param string $name A Seagull (or other) class or interface name.
     * @author Thanks to Solar
     * @return void
     *
     */
    public static function autoload($name)
    {
        // did we ask for a non-blank name?
        if (trim($name) == '') {
            new Exception('No class or interface named for loading');
        }

        // pre-empt further searching for the named class or interface.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        if (class_exists($name, false) || interface_exists($name, false)) {
            return;
        }

        // convert the class name to a file path.
        $file = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';

        // include the file and check for failure. we use Solar_File::load()
        // instead of require() so we can see the exception backtrace.
        SGL2_File::load($file);

        // if the class or interface was not in the file, we have a problem.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        if (! class_exists($name, false) && ! interface_exists($name, false)) {
            throw new Exception('Class or interface does not exist in loaded file');
        }
    }

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
        $lang     = SGL2_Translation::getLangID(SGL2_LANG_ID_SGL);
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
     * @todo move to SGL2_Controller_Page#_raiseMsg()
     */
//    public static function raiseMsg($messageKey, $getTranslation = true, $messageType = SGL2_MESSAGE_ERROR)
//    {
//        //  must not log message here
//        if (is_string($messageKey) && !empty($messageKey)) {
//
//            $message = SGL2_String::translate($messageKey);
//
//            //  catch error message that results for 'logout' where trans file is not loaded
//            if ( (   isset($GLOBALS['_SGL']['ERRORS'][0])
//                        && $GLOBALS['_SGL']['ERRORS'][0]->code == SGL2_ERROR_INVALIDTRANSLATION)
//                        || (!$getTranslation)) {
//                SGL2_Session::set('message', $messageKey);
//            } else {
//                SGL2_Session::set('message', $message);
//            }
//            SGL2_Session::set('messageType', $messageType);
//        } else {
//            SGL::raiseError('supplied message not recognised', SGL2_ERROR_INVALIDARGS);
//        }
//    }

    /**
     * Very useful static method when dealing with PEAR libs ;-)
     *
     * @param unknown_type $mode
     */
    public static function setNoticeBehaviour($mode = SGL2_NOTICES_ENABLED)
    {
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = ($mode) ? false : true;
    }


     /**
      * Loads region list for current language. If not found, loads region
      * list for default language (English). Put found data into $GLOBALS.
      *
      * All region lists should be UTF-8 encoded.
      *
      * @param string $regionType
      * @return mixed
      * @todo remove presence of $GLOBALS
      * @todo move to plugin
      */
    function loadRegionList($regionType)
    {
        $aAllowedTypes = array('countries', 'states', 'counties');
        if (!in_array($regionType, $aAllowedTypes)) {
            throw new Exception('Invalid argument', SGL2_ERROR_INVALIDARGS);
        }
        if (!empty($GLOBALS['_SGL'][strtoupper($regionType)])) {
            return $GLOBALS['_SGL'][strtoupper($regionType)];
        }

        $lang = SGL2_Translation3::getDefaultLangCode();
        $file = SGL2_DAT_DIR . "/ary.$regionType.$lang.php";
        if (!file_exists($file)) {
            // get data with default language
            $file = SGL2_DAT_DIR . "/ary.$regionType.en.php";
        }

        // load data
        include_once $file;
        $list = ${$regionType};

        // sort arrays
        if (is_array($list)) {
            $aList = $list;

            // replace accents for utf-8 encoded string
            array_walk($aList, create_function('&$v',
                '$v = SGL2_String::replaceAccents($v);'));

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
    protected function _toCurrentCharset(&$v)
    {
        $v = function_exists('iconv')
            ? iconv('UTF-8', SGL2_Translation3::getDefaultCharset(), $v)
            : $v;
    }
}
?>