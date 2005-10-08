<?php

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
    
    function load()
    {
        //  use parse ini file for now
    }
    
    function save()
    {
        //  load PEAR::Config
    }
    
    function set($key, $value)
    {
        $this->aProps[$key] = $value;
    }
    
    function get($key)
    {
        return $this->aProps[$key];
    }
}

/*
usage: $conf = SGL_Config::singleton();

*/

//$conf = &SGL_Config::singleton();
//print '<pre>'; print_r($conf);