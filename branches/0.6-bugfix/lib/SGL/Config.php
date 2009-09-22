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

    /**
     * Returns a config key
     *
     * @since Seagull 0.6.3 easier access with static calls, ie
     * $lifetime = SGL_Config::get('cache.lifetime');
     *
     * @param mixed $key array or string
     * @return string the value of the config key
     */
    function get($key)
    {
        //  instance call with 2 keys: $c->get(array('foo' => 'bar'));
        if (is_array($key)) {
            $key1 = key($key);
            $key2 = $key[$key1];
            if (isset( $this->aProps[$key1][$key2])) {
                $ret = $this->aProps[$key1][$key2];
            } else {
                $ret = false;
            }
        //  static call with dot notation: SGL_Config::get('foo.bar');
        } elseif (is_string($key)) {
            $c = &SGL_Config::singleton();
            $aKeys = preg_split("/\./", trim($key));
            if (isset($aKeys[0]) && isset($aKeys[1])) {
                $ret = $c->get(array($aKeys[0] => $aKeys[1]));

            // instance call with 1 key: $c->get('foo');
            } elseif (isset($this->aProps[$key])){
                $ret = $this->aProps[$key];

            //  else set defaults
            } else {
                $key1 = isset($aKeys[0]) ? $aKeys[0] : 'no value' ;
                $key2 = isset($aKeys[1]) ? $aKeys[1] : 'no value' ;
                $ret = false;
            }
        }
        if (!isset($ret)) {
            SGL::logMessage("Config key '[$key1][$key2]' does not exist",
                PEAR_LOG_DEBUG);
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
    function set($key, $value)
    {
        $ret = false;
        if (is_string($key) && is_scalar($value)) {
            $aKeys = split('\.', trim($key));

            //  it's a static call
            if (isset($aKeys[0]) && isset($aKeys[1])) {
                $c = &SGL_Config::singleton();
                $ret = $c->set($aKeys[0], array($aKeys[1] => $value));
            //  else it's an object call with scalar second arg
            } else {
                $this->aProps[$key] = $value;
                $ret = true;
            }
        //  else it's an object call with array second arg
        } elseif (is_string($key) && is_array($value)) {
            $key2 = key($value);
            $this->aProps[$key][$key2] = $value[$key2];
            $ret = true;
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
    function remove($key)
    {
        if (is_array($key)) {
            list($key1, $key2) = $key;
            unset($this->aProps[$key1][$key2]);
        } else {
            unset($this->aProps[$key]);
        }
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
     * @param boolean $force If force is true, master  config file is read, not cached one
     * @return mixed An array of data on success, PEAR error on failure.
     */
    function load($file, $force = false)
    {
        //  create cached copy if module config and cache does not exist
        //  if file has php extension it must be global config
        if (defined('SGL_INSTALLED')) {
            if (substr($file, -3, 3) != 'php') {
                if (!$force) {
                    $file = SGL_Config::locateCachedFile($file);
                }
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

    /**
     * @static
     *
     * @param string $path
     *
     * @return string
     */
    function locateCachedFile($path)
    {
        // ensure config reads are done from cached copy
        $cachedFileName = SGL_Config::getCachedFileName($path);
        if (!is_file($cachedFileName)) {
            $ok = SGL_Config::createCachedFile($cachedFileName);
            if (!$ok) {
                $cachedFileName = $ok;
            }
        }
        return $cachedFileName;
    }

    /**
     * @static
     *
     * @param string $path
     *
     * @return string
     */
    function getCachedFileName($path)
    {
        // make Windows and Unix paths consistent
        $path = str_replace('\\', '/', $path);
        if ($ret = preg_match("#(.*)\/(.*)\/(.+)\.ini$#", $path, $aMatches)) {
            $ret = $aMatches[3] == 'conf'
                // module cached config file
                ? SGL_VAR_DIR . "/config/{$aMatches[2]}.ini"
                // other cached ini file
                : SGL_VAR_DIR . "/{$aMatches[2]}/{$aMatches[3]}.ini";
        }
        return $ret;
    }

    /**
     * @static
     *
     * @param string $dir
     */
    function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            require_once 'System.php';
            $ok = System::mkDir(array('-p', $dir));
            @chmod($dir, 0777);
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

    /**
     * @static
     *
     * @param string $cachedConfigFile
     *
     * @return boolean
     */
    function createCachedFile($cachedConfigFile)
    {
        $aPath    = explode('/', $cachedConfigFile);
        $fileName = array_pop($aPath);
        $dirName  = array_pop($aPath);

        if ($dirName == 'config') {
            list($module,)    = explode('.', $fileName);
            $masterConfigFile = SGL_MOD_DIR . "/$module/conf.ini";
        } else {
            $module           = $dirName;
            $masterConfigFile = SGL_MOD_DIR . "/$module/$fileName";
        }
        SGL_Config::ensureDirExists(dirname($cachedConfigFile));

        $ok = copy($masterConfigFile, $cachedConfigFile);
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

            if (stristr($file, $modDir) || stristr($file, 'modules')) {
                $file = SGL_Config::getCachedFileName($file);
                SGL_Config::ensureDirExists(dirname($file));
            }
        } else {
            // we're saving global so de-merge local config from global
            $moduleConf = new SGL_Config();
            $default = SGL_Config::get('site.defaultModule');
            $aData = $moduleConf->load(SGL_MOD_DIR . "/$default/conf.ini");
            foreach ($aData as $k => $aValue) {
                if ($this->exists($k)) {
                    $this->remove($k);
                }
            }
            //  check for additional local config
            if ($local = SGL_Config::get('localConfig.moduleName')) {
                if ($local != $default) {
                    $localConf = new SGL_Config();
                    $aData = $localConf->load(SGL_MOD_DIR . "/$local/conf.ini");
                    foreach ($aData as $k => $aValue) {
                        if ($this->exists($k)) {
                            $this->remove($k);
                        }
                    }
                }
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

            if ($modConfigPath && file_exists($modConfigPath)) {
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

    /**
     * Given a string of the format moduleName^managerName^action, returns a
     * hash of the values.
     *
     * @param string $str
     * @return array
     */
    function getCommandTarget($str)
    {
        if (empty($str)) {
            return false;
        }
        $aSplitResult = split('\^', $str);
        $aParams = array(
            'moduleName'    => null,
            'managerName'   => null,
            );
        if (array_key_exists(0, $aSplitResult)) {
            $aParams['moduleName'] = $aSplitResult[0];
        }
        if (array_key_exists(1, $aSplitResult)) {
            $aParams['managerName'] = $aSplitResult[1];
        }
        if (array_key_exists(2, $aSplitResult)) {
            $aParams['action'] = $aSplitResult[2];
        }
        return $aParams;
    }
}
?>