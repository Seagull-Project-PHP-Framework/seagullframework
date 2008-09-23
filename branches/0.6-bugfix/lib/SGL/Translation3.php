<?php

/**
* Translation3 class
*/
class SGL_Translation3
{
    private $_driver;
    private static $aInstances;

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
        if (!isset(self::$aInstances[$driver])) {
            $class = __CLASS__;
            self::$aInstances[$driver] = new $class($driver, $aOptions);
        }
        return self::$aInstances[$driver];
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
            $driver = strtolower(SGL_Config::get('translation.container'));
            // BC with SGL translation config option
            $driver = ($driver == 'file') ? 'array' : $driver;
        }
        $className = 'SGL_Translation3_Driver_' . $driver;
        $fileName = 'SGL/Translation3/Driver/' . ucfirst($driver) . '.php';
        require_once $fileName;
        if (!class_exists($className)) {
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
     * Performs same function as SGL::getCurrentLang() and will supersede it
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
        return str_replace('_', '-', SGL_Config::get('translation.fallbackLang'));
    }
}
?>