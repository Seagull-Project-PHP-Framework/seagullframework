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
// | TranslationOutput.php                                                     |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// |         Dmitri Lakachauskis <lakiboy83@gmail.com>                         |
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
    /**
     * Get array's value. Supports max 2dim arrays for now.
     *
     * @todo should be generic and moved to SGL_Output
     *
     * @access public
     *
     * @param array $array   target array
     * @param mixed $value   first key
     * @param mixed $value2  second key
     *
     * @return string
     */
    function getArrayValue($array, $value, $value2 = null)
    {
        return isset($value2)
            ? $array[$value][$value2]
            : $array[$value];
    }

    /**
     * Quote translation key. Need this to keep xHTML code consistent
     * in case keys have preserved xHTML characters.
     *
     * @access public
     *
     * @param string $k
     *
     * @return string
     */
    function getTransKey($k)
    {
        return htmlspecialchars($k, ENT_QUOTES);
    }

    /**
     * Quote translation value for specified array.
     *
     * @todo should use generic function to get array value by certain keys.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $value2
     *
     * @return string
     */
    function getArrayValueQuoted($array, $value, $value2 = null)
    {
        $ret = TranslationOutput::getArrayValue($array, $value, $value2);
        return TranslationOutput::getTransKey($ret);
    }

    /**
     * Detect if current keyword is category's keyword.
     *
     * @access public
     *
     * @param mixed $k
     *
     * @return boolean
     */
    function isSglCategory($k)
    {
        return strpos($k, '__SGL_CATEGORY_') !== false;
    }

    /**
     * Detect if current keyword is comment's keyword.
     *
     * @access public
     *
     * @param mixed $k
     *
     * @return boolean
     */
    function isSglComment($k)
    {
        return strpos($k, '__SGL_COMMENT_') !== false;
    }

    /**
     * Detect if current row should be shown.
     *
     * @access public
     *
     * @param boolean $untranslated  if in "untranslated only" mode
     * @param array $aTargetLang     target translation
     * @param mixed $k               translation key
     * @param mixed $kk              translation subkey if any
     *
     * @return boolean
     */
    function showTranslationRow($untranslated, $aTargetLang, $k, $kk = null)
    {
        return TranslationOutput::isSglCategory($k)
                || TranslationOutput::isSglComment($k)
            // just check if we need to show current row
            ? !$untranslated
            // always show if not in "untranslated only" mode
            // or when no target translation
            : !$untranslated || !TranslationOutput::getArrayValue($aTargetLang, $k, $kk);
    }

    /**
     * Detect if current translation block (array) should be shown.
     *
     * @access public
     *
     * @param boolean $untranslated  if in "untranslated only" mode
     * @param array $aTargetLang     target translation
     * @param mixed $k               translation key
     *
     * @return boolean
     */
    function showTranslationGroup($untranslated, $aTargetLang, $k)
    {
        $showGroup = true;
        foreach ($aTargetLang[$k] as $kk => $tmp) {
            $showGroup = $showGroup
                && TranslationOutput::showTranslationRow($untranslated, $aTargetLang, $k, $kk);
        }
        return !$untranslated || $showGroup;
    }

    function lastModifiedStatus($moduleName, $langName)
    {
        $aMetaData = SGL_Translation2::getTranslationMetaData($moduleName, $langName);
        $ret = '';
        if (!empty($aMetaData)) {

            // get user name
            require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';
            $_da  = User2DAO::singleton();
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

    function showLanguageStatus($aModules, $language, $getWordsCount = true)
    {
        $totalSizeMaster     = 0;
        $totalSizeSlave      = 0;
        $totalSizeSlaveWords = '';
        $fallLang            = SGL_Translation2::getFallbackLangID();
        $fallLang            = SGL_Translation2::transformLangID($fallLang, SGL_LANG_ID_SGL);

        $ret = '';
        foreach ($aModules as $moduleName => $foo) {

            // get sizes
            $sizeSlave = SGL_Translation2::getTranslationStorageSize(
                $moduleName, $language, $getWordsCount);
            $sizeMaster = SGL_Translation2::getTranslationStorageSize(
                $moduleName, $fallLang, $getWordsCount);

            $sizeSlaveWords = '';
            if (is_array($sizeSlave)) {
                $sizeSlaveWords = $sizeSlave['words'];
                $sizeSlave      = $sizeSlave['strings'];
                $sizeMaster     = $sizeMaster['strings'];
            }
            if (is_numeric($sizeSlaveWords)) {
                $totalSizeSlaveWords += $sizeSlaveWords;
                $sizeSlaveWords = ' [' . $sizeSlaveWords . ']';
            }

            // completed ration
            $ratio = $sizeMaster
                ? round($sizeSlave / $sizeMaster, 2) * 100
                : $sizeMaster;

            // calculate total size
            $totalSizeSlave  += $sizeSlave;
            $totalSizeMaster += $sizeMaster;

            $ret .= '<td class="left">' . $ratio . '%' . $sizeSlaveWords . '</td>';
        }

        // overall ratio
        $totalRatio = $totalSizeMaster
            ? round($totalSizeSlave / $totalSizeMaster, 2) * 100
            : $totalSizeMaster;

        // overall words
        $totalWords = $totalSizeSlaveWords
            ? ' [' . $totalSizeSlaveWords . ']'
            : $totalSizeSlaveWords;

        // total
        $ret .= '<td class="left"><strong>' . $totalRatio . '%' . $totalWords . '</strong></td>';

        return $ret;
    }
}
?>