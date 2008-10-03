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
// | Registry.php                                                         |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Registry.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/**
 * Generic data storage object, referred to as $input.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.49 $
 */
class SGL_Registry
{
    protected $aProps = array();
    private static $instance;

    /**
     * Enter description here...
     *
     * @return unknown
     * @todo use php5 singleton
     */
    public static function singleton()
    {
        if (!self::$instance) {
            $class = __CLASS__;
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $key
     * @return unknown
     * @todo make it work like Config
     */
    public static function get($key)
    {
        $reg = SGL_Registry::singleton();
        if (array_key_exists($key, $reg->aProps)) {
            $ret =  $reg->aProps[$key];
        } else {
            $ret = null;
        }
        return $ret;
    }

    /**
     * Add or modify registry data.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        $reg = SGL_Registry::singleton();
        $reg->aProps[$key] = $value;
    }

    public function exists($key) {
        return array_key_exists($key, $this->aProps);
    }

    /**
     * Enter description here...
     *
     * @return unknown
     * @todo forget about url object
     */
    public function getCurrentUrl()
    {
        return $this->get('currentUrl');
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $url
     * @todo forget about url object
     */
    public function setCurrentUrl($url)
    {
        $this->set('currentUrl', $url);
    }
}
?>