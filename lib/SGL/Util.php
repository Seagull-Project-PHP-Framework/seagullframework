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
// | Util.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Util.php,v 1.22 2005/05/11 00:19:40 demian Exp $

/**
 * Various utility methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.22 $
 */
class SGL_Util
{
    // +---------------------------------------+
    // | Column-sorting methods                |
    // +---------------------------------------+

    /**
     * Used by list pages to determine last sort order.
     *
     * If no value passed from Request, returns last value
     * from session
     *
     * @access  public
     * @param   string  $sortOrder  Output object containing validated input
     * @return  string  $order
     */
    function getSortOrder($sortOrder)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if ($sortOrder == '') {
            $order = SGL_Session::get('sortOrder');
        } elseif ($sortOrder == 'ASC') {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }
        //  update session
        SGL_Session::set('sortOrder', $order);
        return $order;
    }

    /**
     * Determines which column results should be sorted by.
     *
     * If no value passed from Request, returns last value
     * from session
     *
     * @access  public
     * @param   string  $frmSortBy      column name passed from Request
     * @param   int     $callingPage    table relevant to sortby
     * @return  string  $sortBy         value to sort by
     */
    function getSortBy($frmSortBy, $callingPage)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        switch ($callingPage) {

        case SGL_SORTBY_GRP:
            $sortByType = 'Grp';
            $sessSortBy = SGL_Session::get('sortByGrp');
            break;

        case SGL_SORTBY_USER:
            $sortByType = 'User';
            $sessSortBy = SGL_Session::get('sortByUser');
            break;
        }
        if ($frmSortBy == '' && $sessSortBy == '') {
            //  take default set in child class
            $sortBy = $this->sortBy;
        } elseif ($frmSortBy == '') {
            $sortBy = $sessSortBy;
        } else {
            $sortBy = $frmSortBy;
        }
        //  update session
        $sessVar = 'sortBy' . $sortByType;
        SGL_Session::set($sessVar, $sortBy);
        return $sortBy;
    }

    function getAllThemes()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';
        //  match all folders except admin themes
        $ret = SGL_Util::listDir(SGL_THEME_DIR, FILE_LIST_DIRS, $sort = FILE_SORT_NONE,
                array('SGL_Util', 'themeFilterCallback'));
        return $ret;
    }

    function themeFilterCallback($str)
    {
         return (stristr($str, '_admin')) ? false : true;
    }

    /**
     * Returns an hash of strings listing the installed (and registered) Modules
     *
        Array
        (
            [block] => block
            [cms] => cms
            [default] => default
            [event] => event
            [export] => export
            [navigation] => navigation
            [user] => user
        )
     * @static
     * @access  public
     * @return  array
     * @param   bool $onlyRegistered
     */

    function getAllModuleDirs($onlyRegistered = true)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';

        //  match all folders except CVS
        $ret = SGL_Util::listDir(SGL_MOD_DIR, FILE_LIST_DIRS, FILE_SORT_NAME,
            create_function('$a', 'return preg_match("/[^CVS]/", $a);'));

        foreach ($ret as $module) {
            $enabled = SGL::moduleIsEnabled($module);
            if (PEAR::isError($enabled)) {
                return $enabled;
            }
            if ($onlyRegistered && !$enabled) {
                unset($ret[$module]);
            }
        }
        return $ret;
    }

    function getAllManagersPerModule($moduleDir)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';

        //  match files with php extension
        $classDir = $moduleDir . '/classes';
        $ret = SGL_Util::listDir($classDir, FILE_LIST_FILES, $sort = FILE_SORT_NAME,
                create_function('$a', 'return preg_match("/.*Mgr\.php$/", $a);'));

        //  parse out filename w/o extension and .
        array_walk($ret, create_function('&$a', 'preg_match("/^.*Mgr/", $a, $matches); $a =  $matches[0]; return true;'));

        return $ret;
    }

    function getAllActionMethodsPerMgr($mgr)
    {
        $managerFileName = basename($mgr);
        $moduleDir = dirname(dirname($mgr));
        $files = SGL_Util::getAllManagersPerModule($moduleDir);

        //  remap 'ContactUsMgr.php => ContactUsMgr' hash to array
        $fileNames = array();
        foreach ($files as $k => $file) {
            $fileNames[] = $k;
        }

        $fileNamesLowerCase = array_map('strtolower', $fileNames);
        $isFound = array_search(strtolower($managerFileName), $fileNamesLowerCase);
        $managerFileName = ($isFound !== false) ? $fileNames[$isFound] : false;

        if (!($managerFileName)) {
            return false;
        }

        $filePath = $moduleDir . '/classes/' . $managerFileName;

        if (is_file($filePath)) {
            require_once $filePath;
            $aElems = explode('/', $filePath);
            $last = array_pop($aElems);
            $className = substr($last, 0, -4);

            $obj = new $className(); // extract classname
            $vars = get_object_vars($obj);
            $actions = array_keys($vars['_aActionsMapping']);
            $ret = array();
            foreach ($actions as $k => $action) {
                $ret[$action] = $action;
            }
            return $ret;
        } else {
            return false;
        }
    }

    function getAllClassesFromFolder($folder = '', $filter = '.*')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';

        //  match files with php extension
        $ret = SGL_Util::listDir($folder, FILE_LIST_FILES, $sort = FILE_SORT_NAME,
                create_function('$a', 'return preg_match("/^.*\.php$/", $a);'));

        //  parse out filename w/o extension and .
        $aClasses = array();
        foreach ($ret as $k => $v) {
            if (preg_match("/^($filter)\.php$/", $v, $matches)) {
                $aClasses[$matches[1]] = $matches[1];
            }
        }
        return $aClasses;
    }

    /**
     * Wrapper for the File_Util::listDir method.
     *
     * Instead of returning an array of objects, it returns an array of
     * strings (filenames).
     *
     * The final argument, $cb, is a callback that either evaluates to true or
     * false and performs a filter operation, or it can also modify the
     * directory/file names returned.  To achieve the latter effect use as
     * follows:
     *
     * <code>
     * function uc(&$filename) {
     *     $filename = strtoupper($filename);
     *     return true;
     * }
     * $entries = File_Util::listDir('.', FILE_LIST_ALL, FILE_SORT_NONE, 'uc');
     * foreach ($entries as $e) {
     *     echo $e->name, "\n";
     * }
     * </code>
     *
     * @static
     * @access  public
     * @return  array
     * @param   string  $path
     * @param   int     $list
     * @param   int     $sort
     * @param   mixed   $cb
     */
    function listDir($path, $list = FILE_LIST_ALL, $sort = FILE_SORT_NONE, $cb = null)
    {
        $aRet = array();
        if (is_array($aFiles = File_Util::listDir($path, $list, $sort, $cb))) {
            foreach ($aFiles as $oFile) {
                $aRet[$oFile->name] = $oFile->name;
            }
        }
        return $aRet;
    }

    /**
     * Ini file protection.
     *
     * By giving ini files a php extension, and inserting some PHP die() code,
     * we can improve security in situations where browsers might be able to
     * read them.  Thanks to Georg Gell for the idea.
     *
     * @param string $file  Path to ini file
     */
    function makeIniUnreadable($file)
    {
        $iniFle = file($file);
        $string = ';<?php die("Eat dust"); ?>' . "\n";
        array_unshift($iniFle, $string);
        file_put_contents($file . '.php', implode('', $iniFle));
        //  remove original ini file
        unlink($file);
    }
    /**
     * Returns a hash of the form array('en-iso-8859-15' => 'English (en-iso-8859-15),) etc.
     *
     * @return array
     */
    function getLangsDescriptionMap($aSelected = array(), $langKeyType = null)
    {
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        uasort($availableLanguages, 'SGL_cmp');
        $aLangs = array();
        foreach ($availableLanguages as $id => $tmplang) {
            $langName = ucfirst(substr(strstr($tmplang[0], '|'), 1));
            $keyId = ($langKeyType)
                ? SGL_Translation::transformLangID($id, $langKeyType)
                : $id;
            if (count($aSelected) && in_array($id, $aSelected)) {
                $aSelectedLangs[$keyId] =  $langName . ' (' . $id . ')';
            } else {
                $aLangs[$keyId] =  $langName . ' (' . $id . ')';
            }
        }

        //  if $aSelectedLangs has elements return it.
        $aLangs = (isset ($aSelectedLangs) && !empty($aSelectedLangs))
            ? $aSelectedLangs
            : $aLangs;

        return $aLangs;
    }

    /**
     * Returns params from ini file.
     *
     * @return array
     */
    function loadParams($ini_file = '', $aSavedParams = array(), $aCurrentParams = array())
    {
        //  set default params
        $aReturn = array();
        $details = '';
        $aPreparedParams = array();

        if (is_file($ini_file)) {

            //  get details section
            $aParams = @parse_ini_file($ini_file, true);
            if (array_key_exists('details', $aParams)) {
                $details = (object)$aParams['details'];
                unset($aParams['details']);
            }

            foreach ($aParams as $key => $value) {
                if (is_array($value) && array_key_exists('value',$value)
                    && array_key_exists('type',$value) && array_key_exists('label',$value)) {
                    $value =  array_key_exists($key, $aCurrentParams) ? $aCurrentParams[$key] : '';
                    if ($value) {
                        $aParams[$key]['value'] = $value;
                    } elseif (array_key_exists($key ,$aSavedParams)) {
                        $aParams[$key]['value'] = $aSavedParams[$key];
                    }
                    $aParams[$key]['name'] = 'aParams['.$key.']';
                    if ($aParams[$key]['type'] == 'wysiwyg') {
                        $aReturn['wysiwyg'] = true;
                    }
                    $aPreparedParams[$key] = (object)$aParams[$key];
                }
            }
        }

        $aReturn['details'] = $details;
        $aReturn['aParams'] = $aPreparedParams;
        return $aReturn;
    }

    /**
     * Returns the system's tmp directory.
     *
     * Mactel doesn't supply tmp path in most of the normal places.
     *
     * @return string  Path to tmp dir
     */
    function getTmpDir()
    {
       // Try to get from environment variable
       if (!empty($_ENV['TMP'])) {
           return realpath($_ENV['TMP']);
       } elseif (!empty($_ENV['TMPDIR'])) {
           return realpath($_ENV['TMPDIR']);
       } elseif (!empty($_ENV['TEMP'])) {
           return realpath($_ENV['TEMP']);
       }
       // Detect by creating a temporary file
       else {
           // Try to use system's temporary directory
           // as random name shouldn't exist
           $temp_file = tempnam(md5(uniqid(rand(), true)), '');
           if ($temp_file) {
               $temp_dir = realpath(dirname($temp_file));
               unlink($temp_file);
               return $temp_dir;
           } else {
               return false;
           }
       }
    }
}
?>