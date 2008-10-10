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
// | Seagull 0.9                                                               |
// +---------------------------------------------------------------------------+
// | String.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: String.php,v 1.14 2005/06/01 11:11:23 demian Exp $

/**
 * Various string helper methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.14 $
 */
class SGL2_String
{

    public static function trimWhitespace($var)
    {
        if (!isset($var)) {
            return false;
        }
        if (is_array($var)) {
            $newArray = array();
            foreach ($var as $key => $value) {
                $newArray[$key] = SGL2_String::trimWhitespace($value);
            }
            return $newArray;
        } else {
            return trim($var);
        }
    }

    /**
     * Returns cleaned user input.
     *
     * Instead of addslashing potential ' and " chars, let's remove them and get
     * rid of any magic quoting which is enabled by default.  Also removes any
     * html tags and ASCII zeros
     *
     * @access  public
     * @param   string $var  The string to clean.
     * @return  string       $cleaned result.
     */
    public static function clean($var)
    {
        if (!isset($var)) {
            return false;
        }
        $var = SGL2_String::trimWhitespace($var);
        if (is_array($var)) {
            $newArray = array();
            foreach ($var as $key => $value) {
                $newArray[$key] = SGL2_String::clean($value);
            }
            return $newArray;
        } else {
            return strip_tags($var);
        }
    }

    public static function removeJs($var)
    {
        if (!isset($var)) {
            return false;
        }
        $var = SGL2_String::trimWhitespace($var);
        if (is_array($var)) {
            $newArray = array();
            foreach ($var as $key => $value) {
                $newArray[$key] = SGL2_String::removeJs($value);
            }
            return $newArray;
        } else {
            $search = "/<script[^>]*?>.*?<\/script\s*>/i";
            $replace = '';
            $clean = preg_replace($search, $replace, $var);
            return $clean;
        }
    }

    /**
     * Uses PHP tidy lib (http://www.coggeshall.org/tidy.php) if enabled and
     * extension is available. Cleans/corrects input html. If $logErrors
     * is set to true and logging is set to true in default.conf.ini, tidy()
     * will add entry to log with a string describing the errors and changes
     * Tidy made via SGL::logMessage().
     *
     * @param string $html the text to clean
     * @param bool $logErrors
     * @return string cleaned text
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @since   PHP 4.3
     * @todo this should be a plugin
     */
    function tidy($html, $logErrors = false)
    {
        if (       !SGL2_Config::get('site.tidyhtml')
                || !extension_loaded('tidy')) {
            return $html;
        }

        $options = array(
            'wrap' => 0,
            'indent' => true,
            'indent-spaces' => 4,
            'output-xhtml' => true,
            'drop-font-tags' => false,
            'clean' => false,
        );
        if (strlen($html)) {
            $tidy = new Tidy();
            $tidy->parseString($html, $options, 'utf8');
            $tidy->cleanRepair();
            $ret = $tidy->body();
        }

        return $ret;
    }

    /**
     * Looks up key in current lang dictionary (SGL2_Translation3) or specific language
     * and returns target value.
     *
     * @param string $key       Translation term
     * @param string $filter    Optional filter fn
     * @param array  $aParams   Optional params
     * @param string $langCode  Optional langCode to force translation in this language
     * @return string
     *
     */
    public static function translate($key, $filter = false, $aParams = array(), $langCode)
    {
        $trans = SGL2_Translation3::singleton('array');
        if ($ret = $trans->translate($langCode, $key)) {
            if (!is_array($ret) && $filter && function_exists($filter)) {
                if (is_object($aParams)) {
                    $aParams = (array)$aParams;
                }
                if (!empty($aParams) && is_array($aParams) && $filter == 'vprintf') {
                    $i = 1;
                    foreach ($aParams as $key => $value) {
                        if (!empty($value) && !is_scalar($value)) {
                            continue;
                        }
                        $value = str_replace('%', '%%', $value);
                        $ret = str_replace("%$i%", $value, $ret);
                        $ret = str_replace("%$i", $value, $ret);
                        $ret = str_replace("%$key%", $value, $ret);
                        $ret = str_replace("%$key", $value, $ret);
                        $i++;
                    }
                    $ret = vsprintf($ret, $aParams);
                } else {
                    $ret = $filter($ret);
                }
            }
            return $ret;
        } else {
            $key = SGL2_Config::get('debug.showUntranslated')
                ? '>' . $key . '<'
                : $key;
            return $key;
        }
    }

