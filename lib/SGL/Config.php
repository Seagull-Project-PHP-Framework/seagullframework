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
// | Seagull 0.9                                                               |
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
class SGL_Config
{
    protected static $_aProps = array();
    protected $_fileName;
    private static $_instance = null;

    public function __construct($autoLoad = false)
    {
        if ($this->isEmpty() && $autoLoad) {

            $siteName   = 'seagull_trunk';
            $configFile = SGL_PATH  . '/var/' . $siteName . '.conf.php';
            if (!is_file($configFile)) {
                $confMapFile = SGL_PATH  . '/var/confmap.php';
                $configFile  = null;
                if ($ok = require_once $confMapFile) {
                    foreach ($confMap as $key => $value) {
                        if (preg_match("/^$key$/", $siteName, $aMatches)) {
                            $configFile = $value;
                            break;
                        }
                    }
                }
                if ($configFile) {
                    $configFile = SGL_PATH  . '/var/' . $configFile;
                }
            }
            $conf = $this->load($configFile);
            $this->_fileName = $configFile;
            $this->replace($conf);
        }
    }

    public static function singleton($autoLoad = true)
    {
        if (!self::$_instance) {
            $class = __CLASS__;
            self::$_instance = new $class($autoLoad);
        }
        return self::$_instance;
    }

    /**
     * Returns true if config key exists.
     *
     * @param mixed $key string or array
     * @return boolean
     */
    public function exists($key)
    {
        if (is_array($key)) {
            $key1 = key($key);
            $key2 = $key[$key1];
            return isset(self::$_aProps[$key1][$key2]);
        } else {
            return isset(self::$_aProps[$key]);
        }
    }

