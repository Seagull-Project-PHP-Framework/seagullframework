<?php

/**
 * Abstract registry class.
 *
 * @abstract 
 */
class SGL_Registry
{   
    var $aProps;
    
    function set($key, $value) {}
    
    function get($key) {}
    
    function getError() {}
    
    function setError() {}
}



class SGL_RequestRegistry extends SGL_Registry 
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
        #$reg = &SGL_RequestRegistry::singleton();
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

//$input = &SGL_RequestRegistry::singleton();
//print '<pre>'; print_r($input);

class SGL_SessionRegistry extends SGL_Registry {} //    same as SGL_HTTP_Session

class SGL_ApplicationRegistry extends SGL_Registry 
{
    var $dirty = false;    
    var $configFile = '';
    
    function &singleton($configFile)
    {
        static $instance;
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class($configFile);
        }
        return $instance;
    }
    
    function SGL_ApplicationRegistry($configFile)
    {
        $this->configFile = $configFile;
        $this->init();
    }
    
    function init()
    {
        $params = SGL_ParamHandler::singleton($this->configFile);
        $this->aProps = $params->getAll();
    }
    
    function isEmpty()
    {
        return empty($this->aProps);
    }
    
    function get($key) 
    {
        return $this->aProps[$key];
    }
    
    function set($key, $value) 
    {
        $this->dirty = true;
        $this->aProps[$key] = $value;
    }
    
    function getAll()
    {
        return $this->aProps;
    }
    
    function save()
    {
        $params = SGL_ParamHandler::singleton($this->configFile);
        $ok = $params->write();
    }

}

#register_shutdown_function('registryDestructor');

/**
 * Emulated Registry destructor.
 *
 * @todo move to __destruct() for php5 version
 *
 */
function registryDestructor()
{
    $appReg = &SGL_ApplicationRegistry::singleton($GLOBALS['_SGL']['configPath']);
    if ($appReg->dirty) {
        $appReg->save();   
    }
}


//  usage example
//$GLOBALS['_SGL']['configPath'] = 'test.ini';
//$reg = &SGL_ApplicationRegistry::singleton($GLOBALS['_SGL']['configPath']);
//
//$conf = $reg->getAll();
//print '<pre>'; print_r($conf);
//$reg->set('foo', array());
//print '<pre>'; print_r($reg);