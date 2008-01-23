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
// $Id: Output.php,v 1.1 2005/01/29 10:17:24 demian Exp $

class NavigationOutput 
{
     /**
     * Generates collection of radio buttons.
     *
     * @access  public
     * @param   array   $array          hash of radio values
     * @return  string  $radioString    list of radio objects
     * @see     PermissionsMgr()
     */
    function generatePermsRadioList($array, $id = 'id')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $radioString = '';
        foreach ($array as $key => $valueObj) {
            $yes    = ($valueObj->isAllowed) ? ' checked' : '';
            $no     = (!$valueObj->isAllowed) ? ' checked' : '';
            $radioString .= "<tr class=" . SGL_Output::switchRowClass() . "><td align='center'>\n";
            $radioString .= "<input class='noBorder' type='radio' id='".$valueObj->$id."_yes' name='category[perms][".$valueObj->$id."]' value='1' $yes><label for=''>" . SGL_Output::translate('Yes') . "</label>\n";
            $radioString .= "<input class='noBorder' type='radio' id='".$valueObj->$id."_no' name='category[perms][".$valueObj->$id."]' value='0' $no><label for=''>" . SGL_Output::translate('No') . "</label>\n";
            $radioString .= "</td><td>$valueObj->name</td>";
            $radioString .= "</tr>\n";
        }
        return $radioString;
    }

     /**
     * Generates collection of radio buttons.
     *
     * @access  public
     * @param   array   $array          hash of radio values
     * @return  string  $radioString    list of radio objects
     * @see     PermissionsMgr()
     */
    function generatePermsRadioList1($array, $id = 'id')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $radioString = '';
        foreach ($array as $key => $valueObj) {
            $yes    = ($valueObj->isAllowed) ? ' checked="checked"' : '';
            $no     = (!$valueObj->isAllowed) ? ' checked="checked"' : '';
            $radioString .= "<p>\n\t\t";
            $radioString .= "<label>$valueObj->name</label>\n\t\t";
            $radioString .= "<input type='radio' id='".$valueObj->$id."_yes' name='category[perms][".$valueObj->$id."]' value='1' $yes>" . SGL_Output::translate('Yes') . "\n\t\t";
            $radioString .= "<input type='radio' id='".$valueObj->$id."_no' name='category[perms][".$valueObj->$id."]' value='0' $no>" . SGL_Output::translate('No') . "\n\t\t";
            $radioString .= "</p>\n";
        }
        return $radioString;
    }
}
?>