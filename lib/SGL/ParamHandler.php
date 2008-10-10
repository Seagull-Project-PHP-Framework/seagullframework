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
// | ParamHandler.php                                                          |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/**
 * Handles reading and writing of config files.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.49 $
 */

interface SGL_Config_Interface
{
    public function read();
    public function write($data);
}

class SGL_ParamHandler implements SGL_Config_Interface
{
    public $source;
    private static $instances;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public static function singleton($source)
    {
        if (!self::$instances) {
            self::$instances = array();
        }

        $signature = md5($source);
        if (!isset(self::$instances[$signature])) {

            $ext = substr($source, -3);
            switch ($ext) {

            case 'xml':
                $ret = new SGL_ParamHandler_Xml($source);
                break;

            case 'php':
                $ret = new SGL_ParamHandler_Array($source);
                break;

            case 'ini':
                $ret =  new SGL_ParamHandler_Ini($source);
                break;
            }
            self::$instances[$signature] = $ret;
        }
        return self::$instances[$signature];
    }

    function read() {}
    function write($data) {}
}

/**
 * Concrete implementation for ini files.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_ParamHandler_Ini extends SGL_ParamHandler
{
    public function read()
    {
        $ret = @parse_ini_file($this->source, true);
        return (count($ret)) ? $ret : false;
    }

    public function write($data)
    {
        //  load PEAR::Config
        require_once 'Config.php';
        $c = new Config();
        $c->parseConfig($data, 'phparray');
        $ok = $c->writeConfig($this->source, 'inifile');
        return $ok;
    }
}

/**
 * Concrete implementation for arrays.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_ParamHandler_Array extends SGL_ParamHandler
{
    public function read()
    {
        if (SGL2_File::exists($this->source)) {
            $ret = SGL2_File::load($this->source);
        } else {
            $ret = false;
        }
        return $ret;
    }

    public function write($data)
    {
        //  load PEAR::Config
        require_once 'Config.php';
        $c = new Config();
        $c->parseConfig($data, 'phparray');
        $ok = $c->writeConfig($this->source, 'phparray');
        return $ok;
    }
}

/**
 * Concrete implementation for XML files.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_ParamHandler_Xml extends SGL_ParamHandler
{
    public function read()
    {
        return simplexml_load_file($this->source);
    }

    public function write($data)
    {
        //  load PEAR::Config
        require_once 'Config.php';
        $c = new Config();
        $c->parseConfig($data, 'phparray');
        $ok = $c->writeConfig($this->source, 'xml');
        return $ok;
    }
}
?>