    public static function get($key)
    {
        $aKeys = split('\.', trim($key));
        if (isset($aKeys[0]) && isset($aKeys[1]) && isset(self::$_aProps[$aKeys[0]][$aKeys[1]])) {
            $ret = self::$_aProps[$aKeys[0]][$aKeys[1]];
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Sets a config property.
     *
     * Using new shorthand method you can do $ok = SGL_Config::set('river.boat', 'green');
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     * @todo define add() and remove() methods, set() should only set existing keys
     */
    public function set($key, $value)
    {
        $ret = false;
        if (is_string($key) && is_scalar($value)) {
            $aKeys = split('\.', trim($key));

            //  it's a static call
            if (isset($aKeys[0]) && isset($aKeys[1])) {
                $c = SGL_Config::singleton();
                $ret = $c->set($aKeys[0], array($aKeys[1] => $value));
            //  else it's an object call with scalar second arg
            } else {
                $this->_aProps[$key] = $value;
                $ret = true;
            }
        }
        return $ret;
    }

    /**
     * Remove a config key, save() must be used to persist changes.
     *
     * To remove the key $conf['site']['blocksEnabled'] = true, you would use
     * $c->remove(array('site', 'blocksEnabled')
     *
     * @param array $key    a) A 2 element array: element one for the section, element
     *                         2 for the section key
     *                      b) a key - the whole section will be removed
     * @return mixed
     * @todo in 0.7 make this consistent with $c->get()
     */
    public function remove($key)
    {
        if (is_array($key)) {
            list($key1, $key2) = $key;
            unset(self::$_aProps[$key1][$key2]);
        } else {
            unset(self::$_aProps[$key]);
        }
        return true;
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
    public function getAll()
    {
        return self::$_aProps;
    }

    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Reads in data from supplied $file.
     *
     * @param string $file
     * @param boolean $force If force is true, master  config file is read, not cached one
     * @return mixed An array of data on success, PEAR error on failure.
     */
    public function load($file, $force = false)
    {
        //  create cached copy if module config and cache does not exist
        //  if file has php extension it must be global config
        if (defined('SGL_INSTALLED')) {
            if (substr($file, -3, 3) != 'php') {
                if (!$force) {
                    $cachedFileName = $this->_getCachedFileName($file);
                    if (!is_file($cachedFileName)) {
                        $ok = $this->_createCachedFile($cachedFileName);
                    }
                    //  ensure module config reads are done from cached copy
                    $file = $cachedFileName;
                }
            }
        }
        $ph = SGL_ParamHandler::singleton($file);
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
        $cachedFileName = SGL_VAR_DIR . '/config/' .$moduleName.'.ini';
        return $cachedFileName;
    }

    protected function _ensureCacheDirExists()
    {
        $varConfigDir = SGL_VAR_DIR . '/config';
        if (!is_dir($varConfigDir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $varConfigDir));
            @chmod($varConfigDir, 0777);
        }
    }

    protected function _getModulesDir()
    {
        static $modDir;
        if (is_null($modDir)) {
            //  allow for custom modules dir
            $c = SGL_Config::singleton();
            $customModDir = $c->get(array('path' => 'moduleDirOverride'));
            $modDir = !empty($customModDir)
                ? $customModDir
                : 'modules';
        }
        return $modDir;
    }

    protected function _createCachedFile($cachedModuleConfigFile)
    {
        $filename = basename($cachedModuleConfigFile);
        list($module, $ext) = split('\.', $filename);
        $masterModuleConfigFile = SGL_MOD_DIR . "/$module/conf.ini";
        $this->_ensureCacheDirExists();
        $ok = copy($masterModuleConfigFile, $cachedModuleConfigFile);
        return $ok;
    }

    public function save($file = null)
    {
        if (is_null($file)) {
            if (empty($this->_fileName)) {
                throw new Exception('No filename specified', SGL_ERROR_NOFILE);
            }
            $file = $this->_fileName;
        }
        //  determine if we're saving a module config file
        //  $file is only defined for module config saving
        if ($file != $this->_fileName) {
            $modDir = $this->_getModulesDir();

            if (stristr($file, $modDir) || stristr($file, 'modules')) {
                $this->_ensureCacheDirExists();
                $file = $this->_getCachedFileName($file);
            }
        }
        $ph = SGL_ParamHandler::singleton($file);
        return $ph->write(self::$_aProps);
    }

    public function merge($aConf)
    {
        $this->_aProps = SGL_Array2::mergeReplace(self::$_aProps, $aConf);
    }

    /**
     * Returns true if the current config object contains no data keys.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return count(self::$_aProps) ? false : true;
    }

    /**
     * Ensures the module's config file was loaded and returns an array
     * containing the global and modulde config.
     *
     * This is required when the homepage is set to custom mod/mgr/params,
     * and the module config file loaded while initialising the request is
     * not the file required for the custom invocation.
     *
     * @param string $moduleName
     * @return mixed    array on success, PEAR_Error on failure
     * @todo this should be hanlded internally
     */
    public function ensureModuleConfigLoaded($moduleName)
    {
        $ret = false;
        if (!defined('SGL_MODULE_CONFIG_LOADED')
                || (isset($this->_aProps['localConfig']['moduleName']) &&
                $this->_aProps['localConfig']['moduleName'] != $moduleName)) {
            $path = SGL_MOD_DIR . '/' . $moduleName . '/conf.ini';
            $modConfigPath = realpath($path);

            if (SGL_File::exists($modConfigPath)) {
                $aModuleConfig = $this->load($modConfigPath);

                if (PEAR::isError($aModuleConfig)) {
                    $ret = $aModuleConfig;
                } else {
                    //  merge local and global config
                    $this->merge($aModuleConfig);

                    //  set local config module name.
                    $this->set('localConfig', array('moduleName' => $moduleName));

                    //  return global & module config
                    $ret = $this->getAll();
                }
            } else {
//                throw new Exception("Config file could not be found at '$path'",
//                    SGL_ERROR_NOFILE);
            }
        } else {
            $ret = $this->getAll();
        }
        return $ret;
    }
}
?>