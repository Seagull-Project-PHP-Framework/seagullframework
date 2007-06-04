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
// | Translation.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Alexander J. Tarachanowicz II <ajt@localhype.net>               |
// +---------------------------------------------------------------------------+
// $Id: Translation.php,v 1.0 2005/05/11 00:00:00 demian Exp $

/**
 * A wrapper to PEAR Translation2.
 *
 * @package SGL
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.0 $
 */

class SGL_Translation
{
    /**
     * Generate singleton for PEAR::Tranlation2
     *
     * Object types:
     *  o translation (default)
     *  o admin - translation2_admin
     * Storage drivers: (Set in global config under site)
     *  o single - all translations in a single table (translations)
     *  o multiple (default) - all translations in a seperate table
     * (translation_en, translation_pl, translation_de)
     *
     * @static
     * @access  public
     * @param   string  $lang           language to return translations
     * @param   string  $type           type of object: translation or admin
     * @return  object  $translation    Translation2 object
     *
     *
     */
    function &singleton($type = 'translation')
    {
        static $instance;

        // If the instance exists, return one
        if (isset($instance[$type])) {
            return $instance[$type];
        }

        $c      = &SGL_Config::singleton();
        $conf   = $c->getAll();
        $dbh    = SGL_DB::singleton();

        //  set translation table parameters
        $params = array(
            'langs_avail_table' => $conf['db']['prefix'] . 'langs',
            'lang_id_col'       => 'lang_id',
            'lang_name_col'     => 'name',
            'lang_meta_col'     => 'meta',
            'lang_errmsg_col'   => 'error_text',
            'lang_encoding_col' => 'encoding',
            'string_id_col'      => 'translation_id',
            'string_page_id_col' => 'page_id',
            'string_text_col'    => '%s'  //'%s' will be replaced by the lang code
        );

        //  set translation driver
        $driver = 'DB';

        //  create translation storage tables
        if ($conf['translation']['container'] == 'db') {

            $prefix = $conf['db']['prefix'] .
                $conf['translation']['tablePrefix'] . '_';
            $aLangs = explode(',', $conf['translation']['installedLanguages']);

            //  set params
            foreach ($aLangs as $lang) {
                $params['strings_tables'][$lang] = $prefix . $lang;
            }
        } else {
            SGL::raiseError('translation table not specified check global config ',
                SGL_ERROR_INVALIDCONFIG, PEAR_ERROR_DIE);
        }

        //  instantiate selected translation2 object
        switch (strtolower($type)) {

        case 'admin':
            require_once 'Translation2/Admin.php';
            $instance[$type] = &Translation2_Admin::factory($driver, $dbh, $params);
            break;

        case 'translation':
        default:
            require_once 'Translation2.php';
            $instance[$type] = &Translation2::factory($driver, $dbh, $params);
        }
        return $instance[$type];
    }

