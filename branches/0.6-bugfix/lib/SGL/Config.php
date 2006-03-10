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
// | Config.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Controller.php,v 1.49 2005/06/23 19:15:25 demian Exp $

require_once dirname(__FILE__) . '/ParamHandler.php';

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

    function SGL_Config($autoLoad = true)
    {
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

    function add($key, $value)
    {
        array_push($this->aProps, $value);
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

    function load($file)
    {
        $ph = &SGL_ParamHandler::singleton($file);
        $data = $ph->read();
        if ($data !== false) {
            return $data;
        } else {
            if (defined('SGL_INITIALISED')) {
                return SGL::raiseError('Problem reading config file',
                    SGL_ERROR_INVALIDFILEPERMS);
            } else {
                SGL::displayStaticPage('No global config file could be found');
            }
        }
    }

    function save($file)
    {
        $ph = &SGL_ParamHandler::singleton($file);
        return $ph->write($this->aProps);
    }

    function merge($aConf)
    {
        $firstKey = key($aConf);
        if (!array_key_exists($firstKey, $this->aProps)) {
            $this->aProps = array_merge_recursive($this->aProps, $aConf);
        }
    }

    function isEmpty()
    {
        return count($this->aProps) ? false : true;
    }
}