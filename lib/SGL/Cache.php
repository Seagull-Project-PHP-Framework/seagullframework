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
// | Cache.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// |         Dmitri Lakachauskis <lakiboy83@gmail.com>                         |
// +---------------------------------------------------------------------------+

require_once 'Cache/Lite.php';

/**
 * A wrapper for PEAR::Cache_Lite.
 *
 * @package SGL
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Cache
{
    /**
     * Returns one of Cache_Lite containers.
     *
     * Example usage:
     *   Default Cache_Lite instance.
     *   1. $cache = &SGL_Cache::singleton();
     *   Cache_Lite_Function instance with specified params on the fly.
     *   2. $cache = &SGL_Cache::singleton('function', array(
     *          'dontCacheWhenTheResultIsFalse' => true,
     *          'dontCacheWhenTheResultIsNull'  => true,
     *          'lifeTime'                      => 3,
     *          'debugCacheLiteFunction'        => true,
     *      ));
     *   Cache_Lite_Output instance.
     *   3. $cache = &SGL_Cache::singleton('output');
     *   Force Cache_Lite_Function instance.
     *   4. $cache = &SGL_Cache::singleton('function', array(), true);
     *   BC way to force cache.
     *   5. $cache = &SGL_Cache::singleton(true);
     *
     * @access public
     *
     * @param mixed $type        Cache_Lite container.
     *                            In BC mode flag to force boolean mode.
     * @param array $aOptions    Options to override config values on the fly.
     * @param boolean $forceNew  Force cache even if not in caching mode.
     *
     * @static
     *
     * @return Cache_Lite
     */
    function &singleton($type = 'default', $aOptions = array(), $forceNew = false)
    {
        static $aInstances;

        // BC
        if (is_bool($type)) {
            if ($type) {
                $forceNew = true;
            }
            $type = 'default';
        }
        $key = $type . '-' . md5(serialize($aOptions));

        if (!isset($aInstances[$key]) || $forceNew) {
            $isEnabled = $forceNew ? true : SGL_Config::get('cache.enabled');
            // basic options
            $aDefaultOptions = array(
                'cacheDir' => SGL_TMP_DIR . '/',
                'lifeTime' => SGL_Config::get('cache.lifetime'),
                'caching'  => $isEnabled
            );
            // additional options
            if (SGL_Config::get('cache.cleaningFactor')) {
                $aDefaultOptions['automaticCleaningFactor'] = SGL_Config::get('cache.cleaningFactor');
            }
            if (SGL_Config::get('cache.readControl')) {
                $aDefaultOptions['readControl'] = SGL_Config::get('cache.readControl');
            }
            if (SGL_Config::get('cache.writeControl')) {
                $aDefaultOptions['writeControl'] = SGL_Config::get('cache.writeControl');
            }
            // override with specified options
            $aOptions = array_merge($aDefaultOptions, $aOptions);
            switch (strtolower($type)) {
                case 'output':
                    require_once 'Cache/Lite/Output.php';
                    $className = 'Cache_Lite_Output';
                    break;
                case 'function':
                    require_once 'Cache/Lite/Function.php';
                    $className = 'Cache_Lite_Function';
                    break;
                default:
                    $className = 'SGL_Cache_Lite';
            }
            $aInstances[$key] = & new $className($aOptions);
        }
        return $aInstances[$key];
    }

    /**
     * Clear cache directory of a specific module's cache files.
     * A simple wrapper to PEAR::Cache_Lite::clean() method.
     *
     * @access public
     *
     * @param  string $group  name of the cache group (e.g. nav, blocks, etc.)
     *
     * @return boolean true on success
     *
     * @author Andy Crain <apcrain@fuse.net>
     */
     function clear($group = false, $mode = 'ingroup', $options = array())
     {
        $cache = &SGL_Cache::singleton();
        return $cache->clean($group, $mode, $options);
     }
}


/**
 * Overridden to allow object callbacks.
 *
 */
class SGL_Cache_Lite extends Cache_Lite
{
    function clean($group = false, $mode = 'ingroup', $options = array())
    {
        return $this->_cleanDir($this->_cacheDir, $group, $mode, $options);
    }

    function _cleanDir($dir, $group = false, $mode = 'ingroup', $options = array())
    {
        if ($this->_fileNameProtection) {
            $motif = ($group) ? 'cache_'.md5($group).'_' : 'cache_';
        } else {
            $motif = ($group) ? 'cache_'.$group.'_' : 'cache_';
        }
        if ($this->_memoryCaching) {
            while (list($key, ) = each($this->_memoryCachingArray)) {
                if (strpos($key, $motif, 0)) {
                    unset($this->_memoryCachingArray[$key]);
                    $this->_memoryCachingCounter = $this->_memoryCachingCounter - 1;
                }
            }
            if ($this->_onlyMemoryCaching) {
                return true;
            }
        }
        if (!($dh = opendir($dir))) {
            return $this->raiseError('Cache_Lite : Unable to open cache directory !', -4);
        }
        $objectCallback = false;
        if (!is_string($mode) && is_callable($mode)) {
            $objectCallback = true;
        }
        $result = true;
        while ($file = readdir($dh)) {
            if (($file != '.') && ($file != '..')) {
                if (substr($file, 0, 6)=='cache_') {
                    $file2 = $dir . $file;
                    if (is_file($file2)) {
                        $match = ($objectCallback)
                            ? 'callback_'
                            : substr($mode, 0, 9);
                        switch ($match) {
                            case 'old':
                                // files older than lifeTime get deleted from cache
                                if (!is_null($this->_lifeTime)) {
                                    if ((mktime() - @filemtime($file2)) > $this->_lifeTime) {
                                        $result = ($result and ($this->_unlink($file2)));
                                    }
                                }
                                break;
                            case 'notingrou':
                                if (!strpos($file2, $motif, 0)) {
                                    $result = ($result and ($this->_unlink($file2)));
                                }
                                break;
                            case 'callback_':
                                if ($objectCallback) {
                                    if (call_user_func_array($mode, $options)) {
                                        $result = ($result and ($this->_unlink($file2)));
                                    }
                                } else {
                                    $func = substr($mode, 9, strlen($mode) - 9);
                                    if ($func($file2, $group)) {
                                        $result = ($result and ($this->_unlink($file2)));
                                    }
                                }
                                break;
                            case 'ingroup':
                            default:
                                if (strpos($file2, $motif, 0)) {
                                    $result = ($result and ($this->_unlink($file2)));
                                }
                                break;
                        }
                    }
                    if ((is_dir($file2)) and ($this->_hashedDirectoryLevel>0)) {
                        $result = ($result and ($this->_cleanDir($file2 . '/', $group, $mode)));
                    }
                }
            }
        }
        return $result;
    }
}
?>