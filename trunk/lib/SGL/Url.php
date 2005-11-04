<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2002-2004, Richard Heyes                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | Url.php                                                                   |
// +---------------------------------------------------------------------------+
// | Authors:   Richard Heyes <richard at php net>                             |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: Url.php,v 1.32 2005/05/29 21:32:17 demian Exp $
//

/**
 * Url related functionality.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.32 $
 * @see seagull/lib/SGL/tests/UrlTest.ndb.php
 */
class SGL_URL
{
    /**
    * Full url
    * @var string
    */
    var $url;

    /**
    * Protocol
    * @var string
    */
    var $protocol;

    /**
    * Username
    * @var string
    */
    var $username;

    /**
    * Password
    * @var string
    */
    var $password;

    /**
    * Host
    * @var string
    */
    var $host;

    /**
    * Port
    * @var integer
    */
    var $port;

    /**
    * Path
    * @var string
    */
    var $path;

    /**
    * Query string
    * @var array
    */
    var $querystring;
    
    var $aQueryData;
    var $frontScriptName;
    var $parserStrategy;

    /**
    * Anchor
    * @var string
    */
    var $anchor;

    /**
    * Whether to use []
    * @var bool
    */
    var $useBrackets;

    /**
    * PHP4 Constructor
    *
    * @see __construct()
    */
    function SGL_URL($url = null, $useBrackets = true, /*SGL_UrlParserStrategy*/ $parserStrategy = null, $conf = null)
    {
        $this->__construct($url, $useBrackets, $parserStrategy, $conf);
    }

