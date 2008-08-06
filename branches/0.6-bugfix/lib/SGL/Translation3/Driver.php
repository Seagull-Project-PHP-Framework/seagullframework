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
    public $fallbackLanguage;

    /**
     * Current language
     */
    public $language;

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
        $this->setFallbackLanguage();
        $this->setLanguage();
    }

    /**
     * Returns language code i.e. fr for fr-utf-8
     *
     */
    public function getLangCode($lang = null)
    {
        if (is_null($lang)) {
            $lang = $this->language;
        }
        return $this->_aLanguages[$lang][2];
    }

    public function setOptions(array $aOptions = array())
    {
        foreach ($aOptions as $key => $value) {
            $this->_aOptions[$key] = $value;
        }
    }

    public function setFallbackLanguage()
    {
        $this->fallbackLanguage = SGL_Translation3::getFallbackLanguage();
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets current language
     */
    public function setLanguage($lang = null)
    {
        if (is_null($lang)) {
            $lang = self::_resolveLanguage();
        }
        if (self::isAllowedLanguage($lang)) {
            $this->language = $lang;
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
     * @param   string  $lang           Language you want the dictionary in, let null value to use
     *                                  automaticaly discovered language
     * @param   array   $aOptions       Run time options to overwrite default options
     *                                  When passing aOption 'clear'  => true, the translation array
     *                                  will be cleared before adding new translation strings
     *
     */
    public function loadDictionary($dictionary, $lang = null, array $aOptions = array())
    {
        $aOptions = array_merge($this->_aOptions, $aOptions);

        if (is_null($lang)) {
            $lang = $this->language;
        }
        $langCode = $this->_aLanguages[$lang][2];
        if (!isset($GLOBALS['_SGL']['TRANSLATION'])) {
            $GLOBALS['_SGL']['TRANSLATION'] = array();
        }
        if (!array_key_exists($langCode, $this->_aDictionaries)) {
            $this->_aDictionaries[$langCode] = array();
        }
        // remember loaded dictionaries
        static $aDictionaries;
        $instance = $dictionary . '_' . $langCode;
        if (!isset($aDictionaries[$instance])) {
            $aDictionary = $this->getDictionary($dictionary, $lang);
            // allow to clear translations before loading a dictionary
            if ($aOptions['clear'] == true) {
                $this->_aDictionaries[$langCode] = $aDictionary;
            } else {
                $this->_aDictionaries[$langCode] = array_merge($this->_aDictionaries[$langCode],
                    $aDictionary);
            }
            // for BC with SGL_Translate
            $GLOBALS['_SGL']['TRANSLATION'] = array_merge($GLOBALS['_SGL']['TRANSLATION'], $aDictionary);
            $GLOBALS['_SGL']['TRANSLATION'][$langCode] = $this->_aDictionaries[$langCode];
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
        $defaultDictionaries = SGL_Config::get('TranslationMgr.defaultDictionaries');
        $aDefaultDictionaries = !empty($defaultDictionaries)
            ? explode(',', $defaultDictionaries)
            : array();
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
    public function addTranslations($dictionary, $lang = null, array $aTranslations = array())
    {
        if (is_null($lang)) {
            return SGL::raiseError('You must specify a language to add these translations to', SGL_ERROR_INVALIDARGS);
        }
        $this->setDictionary($dictionary);
        $this->setLanguage($lang);
        $langCode = $this->_aLanguages[$lang][2];
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
        $lang = SGL_Translation3::getFallbackLanguage();
        return self::extractCharset($lang);
    }

    /**
     * Is a language allowed ?
     *
     * @param   string  $lang   language id, e.g. en-utf-8, fr-utf-8, ...
     *
     * @return  boolean
     *
     */
    public static function isAllowedLanguage($lang)
    {
        return array_key_exists($lang, $GLOBALS['_SGL']['LANGUAGE']);
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
                $lang = $langCode . '-' . self::getFallbackCharset();
                if (self::isAllowedLanguage($lang)) {
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
    public static function resolveLanguageFromDomain()
    {
        $ret = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $langCode = array_pop(explode('.', $_SERVER['HTTP_HOST']));

            // if such language exists, then use it
            $lang = $langCode . '-' . self::getFallbackCharset();
            if (self::isAllowedLanguage($lang)) {
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
    public static function _resolveLanguage()
    {
        // resolve language from request
        $lang = SGL_Request::singleton()->get('lang');

        // 1. look for language in URL
        if (empty($lang) || !self::isAllowedLanguage($lang)) {
            // 2. look for language in settings
            if (!isset($_SESSION['aPrefs']['language'])
                    || !self::isAllowedLanguage($_SESSION['aPrefs']['language'])
                    || SGL_Session::isFirstAnonRequest()) {
                // 3. look for language in browser settings
                if (!SGL_Config::get('translation.languageAutoDiscover')
                        || !($lang = self::resolveLanguageFromBrowser())) {
                    // 4. look for language in domain
                    if (!SGL_Config::get('translation.languageAutoDiscover')
                            || !($lang = self::resolveLanguageFromDomain())) {
                        // 5. get default language
                        $lang = SGL_Translation3::getFallbackLanguage();
                    }
                }
            // get language from settings
            } else {
                $lang = $_SESSION['aPrefs']['language'];
            }
        }
        return $lang;
    }

    /******************************/
    /*       ABSTRACT METHODS     */
    /******************************/
    /**
     * Fetches a dictionary
     *
     * @param   string  $dictionary     Dictionary you want to load
     * @param   string  $lang           Language you want the dictionary in, let null value to use
     *                                  automaticaly discovered language
     *
     */
    abstract public function getDictionary($dictionary, $lang = null);

    /**
     * Updates a string in dictionary given its key
     *
     */
    abstract public function update(array $aString = array(), $dictionary, $lang = null);

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