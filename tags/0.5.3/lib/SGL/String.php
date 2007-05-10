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
// | Seagull 0.5                                                               |
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
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        
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

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        
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
?>