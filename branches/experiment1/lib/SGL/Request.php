<?php

require_once 'Registry.php';

class SGL_Request
{
    var $aProps;
    
    function SGL_Request()
    {
        $this->init();
        SGL_RequestRegistry::setRequest($this);
    }
    
    function init()
    {
        #$conf = & $GLOBALS['_SGL']['CONF'];

        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_REQUEST, $_FILES);
        
        //  remove slashes if necessary
#        SGL_String::dispelMagicQuotes($this->aProps);

        //  get all URL parts after domain and TLD as an array
        #$aUriParts = SGL_Url::getSignificantSegments($_SERVER['PHP_SELF']);
        
        //  parse URL segments into SGL request structure
        #$aSglRequest = SGL_Url::makeSearchEngineFriendly($aUriParts);
        
        //  merge results with cleaned $_REQUEST values and $_POST
#        SGL_String::dispelMagicQuotes($_POST);
#        $this->aProps = array_merge($aSglRequest, $this->aProps, $_POST);
        
        return;
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

new SGL_Request();

$input = &SGL_RequestRegistry::singleton();
print '<pre>'; print_r($input);

?>