    /**
    * PHP5 Constructor
    *
    * Parses the given url and stores the various parts
    * Defaults are used in certain cases
    *
    * @param string $url         Optional URL
    * @param bool   $useBrackets Whether to use square brackets when
    *                            multiple querystrings with the same name
    *                            exist
    * @param SGL_UrlParserStrategy  The strategy object to be used, optional
    * @param array               An array of config elements, optional
    *
    * @todo the main URL attributes always get set twice, this needs to be optimised
    */
    function __construct($url = null, $useBrackets = true, $parserStrategy = null, $conf = null)
    {
        $this->useBrackets = $useBrackets;
        $this->url         = $url;
        $this->user        = '';
        $this->pass        = '';
        $this->host        = '';
        $this->port        = 80;
        $this->path        = '';
        $this->aQueryData = array();
        $this->anchor      = '';

        if (is_null($conf)) {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
        }
        
        $this->frontScriptName = $conf['site']['frontScriptName'];
        $this->parserStrategy = $parserStrategy;
        
        // Only set defaults if $url is not an absolute URL
        if (!preg_match('/^[a-z0-9]+:\/\//i', $url)) {

            $this->protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
                ? 'https' 
                : 'http';

            /**
            * Figure out host/port
            */
            if (!empty($_SERVER['HTTP_HOST']) && preg_match('/^(.*)(:([0-9]+))?$/U', 
                    $_SERVER['HTTP_HOST'], $matches)) {
                $host = $matches[1];
                if (!empty($matches[3])) {
                    $port = $matches[3];
                } else {
                    $port = $this->getStandardPort($this->protocol);
                }
            }

            $this->user        = '';
            $this->pass        = '';
            $this->host        = !empty($host) 
                                    ? $host 
                                    : (isset($_SERVER['SERVER_NAME']) 
                                        ? $_SERVER['SERVER_NAME'] 
                                        : 'localhost');
            $this->port        = !empty($port) 
                                    ? $port 
                                    : (isset($_SERVER['SERVER_PORT']) 
                                        ? $_SERVER['SERVER_PORT'] 
                                        : $this->getStandardPort($this->protocol));
            $this->path        = !empty($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '/';
//            $this->querystring = isset($_SERVER['QUERY_STRING']) 
//                                    ? $this->_parseRawQuerystring($_SERVER['QUERY_STRING']) 
//                                    : null;
            $this->anchor      = '';
        }

        // Parse the url and store the various parts
        if (!empty($url)) {
            $urlinfo = parse_url($url);

            // Default query data
            $this->aQueryData = array();

            foreach ($urlinfo as $key => $value) {
                switch ($key) {
                    
                case 'scheme':
                    $this->protocol = $value;
                    $this->port     = $this->getStandardPort($value);
                    break;

                case 'user':
                case 'pass':
                case 'host':
                case 'port':
                    $this->$key = $value;
                    break;

                case 'path':
                    if ($value{0} == '/') {
                        $frontScriptStartIndex = strpos($value, $this->frontScriptName);
                        $frontScriptEndIndex = $frontScriptStartIndex + strlen($this->frontScriptName);
                        if (!$frontScriptStartIndex) {
                            
                            //  this is an install and index.php was omitted
                            $this->path = $urlinfo['path'];
                            $this->querystring = @$urlinfo['query'];
                            $install = true;
                        } else {
                            $this->path = substr($value, 0, $frontScriptStartIndex);
                            $this->querystring = substr($urlinfo['path'], $frontScriptEndIndex);                            
                        }

                        if (!array_key_exists('query', $urlinfo)) {
                            $this->aQueryData = $this->parseQueryString($conf);
                        }
                    } else {
                        $path = dirname($this->path) == DIRECTORY_SEPARATOR ? '' : dirname($this->path);
                        $this->path = sprintf('%s/%s', $path, $value);
                    }
                    break;

                case 'query':
                    $this->aQueryData = $this->parseQueryString($conf);
                    break;

                case 'fragment':
                    $this->anchor = $value;
                    break;
                }
            }
        }
    }
    
    function &singleton()
    {
        static $instance;
        if (!isset($instance)) {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            $urlHandler = $conf['site']['urlHandler'];
            $class = __CLASS__;
            $instance = new $class(null, true, new $urlHandler());
        }
        return $instance;
    }
    
    function getManagerName()
    {
        return $this->aQueryData['managerName'];
    }
    
    function getModuleName()
    {
        return $this->aQueryData['moduleName'];
    }
    
    /**
     * Returns querystring data as an array.
     *
     * @param boolean $strict If strict is true, managerName and moduleName are removed
     * @return array
     */
    function getQueryData($strict = false)
    {
        $aRet = $this->aQueryData;
        if ($strict) {
            if (isset($aRet['moduleName'])) {
                unset($aRet['moduleName']);    
            }
            if (isset($aRet['managerName'])) {
                unset($aRet['managerName']);    
            }
        }
        return $aRet;
    }
    
    /**
     * Returns querystring portion of url.
     *
     * @return string
     */
    function getQueryString() 
    {
        return $this->querystring;
    }
    
    function parseQueryString($conf) 
    {
        return $this->parserStrategy->parseQueryString($this, $conf);
    }
    
    function toString() 
    {
        return $this->parserStrategy->toString($this);
    }
    
    function makeLink($action = '', $mgr = '', $mod = '', $aList = array(), 
        $params = '', $idx = 0, $output = '')
    {
        //  a hack for 0.4.x style of building SEF URLs
        $url = & SGL_Url::singleton();
        return $url->parserStrategy->makeLink($action, $mgr, $mod, $aList, $params, $idx, $output);
    }

    /**
    * Returns the standard port number for a protocol.
    *
    * @param  string  $scheme The protocol to lookup
    * @return integer         Port number or NULL if no scheme matches
    *
    * @author Philippe Jausions <Philippe.Jausions@11abacus.com>
    */
    function getStandardPort($scheme)
    {
        switch (strtolower($scheme)) {
            
        case 'http':    return 80;
        case 'https':   return 443;
        case 'ftp':     return 21;
        case 'imap':    return 143;
        case 'imaps':   return 993;
        case 'pop3':    return 110;
        case 'pop3s':   return 995;
        default:        return null;
       }
    }

    /**
    * Forces the URL to a particular protocol.
    *
    * @param string  $protocol Protocol to force the URL to
    * @param integer $port     Optional port (standard port is used by default)
    */
    function setProtocol($protocol, $port = null)
    {
        $this->protocol = $protocol;
        $this->port = is_null($port) ? $this->getStandardPort() : $port;
    }
    
    /**
     * Resolves PHP_SELF var depending on implementation, ie apache, iis, cgi, etc.
     *
     * @abstract 
     */
    function resolveServerVars($conf = null)
    {
        //  it's apache
        if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
        
            //  however we're running from cgi, so populate PHP_SELF info from REQUEST_URI
            if (strpos(php_sapi_name(), 'cgi') !== false) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
                
            //  a ? is part of $conf['site']['frontScriptName'] and REQUEST_URI has more info
            } elseif ((strlen($_SERVER['REQUEST_URI']) > strlen($_SERVER['PHP_SELF']) 
                    && strstr($_SERVER['REQUEST_URI'], '?')
                    && !isset($conf['setup']))) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
            } else {
                //  do nothing, PHP_SELF is valid
            }
        //  it's IIS
        } else {
            $frontScriptName = is_null($conf) ? 'index.php' : $conf['site']['frontScriptName'];
            if (substr($_SERVER['SCRIPT_NAME'], -1, 1) != substr($frontScriptName, -1, 1)) {
                $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] . '?' . @$_SERVER['QUERY_STRING'];
            } else {
                $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] . @$_SERVER['QUERY_STRING'];
            }

        } 
    }
    
    function getHostName()
    {
        return $this->host;   
    }
    
    function getPath()
    {
        return $this->path;   
    }
    
    /**
     * Returns the front controller script name.
     *
     * @return string
     */
    function getFrontScriptName()
    {
        return $this->frontScriptName;   
    }

    /**
     * Ensures URL is fully qualified.
     *
     * @access  public
     * @param   string  $url    The relative URL string
     * @return  void
     */
    function toAbsolute(&$url)
    {
        $aUrl = parse_url($url);
        if (!(isset($aUrl['scheme']))) {
            $url = SGL_BASE_URL . '/' . $url;
        }
    }
    
    /**
     * Returns hostname + path with final slashes removed if present.
     *
     * @return string   The base url
     * @todo make logic more generic
     */
    function getBase()
    {
        $aParts = explode('/', $this->path);

        //  accomodate setup exception
        if (in_array('setup.php', $aParts)) {
            array_pop($aParts);
            $this->path = implode('/', $aParts);
        }
        
        $retUrl = $this->protocol . '://'
                   . $this->user . (!empty($this->pass) ? ':' : '')
                   . $this->pass . (!empty($this->user) ? '@' : '')
                   . $this->host . ($this->port == $this->getStandardPort($this->protocol) ? '' : ':' . $this->port)
                   . $this->path;
        
        //  handle case for user's homedir, ie, presence of tilda: example.com/~seagull
        if (preg_match('/~/', $retUrl)) {
            $retUrl = str_replace('~', '%7E', $retUrl);
        }
        //  remove trailing slash
        if (substr($retUrl, -1) == '/') {
            $retUrl = substr($retUrl, 0, -1);
        }
        return $retUrl;
    }
    
    /**
     * Parse string stored in resource_uri field in section table.
     *
     * This will always contain URL elements after the frontScriptName (index.php), never
     * a FQDN, and never simplified names, ie section table must specify module name and 
     * manager name explicitly, even if they are the same, ie user/user
     *
     * @param string $str
     * @return array  A hash containing URL info
     *
     * @todo this method is VERY similar to parseQueryString and should be consolidated
     */
    function parseResourceUri($str)
    {
        $ret = array();
        $default = array(
            'module' => 'default', 
            'manager' => 'default');        
            
        //  catch case for default page, ie, home
        if (empty($str)) {
            return $default;
        }
        $parts = array_filter(explode('/', $str), 'strlen');
        $numElems = count($parts);
        
        //  we need at least 2 elements
        if ($numElems < 2) {
            return $default;
        }
        $ret['module'] = $parts[0];
        $ret['manager'] = $parts[1];
        $actionExists = (isset($parts[2]) && $parts[2] == 'action') ? true : false;
        $ret['actionMapping'] = ($actionExists) ? $parts[3] : null;

        //  parse params
        $idx = ($actionExists) ? 4 : 2;
        
        //  break out if no params detected
        if ($numElems <= $idx) {
            return $ret;
        }        
        $aTmp = array();
        for ($x = $idx; $x < $numElems; $x++) {
            if ($x % 2) { // if index is odd
                $aTmp['varValue'] = urldecode($parts[$x]);
            } else {
                // parsing the parameters
                $aTmp['varName'] = urldecode($parts[$x]);
            }
            //  if a name/value pair exists, add it to request
            if (count($aTmp) == 2) {
                $ret['parsed_params'][$aTmp['varName']] = $aTmp['varValue'];
                $aTmp = array();                
            }
        }
        return $ret;               
    }
    
    /**
     * Checks to see if cookies are enabled, if not, session id is added to URL.
     *
     * PHP's magic querystring functionality is negated in SimpleNav::getTabsByRid(),
     * in other words, the ?PHPSESSID=aeff023230323 is stripped out
     *
     * @param string $url
     * @return void
     */
    function addSessionInfo(&$url)
    {
        //  determine is session propagated in cookies or URL
        $sessionInfo = defined('SID') ? SID : '';
        if (!empty($sessionInfo)) {

            //  determine glue
            $glue = (preg_match("/\?pageID/i", $url)) ? '&amp;' : '?';
            $url .= $glue . $sessionInfo . '&amp;/1/';
        }
    }
    
    /**
     * Removes the session name and session value elements from an array.
     *
     * @param array $aUrl
     */
    function removeSessionInfo(&$aUrl)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $key = array_search($conf['cookie']['name'], $aUrl);
        if ($key !== false) {
            unset($aUrl[$key], $aUrl[$key + 1]);
        }
    }
    
    /**
     * Returns an array of all elements from the front controller script name onwards.
     * 
     * @access  public
     * @static 
     *
     * @param   string  $url        Url to be parsed
     * @return  array   $aUriParts  An array of all significant parts of the URL, ie
     *                              from the front controller script name onwards
     */
    function toPartialArray($url, $frontScriptName)
    {       
        //  split elements (remove eventual leading/trailing slashes)
        $aUriParts = explode('/', trim($url, '/'));

        //  step through array and strip until fc element is reached
        foreach ($aUriParts as $elem) {
            if ($elem != $frontScriptName) {
                array_shift($aUriParts);
            } else {
                break;
            }
        }
        return $aUriParts;
    }
}

