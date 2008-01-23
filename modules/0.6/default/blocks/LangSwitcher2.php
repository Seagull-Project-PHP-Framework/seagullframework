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
// | LangSwitcher2.php                                                         |
// +---------------------------------------------------------------------------+
// | Authors: Dmitri Lakachauskis <lakiboy83@gmail.com>                        |
// +---------------------------------------------------------------------------+

/**
 * Language switcher block.
 *
 * @package seagull
 * @subpackage block
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class Default_Block_LangSwitcher2
{
    var $templatePath = 'default';

    function init(&$output, $blockId, $aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // only one language exists in minimal install
        if (SGL::isMinimalInstall()) {
            return false;
        }

        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, $aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (empty($aParams)) {
            $aParams = array();
        }
        $aDefaultParams = array(
            'template'  => 'blockLangSwitcher.html',
            'extension' => 'png'
        );
        // current params
        $aParams = array_merge($aDefaultParams, $aParams);

        // current theme
        $theme = isset($_SESSION['aPrefs']['theme'])
            ? $_SESSION['aPrefs']['theme']
            : 'default';
        $imageDir = isset($output->imagesDir)
            ? $output->imagesDir
            : SGL_BASE_URL  . '/themes/' . $theme . '/image' ;

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

            if (SGL_Config::get('site.inputUrlHandlers') != 'Horde_Routes') {
                $url->aQueryData['lang'] = $langKey;
                $aQueryData = $url->getQueryData(true);
                $href = $this->_makeOldStyleUrl($aQueryData);
            } else {
                $switch = SGL_Config::get('translation.langInUrl')
                    ? $aLangData[$langKey]['code']
                    : $langKey;
                $aQueryData = array('lang' => $switch);
                $href = $url->makeCurrentLink($aQueryData);
            }

            $imageFile = SGL_WEB_ROOT . '/themes/' . $theme . '/images/flags/'
                . $langKey . '.' . $aParams['extension'];

            // link
            $aLangData[$langKey]['url'] = $href;
            // is current?
            $aLangData[$langKey]['is_current'] =
                SGL::getCurrentLang() == $aLangData[$langKey]['code'];
            // image
            $aLangData[$langKey]['image'] = file_exists($imageFile)
                ? "$imageDir/flags/{$langKey}.{$aParams['extension']}"
                : false;
        }

        $blockOutput                 = & new SGL_Output();
        $blockOutput->conf           = $conf;
        $blockOutput->theme          = $theme;
        $blockOutput->imagesDir      = $imageDir;
        $blockOutput->masterTemplate = $aParams['template'];
        $blockOutput->aLangs         = $aLangData;

        return $this->process($blockOutput);
    }

    function _makeOldStyleUrl($aQueryData = array())
    {
        $action = '';
        $params = '';
        $cookie = SGL_Config::get('cookie.name');
        if (isset($aQueryData['action'])) {
            $action = $aQueryData['action'];
            unset($aQueryData['action']);
        }
        foreach ($aQueryData as $key => $value) {
            if (empty($value) && !is_numeric($value)
                    || false !== strpos($key, $cookie)) {
                continue;
            }
            $params[] = $key . '|' . $value;
        }
        if (!empty($params)) {
            $params = implode('||', $params);
        }
        return SGL_Output::makeUrl($action, '', '', array(), $params);
    }

    function process(&$output)
    {
        $output->moduleName = $this->templatePath;

        $view = & new SGL_HtmlSimpleView($output);
        return $view->render();
    }
}
?>