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
// $Id$

/**
* Translation3 class
*/
class SGL2_Translation
{
    protected $_driver;
    protected static $_aInstances;

    /**
     * FIXME: setDriver possible exception must be caught
     *
     * @param unknown_type $driver
     * @param array $aOptions
     */
    private function __construct($driver = null, array $aOptions = array())
    {
        $this->setDriver($driver, $aOptions);
    }

    /**
     *
     * @param string $driver
     * @param array $aOptions
     * @return unknown
     */
    public static function singleton($driver = null, array $aOptions = array())
    {
        $driver = strtolower($driver);
        if (!isset(self::$_aInstances[$driver])) {
            $class = __CLASS__;
            self::$_aInstances[$driver] = new $class($driver, $aOptions);
        }
        return self::$_aInstances[$driver];
    }

    /**
     * Getter for driver properties
     */
    public function __get($propName)
    {
        if (!isset($this->$propName)) {
            return $this->_driver->$propName;
        }
    }

    /**
     * Factory method to load appropriate driver
     */
    public function setDriver($driver = null, array $aOptions = array())
    {
        if (is_null($driver)) {
            $driver = strtolower(SGL2_Config::get('translation.container'));
            // BC with SGL translation config option
            $driver = ($driver == 'file') ? 'array' : $driver;
        }
        $className = 'SGL2_Translation_Driver_' . ucfirst($driver);
        if (!SGL2_File::exists(SGL2_Inflector::classToFile($className))) {
            throw new Exception("Driver $driver not implemented", 1);
        }
        $this->_driver = new $className($aOptions);
    }

    public function getDriver()
    {
        return $this->_driver;
    }

    /**
     * Calls all methods from the driver
     */
    public function __call($method, array $aOptions)
    {
        if (method_exists($this->_driver, $method)) {
            return call_user_func_array(array($this->_driver, $method), $aOptions);
        }
        throw new Exception("Unknown method '$method' called!");
    }


    /******************************/
    /*       STATIC METHODS       */
    /******************************/


    /**
     *
     * @return string $langCode
     */
    public static function getDefaultLangCode()
    {
        $aLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        $langCodeCharset = self::getDefaultLangCodeCharset();
        return $aLanguages[$langCodeCharset][2];
    }

    public static function getDefaultLangCodeCharset()
    {
        return str_replace('_', '-', SGL2_Config::get('translation.fallbackLang'));
    }

    public static function getDefaultCharset()
    {
        $langCodeCharset = self::getDefaultLangCodeCharset();
        return self::extractCharset($langCodeCharset);
    }

    /**
     * @todo make work with langCode
     */
    public static function extractCharset($langCodeCharset)
    {
        $aLang = explode('-', $langCodeCharset);
        array_shift($aLang);
        if ($aLang[0] == 'tw') {
            array_shift($aLang);
        }
        return implode('-', $aLang);
    }
}
?>