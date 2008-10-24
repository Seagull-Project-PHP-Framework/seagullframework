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
// | Inflector.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Performs transformations on resource names, ie, urls, classes, methods, variables.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_Inflector
{

    /**
     * Returns the full Manager name given the short name, ie, faq becomes FaqMgr.
     *
     * @param string $name
     * @return string
     */
    public static function getControllerClassName($name)
    {
        //  if controller suffix has been left out, append it
        if (strtolower(substr($name, -3)) != 'controller') {
            $name .= 'Controller';
        }
        return ucfirst($name);
    }

   /**
    * Converts "string with spaces" to "camelCase" string.
    *
    * @param   string $s
    * @return  string
    *
    * @author Julien Casanova <julien_casanova AT yahoo DOT fr>
    */
    public static function camelise($s)
    {
        $ret = '';
        $i = 0;

        $s = preg_replace('!\s+!', ' ', $s);
        $s = trim($s);
        $aString = explode(' ', $s);
        foreach ($aString as $value) {
            if ($i == 0) {
                $ret .= strtolower($value);
            } else {
                $ret .= ucfirst(strtolower($value));
            }
            $i++;
        }
        return $ret;
    }

    public static function getTitleFromCamelCase($camelCaseWord)
    {
        if (!self::isCamelCase($camelCaseWord)) {
            return $camelCaseWord;
        }
        $ret = '';
        for ($x = 0; $x < strlen($camelCaseWord); $x ++) {
            if (preg_match("/[A-Z]/", $camelCaseWord{$x})) {
                $ret .= ' ';
            }
            $ret .= $camelCaseWord{$x};
        }
        return ucfirst($ret);
    }

    public static function isCamelCase($str)
    {
        //  ensure no non-alpha chars
        if (preg_match("/[^a-z].*/i", $str)) {
            return false;
        }
        //  and at least 1 capital not including first letter
        for ($x = 1; $x < strlen($str)-1; $x ++) {
            if (preg_match("/[A-Z]/", $str{$x})) {
                return true;
            }
        }
        return false;
    }

    public static function isConstant($str)
    {
        if (empty($str)) {
            return false;
        }
        if (preg_match('/sessid/i', $str)) {
            return false;
        }
        $pattern = '@^[A-Z_\'][A-Z_0-9\']*$@';
        if (!preg_match($pattern, $str)) {
            return false;
        }
        return true;
    }

    /**
     * Returns a human-readable string from $lower_case_and_underscored_word,
     * by replacing underscores with a space, and by upper-casing the initial characters.
     *
     * @param string $lower_case_and_underscored_word String to be made more readable
     * @return string Human-readable string
     */
    public static function humanise($lowerCaseAndUnderscoredWord)
    {
        $replace = ucwords(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));
        return $replace;
    }

    /**
     *
     * Returns "Class_Name" as "Class/Name.php".
     *
     * @param string $str The class name.
     * @return string The class as a file name.
     * @author Thanks to Solar fw
     *
     */
    public static function classToFile($str)
    {
        return str_replace('_', '/', $str) . '.php';
    }
}
?>