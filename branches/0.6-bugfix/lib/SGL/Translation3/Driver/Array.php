<?php
require_once 'Config.php';
require_once 'SGL/Translation3/Driver.php';

/**
*
*/
class SGL_Translation3_Driver_Array extends SGL_Translation3_Driver
{
    function __construct(array $aOptions = array())
    {
        // Driver specific options
        $_aOptions = array(

        );
        $aOptions = array_merge($_aOptions, $aOptions);
        parent::__construct($aOptions);
    }

    public function setAvailableLanguages()
    {
        foreach ($GLOBALS['_SGL']['LANGUAGE'] as $k => $aLang) {
            if (!preg_match('/utf-8$/', $k)) {
                unset($GLOBALS['_SGL']['LANGUAGE'][$k]);
            }
        }
        $this->_aLanguages = $GLOBALS['_SGL']['LANGUAGE'];
    }

    public function getFileName($dictionary = null, $langCodeCharset = null)
    {
        if (is_null($dictionary)) {
            $dictionary = $this->dictionary;
        }
        if (is_null($langCodeCharset)) {
            $langCodeCharset = $this->langCodeCharset;
        }
        $langFileName = $this->_aLanguages[$langCodeCharset][1];
        return SGL_PATH . '/data/lang/' . $dictionary . '/' . $langFileName . '.php';
    }

    /**
     * Fetches a dictionary
     *
     * @param   string  $dictionary     Dictionary you want to load
     * @param   string  $langCodeCharset Language you want the dictionary in, let null value to use
     *                                   automaticaly discovered language
     */
    public function getDictionary($dictionary, $langCodeCharset = null)
    {
        //  handle langCode as well as langCodeCharset
        if (!is_null($langCodeCharset)) {
            if (!preg_match('/utf-8/', $langCodeCharset)) {
                $langCode = $langCodeCharset;
                $langCodeCharset = self::langCodeToLangCodeCharset($langCode);
            }
        }
        if ($langCodeCharset === false || is_null($langCodeCharset)) {
            $langCodeCharset = $this->langCodeCharset;
            $langCode = $this->_aLanguages[$langCodeCharset][2];
        }

        $langFileName = $this->_aLanguages[$langCodeCharset][1];

        // looking for a language file in paths
        $projectPath    = SGL_PATH . '/data/lang/' . $dictionary;
        $modulePath     = SGL_MOD_DIR . '/' . $dictionary  . '/lang';

        if (is_file($projectPath . '/' . $langFileName . '.php')) {
            $file = $projectPath . '/' . $langFileName . '.php';
        } elseif (is_file($modulePath . '/' . $langFileName . '.php')) {
            $file = $modulePath . '/' . $langFileName . '.php';
        }
        $words = array();
        // loading translations from php file
        if (isset($file) && is_readable($file)) {
            include $file;
            if ($dictionary == 'default') {
                $words = $defaultWords;
            }
        }
        $words = $this->_removeMetaData($words);
        return $words;
    }

    /**
     * Updates a string in a dictionary given its key
     * If the language we are editing is the master (fallback) then the key
     * will be updated in for all languages
     *
     */
    public function update(array $aString = array(), $dictionary, $langCodeCharset = null)
    {
        $originalKey = $aString[0];
        $key    = $aString[1];
        $value  = $aString[2] ? $aString[2] : $aString[1];
        if ($langCodeCharset == $this->fallbackLangCodeCharset) {
            $this->_updateMaster($originalKey, $key, $value, $dictionary);
            $this->_syncSlaveLanguages($originalKey, $key, $value, $dictionary);
        } else {
            $this->_updateSlaveValue($key, $value, $dictionary, $langCodeCharset);
        }
    }

    protected function _updateMaster($originalKey, $key, $value, $dictionary)
    {
        $aDictionary = $this->getDictionary($dictionary, $this->fallbackLangCodeCharset);

        if ($originalKey != 'New Category') {
            unset($aDictionary[$originalKey]);
        }
        $aDictionary[$key] = $value;
        $this->addTranslations($dictionary, $this->fallbackLangCodeCharset, $aDictionary);
        $this->save();
    }

    protected function _syncSlaveLanguages($originalKey, $key, $value, $dictionary)
    {
        foreach ($this->_aLanguages as $langCodeCharset => $aLang) {
            if ($langCodeCharset == $this->fallbackLangCodeCharset) {
                // do nothing with master language
                continue;
            }
            $aDictionary = $this->getDictionary($dictionary, $langCodeCharset);
            if (array_key_exists($originalKey, $aDictionary) && $originalKey != 'New Category') {
                $oldStringValue = $aDictionary[$originalKey];
                unset($aDictionary[$originalKey]);
            }
            $aDictionary[$key] = !empty($oldStringValue)
                ? $oldStringValue
                : $value;
            $this->addTranslations($dictionary, $langCodeCharset, $aDictionary);
            $this->save();
        }
    }

    protected function _updateSlaveValue($key, $value, $dictionary, $lang)
    {
        $aDictionary = $this->getDictionary($dictionary, $lang);
        $aDictionary[$key] = $value;
        $this->addTranslations($dictionary, $lang, $aDictionary);
        $this->save();
    }

    /**
     * Saves current dictionary translations.
     *
     */
    public function save()
    {
        $langCode       = $this->getLangCode();
        $aDictionary    = &$this->_aDictionaries[$langCode];
        $this->_updateMetaData();
        $aDictionaryEscaped = SGL_String::escapeSingleQuoteInArrayKeys($aDictionary);

        //  read translation data and get reference to root
        $c = new Config();
        $root = & $c->parseConfig($aDictionaryEscaped, 'phparray');
        //  write translation to file
        $filename = $this->getFileName();
        $this->_ensureLangFileExists($filename);
        if (!is_writable($filename)) {
            return SGL::raiseError('Please give perms to write ' . $filename, SGL_ERROR_INVALIDFILEPERMS);
        }
        $arrayName = ($this->dictionary == 'default') ? 'defaultWords' : 'words';
        $result = $c->writeConfig($filename, 'phparray', array('name' => $arrayName));
        if ($result instanceOf PEAR_Error) {
            return $result;
        }
        return true;
    }

    /**
     * Checks if the lang file exists in data/lang/ directory.
     *
     * If this file doesn't exist it will be created.
     */
    private function _ensureLangFileExists($langFile)
    {
        $langDir = dirname($langFile);
        if (!is_dir($langDir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $langDir));
            @chmod($langDir, 0777);
        }

        if (!is_file($langFile)) {
            $ok = touch($langFile);
        }
    }
    /**
     * Updates dictionary meta data
     *
     */
    private function _updateMetaData()
    {
        $langCode   = $this->getLangCode();
        $aDictionary = $this->_aDictionaries[$langCode];
        $aMetaData = array(
            '__SGL_UPDATED_BY'    => SGL_Session::getUsername(),
            '__SGL_UPDATED_BY_ID' => SGL_Session::getUid(),
            '__SGL_LAST_UPDATED'  => SGL_Date::getTime(true)
        );
        // we do it in this way to put meta data first in array
        foreach ($aMetaData as $k => $v) {
            if (isset($aDictionary[$k])) {
                unset($aDictionary[$k]);
            }
        }
        $this->_aDictionaries[$langCode] = $aMetaData + $aDictionary;
    }

    /**
     * Does nothing, this Driver is already using files
     */
    public function clearCache()
    {
        return true;
    }

    /**
     * Returns the driver name.
     *
     * @return string
     */
    public function toString()
    {
        return 'Array';
    }
}
?>