/**
 * Abstract url parser strategy
 *
 * @abstract
 */
class SGL_UrlParserStrategy
{   
    function parseQueryString() {}
    
    function makeLink($action, $mgr, $mod, $aList, $params, $idx, $output) {}
    
    function toString() {}
}

/**
 * Concrete SEF url parser strategy
 *
 */
class SGL_UrlParserSefStrategy extends SGL_UrlParserStrategy
{
    /**
    * Returns full url
    *
    * @return string Full url
    * @access public
    */
    function toString(/*SGL_Url*/$url)
    {
        $retUrl = $url->protocol . '://'
                   . $url->user . (!empty($url->pass) ? ':' : '')
                   . $url->pass . (!empty($url->user) ? '@' : '')
                   . $url->host . ($url->port == $url->getStandardPort($url->protocol) ? '' : ':' . $url->port)
                   . $url->path
                   . $url->frontScriptName
                   . (!empty($url->querystring) ? $url->querystring : '')
                   . (!empty($url->anchor) ? '#' . $url->anchor : '');

        return $retUrl;
    }
    
    /**
     * Analyzes querystring content and parses it into module/manager/action and params.
     *
     * @param SGL_Url $url
     * @return array        An array to be assigned to SGL_Url::aQueryData
     * @todo frontScriptName is already dealt with in SGL_Url constructor, remove from here
     */
    function parseQueryString(/*SGL_Url*/$url, $conf)
    {
        #$conf = $c->getAll();

        $aUriParts = SGL_Url::toPartialArray($url->url, $conf['site']['frontScriptName']);
        
        //  remap
        $aParsedUri['frontScriptName'] = array_shift($aUriParts);
        $aParsedUri['moduleName'] = strtolower(array_shift($aUriParts));
        $mgrCopy = array_shift($aUriParts);
        $aParsedUri['managerName'] = strtolower($mgrCopy);
        
        //  if frontScriptName empty, get from config
        $default = false;
        if (empty($aParsedUri['frontScriptName'])
                || $aParsedUri['frontScriptName'] != $conf['site']['frontScriptName']) {
            $aParsedUri['frontScriptName'] = $conf['site']['frontScriptName'];
        }

        //  if no module name present, get from config
        //  catch case where debugging with Zend supplies querystring params
        if (empty(  $aParsedUri['moduleName'])
                || (preg_match('/start_debug/', $aParsedUri['moduleName']))
                || (preg_match('/\?/i', $aParsedUri['moduleName']))) {
            $aParsedUri['moduleName'] = $conf['site']['defaultModule'];
            $default = true;
        }

        //  if no manager name, must be default manager, ie, has same name as module
        //  the exception is when the moduleName comes from the conf
        if ((empty( $aParsedUri['managerName']) && !$default)
                || (preg_match('/start_debug/', $aParsedUri['managerName']))) {
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];

        //  we are here because we're using defaults from config
        } elseif ($default) {
            $aParsedUri['managerName'] = $conf['site']['defaultManager'];
            if (!empty($conf['site']['defaultParams'])) {
                $aParsedUri['defaultParams'] = $conf['site']['defaultParams'];
            }
        }
        
