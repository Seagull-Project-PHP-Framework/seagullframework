<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | LangSwitcher2.php                                                         |
// +---------------------------------------------------------------------------+
// | Authors: Dmitri Lakachauskis <dmitri at telenet dot lv>                   |
// +---------------------------------------------------------------------------+

/**
 * Language switcher block.
 *
 * @subpackage block
 * @package    seagull
 * @author     Dmitri Lakachauskis <dmitri at telenet dot lv>
 */
class Default_Block_LangSwitcher2
{
    var $templatePath = 'default';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL::isMinimalInstall()) {
            // only one language exists in minimal install
            return false;
        }
        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (empty($aParams)) {
            $aParams = array();
        }
        $aDefaultParams = array(
            'template'  => 'blockLangSwitcher.html',
            'extension' => 'png'
        );
        $aParams = array_merge($aDefaultParams, $aParams);

        $input = &SGL_Registry::singleton();
        $conf  = $input->getConfig();
        $url   = $input->getCurrentUrl();

        $aLangs          = SGL_Util::getLangsDescriptionMap();
        $aLangsDef       = $GLOBALS['_SGL']['LANGUAGE'];
        $aInstalledLangs = str_replace('_', '-',
            explode(',', $conf['translation']['installedLanguages']));

        $aLangData = array();
        foreach ($aLangs as $langKey => $langName) {
            if (!in_array($langKey, $aInstalledLangs)) {
                continue;
            }
            preg_match('/(.+) \(.+\)/', $langName, $matches);

            // main data
            $aLangData[$langKey]['name'] = $matches[1];
            $aLangData[$langKey]['code'] = $aLangsDef[$langKey][2];
            $aLangData[$langKey]['key']  = $langKey;

            // the best way to build lang switching url found so far taking into
            // account UrlParser strategy, cleanUrls, session info etc
            $url->aQueryData['lang'] = $langKey;
            $aQueryData = $url->getQueryData(true);
            $action = '';
            $params = '';
            if (isset($aQueryData['action'])) {
                $action = $aQueryData['action'];
                unset($aQueryData['action']);
            }
            foreach ($aQueryData as $key => $value) {
                if (empty($value) && !is_numeric($value)
                        || false !== strpos($key, $conf['cookie']['name'])) {
                    continue;
                }
                $params[] = $key . '|' . $value;
            }
            if (!empty($params)) {
                $params = implode('||', $params);
            }
            $aLangData[$langKey]['url'] = $url->makeLink($action, '', '',
                array(), $params);

            $aLangData[$langKey]['is_current'] =
                SGL::getCurrentLang() == $aLangData[$langKey]['code'];
            $imageFile = SGL_WEB_ROOT . '/themes/' . $_SESSION['aPrefs']['theme'] .
                '/images/flags/' . $langKey . '.' . $aParams['extension'];
            $aLangData[$langKey]['image'] = file_exists($imageFile)
                ? "{$output->imagesDir}/flags/{$langKey}.{$aParams['extension']}"
                : false;
        }

        $blockOutput                 = & new SGL_Output();
        $blockOutput->conf           = $conf;
        $blockOutput->theme          = $_SESSION['aPrefs']['theme'];
        $blockOutput->imagesDir      = $output->imagesDir;
        $blockOutput->masterTemplate = $aParams['template'];
        $blockOutput->aLangs         = $aLangData;

        return $this->process($blockOutput);
    }

    function process(&$output)
    {
        $output->moduleName = $this->templatePath;

        $view = & new SGL_HtmlSimpleView($output);
        return $view->render();
    }
}
?>