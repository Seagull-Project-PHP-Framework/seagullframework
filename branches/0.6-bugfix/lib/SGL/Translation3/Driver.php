<?php

/**
*
*/
abstract class SGL_Translation3_Driver
{
    protected $_aOptions = array(
        'clear'     => false,
        'loadDefault' => true
    );
    /**
     * Supported languages
     */
    protected $_aLanguages = array();

    /**
     * Fallback language
     */
    public $fallbackLangCodeCharset;

    /**
     * Current language
     */
    public $langCodeCharset;

    /**
     * Current dictionary
     */
    public $dictionary;

    /**
     * Dictionaries table
     */
    protected $_aDictionaries = array();

    public function __construct(array $aOptions = array())
    {
        $aOptions = array_merge($this->_aOptions, $aOptions);
        $this->setOptions($aOptions);
        $this->init();
    }

    /**
     * Initialize Translate Driver, setting available languages, current language, charset
     */
    private function init()
    {
        $this->setAvailableLanguages();
        $this->setFallbackLangCodeCharset();
        $this->setLangCodeCharset();
    }

    /**
     * Returns language langCode i.e. fr for fr-utf-8
     *
     */
    public function getLangCode($langCodeCharset = null)
    {
        if (is_null($langCodeCharset)) {
            $langCodeCharset = $this->langCodeCharset;
        }
        return $this->_aLanguages[$langCodeCharset][2];
    }

    public function setOptions(array $aOptions = array())
    {
        foreach ($aOptions as $key => $value) {
            $this->_aOptions[$key] = $value;
        }
    }

    public function setFallbackLangCodeCharset()
    {
        $this->fallbackLangCodeCharset = SGL_Translation3::getFallbackLangCodeCharset();
    }

    public function getLangCodeCharset()
    {
        return $this->langCodeCharset;
    }