        //  we've got module name so load and merge local and global configs
        //  unless we're running the setup wizard
        if (!isset($conf['setup'])) {
            $c = &SGL_Config::singleton();            
            $aModuleConfig = $c->load(SGL_MOD_DIR . '/' . $aParsedUri['moduleName'] . '/conf.ini');

            if ($aModuleConfig) {
                $c->merge($aModuleConfig);
            } else {         
                return PEAR::raiseError('Could not read current module\'s conf.ini file', 
                    SGL_ERROR_NOFILE);
            }
        }
        
        //  determine is moduleName is simplified, in other words, the mgr
        //  and mod names should be the same
        if ($aParsedUri['moduleName'] != $aParsedUri['managerName']) {
            if (SGL_Inflector::isMgrNameOmitted($aParsedUri)) {
                array_unshift($aUriParts, $mgrCopy);
                $aParsedUri['managerName'] = $aParsedUri['moduleName'];                
            }
        }
        
        //  catch case where when manger + mod names are the same, and cookies
        //  disabled, sglsessid gets bumped into wrong slot
        if (preg_match('/'.strtolower($conf['cookie']['name']).'/', $aParsedUri['managerName'])) {
            @list(,$cookieValue) = split('=', $aParsedUri['managerName']);
            $cookieValue = substr($cookieValue, 0, -1);
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            array_unshift($aUriParts, $cookieValue);
            array_unshift($aUriParts, $conf['cookie']['name']);
        }

