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
// | Output.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: Output.php,v 1.9 2005/01/27 12:33:45 demian Exp $

class DefaultOutput
{
    function replaceSlashes($str)
    {
        return str_replace('/', '^', $str);
    }

    function createConfigField($section, $name, $field)
    {
        if (is_array($field)) {
            $field = (object) $field;
        }
        $ret = '';
        switch ($field->type) {
            case 'bool':
                if ($field->value == 1) {
                    $rawChecked = 'checked="checked"';
                } else {
                    $rawChecked = '';
                }
                $ret .= "<input type='hidden' name='config[$section][$name][type]' value='bool' />";
                $ret .= "\n<input type='hidden' name='config[$section][$name][value]' value='0' />";
                $ret .= "\n<input type='checkbox' name='config[$section][$name][value]' value='1' $rawChecked />";
                break;
            case 'string':
            default:
                $ret .= "<input type='hidden' name='config[$section][$name][type]' value='string' />";
                switch (true) {
                case strlen($field->value) < 10 && is_numeric($field->value):
                    $ret .= "<input type='text' class='smallText' name='config[$section][$name][value]' value='$field->value' />";
                    break;

                case strlen($field->value) < 60 && $name != 'extraLanguages':
                    $ret .= "<input type='text' class='longText altFont' name='config[$section][$name][value]' value='$field->value' />";
                    break;

                default:
                    $ret .= "<textarea class='longText' name='config[$section][$name][value]'>";
                    $ret .= "$field->value";
                    $ret .= "</textarea>";
                }

                break;
            case 'list':
                $ret .= "<input type='hidden' name='config[$section][$name][type]' value='list' />";
                $ret .= "<input type='hidden' name='config[$section][$name][list]' value='$field->list' />";
                $ret .= "\n<select name='config[$section][$name][value]'>";
                $aOptions = explode(',', $field->list);
                foreach ($aOptions as $value) {
                    $rawSelected = '';
                    if ($value == $field->value) {
                        $rawSelected = ' selected="selected"';
                    }
                    $ret .= "<option value='$value'$rawSelected>$value</option>";
                }
                $ret .= "</select>";
                break;
        }
        return $ret;
    }

    function getArrayValue($array, $value)
    {
        return $array[$value];
    }

    function actionToRadioState($radioAction)
    {
        $req = &SGL_Request::singleton();
        $action = $req->get('action');
        return ($radioAction == $action) ? ' checked="checked"' : '';
    }

    function getTransKey($k)
    {
        return htmlspecialchars($k, ENT_QUOTES);
    }
}
?>