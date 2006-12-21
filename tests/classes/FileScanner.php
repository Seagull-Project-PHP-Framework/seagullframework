<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2003-2005 m3 Media Services Limited                         |
// | For contact details, see: http://www.m3.net/                              |
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
// | FileScanner.php                                                           |
// +---------------------------------------------------------------------------+
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

require_once STR_PATH . '/lib/SGL.php';

/**
 * A class for locating test files in different ways.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */
class STR_FileScanner
{
    /**
     * A method to scan a folder (and all sub-folders) and find all
     * tests relating to a supplied layer.
     *
     * @param string $type The type of test being run (eg. "unit").
     * @param string $code The layer's code (eg. "dal").
     * @param string $folder Base folder name to begin search from.
     * @return mixed An array containing all of the files found that
     *               match the layer test code supplied.
     */
    function getTestFiles($type, $code, $folder)
    {
        $files = array();
        $dh = opendir($folder);
        if ($dh) {
            while (false !== ($file = readdir($dh))) {
                if (($file == '.') || ($file == '..') || ($file == '.svn')) {
                    continue;
                }
                // Is the file another directory?
                if (is_dir($folder . '/' . $file)) {
                    $files = array_merge($files, STR_FileScanner::getTestFiles($type, $code, $folder . '/' . $file));
                }
            }
            closedir($dh);
        }
        // Can we open a tests directory?
        $dh = @opendir($folder . '/' . constant($type . '_TEST_STORE'));
        if ($dh) {
            while (($file = readdir($dh)) !== false) {

                // Does the filename match?
                if (preg_match("/[^.]+\.$code\.php/", $file)) {
                    // Strip the STR_PATH from the folder before storing
                    $storeFolder = preg_replace('#' . str_replace('\\', '\\\\', STR_PATH) . '/#', '', $folder);
                    $files[$storeFolder][] = $file;
                }
            }
            closedir($dh);
            if (isset($storeFolder) && count($files[$storeFolder]) > 1) {
                asort($files[$storeFolder]);
            }
        }
        //  filter out disabled modules
        $c = SGL_Config::singleton();
        $moduleDir = ($c->get(array('path' => 'moduleDirOverride')))
            ? $c->get(array('path' => 'moduleDirOverride'))
            : 'modules';
        if (count($files)) {
            foreach ($files as $key => $tests) {
                if (stristr($key, $moduleDir)) {
                    $moduleName = substr($key, strlen($moduleDir) +1);
                    if (!SGL::moduleIsEnabled($moduleName)) {
                        unset($files[$key]);
                    }
                }
            }
        }
        return $files;
    }

    /**
     * A method to get all test files in the Max project for a specified layer.
     *
     * @param string $type The type of test being run (eg. "unit").
     * @param $layer string The layer code.
     * @return mixed An array containing the details of all the test files
     *               in the project for the specified layer.
     */
    function getLayerTestFiles($type, $layer)
    {
        $tests = array();
        foreach ($GLOBALS['_STR']['directories'] as $path) {
            if (empty($tests[$layer])) {
                $tests[$layer] = array();
            }
            $tests[$layer] = array_merge($tests[$layer],
                STR_FileScanner::getTestFiles($type, $layer, STR_PATH.'/'.$path));
        }
        return $tests;
    }


//    function getTestFiles($type, $code, $folder)
//    {
//        require_once 'File/Util.php';
//        //  match all folders except SVN
//        $ret = STR_FileScanner::listDir($folder, FILE_LIST_DIRS, $sort = FILE_SORT_NONE,
//                create_function('$a', 'return preg_match("/[^\.svn]/", $a);'));
//        return $ret;
//    }


    /**
     * A method to get all test files in the Max project.
     *
     * @param string $type The type of test being run (eg. "unit").
     * @return mixed An array containing the details of all the test files
     *               in the Max project.
     */
    function getAllTestFiles($type)
    {
        $tests = array();
        foreach ($GLOBALS['_STR'][$type . '_layers'] as $layer => $data) {
            foreach ($GLOBALS['_STR']['directories'] as $path) {
                if (empty($tests[$layer])) {
                    $tests[$layer] = array();
                }
                $tests[$layer] = array_merge($tests[$layer],
                    STR_FileScanner::getTestFiles($type, $layer, STR_PATH.'/'.$path.''));
            }
        }
        return $tests;
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
