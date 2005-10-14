<?php
require_once dirname(__FILE__) . '/ParamHandler.php';

class SGL_Config
{
    var $aProps = array();
    
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
    
    function load($file)
    {
        $ph = &SGL_ParamHandler::singleton($file);
        $data = $ph->read();
        if ($data !== false) {
            return $data;
        } else {
            return SGL::raiseError('Problem reading config file', 
                SGL_ERROR_INVALIDFILEPERMS);    
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
    
    /**
     * Ini file protection.
     *
     * By giving ini files a php extension, and inserting some PHP die() code,
     * we can improve security in situations where browsers might be able to
     * read them.  Thanks to Georg Gell for the idea.
     *
     * @param unknown_type $file
     */
    function makeIniUnreadable($file)
    {
        $iniFle = file($file);
        $string = ';<?php die("Eat dust"); ?>' . "\n";
        array_unshift($iniFle, $string);
        file_put_contents($file, implode("", $iniFle));
    }
}