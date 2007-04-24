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
// | Cache.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * A wrapper for PEAR's Cache_Lite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Cache
{
    /**
     * Returns a singleton Cache_Lite instance.
     *
     * example usage:
     * $cache = & SGL_Cache::singleton();
     * warning: in order to work correctly, the cache
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @param boolean $forceNew     If true and $conf['cache']['enabled'] is set to false,
     *                              this will be ignored and caching enabled
     * @static
     * @return  mixed reference to Cache_Lite object
     */
    function &singleton($forceNew = false)
    {
        static $instance;

        // If the instance doesn't exist, create one
        if (!isset($instance) || $forceNew) {
            require_once 'Cache/Lite.php';
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();

            $isEnabled = ($forceNew) ? true : $conf['cache']['enabled'];
            $options = array(
                'cacheDir'  => SGL_TMP_DIR . '/',
                'lifeTime'  => $conf['cache']['lifetime'],
                'caching'   => $isEnabled);
            // new options are added via issets for BC
            if (isset($conf['cache']['cleaningFactor'])) {
                $options['automaticCleaningFactor'] = $conf['cache']['cleaningFactor'];
            }
            if (isset($conf['cache']['readControl'])) {
                $options['readControl'] = $conf['cache']['readControl'];
            }
            if (isset($conf['cache']['writeControl'])) {
                $options['writeControl'] = $conf['cache']['writeControl'];
            }
            $instance = new Cache_Lite($options);
        }
        return $instance;
    }

    /**
     * Clear cache directory of a specific module's cache files. A simple wrapper to
     * PEAR::Cache_Lite's clean() method.
     *
     * @access public
     * @param  string $group name of the cache group (e.g. nav, blocks, etc.)
     * @return boolean true on success
     * @author  Andy Crain <apcrain@fuse.net>
     */
     function clear($group = false)
     {
        $cache = & SGL_Cache::singleton();
        return $cache->clean($group);
     }
}
?>