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
// | String.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: String.php,v 1.14 2005/06/01 11:11:23 demian Exp $

define('SGL_CENSOR_DISABLE',        0);
define('SGL_CENSOR_EXACT_MATCH',    1);
define('SGL_CENSOR_WORD_BEGINNING', 2);
define('SGL_CENSOR_WORD_FRAGMENT',  3);

/**
 * Various string helper methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.14 $
 */
class SGL_String
{
    /**
     * Censors profanity.
     *
     * @author  Tony Bibbs  <tony@tonybibbs.com>
     * @access  public
     * @static
     * @param   string  $text           string to check
     * @return  string  $editedText     edited text
     */
    function censor($text)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $editedText = $text;
        $censorMode = SGL_String::pseudoConstantToInt($conf['censor']['mode']);
        if ($censorMode != SGL_CENSOR_DISABLE) {
            $aBadWords = explode(',', $conf['censor']['badWords']);
            if (is_array($aBadWords)) {
                $replacement = $conf['censor']['replaceString'];

                switch ($censorMode) {
                case SGL_CENSOR_EXACT_MATCH:
                    $regExPrefix = '(\s*)';
                    $regExSuffix = '(\W*)';
                    break;
                case SGL_CENSOR_WORD_BEGINNING:
                    $regExPrefix = '(\s*)';
                    $regExSuffix = '(\w*)';
                    break;
                case SGL_CENSOR_WORD_FRAGMENT:
                    $regExPrefix   = '(\w*)';
                    $regExSuffix   = '(\w*)';
                    break;
                }
                for ($i = 0; $i < count($aBadWords); $i++ ) {
                    $editedText = eregi_replace( $regExPrefix .
                        $aBadWords[$i] .
                        $regExSuffix, "\\1$replacement\\2", $editedText);
                }
            }
        }
        return $editedText;
    }

    /**
     * Defines the <CR><LF> value depending on the user OS.
     *
     * @return  string   the <CR><LF> value to use
     * @access  public
     */
    function getCrlf()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        // TODO: deal with Mac OS Classic i.e. < OS X when line ending is CR
        $crlf = (substr(PHP_OS, 0, 3) == 'WIN')
            ? "\r\n"
            : "\n";
        return $crlf;
    }

    function trimWhitespace($var)
    {
        if (!isset($var)) {
            return false;
        }
        if (is_array($var)) {
            $newArray = array();
            foreach ($var as $key => $value) {
                $newArray[$key] = SGL_String::trimWhitespace($value);
            }
            return $newArray;
        } else {
            return trim($var);
        }
    }

    /**
     * If magic_quotes_gpc is in use, run stripslashes() on $var.
     *
     * @access  public
     * @param   string $var  The string to un-quote, if necessary.
     * @return  string       $var, minus any magic quotes.
     * @author  Chuck Hagenbuch <chuck@horde.org>
     */
    function dispelMagicQuotes(&$var)
    {
        static $magicQuotes;
        if (!isset($magicQuotes)) {
            $magicQuotes = get_magic_quotes_gpc();
        }
        if ($magicQuotes) {
            if (!is_array($var)) {
                $var = stripslashes($var);
            } else {
                array_walk($var, array('SGL_String', 'dispelMagicQuotes'));
            }
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
    function clean($var)
    {
        if (!isset($var)) {
            return false;
        }
        $var = SGL_String::trimWhitespace($var);
        if (is_array($var)) {
            $newArray = array();
            foreach ($var as $key => $value) {
                $newArray[$key] = SGL_String::clean($value);
            }
            return $newArray;
        } else {
            return strip_tags($var);
        }
    }

    function removeJs($var)
    {
        if (!isset($var)) {
            return false;
        }
        $var = SGL_String::trimWhitespace($var);
        if (is_array($var)) {
            $newArray = array();
            foreach ($var as $key => $value) {
                $newArray[$key] = SGL_String::removeJs($value);
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
     * @access public
     * @param string $html the text to clean
     * @param bool $logErrors
     * @return string cleaned text
     *
     * @author  Andy Crain <andy@newslogic.com>
     * @since   PHP 4.3
     */
    function tidy($html, $logErrors = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        if (       !$conf['site']['tidyhtml']
                || !extension_loaded('tidy')) {
            return $html;
        }
        //  PHP5 version
        if (SGL::isPhp5()) {
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
        //  PHP4 version
        } else {
            //  so we don't get doctype, html, head, body etc. tags added; default is false
            tidy_setopt('show-body-only', true);
            //  no wrapping of lines
            tidy_setopt('wrap', 0);
            tidy_setopt('indent', 1);
            tidy_setopt('indent-spaces', 1);
            tidy_parse_string($html);
            if ((tidy_warning_count() || tidy_error_count()) && $logErrors) {
                SGL::logMessage('PHP Tidy error or warning: ' . tidy_get_error_buffer(), PEAR_LOG_NOTICE);
            }
            $ret = tidy_get_output();
        }
        return $ret;
    }

    /**
     * Looks up key in current lang file (determined by preference)
     * and returns target value.
     *
     * @param string $key       Translation term
     * @param string $filter    Optional filter fn
     * @param array  $aParams
     * @param string $langCode  Pass the lang code for the language you wish to translate
     * @return string
     *
     * @todo better integrate $lang handling
     */
    function translate($key, $filter = false, $aParams = array(), $langCode = null)
    {
        if (!is_null($langCode)) {
            return SGL_String::translate2($key, $filter, $aParams, $langCode);
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $trans = &$GLOBALS['_SGL']['TRANSLATION'];
        if (isset($trans[$key])) {
            if (!is_array($trans[$key])
                    && strstr($trans[$key], '||')
                    && $conf['translation']['container'] == 'db') {
                preg_match_all('/([^|]*)\|([^|]*)(?=\|\|)/', $trans[$key], $aPieces);
                $ret = array_combine($aPieces[1], $aPieces[2]);
            } else {
                $ret = $trans[$key];
            }
            if (!is_array($trans[$key]) && $filter && function_exists($filter)) {
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
            //  add translation
            if (!empty($key)
                    && isset($conf['translation']['addMissingTrans'])
                    && $conf['translation']['addMissingTrans']
                    && isset($conf['translation']['container'])
                    && $conf['translation']['container'] == 'db') {

                //  get a reference to the request object
                $req = & SGL_Request::singleton();
                $moduleName = $req->get('moduleName');

                //  fetch fallback lang
                $fallbackLang = $conf['translation']['fallbackLang'];

                $trans = SGL_Translation3::singleton('admin');
                $result = $trans->add($key, $moduleName, array($fallbackLang => $key));
            }
            SGL::logMessage('Key \''.$key.'\' Not found', PEAR_LOG_NOTICE);
            $key = (isset($conf['debug']['showUntranslated']) && $conf['debug']['showUntranslated'])
                ? '>' . $key . '<'
                : $key;
            return $key;
        }
    }


    /**
     * Looks up key in current lang dictionary (SGL_Translation3) or specific language
     * and returns target value.
     *
     * @param string $key       Translation term
     * @param string $filter    Optional filter fn
     * @param array  $aParams   Optional params
     * @param string $langCode  Optional langCode to force translation in this language
     * @return string
     *
     */
    function translate2($key, $filter = false, $aParams = array(), $langCode)
    {
        require_once 'SGL/Translation3.php';
        $trans = SGL_Translation3::singleton('array');
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
            SGL::logMessage('Key \''.$key.'\' Not found', PEAR_LOG_NOTICE);
            $key = SGL_Config::get('debug.showUntranslated')
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
     * Primarily used for obfuscating email addresses to prevent spam
     * harvesting. Since it is URL-encoded, this can be used only in the href
     * part of a <a> tag (mailto: scheme).
     *
     * @param string $str String to encode
     * @return string $encoded Encoded string
     */
    function obfuscate($str)
    {
        $encoded = bin2hex($str);
        $encoded = chunk_split($encoded, 2, '%');
        $encoded = '%' . substr($encoded, 0, strlen($encoded) - 1);
        return $encoded;
    }

    /**
     * Primarily used for obfuscating email addresses to prevent spam
     * harvesting.
     *
     * @param string $str String to encode
     * @param bool $bForLink true if used in the href part of a <a> tag, false to be used in HTML
     * @return string $encoded Encoded string
     *
     * @author  Philippe Lhoste <PhiLho(a)GMX.net>
     */
    function obfuscate2($str, $bForLink = true)
    {
        if ($bForLink) {
            $e = "'R'";
        } else {
            $e = "'E'";
        }
        $encoded = preg_replace_callback(
                '|([-?=@._emailto:])|', // Mostly arbitrary, to mix encoded and unencoded chars...
                create_function(
                        '$matches',
                        "return char2entity(\$matches[0], $e);"
                ),
                $str
        );
        return $encoded;
     }

    /**
     * Lightweight function to obfuscate mail addresses: substitutes @ and dots
     * in the domain part with a string.
     *
     * @access  public
     * @param   string $address
     * @return  string
     *
     * @author Riccardo Magliocchetti <riccardo@datahost.it>
     */
    function obfuscate3($address)
    {
        $at = '(at)';
        $dot = '(dot)';

        $len = strlen($address);
        $pos = strpos($address, '@');
        $name = substr($address, 0, $pos-$len);

        /* Trick to avoid the regex */
        $domain = $at . substr($address, $pos+1);
        $domain = preg_replace('/\./', $dot, $domain);

        return $name . $domain;
    }

    /**
     * Munge email addresses swapping plain text with html char entities as
     * explained here: http://perso.crans.org/~raffo/aem/index.php.
     *
     * @access  public
     * @param   string $address
     * @return  string
     *
     * @author Riccardo Magliocchetti <riccardo@datahost.it>
     */
    function mungeMailAddress($address)
    {
        $len = strlen($address);
        $aOld = str_split($address);
        $aMunged = array();

        for ($i = 0; $i < $len; $i++) {
            $aMunged[$i] =  "&#" . ord($aOld[$i]) . ";";
        }

        $munged = implode('' ,$aMunged);

        return $munged;
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
     */
    function summarise($str, $limit = 50, $element = SGL_WORD,
        $appendString = ' ...')
    {
        switch ($element) {

        // strip by chars
        case SGL_CHAR:
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
        case SGL_WORD:
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
     * Returns a set number of lines of a block of html, for summarising articles.
     *
     * @param string $str
     * @param integer $lines
     * @param string $appendString
     * @return string
     * @todo    needs to handle orphan <b> and <strong> tags
     */
    function summariseHtml($str, $lines=10)
    {
        $aLines = explode("\n", $str);
        $aSegment = array_slice($aLines, 0, $lines);

        //  close tags like <ul> so page layout doesn't break
        $unclosedListTags = 0;
        $aMatches = array();
        foreach ($aSegment as $line) {
            if (preg_match("/<[u|o]l/i", $line, $matches)) {
                $aMatches[] = $matches;
                $unclosedListTags++;
            }
            if (preg_match("/<\/[u|o]l>/i", $line)) {
                $unclosedListTags--;
            }
        }
        //  reinstate close tags
        for ($x=0; $x < $unclosedListTags; $x++) {
            array_push($aSegment, '</ul>');
        }
        $ret = implode("\n", $aSegment);
        $ret = SGL_String::tidy($ret);
        return $ret;
    }

    /**
     * Converts bytes to KB/MB/GB as appropriate.
     *
     * @access  public
     * @param   int $bytes
     * @return  int B/KB/MB/GB
     */
     function formatBytes($size, $decimals = 1, $lang = '--')
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

    function toValidVariableName($str)
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

    function toValidFileName($origName)
    {
        return SGL_String::dirify($origName);
    }

    //  from http://kalsey.com/2004/07/dirify_in_php/
    function dirify($s)
    {
         $s = SGL_String::convertHighAscii($s);     ## convert high-ASCII chars to 7bit.
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

    function convertHighAscii($s)
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

    function to7bit($text)
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
     * @see SGL_String::isCyrillic()
     *
     * @param string $str
     *
     * @return string
     */
    function replaceAccents($str)
    {
        if (!SGL_String::isCyrillic($str)) {
            $str = SGL_String::to7bit($str);
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
    function isCyrillic($str)
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
    function stripIniFileIllegalChars($string)
    {
        return preg_replace("/[\|\&\~\!\"\(\)]/i", "", $string);
    }

    /**
     * Converts strings representing constants to int values.
     *
     * Used for when constants are stored as strings in config.
     *
     * @static
     * @param string $string
     * @return integer
     */
    function pseudoConstantToInt($string)
    {
        $ret = 0;
        if (is_int($string)) {
            $ret = $string;
        }
        if (is_numeric($string)) {
            $ret = (int)$string;
        }
        if (SGL_Inflector::isConstant($string)) {
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
     *
     * @return  string
     *
     * @static
     */
    function escapeSingleQuote($string)
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

if (!function_exists('array_combine')) {
    function array_combine($a, $b)
    {
        $c = array();
        if (is_array($a) && is_array($b))
            while (list(, $va) = each($a))
                if (list(, $vb) = each($b)) {
                    $c[$va] = $vb;
                } else {
                    break 1;
                }
        return $c;
    }
}
?>