    /**
     * Encode a given character to a decimal or hexadecimal HTML entity or
     * to an hexadecimal URL-encoded symbol.
     *
     * @param string $char Char to encode
     * @param mixed $encoding 1 or D for decimal entity, 2 or H for hexa entity,
     *        3 or U for URL-encoding,
     *        R for a random choice of any of the above,
     *        E for a random choice of any of the HTML entities.
     * @return string $encoded Encoded character (or raw char if unknown encoding)
     *
     * @author  Philippe Lhoste <PhiLho(a)GMX.net>
     * @todo move to plugin
     */
    function char2entity($char, $encoding = 'H')
    {
        $pad = 1;
        if ($encoding == 'R' || $encoding == 'E') {
            // Use random padding with zeroes
            // Unicode stops at 0x10FFFF, ie. at 1114111 (7 digits)
            $pad = rand(2, 7);
            if ($encoding == 'R') {
                // Full random
                $encoding = rand(1, 3);
            } else {
                // Random only to entity
                $encoding = rand(1, 2);
            }
        }
        $asc = ord($char);

        switch ($encoding) {
        case 1: // Decimal entity
        case 'D':
            return sprintf("&#%0{$pad}d;", $asc);
            break;
        case 2: // Hexadecimal entity
        case 'H':
            return sprintf("&#x%0{$pad}X;", $asc);
            break;
        case 3: // URL-encoding
        case 'U':
            return sprintf("%%%02X", $asc);
            break;
        default:
            return $char;
        }
    }

    /**
     * Returns a shortened version of text string resolved by word boundaries.
     *
     * @static
     *
     * @access public
     *
     * @param string $str           Text to be shortened.
     * @param integer $limit        Number of words/chars to cut to.
     * @param integer $element      What string element type to count by.
     * @param string $appendString  Trailing string to be appended.
     *
     * @return string Correctly shortened text.
     * @todo move to plugin
     */
    public function summarise($str, $limit = 50, $element = SGL2_WORD,
        $appendString = ' ...')
    {
        switch ($element) {

        // strip by chars
        case SGL2_CHAR:
            if (extension_loaded('mbstring')) {
                $enc = mb_detect_encoding($str);
                $len = mb_strlen($str, $enc);
                $ret = $len > $limit
                    ? mb_substr($str, 0, $limit, $enc) . $appendString
                    : $str;
            } else {
                $len = strlen($str);
                $ret = $len > $limit
                    ? substr($str, 0, $limit) . $appendString
                    : $str;
            }
            break;

        // strip by words
        case SGL2_WORD:
            $aWords = explode(' ', $str);
            if (count($aWords) > $limit) {
                $ret = implode(' ', array_slice($aWords, 0, $limit)) . $appendString;
            } else {
                $ret = $str;
            }
            break;
        }

        return  $ret;
    }

    /**
     * Converts bytes to KB/MB/GB as appropriate.
     *
     * @param   int $bytes
     * @return  int B/KB/MB/GB
     */
     public static function formatBytes($size, $decimals = 1, $lang = '--')
    {
        $aSizeList = array(1073741824, 1048576, 1024, 0);
        // Should check if string is in an array, other languages may use octets
        if ($lang == 'FR') {
            $aSizeNameList = array('&nbsp;Go', '&nbsp;Mo', '&nbsp;Ko', '&nbsp;octets');
            // Note: should also use French decimal separator (coma)
        } else {
            $aSizeNameList = array('GB', 'MB', 'KB', 'B');
        }
        $i = 0;
        foreach ($aSizeList as $bytes) {
            if ($size >= $bytes) {
                if ($bytes == 0) {
                    // size 0 override
                    $bytes = 1;
                    $decimals = 0;
                }
                $formated = sprintf("%.{$decimals}f{$aSizeNameList[$i]}", $size / $bytes);
                break;
            }
            $i++;
        }
        return $formated;
    }

