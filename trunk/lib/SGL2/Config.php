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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// | Config.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Controller.php,v 1.49 2005/06/23 19:15:25 demian Exp $

/**
 * Config file parsing and handling, acts as a registry for config data.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.5 $
 */
class SGL2_Config
{
    protected static $_aProps = array();
    protected $_fileName;

    public function __construct()
    {
        if ($this->isEmpty()) {

            $siteName   = 'seagull_trunk';
            $configFile = SGL2_PATH  . '/var/' . $siteName . '.conf.php';
            if (!SGL2_File::exists($configFile)) {
                $confMapFile = SGL2_PATH  . '/var/confmap.php';
                $configFile  = null;
                if ($confMap = SGL2_File::load($confMapFile)) {
                    foreach ($confMap as $key => $value) {
                        if (preg_match("/^$key$/", $siteName, $aMatches)) {
                            $configFile = $value;
                            break;
                        }
                    }
                }
                if ($configFile) {
                    $configFile = SGL2_PATH  . '/var/' . $configFile;
                }
            }
            $conf = $this->load($configFile);
            $this->_fileName = $configFile;
            $this->replace($conf);
        }
    }


    public static function get($key, $default = false)
    {
        list($dim1, $dim2) = split('\.', trim($key));
        if (isset($dim1) && isset($dim2) && isset(self::$_aProps[$dim1][$dim2])) {
            $ret = self::$_aProps[$dim1][$dim2];
            if (empty($ret)) {
                $ret = false;
            }
        } else {
            $ret = $default;
        }
        return $ret;
    }

    /**
     * Sets a config property.
     *
     * Example usage:  $ok = SGL2_Config::set('river.boat', 'green');
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function set($key, $value)
    {
        $ret = false;
        if (is_string($key) && is_scalar($value)) {
            list($dim1, $dim2) = split('\.', trim($key));
            if (isset($dim1) && isset($dim2)) {
                if (isset(self::$_aProps[$dim1][$dim2])) {
                    self::$_aProps[$dim1][$dim2] = $value;
                    $ret = true;
                }
            }
        }
        return $ret;
    }

    /**
     * Returns true if the current config object contains no data keys.
     *
     * @return boolean
     */
    public static function isEmpty()
    {
        return count(self::$_aProps) ? false : true;
    }

    public function merge($aConf)
    {
        self::$_aProps = SGL2_Array::mergeReplace(self::$_aProps, $aConf);
    }

    public function replace($aConf)
    {
        self::$_aProps = $aConf;
    }

    /**
     * Return an array of all Config properties.
     *
     * @return array
     */
    public static function getAll()
    {
        return self::$_aProps;
    }

    public function reset()
    {
        self::$_aProps = null;
    }

    public static function count()
    {
        return count(self::$_aProps);
    }

    /**
     * Reads in data from supplied $file.
     *
     * @param string $file
     * @param boolean $force If force is true, master  config file is read, not cached one
     * @return mixed An array of data on success
     */
    public function load($file, $force = false)
    {
        if (!strlen($file)) {
            throw new Exception('A filename must be provided');
        }
        if (!SGL2_File::exists($file)) {
            throw new Exception('The provided filename does not exist or is not readable:' . $file);
        }
        //  create cached copy if module config and cache does not exist
        //  if file has php extension it must be global config
        if (defined('SGL2_INSTALLED')) {
            if (substr($file, -3, 3) != 'php') {
                if (!$force) {
                    $cachedFileName = $this->_getCachedFileName($file);
                    if (!SGL2_File::exists($cachedFileName)) {
                        $ok = $this->_createCachedFile($cachedFileName);
                    }
                    //  ensure module config reads are done from cached copy
                    $file = $cachedFileName;
                }
            }
        }
        $ph = SGL2_ParamHandler::singleton($file);
        $data = $ph->read();
        if ($data !== false) {
            return $data;
        } else {
            throw new Exception('Problem reading config file');
        }
    }

    protected function _getCachedFileName($path)
    {
        /*
        get module name - expecting:
            Array
            (
                [0] => /foo/bar/baz/mymodules/conf.ini
                [1] => /foo/bar/baz
                [2] => mymodules
                [3] => conf.ini
            )
        */

        // make Windows and Unix paths consistent
        $path = str_replace('\\', '/', $path);

        //  if file is called conf.ini, it's a template from root of module
        //  dir and needs to be cached
        if (basename($path) != 'conf.ini') {
            return $path;
        }

        preg_match("#(.*)\/(.*)\/(conf.ini)$#", $path, $aMatches);
        $moduleName = $aMatches[2];

        //  ensure we operate on copy of master
        $cachedFileName = SGL2_VAR_DIR . '/config/' .$moduleName.'.ini';
        return $cachedFileName;
    }

    /**
     * Enter description here...
     * @todo move to SGL2_File
     *
     */
    protected function _ensureCacheDirExists()
    {
        $varConfigDir = SGL2_VAR_DIR . '/config';
        if (!is_dir($varConfigDir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $varConfigDir));
            @chmod($varConfigDir, 0777);
        }
    }

    protected function _createCachedFile($cachedModuleConfigFile)
    {
        $filename = basename($cachedModuleConfigFile);
        list($module, $ext) = split('\.', $filename);
        $masterModuleConfigFile = SGL2_MOD_DIR . "/$module/conf.ini";
        $this->_ensureCacheDirExists();
        $ok = copy($masterModuleConfigFile, $cachedModuleConfigFile);
        return $ok;
    }
}
?>