        //  if 'action' is in manager slot, move it to querystring array, and replace 
        //  manager name with default mgr name, ie, that of the module
        if ($aParsedUri['managerName'] == 'action') {
            $aParsedUri['managerName'] = $aParsedUri['moduleName'];
            array_unshift($aUriParts, 'action');
        }

        //  if default params exist, append them to the uri array
        if (!empty($aParsedUri['defaultParams'])) {
            $aUriParts = array_merge($aUriParts, explode('/', $aParsedUri['defaultParams']));
        }

        $numParts = count($aUriParts);

        //  if varName/varValue don't match, assign a null varValue to the last varName
        if ($numParts % 2) {
            array_push($aUriParts, null);
            ++ $numParts;
        }

        //  parse FC querystring params
        $aQsParams = array();
        
        for ($i = 0; $i < $numParts; $i += 2) {
            $varName  = urldecode($aUriParts[$i]);
            $varValue = urldecode($aUriParts[$i+1]);

            //  check if the variable is an array
            if ((strpos($varName, '[') !== false) &&
                (strpos($varName, ']') !== false))
            {
                //  retrieve the array name ($matches[1]) and its eventual key ($matches[2])
                preg_match('/([^\[]*)\[([^\]]*)\]/', $varName, $matches);
                $aRequestVars = array_merge($_REQUEST, $aParsedUri);
                if (    !array_key_exists($matches[1], $aRequestVars)
                    &&  !array_key_exists($matches[1], $aQsParams)) {
                        $aQsParams[$matches[1]] = array();
                }
                //  no key given => append to array                
                if (empty($matches[2])) {
                    array_push($aQsParams[$matches[1]], $varValue);
                } else {
                    $aQsParams[$matches[1]][$matches[2]] = $varValue;
                }
            } else {
                $aQsParams[$varName] = $varValue;
            }
        }
        
