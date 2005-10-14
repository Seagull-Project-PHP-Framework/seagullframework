<?php
class SGL_ParamHandler
{
    var $source;
    
    function SGL_ParamHandler($source)
    {
        $this->source = $source;  
    }

    function &singleton($source)
    { 
        static $instances;
        if (!isset($instances)) $instances = array();
        
        $signature = md5($source);
        if (!isset($instances[$signature])) {
            
            $ext = substr($source, -3);
            switch ($ext) {

            case 'xml':
                $ret = new SGL_ParamHandler_Xml($source);
                break;
                
            case 'php':
                $ret = new SGL_ParamHandler_Array($source);
                break;
            //  at the moment .php file are ini files
            //  but they should be arrays
            case 'ini':
                $ret =  new SGL_ParamHandler_Ini($source);
                break;
            }
            $instances[$signature] = $ret;
        }
        return $instances[$signature];
    }
    
    function read() {}
    function write() {}
    
//    function getAll()
//    {
//        if (empty($this->aParams)) {
//            $this->read();   
//        }
//        return $this->aParams;
//    }
}

class SGL_ParamHandler_Ini extends SGL_ParamHandler
{
    function read() 
    {
        return parse_ini_file($this->source, true);
    }
    
    function write($data) 
    {
        //  load PEAR::Config
        require_once 'Config.php';
        $c = new Config();
        $c->parseConfig($data, 'phparray');
        $ok = $c->writeConfig($this->source, 'inifile');        
        return $ok;
    }
}

class SGL_ParamHandler_Array extends SGL_ParamHandler
{
    function read() 
    {
        require $this->source;
        return $conf;
    }
    
    function write($data) 
    {
        //  load PEAR::Config
        require_once 'Config.php';
        $c = new Config();
        $c->parseConfig($data, 'phparray');
        $ok = $c->writeConfig($this->source, 'phparray');        
        return $ok;      
    }
}

class SGL_ParamHandler_Xml extends SGL_ParamHandler
{
    function read() 
    {
        return simplexml_load_file($this->source);
    }
    
    function write($data) 
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