    public static function toValidVariableName($str)
    {
        //  remove illegal chars
        $search = '/[^a-zA-Z1-9_]/';
        $replace = '';
        $res = preg_replace($search, $replace, $str);
        //  ensure 1st letter is lc
        $firstLetter = strtolower($res[0]);
        $final = substr_replace($res, $firstLetter, 0, 1);
        return $final;
    }

    public static function toValidFileName($origName)
    {
        return SGL2_String::dirify($origName);
    }

    //  from http://kalsey.com/2004/07/dirify_in_php/
    public static function dirify($s)
    {
         $s = self::_convertHighAscii($s);     ## convert high-ASCII chars to 7bit.
         $s = strtolower($s);                       ## lower-case.
         $s = strip_tags($s);                       ## remove HTML tags.
         // Note that &nbsp (for example) is legal in HTML 4, ie. semi-colon is optional if it is followed
         // by a non-alphanumeric character (eg. space, tag...).
//         $s = preg_replace('!&[^;\s]+;!','',$s);    ## remove HTML entities.
         $s = preg_replace('!&#?[A-Za-z0-9]{1,7};?!', '', $s);    ## remove HTML entities.
         $s = preg_replace('![^\w\s-]!', '',$s);    ## remove non-word/space chars.
         $s = preg_replace('!\s+!', '_',$s);        ## change space chars to underscores.
         return $s;
    }

    protected function _convertHighAscii($s)
    {
        // Seems to be for Latin-1 (ISO-8859-1) and quite limited (no ae/oe, no y:/Y:, etc.)
         $aHighAscii = array(
           "!\xc0!" => 'A',    # A`
           "!\xe0!" => 'a',    # a`
           "!\xc1!" => 'A',    # A'
           "!\xe1!" => 'a',    # a'
           "!\xc2!" => 'A',    # A^
           "!\xe2!" => 'a',    # a^
           "!\xc4!" => 'A',    # A:
           "!\xe4!" => 'a',    # a:
           "!\xc3!" => 'A',    # A~
           "!\xe3!" => 'a',    # a~
           "!\xc8!" => 'E',    # E`
           "!\xe8!" => 'e',    # e`
           "!\xc9!" => 'E',    # E'
           "!\xe9!" => 'e',    # e'
           "!\xca!" => 'E',    # E^
           "!\xea!" => 'e',    # e^
           "!\xcb!" => 'E',    # E:
           "!\xeb!" => 'e',    # e:
           "!\xcc!" => 'I',    # I`
           "!\xec!" => 'i',    # i`
           "!\xcd!" => 'I',    # I'
           "!\xed!" => 'i',    # i'
           "!\xce!" => 'I',    # I^
           "!\xee!" => 'i',    # i^
           "!\xcf!" => 'I',    # I:
           "!\xef!" => 'i',    # i:
           "!\xd2!" => 'O',    # O`
           "!\xf2!" => 'o',    # o`
           "!\xd3!" => 'O',    # O'
           "!\xf3!" => 'o',    # o'
           "!\xd4!" => 'O',    # O^
           "!\xf4!" => 'o',    # o^
           "!\xd6!" => 'O',    # O:
           "!\xf6!" => 'o',    # o:
           "!\xd5!" => 'O',    # O~
           "!\xf5!" => 'o',    # o~
           "!\xd8!" => 'O',    # O/
           "!\xf8!" => 'o',    # o/
           "!\xd9!" => 'U',    # U`
           "!\xf9!" => 'u',    # u`
           "!\xda!" => 'U',    # U'
           "!\xfa!" => 'u',    # u'
           "!\xdb!" => 'U',    # U^
           "!\xfb!" => 'u',    # u^
           "!\xdc!" => 'U',    # U:
           "!\xfc!" => 'u',    # u:
           "!\xc7!" => 'C',    # ,C
           "!\xe7!" => 'c',    # ,c
           "!\xd1!" => 'N',    # N~
           "!\xf1!" => 'n',    # n~
           "!\xdf!" => 'ss'
         );
         $find = array_keys($aHighAscii);
         $replace = array_values($aHighAscii);
         $s = preg_replace($find, $replace, $s);
         return $s;
    }