        //  remove frontScriptName
        unset($aParsedUri['frontScriptName']);
        
        //  and merge the default request fields with extracted param k/v pairs
        return array_merge($aParsedUri, $aQsParams);        
    }
    
    /**
     * Best way I've come up with so far for passing all params required by Flexy to build a URL.
     *
     * @param string $action
     * @param string $mgr
     * @param string $mod
     * @param array $aList
     * @param string $params
     * @param integer $idx
     * @param object $output
     * @return string
     */
    function makeLink($action, $mgr, $mod, $aList, $params, $idx, $output)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  get a reference to the request object
        $req = & SGL_Request::singleton();

        //  determine module and manager names
        $mgr = (empty($mgr)) ? $req->get('managerName') : $mgr;
        $mod = (empty($mod)) ? $req->get('moduleName'): $mod;
        $url = $conf['site']['frontScriptName'] . '/';

        //  allow for default managers, ie, in faqMgr, don't
        //  return http://localhost.localdomain/seagull/www/index.php/faq/faq/action/edit/
        if ($mgr != $mod) {
            $url .= $mod . '/';
        }
        $url .= $mgr;

        //  only add action param if an action was supplied/found
        if (!(empty($action))) {
            $url .= '/action/' . $action;
        }

        //  if qs params are supplied
        if (!(empty($params))) {
            $aParams = explode('||', $params);
            $qs = '';
            foreach ($aParams as $param) {
                @list($qsParamName, $listKey) = explode('|', $param);

                //  regarding $aList:
                //  if we have an array of arrays (we're interating through a resultset)
                //  or no resulset was passed (qs params are literals)
                //  - empty array if invoked from manager (default arg)
                //  - string equal to 0 if ## passed from template
                if (is_array(end($aList)) 
                    || (is_array($aList) && !is_object(end($aList))) 
                    || !(count($aList)) 
                    || $aList == 0) {
                
                    //  determine type of param value
                    if (isset($aList[$idx][$listKey]) && !is_null($listKey)) { // pass referenced array element
                        $qsParamValue = $aList[$idx][$listKey];
                        
                    //  we're here because a simple array was passed for $aList, ie:
                    //  makeUrl(#edit#,#orgType#,#user#,orgTypes,#frmOrgTypeID#,id)
                    //  in this case, the key from the flexy foreach is what we want to assign as the value, ie
                    //  - frmOrgTypeId/0
                    //  - frmOrgTypeId/1 ... etc
                    } elseif (isset($aList[$idx]) && is_null($listKey)) {
                        $qsParamValue = $idx;
                        
                    } else {
                        if (stristr($listKey, '[')) { // it's a hash

                            //  split out images[fooBar] to array(images,fooBar)
                            $aElems = array_filter(preg_split('/[^a-z_]/i', $listKey), 'strlen');
                            if (!($aList) && is_a($output, 'SGL_Output')) {
                                
                                //  variable is of type $output->org['organisation_id'] = 'foo';
                                $qsParamValue = $output->{$aElems[0]}[$aElems[1]];
                            } else {
                                $qsParamValue = $aList[$idx][$aElems[0]][$aElems[1]];
                            }
                        } elseif (is_a($output, 'SGL_Output') && isset($output->{$listKey})) {
                            $qsParamValue = $output->{$listKey}; // pass $output property 
                        } else {
                            //  see blocks/SiteNews, not called from template                            
                            $qsParamValue = $listKey; // pass literal                        
                        }
                    }
                    $qs .= '/' . $qsParamName . '/' . $qsParamValue;
                } else {
                    $qs .= '/' . $qsParamName . '/' . $aList[$idx]->$listKey;
                }
            }
            //  append querystring
            $url .= $qs;
        }
        //  add url scheme and SGL prefix if necessary
        SGL_Url::toAbsolute($url);

        //  add a trailing slash if one is not present
        if (substr($url, -1) != '/') {
            $url .= '/';
        }
        //  add session info if necessary
        SGL_Url::addSessionInfo($url);
        
        return $url;
    }
}
?>