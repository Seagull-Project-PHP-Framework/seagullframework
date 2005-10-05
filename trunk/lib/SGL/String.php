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
 * Various string related methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.14 $
 * @since   PHP 4.1
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
        $conf = & $GLOBALS['_SGL']['CONF'];
        $editedText = $text;
        if ($conf['censor']['mode'] != SGL_CENSOR_DISABLE) {
            $aBadWords = explode(',', $conf['censor']['badWords']);
            if (is_array($aBadWords)) {
                $replacement = $conf['censor']['replaceString'];

                switch ($conf['censor']['mode']) {
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
        return ($editedText);
    }

    /**
     * Defines the <CR><LF> value depending on the user OS.
     *
     * From the phpMyAdmin common library
     *
     * @return  string   the <CR><LF> value to use
     * @access  public
     * @author  Marc Delisle <DelislMa@CollegeSherbrooke.qc.ca>
     */
    function getCrlf()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $crlf = "\n";

        // Win case
        if (SGL_USR_OS == 'Win') {
            $crlf = "\r\n";
        }
        // Mac case
        else if (SGL_USR_OS == 'Mac') {
            $crlf = "\r";
        }
        // Others
        else {
            $crlf = "\n";
        }
        return $crlf;
    }

    function trimWhitespace($var)
    {
        if (!is_array($var)) {
            $clean = trim($var);
        } else {
            $clean = array_map(array('SGL_String', 'trimWhitespace'), $var);
        }
        return $clean;
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
        if (isset($var)) {
            if (!is_array($var)) {
                $clean = strip_tags($var);
            } else {
                $clean = array_map(array('SGL_String', 'clean'), $var);
            }
        }
        return SGL_String::trimWhitespace($clean);
    }

    function removeJs($html)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $search = "/<script[^>]*?>.*?<\/script\s*>/i";
        $replace = '';
        $html = preg_replace($search, $replace, $html);
        return SGL_String::trimWhitespace($html);
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
        $conf = & $GLOBALS['_SGL']['CONF'];
        if (       !$conf['site']['tidyhtml']
                || !function_exists('tidy_parse_string')
                || SGL::isPhp5()) { // tidy 2 in PHP5 has different API
            return $html;
        }
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
        return tidy_get_output();
    }

    /**
     * Looks up key in current lang file (determined by preference)
     * and returns target value.
     *
     * @param string $key       Translation term
     * @param string $filter    Optional filter fn
     * @return string
     */
    function translate($key, $filter = false)
    {
        $trans = &$GLOBALS['_SGL']['TRANSLATION'];
        if (isset($trans[$key])) {
            $ret = $trans[$key];
            if ($filter && function_exists($filter)) {
                $ret = $filter($ret);
            }
            return $ret;
        } else {
            SGL::logMessage('Key \''.$key.'\' Not found', PEAR_LOG_NOTICE);
            return '>' . $key . '<';
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
                '|([-?=@._emailto:])|',	// Mostly arbitrary, to mix encoded and unencoded chars...
                create_function(
                        '$matches',
                        "return char2entity(\$matches[0], $e);"
                ),
                $str
        );
        return $encoded;
     }

    /**
     * Returns a shortened version of text string.
     *
     * @access  public
     * @param   string  $str    Text to be shortened
     * @param   integer $limit  Number of characters to cut to
     * @param   string  $appendString  Trailing string to be appended
     * @return  string  $processedString    Correctly shortened text
     * @author  Lukas Feiler <lukas.feiler@endlos.at>
     */
    function summarise($str, $limit=50, $appendString=' ...')
    {
         if (strlen($str) > $limit) {
            $str = substr($str, 0, $limit) . $appendString;
         }
         return $str;
    }

    /**
     * Returns a set number of lines of a block of html, for summarising articles.
     *
     * @param   string $str
     * @param   integer $lines
     * @param   string $appendString
     * @return  string
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
        return implode("\n", $aSegment);
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
         $s = preg_replace('![^\w\s]!', '', $s);      ## remove non-word/space chars.
         $s = preg_replace('!\s+!', '_', $s);         ## change space chars to underscores.
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
           "!\xc4!" => 'A',   # A:
           "!\xe4!" => 'a',   # a:
           "!\xc3!" => 'A',    # A~
           "!\xe3!" => 'a',    # a~
           "!\xc8!" => 'E',    # E`
           "!\xe8!" => 'e',    # e`
           "!\xc9!" => 'E',    # E'
           "!\xe9!" => 'e',    # e'
           "!\xca!" => 'E',    # E^
           "!\xea!" => 'e',    # e^
           "!\xcb!" => 'E',   # E:
           "!\xeb!" => 'e',   # e:
           "!\xcc!" => 'I',    # I`
           "!\xec!" => 'i',    # i`
           "!\xcd!" => 'I',    # I'
           "!\xed!" => 'i',    # i'
           "!\xce!" => 'I',    # I^
           "!\xee!" => 'i',    # i^
           "!\xcf!" => 'I',   # I:
           "!\xef!" => 'i',   # i:
           "!\xd2!" => 'O',    # O`
           "!\xf2!" => 'o',    # o`
           "!\xd3!" => 'O',    # O'
           "!\xf3!" => 'o',    # o'
           "!\xd4!" => 'O',    # O^
           "!\xf4!" => 'o',    # o^
           "!\xd6!" => 'O',   # O:
           "!\xf6!" => 'o',   # o:
           "!\xd5!" => 'O',    # O~
           "!\xf5!" => 'o',    # o~
           "!\xd8!" => 'O',   # O/
           "!\xf8!" => 'o',   # o/
           "!\xd9!" => 'U',    # U`
           "!\xf9!" => 'u',    # u`
           "!\xda!" => 'U',    # U'
           "!\xfa!" => 'u',    # u'
           "!\xdb!" => 'U',    # U^
           "!\xfb!" => 'u',    # u^
           "!\xdc!" => 'U',   # U:
           "!\xfc!" => 'u',   # u:
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
}

/**
 * Various date related methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.14 $
 * @since   PHP 4.1
 */
