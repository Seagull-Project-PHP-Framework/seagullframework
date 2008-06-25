<?php

/**
 * Resolve current language and put in current user preferences.
 * Load relevant language translation file.
 *
 * @package Task
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Filter_SetupLangSupport extends SGL_DecorateProcess
{
    /**
     * Main workflow.
     *
     * @access public
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // save language in settings
        $_SESSION['aPrefs']['language'] = $lang = $this->_resolveLanguage();

        $req = $input->getRequest();

        // modules to get translation for
        if (SGL_Config::get('translation.defaultLangBC')) {
            $moduleDefault = 'default';
        } else {
            $moduleDefault = SGL_Config::get('site.defaultModule');
        }

        $moduleCurrent = $req->get('moduleName')
            ? $req->get('moduleName')
            : $moduleDefault;

        // get translations
        $aWords = SGL_Translation::getTranslations($moduleDefault, $lang);
        if ($moduleCurrent != $moduleDefault) {
            $aWords = array_merge(
                $aWords,
                SGL_Translation::getTranslations($moduleCurrent, $lang)
            );
        }

        // populate SGL globals
        $GLOBALS['_SGL']['TRANSLATION'] = $aWords;
        // we can remove this one, left for BC
        $GLOBALS['_SGL']['CHARSET'] = SGL_Translation::getCharset();

        // continue chain execution
        $this->processRequest->process($input, $output);
    }

    /**
     * Resolve language from browser settings.
     *
     * @access public
     *
     * @return mixed  language or false on failure
     */
    function resolveLanguageFromBrowser()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $env = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $aLangs = preg_split(
                ';[\s,]+;',
                substr($env, 0, strpos($env . ';', ';')), -1,
                PREG_SPLIT_NO_EMPTY
            );
            foreach ($aLangs as $langCode) {
                $lang = $langCode . '-' . SGL_Translation::getFallbackCharset();
                if (SGL_Translation::isAllowedLanguage($lang)) {
                    $ret = $lang;
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * Resolve language from domain name.
     *
     * @access public
     *
     * @return mixed  language or false on failure
     */
    function resolveLanguageFromDomain()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $langCode = array_pop(explode('.', $_SERVER['HTTP_HOST']));

            // if such language exists, then use it
            $lang = $langCode . '-' . SGL_Translation::getFallbackCharset();
            if (SGL_Translation::isAllowedLanguage($lang)) {
                $ret = $lang;
            }
        }
        return $ret;
    }

    /**
     * Resolve current language.
     *
     * @access private
     *
     * @return string
     */
    function _resolveLanguage()
    {
        $req = SGL_Request::singleton();

        // resolve language from request
        $lang = $req->get('lang');

        // 1. look for language in URL
        if (empty($lang) || !SGL_Translation::isAllowedLanguage($lang)) {
            // 2. look for language in settings
            if (!isset($_SESSION['aPrefs']['language'])
                    || !SGL_Translation::isAllowedLanguage($_SESSION['aPrefs']['language'])
                    || SGL_Session::isFirstAnonRequest()) {
                // 3. look for language in browser settings
                if (!SGL_Config::get('translation.languageAutoDiscover')
                        || !($lang = $this->resolveLanguageFromBrowser())) {
                    // 4. look for language in domain
                    if (!SGL_Config::get('translation.languageAutoDiscover')
                            || !($lang = $this->resolveLanguageFromDomain())) {
                        // 5. get default language
                        $lang = SGL_Translation::getFallbackLangID(SGL_LANG_ID_SGL);
                    }
                }
            // get language from settings
            } else {
                $lang = $_SESSION['aPrefs']['language'];
            }
        }
        return $lang;
    }
}
?>