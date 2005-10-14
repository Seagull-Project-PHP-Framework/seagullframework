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
// | Registry.php                                                         |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/**
 * Temporary data and resource storage.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.49 $
 */
class SGL_Registry
{
    function &singleton()
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }
    
    function get($key) 
    {
        return $this->aProps[$key];
    }
    
    function set($key, $value) 
    {
        $this->aProps[$key] = $value;
    }
    
    function getRequest()
    {
        return $this->get('request');
    }
    
    function setRequest($req)
    {
        #$reg = &SGL_RequestRegistry::singleton();
        $this->set('request', $req);
        //  php 4 version of
        //  self::singleton()->set('request', $req);
    }
    
    function getCurrentUrl()
    {
        return $this->get('currentUrl');
    }
    
    function setCurrentUrl($url)
    {
        $this->set('currentUrl', $url);
    }
    
    function getConfig()
    {
        $c = &SGL_Config::singleton();
        return $c->getAll();
    }
    
    /**
     * Copies properties from source object to destination object.
     *
     * @access  public
     * @static
     * @param   object  $dest   typically the ouput object
     * @return  void
     */
    function aggregate(& $dest)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aObjAttrs = get_object_vars($this);
        if (is_array($aObjAttrs)) {
            foreach ($aObjAttrs as $objAttrName => $objAttrValue) {
                $dest->$objAttrName = $objAttrValue;
            }
        }
    }
}