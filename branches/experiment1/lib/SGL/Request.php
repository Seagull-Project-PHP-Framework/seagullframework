<?php
/**
 * Request wraps all $_GET $_POST $_FILES arrays into a Request object.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 * @since   PHP 4.1
 */
class SGL_Request
{
    var $aProps;
    
    function SGL_Request()
    {
        $this->init();
    }
    
    function init()
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_REQUEST, $_FILES);
        
        //  remove slashes if necessary
        SGL_String::dispelMagicQuotes($this->aProps);

        //  get all URL parts after domain and TLD as an array
        $aUriParts = SGL_Url::getSignificantSegments($_SERVER['PHP_SELF']);
        
        //  parse URL segments into SGL request structure
        $aSglRequest = SGL_Url::makeSearchEngineFriendly($aUriParts);
        
        //  merge results with cleaned $_REQUEST values and $_POST
        SGL_String::dispelMagicQuotes($_POST);
        $this->aProps = array_merge($aSglRequest, $this->aProps, $_POST);
        
        return;
    }
    
    /**
     * Returns a singleton Request instance.
     *
     * example usage: 
     * $req = & SGL_Request::singleton();
     * warning: in order to work correctly, the request
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  mixed           reference to Request object
     */
    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new SGL_Request();
        }
        return $instance;
    }
    
    /**
     * Retrieves values from Request object.
     *
     * @access  public
     * @param   mixed   $paramName  Request param name
     * @param   boolean $allowTags  If html/php tags are allowed or not
     * @return  mixed               Request param value or null if not exists
     */
    function get($key, $allowTags = false) 
    {
        if (isset($this->aProps[$key])) {
            
            //  if html not allowed, run an enhanced strip_tags()
            if (!$allowTags) {
                SGL_String::clean($this->aProps[$key]);
            
            //  if html is allowed, at least remove javascript
            } else {
                SGL_String::removeJs($this->aProps[$key]);
            }
            return $this->aProps[$key];
        
        } else {
            return null;
        }
    }
    
    /**
     * Set a value for Request object.
     *
     * @access  public
     * @param   mixed   $name   Request param name
     * @param   mixed   $value  Request param value
     * @return  void
     */
    function set($key, $value) 
    {
        $this->aProps[$key] = $value;
    }
    
    function getModuleName()
    {
        return $this->aProps['moduleName'];
    }
    
    function getManagerName()
    {
        return $this->aProps['managerName'];
    }
    
    function debug()
    {
        $GLOBALS['_SGL']['site']['blocksEnabled'] = 0;
        print '<pre>';
        print_r($this->aProps[$key]);
    }
}
?>