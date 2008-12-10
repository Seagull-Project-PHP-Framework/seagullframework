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
     * The default language code charset
     */
    public $defaultLangCodeCharset;

    /**
     * The current language code charset, eg, en-utf-8
     */
    public $langCodeCharset;

    /**
     * The current language code, eg, en
     */
    public $langCode;

    /**
     * The framework default langCode
     */
    public $defaultLangCode;

    /**
     * Current dictionary
     */
    public $dictionary;

    /**
     * A hash of translation arrays ie default (module), cms(module), navigation, categories
     * keyed by langCode, ie zh-TW
     */
    protected $_aDictionaries = array();

    public function __construct(array $aOptions = array())
    {
        $aOptions = array_merge($this->_aOptions, $aOptions);
        $this->setOptions($aOptions);
        $this->init();
    }

    /**
     * Initializea the Translate Driver, setting available languages, the default
     * language and the current language.
     */
    private function init()
    {
        $this->setAvailableLanguages();
        $this->setDefaultLangCode();
        $this->setLangCode();
    }

    /**
     * Returns language langCode i.e. fr for fr-utf-8
     *
     * @todo make more similar to langCodeToLangCodeCharset()
     *
     */
    public function getLangCode($langCodeCharset = null)
    {
        //  handle langCode as well as langCodeCharset
        if (!is_null($langCodeCharset)) {
            if (!preg_match('/utf-8/', $langCodeCharset)) {
                $langCode = $langCodeCharset;
                $langCodeCharset = self::langCodeToLangCodeCharset($langCode);
            }
        } else {
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

    public function setDefaultLangCode()
    {
        $this->defaultLangCode = SGL_Translation3::getDefaultLangCode();
        // BC - as long as language list keys are $langCodeCharset we must set this
        $this->defaultLangCodeCharset = SGL_Translation3::getDefaultLangCodeCharset();
    }

    public function getLangCodeCharset($langCode = null)
    {
        if (!is_null($langCode)) {
            $langCodeCharset = self::langCodeToLangCodeCharset($langCode);
            return $langCodeCharset;
        } else {
            return $this->langCodeCharset;
        }
    }

    public function getDefaultLangCode()
    {
        return $this->defaultLangCode;
    }


    /**
     * Sets current language code.
     */
    public function setLangCode($langCode = null)
    {
        if (is_null($langCode)) {
            $langCode = $this->_resolveLangCode();
        }
        if ($langCodeCharset = self::langCodeToLangCodeCharset($langCode)) {
            $this->langCode = $langCode;
            // BC - as long as language list keys are $langCodeCharset we must set this
            $this->langCodeCharset = $langCodeCharset;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets current dictionary.
     */
    public function setDictionary($dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * Fetches a dictionary and loads it into _aDictionaries array + $GLOBALS['_SGL']['TRANSLATION'] for BC.
     *
     * @param   string  $dictionary     Dictionary you want to load
     * @param   string  $langCodeCharset Language you want the dictionary in  leave as null for
     *                                   automaticaly discovered language
     * @param   array   $aOptions       Run ime options to overwrite default options
     *                                   When passing aOption 'clear'  => true, the translation array
     *                                   will be cleared before adding new translation strings
     *
     */
    public function loadDictionary($dictionary, $langCode = null,
        array $aOptions = array())
    {
        $aOptions = array_merge($this->_aOptions, $aOptions);

        if (is_null($langCode)) {
            $langCodeCharset = $this->langCodeCharset;
            $langCode = $this->_aLanguages[$langCodeCharset][2];
        } else {
            $langCodeCharset = self::langCodeToLangCodeCharset($langCode);
        }

        if (!array_key_exists($langCode, $this->_aDictionaries)) {
            $this->_aDictionaries[$langCode] = array();
        }
        // remember loaded dictionaries
        static $aDictionaries;
        $instance = $dictionary . '_' . $langCodeCharset;
        if (!isset($aDictionaries[$instance])) {
            $aDictionary = $this->getDictionary($dictionary, $langCode);
            // allow to clear translations before loading a dictionary
            if ($aOptions['clear'] == true) {
                $this->_aDictionaries[$langCode] = $aDictionary;
            } else {
                $this->_aDictionaries[$langCode] = array_merge($this->_aDictionaries[$langCode],
                    $aDictionary);
            }
            $aDictionaries[$instance] = true;
        }
    }

    public function langCodeToLangCodeCharset($langCode)
    {
        foreach ($GLOBALS['_SGL']['LANGUAGE'] as $k => $aLangs) {
            if ($aLangs[2] == $langCode) {
                return $k;
            }
        }
        return false;
    }

    /**
     * Loading default dictionaries following SGL process.
     *
     * Additionaly you can add default dictionaries to be loaded in
     * the Translation module's conf.ini file
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
        // Or load default dictionaries the Seagull way
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
        //  BC for SGL_String::translate() method
        //  loads currently specified user lang into $GLOBALS['_SGL']['TRANSLATION']
        $GLOBALS['_SGL']['TRANSLATION'] = $this->_aDictionaries[$this->getLangCode()];
    }

    /**
     * Adds an array of key => value translations.
     *
     * @param   string  $dictionary
     * @param   string  $langCode
     * @param   array   $aTranslations
     *
     * @return  object  Specific SGL_Translation3_Driver instance (this method is chainable)
     */
    public function addTranslations($dictionary, $langCode, array $aTranslations = array())
    {
        $this->setDictionary($dictionary);
        $this->setLangCode($langCode);
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

    /**
     * Enter description here...
     *
     * @param string $langCode
     * @param string $key
     * @return string
     *
     * @todo is this used?
     */
    public function translate($langCode, $key)
    {
        return isset($this->_aDictionaries[$langCode][$key])
            ? $this->_aDictionaries[$langCode][$key]
            : false;
    }

    public function getAvailableLanguages()
    {
        return $this->_aLanguages;
    }

    /**
     * Resolve current language.
     *
     * @access private
     *
     * @return string
     */
    public function _resolveLangCode()
    {
        // resolve language from request
        $langCode = SGL_Request::singleton()->get('lang');
        $langCodeCharset = self::langCodeToLangCodeCharset($langCode);

        // 1. look for language in URL
        if (empty($langCode) || !self::langCodeToLangCodeCharset($langCode)) {
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
                        $langCodeCharset = SGL_Translation3::getDefaultLangCodeCharset();
                    }
                }
            // get language from settings
            } else {
                $langCodeCharset = $_SESSION['aPrefs']['language'];
            }
        }
        return $this->_aLanguages[$langCodeCharset][2];
    }


    /******************************/
    /*       STATIC METHODS       */
    /******************************/

    /**
     * @todo make work with langCode
     */
    public static function extractCharset($langCodeCharset)
    {
        $aLang = explode('-', $langCodeCharset);
        array_shift($aLang);
        if ($aLang[0] == 'tw') {
            array_shift($aLang);
        }
        return implode('-', $aLang);
    }

    public static function getDefaultCharset()
    {
        $langCodeCharset = SGL_Translation3::getDefaultLangCodeCharset();
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
				$langCode = ($langCode == 'zh-tw') ? 'zhtw' : substr($langCode, 0, 2);
                $langCodeCharset = $langCode . '-' . self::getDefaultCharset();
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
            $langCodeCharset = $langCode . '-' . self::getDefaultCharset();
            if (self::isAllowedLangCodeCharset($langCodeCharset)) {
                $ret = $langCodeCharset;
            }
        }
        return $ret;
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
    abstract public function getDictionary($dictionary, $langCode = null);

    /**
     * Updates a string in dictionary given its key
     *
     */
    abstract public function update(array $aStrings = array(), $dictionary,
        $langCode = null);

    /**
     * Saves current dictionary translations
     *
     */
    abstract public function save($dictionary = null, $langCode = null);

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