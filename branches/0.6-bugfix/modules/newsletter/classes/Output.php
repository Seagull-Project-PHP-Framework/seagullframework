<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Rares Benea                                           |
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
// | Author:   Benea Rares <rbenea@bluestardesign.ro>                          |
// +---------------------------------------------------------------------------+
// $Id: Output.php,v 1.4 2005/05/31 23:34:23 demian Exp $

class NewsletterOutput
{

    function generateCheckboxNewsList($hElements, $aChecked, $groupName)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!is_array($hElements)) {
            SGL::raiseError('incorrect args passed to ' . __FILE__ . ',' . __LINE__,
                SGL_ERROR_INVALIDARGS);
            return false;
        }
        if (!is_array($aChecked)) {
            $aChecked = array();
        }
        $html = '';
        foreach ($hElements as $k => $v) {
            $isChecked = (in_array($k, $aChecked)) ? ' checked' : '';
            $html .= '<input name="'.$groupName.'" type="checkbox" value="'.$k.'" '.$isChecked.'>'.
                     '<strong>'.$v['name'].'</strong> - '.$v['description'].
                     "<br />\n";
        }
        return $html;
    }

    function statusToString($statusID)
    {
        return SGL_String::translate('Status_'.$statusID);
    }

    function statusOpts($selected)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $aSatusOpts = $conf['statusOpts'];
        return SGL_Output::generateSelect($aSatusOpts, $selected);
    }

    function actionOpts($action)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $aActionOpts = $conf['ActionOpts'];
        $selectedAction = $action == ''  ? 'empty' : $action;
        return SGL_Output::generateSelect($aActionOpts, $selectedAction);
    }
}
?>
