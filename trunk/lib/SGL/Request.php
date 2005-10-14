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
    
    /**
     * Sets up a request object.
     *
     * @return SGL_Request
     *
     * @todo implement CLI request initialiser
     */
    function SGL_Request()
    {
        if (!SGL::runningFromCLI()) {
            $this->initHttp();
        } else {
            $this->initCli();
        }
    }
    
    function initCli()
    {
        die("CLI interface not implemented yet\n");
    }
    
    function initHttp()
    {
        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_REQUEST, $_FILES);
        
        //  remove slashes if necessary
        SGL_String::dispelMagicQuotes($this->aProps);
        
        //  merge results with cleaned $_REQUEST values and $_POST
        SGL_String::dispelMagicQuotes($_POST);
        
        //  also merge with SEF url params
        $reg = &SGL_Registry::singleton();
        $url = $reg->getCurrentUrl();
        $aUrlParams = $url->getQueryData();
        
        $this->aProps = array_merge($this->aProps, $aUrlParams, $_POST);
        
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
            
            //  don't operate on reference to avoid segfault :-(
            $copy = $this->aProps[$key];
            
            //  if html not allowed, run an enhanced strip_tags()
            if (!$allowTags) {
                $clean = SGL_String::clean($copy);
            
            //  if html is allowed, at least remove javascript
            } else {
                $clean = SGL_String::removeJs($copy);
            }
            $this->set($key, $clean);
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
    
    /**
     * Return an array of all Request properties.
     *
     * @return array
     */
    function getAll()
    {
        return $this->aProps;   
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

class SGL_Inflector
{
    /**
    * Returns true if URL has been abbreviated
    *
    * This happens when a manager name is the same as its module name, ie
    * UserManger in the 'user' module would become user/user which gets
    * reduced to user
    *
    * @param string $url            From the querystring
    * @param string $sectionName    From the database
    * @return boolean
    */
    function isUrlSimplified($url, $sectionName)
    {
        if (!(empty($url))) {
            $aUrlPieces = explode('/', $url);
            $moduleNameUrl = $aUrlPieces[0];
            $aSections =  explode('/', $sectionName);
            $ret = in_array($moduleNameUrl, $aSections) && (SGL_Inflector::urlContainsDuplicates($sectionName));
        } else {
            $ret = false;
        }
        return $ret;
    }
    
    /**
     * Returns true if manager name is the same of module name, ie, index.php/faq/faq/.
     *
     * @param string $url
     * @return boolean
     */
    function urlContainsDuplicates($url)
    {
        if (!empty($url)) {
            $aPieces = explode('/', $url);
            $initial = count($aPieces);
            $unique = count(array_unique($aPieces));
            $ret = $initial != $unique;
        } else {
            $ret = false;
        }
        return $ret;
    }
    
    /**
     * Determine if a simplified notation is being used.
     *
     * If the url was of the form example.com/index.php/contactus/contactus/
     * and it got simplifeid too example.com/index.php/contactus/ it is important
     * to determine if that simplification happened, so subsequent parameters
     * don't get interpreted as 'managerName'
     *
     * @param array $aParsedUri
     * @return boolean
     */
    function isMgrNameOmitted($aParsedUri)
    {
        $fullMgrName = SGL_Inflector::getManagerNameFromSimplifiedName(
            $aParsedUri['managerName']);
        
        //  compensate for case-sensitivity
        $corrected = SGL_Inflector::caseFix($fullMgrName, true);
        $path = SGL_MOD_DIR .'/'. $aParsedUri['moduleName'] . '/classes/' . $corrected . '.php';
        
        //  if the file exists, mgr name is valid and has not been omitted 
        return !file_exists($path);
    }
    
    /**
     * Returns the full Manager name given the short name, ie, faq becomes FaqMgr.
     *
     * @param string $name
     * @return string
     */
    function getManagerNameFromSimplifiedName($name)
    {
        //  if Mgr suffix has been left out, append it
        if (strtolower(substr($name, -3)) != 'mgr') {
            $name .= 'Mgr';
        }
        return ucfirst($name);
    }
    
    /**
     * Returns the short name given the full Manager name, ie FaqMgr becomes faq.
     *
     * @param unknown_type $name
     * @return unknown
     */
    function getSimplifiedNameFromManagerName($name)
    {
        //  strip file extension if exists
        if (substr($name, -4) == '.php') {
            $name = substr($name, 0, -4);
        }
        
        //  strip 'Mgr' if exists
        if (strtolower(substr($name, -3)) == 'mgr') {
            $name = substr($name, 0, -3);
        }
        return strtolower($name);      
    }
    
    /**
     * Makes up for case insensitive classnames in php4 with get_class().
     *
     * @access   public
     * @static    
     * @param    string     $str    Classname  
     * @param    boolean    $force  Force the operation regardless of php version
     * @return   mixed              Either correct case classname or false
     */
    function caseFix($str, $force = false)
    {
        if (!$force && (($phpVersion{0} = PHP_VERSION) == 5)) {
            return $str;
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $aConfValues = array_keys($conf);
        $aConfValuesLowerCase = array_map('strtolower', $aConfValues);
        $isFound = array_search(strtolower($str), $aConfValuesLowerCase);
        return ($isFound !== false) ? $aConfValues[$isFound] : false;
    }
}
?>