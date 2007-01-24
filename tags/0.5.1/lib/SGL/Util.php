<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
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
            $order = SGL_HTTP_Session::get('sortOrder');
        } elseif ($sortOrder == 'ASC') {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }
        //  update session
        SGL_HTTP_Session::set('sortOrder', $order);
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
            $sessSortBy = SGL_HTTP_Session::get('sortByGrp');
            break;
        case SGL_SORTBY_USER:
            $sortByType = 'User';
            $sessSortBy = SGL_HTTP_Session::get('sortByUser');
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
        SGL_HTTP_Session::set($sessVar, $sortBy);
        return $sortBy;
    }

    /**
     * Fetches the .css files in theme/css/ into array of form:
     * filename => filename [without ".css" extension] for use by
     * Output->generateSelect()
     *
     * @return  array of .css files from www/css
     * @access  private
     */
    function getStyleFiles($curStyle = false)
    {
        $aFiles = array();
        $theme = $_SESSION['aPrefs']['theme'];
        //  get array of files in /www/css/
        if ($fh = opendir(SGL_THEME_DIR . "/$theme/css/")) {
            while (false !== ($file = readdir($fh))) {

                //  remove unwanted dir elements
                if ($file == '.' || $file == '..' || $file == 'CVS') {
                    continue;
                }
                //  and anything without .nav.php extension
                $ext = substr($file, -8);
                if ($ext != '.nav.php') {
                    continue;
                }

                $filename = substr($file, 0, strpos($file, '.'));

                //  if $curStyle is not false, we need an array of hashes for NavStyleMgr
                if ($curStyle) {
                    $aFiles[$filename]['currentStyle'] = ($filename == $curStyle) ? true : false;
                    $aFiles[$filename]['fileMtime'] =  strftime('%Y-%b-%d %H:%M:%S', 
                        filemtime(SGL_THEME_DIR . '/' . $theme . '/css/' . $file));

                //  otherwise a simple hash will do for ConfigMgr
                } else {
                    $aFiles[$filename] = $filename;
                }
            }
            closedir($fh);
        } else {
            SGL::raiseError('There was a problem reading the navigation style dir', 
                SGL_ERROR_INVALIDFILEPERMS);
        }
        return $aFiles;
    }

    function getAllThemes()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';
        //  match all folders except CVS
        $ret = SGL_Util::listDir(SGL_THEME_DIR, FILE_LIST_DIRS, $sort = FILE_SORT_NONE, 
                create_function('$a', 'return preg_match("/[^CVS]/", $a);'));
        return $ret;
    }


    function getAllModuleDirs()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';

        //  match all folders except CVS
        $ret = SGL_Util::listDir(SGL_MOD_DIR, FILE_LIST_DIRS, FILE_SORT_NAME, 
                create_function('$a', 'return preg_match("/[^CVS]/", $a);'));

        //  until i get rid of this folder
        unset($ret['wizardExample']);
        return $ret;
    }

    function getAllFilesPerModule($moduleDir)
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
        $files = SGL_Util::getAllFilesPerModule($moduleDir);
        
        //  remap 'ContactUsMgr.php => ContactUsMgr' hash to array
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
        
        if (file_exists($filePath)) {
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

    function getAllNavDrivers()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'File/Util.php';
        $navDir = SGL_MOD_DIR . '/navigation/classes';

        //  match files with *Nav.php format
        $ret = SGL_Util::listDir($navDir, FILE_LIST_FILES, $sort = FILE_SORT_NONE, 
                create_function('$a', 'return preg_match("/.*Nav\.php$/", $a);'));

        //  parse out filename w/o extension and .
        array_walk($ret, create_function('&$a', 'preg_match("/^.*Nav/", $a, $matches); $a =  $matches[0]; return true;'));

        //  propagate changes to keys as well
        $aDrivers = array();
        foreach ($ret as $k => $v) {
            $aDrivers[$v] = $v;
        }       
        return $aDrivers;
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
        $aFiles = File_Util::listDir($path, $list, $sort, $cb);
        $aRet = array();
        foreach ($aFiles as $oFile) {
            $aRet[$oFile->name] = $oFile->name;
        }
        return $aRet;
    }
}
?>