    /**
     * Sets current language
     */
    public function setLangCodeCharset($langCodeCharset = null)
    {
        if (is_null($langCodeCharset)) {
            $langCodeCharset = self::_resolveLanguage();
        }
        if (self::isAllowedLangCodeCharset($langCodeCharset)) {
            $this->langCodeCharset = $langCodeCharset;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets current dictionary
     */
    public function setDictionary($dictionary = null)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * Fetches a dictionary and loads it into _aDictionaries array + $GLOBALS['_SGL']['TRANSLATION'] for BC.
     *
     * @param   string  $dictionary     Dictionary you want to load
     * @param   string  $langCodeCharset Language you want the dictionary in, (eg: en, zh-TW) let null value to use
     *                                   automaticaly discovered language
     * @param   array   $aOptions       Run ime options to overwrite default options
     *                                   When passing aOption 'clear'  => true, the translation array
     *                                   will be cleared before adding new translation strings
     *
     */
    public function loadDictionary($dictionary, $langCodeCharset = null, array $aOptions = array())
    {
        $aOptions = array_merge($this->_aOptions, $aOptions);

        if (is_null($langCodeCharset)) {
            $langCodeCharset = $this->langCodeCharset;
            $langCode = $this->_aLanguages[$langCodeCharset][2];
        }

        if (!isset($GLOBALS['_SGL']['TRANSLATION'])) {
            $GLOBALS['_SGL']['TRANSLATION'] = array();
        }
        if (!array_key_exists($langCodeCharset, $this->_aDictionaries)) {
            $this->_aDictionaries[$langCodeCharset] = array();
        }
        // remember loaded dictionaries
        static $aDictionaries;
        $instance = $dictionary . '_' . $langCodeCharset;
        if (!isset($aDictionaries[$instance])) {
            $aDictionary = $this->getDictionary($dictionary, $langCodeCharset);
            // allow to clear translations before loading a dictionary
            if ($aOptions['clear'] == true) {
                $this->_aDictionaries[$langCodeCharset] = $aDictionary;
            } else {
                $this->_aDictionaries[$langCodeCharset] = array_merge($this->_aDictionaries[$langCodeCharset],
                    $aDictionary);
            }
            // for BC with SGL_Translate
            $GLOBALS['_SGL']['TRANSLATION'] = array_merge($GLOBALS['_SGL']['TRANSLATION'], $aDictionary);
            $GLOBALS['_SGL']['TRANSLATION'][$langCodeCharset] = $this->_aDictionaries[$langCodeCharset];
            $aDictionaries[$instance] = true;
        }
    }

    /**
     * Loading default dictionaries following SGL process.
     *
     * Additionaly you can add default dictionaries to be loaded in Translation ini file
     *
     */
    public function loadDefaultDictionaries()
    {
        $conf = SGL_Config::singleton()->ensureModuleConfigLoaded('translation');
        // Look for default dictionaries to be loaded
        $defaultDictionaries = SGL_Config::get('TranslationMgr.defaultDictionaries');
        $aDefaultDictionaries = !empty($defaultDictionaries)
            ? explode(',', $defaultDictionaries)
            : array();
        // Or load default dictionaries the seagull way
        if (!count($aDefaultDictionaries)) {
            $moduleDefault = SGL_Config::get('site.defaultModule');
            $current = SGL_Request::singleton()->get('moduleName');
            $moduleCurrent = $current
                ? $current
                : $moduleDefault;
            $aDefaultDictionaries[] = $moduleDefault;
            if ($moduleCurrent != $moduleDefault) {
                $aDefaultDictionaries[] = $moduleCurrent;
            }
            if (!(array_key_exists('default', $aDefaultDictionaries))
                    && $this->_aOptions['loadDefault']) {
                array_unshift($aDefaultDictionaries, 'default');
            }
        }
        // Look for additional dictionaries to load each request
        $additionalDictionaries = SGL_Config::get('TranslationMgr.otherDictionaries');
        if (!empty($additionalDictionaries)) {
            $aAdditionalDictionaries = explode(',', $additionalDictionaries);
            foreach ($aAdditionalDictionaries as $dictionary) {
                $aDefaultDictionaries[] = $dictionary;
            }
        }
        // now load the dictionaries
        foreach ($aDefaultDictionaries as $dictionary) {
            $this->loadDictionary($dictionary);
        }
    }

    /**
     * Adds an array of key => value translations.
     *
     * @param   string  $dictionary
     * @param   string  $lang
     * @param   array   $aTranslations
     *
     * @return  object  Specific SGL_Translation3_Driver instance (this method is chainable)
     *
     */
    public function addTranslations($dictionary, $langCodeCharset = null, array $aTranslations = array())
    {
        if (is_null($langCodeCharset)) {
            return SGL::raiseError('You must specify a language to add these translations to',
                SGL_ERROR_INVALIDARGS);
        }
        $this->setDictionary($dictionary);
        $this->setLangCodeCharset($langCodeCharset);
        $langCode = $this->_aLanguages[$langCodeCharset][2];
        $this->_aDictionaries[$langCode] = $aTranslations;
        return $this;
    }

    /**
     * Remove meta data from translation array.
     *
     * @param array   $aTranslations
     * @param boolean $removeAll
     *
     * @return array
     *
     * @static
     */
    protected function _removeMetaData($aTranslations, $removeAll = false)
    {
        foreach ($aTranslations as $k => $v) {
            if (strpos($k, '__SGL_') === 0) {
                if (((strpos($k, '__SGL_CATEGORY_') === 0)
                        || (strpos($k, '__SGL_COMMENT_') === 0))
                        && !$removeAll) {
                    continue;
                }
                unset($aTranslations[$k]);
            }
        }
        return $aTranslations;
    }

    public function translate($key)
    {
        return $this->_aDictionaries[$key];
    }

    public function getAvailableLanguages()
    {
        return $this->_aLanguages;
    }


    /******************************/
    /*       STATIC METHODS       */
    /******************************/
    public static function extractCharset($lang)
    {
        $aLang = explode('-', $lang);
        array_shift($aLang);
        if ($aLang[0] == 'tw') {
            array_shift($aLang);
        }
        return implode('-', $aLang);
    }

    public static function getFallbackCharset()
    {
        $langCodeCharset = SGL_Translation3::getFallbackLangCodeCharset();
        return self::extractCharset($langCodeCharset);
    }

    /**
     * Is a language allowed ?
     *
     * @param   string  $langCodeCharset   language id, e.g. en-utf-8, fr-utf-8, ...
     *
     * @return  boolean
     *
     */
    public static function isAllowedLangCodeCharset($langCodeCharset)
    {
        return array_key_exists($langCodeCharset, $GLOBALS['_SGL']['LANGUAGE']);
    }

    /**
     * Resolve language from browser settings.
     *
     * @access public
     *
     * @return mixed  language or false on failure
     */
    public static function resolveLanguageFromBrowser()
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
                // don't take care of locale for now, only main language
                $langCode = substr($langCode, 0, 2);
                $langCodeCharset = $langCode . '-' . self::getFallbackCharset();
                if (self::isAllowedLangCodeCharset($langCodeCharset)) {
                    $ret = $langCodeCharset;
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
    public static function resolveLanguageFromDomain()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $langCode = array_pop(explode('.', $_SERVER['HTTP_HOST']));

            // if such language exists, then use it
            $langCodeCharset = $langCode . '-' . self::getFallbackCharset();
            if (self::isAllowedLangCodeCharset($langCodeCharset)) {
                $ret = $langCodeCharset;
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
    public static function _resolveLanguage()
    {
        // resolve language from request
        $langCodeCharset = SGL_Request::singleton()->get('lang');

        // 1. look for language in URL
        if (empty($langCodeCharset) || !self::isAllowedLangCodeCharset($langCodeCharset)) {
            // 2. look for language in settings
            if (!isset($_SESSION['aPrefs']['language'])
                    || !self::isAllowedLangCodeCharset($_SESSION['aPrefs']['language'])
                    || SGL_Session::isFirstAnonRequest()) {
                // 3. look for language in browser settings
                if (!SGL_Config::get('translation.languageAutoDiscover')
                        || !($langCodeCharset = self::resolveLanguageFromBrowser())) {
                    // 4. look for language in domain
                    if (!SGL_Config::get('translation.languageAutoDiscover')
                            || !($langCodeCharset = self::resolveLanguageFromDomain())) {
                        // 5. get default language
                        $langCodeCharset = SGL_Translation3::getFallbackLangCodeCharset();
                    }
                }
            // get language from settings
            } else {
                $langCodeCharset = $_SESSION['aPrefs']['language'];
            }
        }
        return $langCodeCharset;
    }

    /******************************/
    /*       ABSTRACT METHODS     */
    /******************************/
    /**
     * Fetches a dictionary
     *
     * @param   string  $dictionary     Dictionary you want to load
     * @param   string  $langCodeCharset Language you want the dictionary in, let null value to use
     *                                   automaticaly discovered language
     *
     */
    abstract public function getDictionary($dictionary, $langCodeCharset = null);

    /**
     * Updates a string in dictionary given its key
     *
     */
    abstract public function update(array $aString = array(), $dictionary, $langCodeCharset = null);

    /**
     * Saves current dictionary translations
     *
     */
    abstract public function save();

    abstract public function clearCache();

    /**
     * Returns the driver name
     *
     * @return string
     */
    abstract public function toString();

    abstract public function setAvailableLanguages();
}
?>