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
// | Array.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Provides array manipulation methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_Array
{
    /**
     * Strips 'empty' elements from supplied array.
     *
     * 'Empty' can be a null, empty string, false or empty array.
     *
     * @param array $elem
     * @return array
     */
    public static function removeBlanks(array $elem)
    {
        if (is_array($elem)) {
            $clean = array_filter($elem);
        }
        return $clean;
    }

    /**
     * Returns an array with imploded keys.
     *
     * @param string $glue
     * @param array $hash
     * @param string $valwrap
     * @return string
     */
    public static function implodeWithKeys($glue, $hash, $valwrap='')
    {
        if (is_array($hash) && count($hash)) {
            foreach ($hash as $key => $value) {
                $aResult[] = $key.$glue.$valwrap.$value.$valwrap;
            }
            $ret = implode($glue, $aResult);
        } else {
            $ret = '';
        }
        return $ret;
    }

    /**
     * Merges two arrays and replace existing entrys.
     *
     * Merges two Array like the PHP Function array_merge_recursive.
     * The main difference is that existing keys will be replaced with new values,
     * not combined in a new sub array.
     *
     * Usage:
     *        $newArray = SGL2_Array::mergeReplace($array, $newValues);
     *
     * @param array $array First Array with 'replaceable' Values
     * @param array $newValues Array which will be merged into first one
     * @return array Resulting Array from replacing Process
     */
    public static function mergeReplace($array, $newValues)
    {
        foreach ($newValues as $key => $value) {
            if (is_array($value)) {
                if (!isset($array[$key])) {
                    $array[$key] = array();
                }
                $array[$key] = self::mergeReplace($array[$key], $value);
            } else {
                if (isset($array[$key]) && is_array($array[$key])) {
                    $array[$key][0] = $value;
                } else {
                    if (isset($array) && !is_array($array)) {
                        $temp = $array;
                        $array = array();
                        $array[0] = $temp;
                    }
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }
}
?>