    protected function _to7bit($text)
    {
        if (!function_exists('mb_convert_encoding')) {
            return $text;
        }
        $text = mb_convert_encoding($text,'HTML-ENTITIES',mb_detect_encoding($text));
        $text = preg_replace(
           array('/&szlig;/','/&(..)lig;/',
                 '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
           array('ss',"$1","$1".'e',"$1"),
           $text);
        return $text;
    }

    /**
     * Replaces accents in string.
     *
     * @static
     *
     * @todo make it work with cyrillic chars
     * @todo make it work with non utf-8 encoded strings
     *
     * @see SGL2_String::isCyrillic()
     *
     * @param string $str
     *
     * @return string
     */
    public static function replaceAccents($str)
    {
        if (!self::_isCyrillic($str)) {
            $str = self::_to7bit($str);
            $str = preg_replace('/[^A-Z^a-z^0-9()]+/',' ',$str);
        }
        return $str;
    }

    /**
     * Checks if strings has cyrillic chars.
     *
     * @static
     *
     * @param string $str
     *
     * @return boolean
     */
    protected function _isCyrillic($str)
    {
        $ret = false;
        if (function_exists('mb_convert_encoding') && !empty($str)) {
            // codes for Russian chars
            $aCodes = range(1040, 1103);
            // convert to entities
            $encoded = mb_convert_encoding($str, 'HTML-ENTITIES',
                mb_detect_encoding($str));
            // get codes of the string
            $aChars = explode(';', str_replace('&#', '', $encoded));
            array_pop($aChars);
            $aChars = array_unique($aChars);
            // see if cyrillic chars there
            $aNonCyrillicChars = array_diff($aChars, $aCodes);
            // if string is the same -> no cyrillic chars
            $ret = count($aNonCyrillicChars) != count($aChars);
        }
        return $ret;
    }

    /**
     * Removes chars that are illegal in ini files.
     *
     * @param string $string
     * @return string
     */
    public static function stripIniFileIllegalChars($string)
    {
        return preg_replace("/[\|\&\~\!\"\(\)]/i", "", $string);
    }

    /**
     * Converts strings representing constants to int values.
     *
     * Used for when constants are stored as strings in config.
     *
     * @param string $string
     * @return integer
     */
    public static function pseudoConstantToInt($string)
    {
        $ret = 0;
        if (is_int($string)) {
            $ret = $string;
        }
        if (is_numeric($string)) {
            $ret = (int)$string;
        }
        if (SGL2_Inflector::isConstant($string)) {
            $const = str_replace("'", '', $string);
            if (defined($const)) {
                $ret = constant($const);
            }
        }
        return $ret;
    }

    /**
     * Esacape single quote.
     *
     * @param   string $string
     * @return  string
     */
    public static function escapeSingleQuote($string)
    {
        $ret = str_replace('\\', '\\\\', $string);
        $ret = str_replace("'", '\\\'', $ret);
        return $ret;
    }

    /**
     * Escape single quotes in every key of given array.
     *
     * @param   array $array
     *
     * @return  array
     *
     * @static
     */
    public static function escapeSingleQuoteInArrayKeys($array)
    {
        $ret = array();
        foreach ($array as $key => $value) {
            $k = self::escapeSingleQuote($key);
            $ret[$k] = is_array($value)
                ? self::escapeSingleQuoteInArrayKeys($value)
                : $value;
        }
        return $ret;
    }
}
?>
