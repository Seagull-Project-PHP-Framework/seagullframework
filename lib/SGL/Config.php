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
    var $aProps = array();
    var $fileName;

    function SGL_Config($autoLoad = false)
    {
        $this->aProps = array();
        if ($this->isEmpty() && $autoLoad) {
            $configFile = SGL_VAR_DIR  . '/'
                . SGL_Task_SetupPaths::hostnameToFilename() . '.conf.php';
            $conf = $this->load($configFile);
            $this->fileName = $configFile;
            $this->replace($conf);
        }
    }

    function &singleton($autoLoad = true)
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class($autoLoad);
        }
        return $instance;
    }

    /**
     * Returns true if config key exists.
     *
     * @param mixed $key string or array
     * @return boolean
     */
    function exists($key)
    {
        if (is_array($key)) {
            $key1 = key($key);
            $key2 = $key[$key1];
            return isset($this->aProps[$key1][$key2]);
        } else {
            return isset($this->aProps[$key]);
        }
    }

    function get($key)
    {
        if (is_array($key)) {
            $key1 = key($key);
            $key2 = $key[$key1];
            return $this->aProps[$key1][$key2];
        } else {
            return $this->aProps[$key];
        }
    }

    function set($key, $value)
    {
        if (isset($this->aProps[$key])
                && is_array($this->aProps[$key])
                && is_array($value)) {
            $key2 = key($value);
            $this->aProps[$key][$key2] = $value[$key2];
        } else {
            $this->aProps[$key] = $value;
        }
    }

    /**
     * Remove a config key, save() must be used to persist changes.
     *
     * To remove the key $conf['site']['blocksEnabled'] = true, you would use
     * $c->remove(array('site', 'blocksEnabled').
     *
     * @param array $aKey   A 2 element array: element one for the section, element
     *                      2 for the section key.
     * @return mixed
     */
    function remove($aKey)
    {
        if (!is_array($aKey)) {
            return SGL::raiseError('Array arg expected',
                SGL_ERROR_INVALIDARGS);
        }
        list($key1, $key2) = $aKey;
        unset($this->aProps[$key1][$key2]);
        return true;
    }

    function replace($aConf)
    {
        $this->aProps = $aConf;
    }

    /**
     * Return an array of all Config properties.
     *
     * @return array
     */
    function getAll()
    {
        return $this->aProps;
    }

    function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Reads in data from supplied $file.
     *
     * @param string $file
     * @return mixed An array of data on success, PEAR error on failure.
     */
    function load($file)
    {
        //  create cached copy if module config and cache does not exist
        //  if file has php extension it must be global config
        if (defined('SGL_INSTALLED')) {
            if (substr($file, -3, 3) != 'php') {
                $cachedFileName = $this->getCachedFileName($file);
                if (!is_file($cachedFileName)) {
                    $ok = $this->createCachedFile($cachedFileName);
                }
                //  ensure module config reads are done from cached copy
                $file = $cachedFileName;
            }
        }
        $ph = &SGL_ParamHandler::singleton($file);
        $data = $ph->read();
        if ($data !== false) {
            return $data;
        } else {
            if (defined('SGL_INITIALISED')) {
                return SGL::raiseError('Problem reading config file',
                    SGL_ERROR_INVALIDFILEPERMS);
            } else {
                SGL::displayStaticPage('No global config file could be found, '.
                    'file searched for was ' .$file);
            }
        }
    }

    function getCachedFileName($path)
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
        $modDirPath = str_replace('\\', '/', SGL_MOD_DIR);

        // configuration path must be within SGL_MOD_DIR
        if (strpos($path, $modDirPath) === false) {
            return $path;
        }

        preg_match("#(.*)\/(.*)\/(conf.ini)$#", $path, $aMatches);
        $moduleName = $aMatches[2];

        //  ensure we operate on copy of master
        $cachedFileName = SGL_VAR_DIR . '/config/' .$moduleName.'.ini';
        return $cachedFileName;
    }

    function ensureCacheDirExists()
    {
        $varConfigDir = SGL_VAR_DIR . '/config';
        if (!is_dir($varConfigDir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $varConfigDir));
            @chmod($varConfigDir, 0777);
        }
    }

    function getModulesDir()
    {
        static $modDir;
        if (is_null($modDir)) {
        //  allow for custom modules dir
            $c = &SGL_Config::singleton();
            $customModDir = $c->get(array('path' => 'moduleDirOverride'));
            $modDir = !empty($customModDir)
                ? $customModDir
                : 'modules';
        }
        return $modDir;
    }

    function createCachedFile($cachedModuleConfigFile)
    {
        $filename = basename($cachedModuleConfigFile);
        list($module, $ext) = split('\.', $filename);
        $masterModuleConfigFile = SGL_MOD_DIR . "/$module/conf.ini";
        $this->ensureCacheDirExists();
        $ok = copy($masterModuleConfigFile, $cachedModuleConfigFile);
        return $ok;
    }

    function save($file = null)
    {
        if (is_null($file)) {
            if (empty($this->fileName)) {
                return SGL::raiseError('No filename specified',
                    SGL_ERROR_NOFILE);
            }
            $file = $this->fileName;
        }
        //  determine if we're saving a module config file
        //  $file is only defined for module config saving
        if ($file != $this->fileName) {
            $modDir = $this->getModulesDir();

            if (stristr($file, $modDir)) {
                $this->ensureCacheDirExists();
                $file = $this->getCachedFileName($file);
            }
        }
        $ph = &SGL_ParamHandler::singleton($file);
        return $ph->write($this->aProps);
    }

    function merge($aConf)
    {
        $this->aProps = SGL_Array::mergeReplace($this->aProps, $aConf);
    }

    /**
     * Returns true if the current config object contains no data keys.
     *
     * @return boolean
     */
    function isEmpty()
    {
        return count($this->aProps) ? false : true;
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
     */
    function ensureModuleConfigLoaded($moduleName)
    {
        if (!defined('SGL_MODULE_CONFIG_LOADED')
                || $this->aProps['localConfig']['moduleName'] != $moduleName) {
            $path = SGL_MOD_DIR . '/' . $moduleName . '/conf.ini';
            $modConfigPath = realpath($path);

            if ($modConfigPath) {
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
                $ret = SGL::raiseError("Config file could not be found at '$path'",
                    SGL_ERROR_NOFILE);
            }
        } else {
            $ret = $this->getAll();
        }
        return $ret;
    }
}
?>