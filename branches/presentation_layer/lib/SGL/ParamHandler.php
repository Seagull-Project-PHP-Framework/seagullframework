<?php
class SGL_ParamHandler
{
    var $source;
    var $aParams;
    
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
                return new SGL_ParamHandler_Xml($source);
                break;
                
            case 'ini':
            
            //  at the moment .php file are ini files
            //  but they should be arrays
            case 'php':
                return new SGL_ParamHandler_Ini($source);
                break;
            }
            $class = __CLASS__;
            $instance = new $class($source);
        }
        return $instance;
    }
    
    function read() {}
    function write() {}
    
    function addParam($key, $value)
    {
        $this->aParams[$key] = $value;    
    }
    
    function getAll()
    {
        if (empty($this->aParams)) {
            $this->read();   
        }
        return $this->aParams;
    }
}

class SGL_ParamHandler_Ini extends SGL_ParamHandler
{
    function read() 
    {
        $this->aParams = parse_ini_file($this->source, true);
    }
    
    function write() 
    {
        //  lazy load PEAR::Config to do the job
        print 'writing file ...';
    }
}

class SGL_ParamHandler_Array extends SGL_ParamHandler
{

}

class SGL_ParamHandler_Xml extends SGL_ParamHandler
{

}

/*
$params = SGL_ParamHandler::singleton('conf.ini');
$params->read();
*/
?>