    /**
     * Clear translation2 and GUI cache
     *
     * @static
     * @access  public
     *
     * @return  boolean     true on success/false on failure
     */
    function clearCache()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        if ('db' == $conf['translation']['container']) {
            //   clear Translation2 cache
            $trans = SGL_Translation::singleton('admin');
            $trans->options['cacheOptions']['cacheDir'] = SGL_TMP_DIR .'/';
            $trans->options['cacheOptions']['defaultGroup'] = 'translation';
            return $trans->cleanCache();
        } else {
            //   clear file GUI cache
            return SGL_Translation::clearGuiTranslationsCache();
        }
    }

    /**
     * Clear GUI Translations cache
     *
     * @static
     * @access  public
     *
     * @return boolean      true on success/false on failure
     */
    function clearGuiTranslationsCache()
    {
        $c = &SGL_Config::singleton();

        $aLangs = $aLangs = explode(',', $this->conf['translation']['installedLanguages']);

        if (count($aLangs) > 0) {
            $cache = & SGL_Cache::singleton();
            $cache->setOption('cacheDir', SGL_TMP_DIR .'/');

            $success = true;
            foreach ($aLangs as $group) {
                $result = SGL_Cache::clear('translation_'. $group);
                $success = $success && $result;
            }
            return $success;
        }
    }

    /**
     * Returns a dictionary of translated strings.
     *
     * @static
     * @param string $module
     * @param string $lang
     * @return array
     */
    function getGuiTranslationsFromFile($module, $lang)
    {
        //  fetch translations from database and cache
        $cache = & SGL_Cache::singleton();
        $lang = SGL_Translation::transformLangID($lang, SGL_LANG_ID_SGL);

        //  returned cached translations else fetch from db and cache
        if ($serialized = $cache->get($module, 'translation_'. $lang)) {
            $words = unserialize($serialized);
            SGL::logMessage('translations from cache', PEAR_LOG_DEBUG);
            return $words;

        } else {

            //  fetch available languages
            $aLanguages = $GLOBALS['_SGL']['LANGUAGE'];

            //  build global lang file
            $langID = str_replace('_', '-', $lang);
            $language = @$aLanguages[$langID][1];
            $globalLangFile = $language .'.php';
            $path = SGL_MOD_DIR . '/' . $module . '/lang/';
            if (is_readable($path . $globalLangFile)) {
                include $path . $globalLangFile;
                if ($module == 'default') {
                    $words = &$defaultWords;
                }
                if (isset($words) && !empty($words)) {
                    $serialized = serialize($words);
                    $cache->save($serialized, $module, 'translation_'. $lang);
                    SGL::logMessage('translations from file', PEAR_LOG_DEBUG);
                    return $words;
                } else {
                    return array();
                }
            } elseif ($module == 'default') {
                SGL::raiseError('could not locate the global language file', SGL_ERROR_NOFILE);
            } else {
                return array();
            }
        }
    }

    /**
     * Update GUI Translations.
     *
     * @static
     * @param array $aTrans hash containing tranlsations to be updated
     * @param string $langID language id
     * @param string $module module
     *
     * @return boolean true on success and PEAR Error on failure
     */

    function updateGuiTranslations($module, $langID, $aTrans)
    {
        switch (strtolower($this->conf['translation']['container'])) {
        case 'db':
            require_once SGL_CORE_DIR . '/Translation.php';
            $trans = &SGL_Translation::singleton('admin');

            $langID = SGL_Translation::transformLangID($langID, SGL_LANG_ID_TRANS2);

            foreach ($aTrans as $key => $value) {
                $string = array($langID => $value);
                $result = $trans->add(stripslashes($key), $module, $string);

                if (is_a($result, 'PEAR_Error')) {
                    return $result;
                }
            }
            return true;
        case 'file':
        default:
            //  read translation data and get reference to root
            $c = new Config();
            foreach ($aTrans as $key => $value) {
                $aTransStrip[stripslashes($key)] = $value;
            }
            $root = & $c->parseConfig($aTransStrip, 'phparray');

            $langID = SGL_Translation::transformLangID($langID, SGL_LANG_ID_SGL);

            //  write translation to file
            $filename = SGL_MOD_DIR . '/' . $module . '/lang/' .
                $GLOBALS['_SGL']['LANGUAGE'][$langID][1] . '.php';
            $arrayName = ($module == 'default') ? 'defaultWords' : 'words';
            $result = $c->writeConfig($filename, 'phparray', array('name' => $arrayName));
            if (is_a($result, 'PEAR_Error')) {
                return $result;
            }
            return true;
        }
    }

    /**
     * Returns a dictionary of translated strings from the db.
     *
     * @static
     * @param string $module
     * @param string $lang
     * @param string $fallbackLang
     * @return array
     */
    function getTranslations($module, $lang, $fallbackLang = false)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        if (!empty($module) && !empty($lang)) {
            $lang = SGL_Translation::transformLangID($lang, SGL_LANG_ID_TRANS2);
            $installedLangs = explode(',', $conf['translation']['installedLanguages']);

            if ($conf['translation']['container'] == 'db'
                && in_array($lang, $installedLangs)) {
                //  instantiate translation2 object
                $translation = &SGL_Translation::singleton();

                //  set language
                $langInstalled = $translation->setLang($lang);

                //  set translation group
                $translation->setPageID($module);

                //  create decorator for fallback language
                if ($fallbackLang) {
                    $fallbackLang = (is_string($fallbackLang))
                        ? $fallbackLang
                        : $conf['translation']['fallbackLang'];

                    $translation = & $translation->getDecorator('Lang');
                    $translation->setOption('fallbackLang', $fallbackLang);
                }
                //  instantiate cachelite decorator and set options
                if ($conf['cache']['enabled']) {
                    $translation = &$translation->getDecorator('CacheLiteFunction');
                    $translation->setOption('cacheDir', SGL_TMP_DIR .'/');
                    $translation->setOption('lifeTime', $conf['cache']['lifetime']);
                    $translation->setOption('defaultGroup', 'translation');
                }

                //  fetch translations
                $words = ($words = $translation->getPage()) ? $words : array();

                SGL::logMessage('translations from db for '. $module, PEAR_LOG_DEBUG);
                return $words;
            } elseif ($conf['translation']['container'] == 'file') {
                return  SGL_Translation::getGuiTranslationsFromFile($module, $lang);
            } else {
                return array();
            }
        } else {
            return SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__,
                SGL_ERROR_INVALIDARGS);
        }
    }

    /**
     * Returns language ID for currently active lang.
     *
     * @static
     * @return string
     */
    function getLangID()
    {
        if ($langID = str_replace('-', '_', SGL::getCurrentLang() .'_'. $GLOBALS['_SGL']['CHARSET'])) {
            return $langID;
        } else {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            return $conf['translation']['fallbackLang'];
        }
    }

    /**
     * Returns default lang as set in installer.
     *
     * @static
     * @return string
     */
    function getFallbackLangID()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        return $conf['translation']['fallbackLang'];
    }

    /**
     * Transform langID to opposite format
     *
     * SGL_LANG_ID_SGL - en-iso-8859-15
     * SGL_LANG_ID_TRANS2 - en_iso_8859_15
     *
     * @static
     * @param string langID language id
     * @param int format language id format
     * @return langID string
     */
     function transformLangID($langID, $format = null)
     {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        if (isset($format)) {
            $langID = ($format == SGL_LANG_ID_SGL)
                ? str_replace('_', '-', $langID)
                : str_replace('-', '_', $langID);

            return $langID;
        } else {
            $langID = (strstr($langID, '-'))
                ? str_replace('-', '_', $langID)
                : str_replace('_', '-', $langID);
            return $langID;
        }
    }

    /**
     * Remove all translations for all languages for specified module.
     *
     * @static
     * @param  string  $moduleName  module/page name
     * @return boolean
     */
    function removeTranslations($moduleName)
    {
        $trans  = &SGL_Translation::singleton('admin');
        $aPages = $trans->getPageNames();
        if (PEAR::isError($aPages)) {
            return $aPages;
        }
        if (!in_array($moduleName, $aPages)) {
            return false; // no translations
        }
        $aLangs = $trans->getLangs('ids');
        if (PEAR::isError($aLangs)) {
            return $aLangs;
        }
        $aStrings = array();
        foreach ($aLangs as $langId) {
            $ret = SGL_Translation::getTranslations($moduleName, $langId);
            $aStrings = array_merge($aStrings, array_keys($ret));
        }
        foreach ($aStrings as $stringId) {
            $ret = $trans->remove($stringId, $moduleName);
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }
        return true;
    }
}
?>