<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | NestedSet.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Alexander J. Tarachanowicz II <ajt@localhype.net>               |
// +---------------------------------------------------------------------------+
// $Id: Translation.php,v 1.0 2005/05/11 00:00:00 demian Exp $

require_once 'Translation2.php';
                
/**
 * A wrapper to PEAR Translation2.
 *
 * @package SGL
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.0 $
 * @since   PHP 4.1
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
     *  o multiple (default) - all translations in a seperate table (translation_en, translation_pl, translation_de)
     * 
     * @access  public 
     * @param	string	$lang			language to return translations
     * @param	string	$type			type of object: translation or admin 
     * @return	object	$translation	Translation2 object
     * 
     * 
     */
    function &singleton($type = 'translation')
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  set translation table parameters
        $params = array(
            'langs_avail_table' => 'langs',
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

        //  retreive DSN  
        $dsn = SGL_DB::getDsn('SGL_DSN_ARRAY');        
     
        //  create translation storage tables
        if ($conf['table']['translation']) {
            
            //  fetch translation table prefix
            $prefix = $conf['table']['translation'] .'_';

            //  fetch languages
            $langs = $GLOBALS['_SGL']['INSTALLED_LANGUAGES'];
            
            //  set params
            foreach ($langs as $aKey => $aValue) {
                $params['strings_tables'][$aKey] = $prefix . $aKey;
            }

        } else {
            SGL::raiseError('translation table not specified check global config ', 
                SGL_ERROR_INVALIDCONFIG, PEAR_ERROR_DIE);
        }
                           
        //  instantiate selected translation2 object
        switch (strtolower($type)) {
            
        case 'admin':
            require_once 'Translation2/Admin.php';
            $oTranslation = &Translation2_Admin::factory($driver, $dsn, $params);
            break;
            
        case 'translation':
        
        default:
            $oTranslation = &Translation2::factory($driver, $dsn, $params);
        }        

        return $oTranslation;                
    }
    

    function getGuiTranslationsFromFile($module, $lang)
    {
        //  fetch translations from database and cache
        $cache = &SGL::cacheSingleton();
        
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
            $language = $aLanguages[$langID][1];
            $globalLangFile = $language .'.php';     
            $path = SGL_MOD_DIR . '/' . $module . '/lang/';
            if (is_readable($path . $globalLangFile)) {
                include $path . $globalLangFile;
                if ($module == 'default') {
                    $words = $defaultWords;
                }
                $serialized = serialize($words);
                $cache->save($serialized, $module, 'translation_'. $lang);
                SGL::logMessage('translations from file', PEAR_LOG_DEBUG);  
                return $words;
                
            } elseif ($module == 'default') {
                SGL::raiseError('could not locate the global language file', SGL_ERROR_NOFILE);
            }
        }
    }
    
    
    function getTranslations($module, $lang, $fallbackLang = false) 
    {
        if (!empty($module) && !empty($lang)) {
            $conf = &$GLOBALS['_SGL']['CONF'];
            
            //  fallback lang clause
            $fallbackLang = ($fallbackLang) ? $fallbackLang : $conf['translation']['fallbackLang'];            

            //  if langauge not installed resort to fallback
            if (!array_key_exists($lang, $GLOBALS['_SGL']['INSTALLED_LANGUAGES'])) {
                $lang = $fallbackLang;
            }
                            
            //  instantiate translation2 object
            $translation = &SGL_Translation::singleton();
                           
            //  set language
            $langInstalled = $translation->setLang($lang);
                            
            //  set translation group
            $translation->setPageID($module);
                                               
            //  create decorator for fallback language
            if ($lang !== $fallbackLang && $fallbackLang) {
                $translation = & $translation->getDecorator('Lang');
                $translation->setOption('fallbackLang', $fallbackLang);
            }

            //  instantiate cachelite decorator and set options
            if ($conf['cache']['enabled']) {
                $translation = &$translation->getDecorator('CacheLiteFunction');
                $translation->setOption('cacheDir', SGL_TMP_DIR .'/');
                $translation->setOption('lifeTime', $conf['cache']['lifetime']);
            }
            
            //  fetch translations
            $words = $translation->getPage();
            
            SGL::logMessage('translations from db for '. $module, PEAR_LOG_DEBUG);

            return $words;
           
        } else {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_INVALIDARGS);    
        }
    }
    
    function getLangID()
    {
        return str_replace('-', '_', SGL::getCurrentLang() .'_'. $GLOBALS['_SGL']['CHARSET']);
    }

}
?>