class SGL_Date
{
    /**
     * Converts date array into MySQL datetime format.
     *
     * @access  public
     * @param   array   $aDate
     * @return  string  MySQL datetime format
     * @see     publisher::ArticleMgr::process/edit
     */
    function arrayToString($aDate)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($aDate)) {
            $month  = $aDate['month'];
            $day    = $aDate['day'];
            $year   = $aDate['year'];
            $hour   = (array_key_exists('hour',$aDate))? $aDate['hour'] : '00';
            $minute = (array_key_exists('minute',$aDate))? $aDate['minute'] : '00';
            $second = (array_key_exists('second',$aDate))? $aDate['second'] : '00';
            return $year . '-' . $month . '-' . $day .' ' . $hour . ':' . $minute . ':' . $second;
        }
    }

    /**
     * Converts date into date array.
     *
     * @access  public
     * @param   string  $sDate date (may be in the ISO, TIMESTAMP or UNIXTIME format) format
     * @return  array   $aDate
     * @see     publisher::ArticleMgr::process/edit
     */
    function stringToArray($sDate)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_scalar($sDate)) {
            include_once 'Date.php';
            $date = & new Date($sDate);
            $aDate =      array('day'    => $date->getDay(),
                                'month'  => $date->getMonth(),
                                'year'   => $date->getYear(),
                                'hour'   => $date->getHour(),
                                'minute' => $date->getMinute(),
                                'second' => $date->getSecond());
            return $aDate;
        }
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into "Mar 31, 2003 18:29".
     *
     * @access  public
     * @param   string  $date  Date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $formatted  user-friendly format (european)
     */
    function formatPretty($date)
    {
        if (is_string($date)) {
            require_once 'Date.php';
            $date = & new Date($date);
            if ($_SESSION['aPrefs']['dateFormat'] == 'FR') {
                $output = $date->format('%d %B %Y, %H:%M');
            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'BR') {
				// Brazilian date format
                $output = $date->format('%d de %B de %Y %H:%M');
            } else {
                //  else UK and US
                $output = $date->format('%B %d, %Y %H:%M');
            }
            return $output;
        } else {
            SGL::raiseError('no input date passed to SGL_Date::formatPretty incorrect type',
                SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Converts date (may be in the ISO, TIMESTAMP or UNIXTIME format) into locale dependent form.
     *
     * @access  public
     * @param   string  $input  date (may be in the ISO, TIMESTAMP or UNIXTIME format) value
     * @return  string  $output user-friendly format (locale dependent)
     */
    function format($date)
    {
        if (is_string($date)) {
            include_once 'Date.php';
            $date = & new Date($date);
			// Neither elegant nor efficient way of doing that
			// (what if we have 30 formats/locales?).
			// We should move that to a language/locale dependent file.
            if ($_SESSION['aPrefs']['dateFormat'] == 'UK') {
                $output = $date->format('%d.%m.%Y');
            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'BR'
                     || $_SESSION['aPrefs']['dateFormat'] == 'FR') {
				// Brazilian/French date format
                $output = $date->format('%d/%m/%Y');
            } elseif ($_SESSION['aPrefs']['dateFormat'] == 'US') {
                $output = $date->format('%m.%d.%Y');
            } else {
                //  else display ISO (international, unambiguous) format, YYYY-MM-DD
                $output = $date->format('%Y-%m-%d');
            }
            return $output;
        } else {
            SGL::raiseError('no input date passed to SGL_Date::format incorrect type',
                SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Generates a select of month values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $month_options  select month options
     * @see     showDateSelector()
     */
    function getMonthFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aMonths = SGL_String::translate('aMonths');
        $monthOptions = '';
        if (empty($selected)) {
            $selected = date('m',time());
        }
        for ($i = 1; $i <= 12; $i++) {
            if ($i < 10) {
                $mval = '0' . $i;
            } else {
                $mval = $i;
            }
            $monthOptions .= "\n<option value=\"" . $mval . '" ';
            if ($i == $selected) {
                $monthOptions .= 'selected="SELECTED"';
            }
            $monthOptions .= '>' . $aMonths[$mval + 12] . '</option>';
        }
        return $monthOptions;
    }

    /**
     * Generates a select of day values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $day_options    select day options
     * @see     showDateSelector()
     */
    function getDayFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $day_options = '';
        for ($i = 1; $i <= 31; $i++) {
            if ($i < 10) {
                $dval = '0' . $i;
            } else {
                $dval = $i;
            }
            $day_options .= "\n<option value=\"" . $dval . '" ';
            if ($i == $selected) {
                $day_options .= 'selected="SELECTED"';
            }
            $day_options .= '>' . $dval . '</option>';
        }
        return $day_options;
    }

    /**
     * Generates a select of year values.
     *
     * @access  public
     * @param   string  $selected
     * @param   boolean $asc
     * @param   int     $number         number of years to show
     * @return  string  $year_options   select year options
     * @see     showDateSelector()
     */
    function getYearFormOptions($selected = '', $asc = true, $number = 5)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $year_options = '';
        $cur_year = date('Y',time());
        $start_year = $cur_year;
        if (!empty($selected)) {
            if ($selected < $cur_year) {
                $start_year = $selected;
            }
        }
        if ($asc) {
             for ($i = $start_year; $i <= $cur_year+$number; $i++) {
                 $year_options .= "\n<option value=\"" . $i . '" ';
                 if ($i == $selected) {
                     $year_options .= 'selected="selected"';
                 }
                 $year_options .= '>' . $i . '</option>';
             }
        } else {
             for ($i = $start_year+1; $i >= $cur_year-($number-1); $i--) {
                 $year_options .= "\n<option value=\"" . $i . '" ';
                 if ($i == $selected) {
                     $year_options .= 'selected="selected"';
                 }
                 $year_options .= '>' . $i . '</option>';
             }
        }
        return $year_options;
    }

    /**
     * Generates a select of hour values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $hour_options   select hour options
     * @see     showDateSelector()
     */
    function getHourFormOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $hour_options = '';
        for ($i = 0; $i <= 23; $i++) {
            $hval = sprintf("%02d",  $i);
            $hour_options .= "\n<option value=\"" . $hval . '" ';
            if ($selected == $i) {
                $hour_options .= 'selected="selected"';
            }
            $hour_options .= '>' . $hval . '</option>';
        }
        return $hour_options;
    }

    /**
     * Generates a select of minute/second values.
     *
     * @access  public
     * @param   string  $selected
     * @return  string  $minute_options select minute/second options
     * @see     showDateSelector()
     */
    function getMinSecOptions($selected = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $minute_options = '';
        for ($i = 0; $i <= 59; $i++) {
            if ($i < 10) {
                $mval = '0' . $i;
            } else {
                $mval = $i;
            }
            $minute_options .= "\n<option value=\"" . $mval . '" ';
            if ($selected == $i) {
                $minute_options .= 'selected="SELECTED"';
            }
            $minute_options .= '>' . $mval . '</option>';
        }
        return $minute_options;
    }

    /**
     * Generates date/time selector widget.
     *
     * usage:
     * $timestamp=mktime();
     * $day = date('d', $timestamp);
     * $month = date('m', $timestamp);
     * $year = date('Y', $timestamp);
     * $hour = date('H', $timestamp);
     * $minute = date('i', $timestamp);
     * $second = date('s', $timestamp);
     *
     * $aDate = array(  'day' => $day,
     *                  'month' => $month,
     *                  'year' => $year,
     *                  'hour' => $hour,
     *                  'minute' => $minute,
     *                  'second' => $second);
     * print showDateSelector($aDate, 'myForm', false);
     *
     * @access  public
     * @param   array   $aDate
     * @param   string  $sFormName  name of form
     * @param   boolean $bShowTime  toggle to display HH:MM:SS
     * @param   bool    $asc
     * @param   int     $years      number of years to show
     * @return  string  $html       html for widget
*/
    function showDateSelector($aDate, $sFormName, $bShowTime = true, $asc = true, $years = 5)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $html = '';
        $html .= "\n<select name='" . $sFormName . "[month]'>" . SGL_Date::getMonthFormOptions($aDate['month']) . '</select> / ';
        $html .= "\n<select name='" . $sFormName . "[day]'>" . SGL_Date::getDayFormOptions($aDate['day']) . '</select> / ';
        $html .= "\n<select name='" . $sFormName . "[year]'>" . SGL_Date::getYearFormOptions($aDate['year'], $asc, $years) . '</select>';
        if ($bShowTime) {
            $html .= '&nbsp;&nbsp; ';
            $html .= SGL_String::translate('at time');
            $html .= ' &nbsp;&nbsp;';
            $html .= "\n<select name='" . $sFormName . "[hour]'>" . SGL_Date::getHourFormOptions($aDate['hour']) . '</select> : ';
            $html .= "\n<select name='" . $sFormName . "[minute]'>" . SGL_Date::getMinSecOptions($aDate['minute']) . '</select> : ';
            $html .= "\n<select name='" . $sFormName . "[second]'>" . SGL_Date::getMinSecOptions($aDate['second']) . '</select>';
        }
        return $html;
    }
}
?>
