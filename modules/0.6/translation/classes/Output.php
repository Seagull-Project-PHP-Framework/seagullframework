<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007, Demian Turner                                         |
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
// | TranslationOutput.php                                                     |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+

/**
 * Translation module output helper.
 *
 * @package seagull
 * @subpackage translation
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class TranslationOutput
{
    function replaceSlashes($str)
    {
        return str_replace('/', '^',$str);
    }

    function getArrayValue($array, $value, $value2 = null)
    {
        return isset($value2)
            ? $array[$value][$value2]
            : $array[$value];
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

    function getArrayValueQuoted($array, $value, $value2 = null)
    {
        $ret = TranslationOutput::getArrayValue($array, $value, $value2);
        return TranslationOutput::getTransKey($ret);
    }

    function lastModifiedStatus($moduleName, $langName)
    {
        $aMetaData = SGL_Translation2::getTranslationMetaData($moduleName, $langName);
        $ret = '';
        if (!empty($aMetaData)) {

            // get user name
            require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
            $_da  = UserDAO::singleton();
            $user = $_da->getUserById($aMetaData['__SGL_UPDATED_BY_ID']);
            $displayName = trim($user->first_name . ' ' . $user->last_name);
            if (empty($displayName)) {
                $displayName = $aMetaData['__SGL_UPDATED_BY'];
            }

            $aTrans['user'] = $displayName;
            $aTrans['date'] = SGL_Output::formatDatePretty($aMetaData['__SGL_LAST_UPDATED']);

            $ret = SGL_Output::translate('Last modified by %user on %date',
                'vprintf', $aTrans);
        }
        return $ret;
    }

    function isSglCategory($k)
    {
        return strpos($k, '__SGL_CATEGORY_') !== false;
    }

    function isSglComment($k)
    {
        return strpos($k, '__SGL_COMMENT_') !== false;
    }

    function renderEditField($k, $aTargetLang)
    {
        $value = TranslationOutput::getArrayValueQuoted($aTargetLang,$k);
        if (strlen($value) < 65) {
            $html = '
                <input type="text" name="translation[' . TranslationOutput::getTransKey($k) . ']"
                       value="' . $value . '" size="50" />
            ';
        } else {
            $html = '
                <textarea cols="56" name="translation[' . TranslationOutput::getTransKey($k) . ']">' . $value . '</textarea>';
        }
        return $html;
    }

    function showLanguageStatus($aModules, $language)
    {
        $totalSizeMaster = 0;
        $totalSizeSlave  = 0;
        $fallLang        = SGL_Translation2::getFallbackLangID();
        $fallLang        = SGL_Translation2::transformLangID($fallLang, SGL_LANG_ID_SGL);

        $ret = '';
        foreach ($aModules as $moduleName => $foo) {

            // get sizes
            $sizeSlave = SGL_Translation2::getTranslationStorageSize(
                $moduleName, $language);
            $sizeMaster = SGL_Translation2::getTranslationStorageSize(
                $moduleName, $fallLang);

            // completed ration
            $ratio = $sizeMaster
                ? round($sizeSlave / $sizeMaster, 2) * 100
                : $sizeMaster;

            // calculate total size
            $totalSizeSlave  += $sizeSlave;
            $totalSizeMaster += $sizeMaster;

            $ret .= '<td class="left">' . $ratio . '%</td>';
        }
        // overall ratio
        $totalRatio = $totalSizeMaster
            ? round($totalSizeSlave / $totalSizeMaster, 2) * 100
            : $totalSizeMaster;

        // total
        $ret .= '<td class="left"><strong>' . $totalRatio . '%</strong></td>';

        return $ret;
    }
}
?>