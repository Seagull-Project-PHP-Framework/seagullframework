<?php
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