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
// | Seagull 0.4                                                               |
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
 * @since   PHP 4.1
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
    function SGL_URL($url = null, $useBrackets = true)
    {
        $this->__construct($url, $useBrackets);
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
    */
    function __construct($url = null, $useBrackets = true)
    {
        $HTTP_SERVER_VARS  = !empty($_SERVER) ? $_SERVER : $GLOBALS['HTTP_SERVER_VARS'];

        $this->useBrackets = $useBrackets;
        $this->url         = $url;
        $this->user        = '';
        $this->pass        = '';
        $this->host        = '';
        $this->port        = 80;
        $this->path        = '';
        $this->querystring = array();
        $this->anchor      = '';

        // Only use defaults if not an absolute URL given
        if (!preg_match('/^[a-z0-9]+:\/\//i', $url)) {

            $this->protocol    = (@$HTTP_SERVER_VARS['HTTPS'] == 'on' ? 'https' : 'http');

            /**
            * Figure out host/port
            */
            if (!empty($HTTP_SERVER_VARS['HTTP_HOST']) AND preg_match('/^(.*)(:([0-9]+))?$/U', $HTTP_SERVER_VARS['HTTP_HOST'], $matches)) {
                $host = $matches[1];
                if (!empty($matches[3])) {
                    $port = $matches[3];
                } else {
                    $port = $this->getStandardPort($this->protocol);
                }
            }

            $this->user        = '';
            $this->pass        = '';
            $this->host        = !empty($host) ? $host : (isset($HTTP_SERVER_VARS['SERVER_NAME']) ? $HTTP_SERVER_VARS['SERVER_NAME'] : 'localhost');
            $this->port        = !empty($port) ? $port : (isset($HTTP_SERVER_VARS['SERVER_PORT']) ? $HTTP_SERVER_VARS['SERVER_PORT'] : $this->getStandardPort($this->protocol));
            $this->path        = !empty($HTTP_SERVER_VARS['PHP_SELF']) ? $HTTP_SERVER_VARS['PHP_SELF'] : '/';
            $this->querystring = isset($HTTP_SERVER_VARS['QUERY_STRING']) ? $this->_parseRawQuerystring($HTTP_SERVER_VARS['QUERY_STRING']) : null;
            $this->anchor      = '';
        }

        // Parse the url and store the various parts
        if (!empty($url)) {
            $urlinfo = parse_url($url);

            // Default querystring
            $this->querystring = array();

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
                        $this->path = $value;
                    } else {
                        $path = dirname($this->path) == DIRECTORY_SEPARATOR ? '' : dirname($this->path);
                        $this->path = sprintf('%s/%s', $path, $value);
                    }
                    break;

                case 'query':
                    $this->querystring = $this->_parseRawQueryString($value);
                    break;

                case 'fragment':
                    $this->anchor = $value;
                    break;
                }
            }
        }
    }

    /**
    * Returns full url
    *
    * @return string Full url
    * @access public
    */
    function getURL()
    {
        $querystring = $this->getQueryString();

        $this->url = $this->protocol . '://'
                   . $this->user . (!empty($this->pass) ? ':' : '')
                   . $this->pass . (!empty($this->user) ? '@' : '')
                   . $this->host . ($this->port == $this->getStandardPort($this->protocol) ? '' : ':' . $this->port)
                   . $this->path
                   . (!empty($querystring) ? '?' . $querystring : '')
                   . (!empty($this->anchor) ? '#' . $this->anchor : '');

        return $this->url;
    }

    /**
    * Adds a querystring item
    *
    * @param  string $name       Name of item
    * @param  string $value      Value of item
    * @param  bool   $preencoded Whether value is urlencoded or not, default = not
    * @access public
    */
    function addQueryString($name, $value, $preencoded = false)
    {
        if ($preencoded) {
            $this->querystring[$name] = $value;
        } else {
            $this->querystring[$name] = is_array($value) ? array_map('rawurlencode', $value): rawurlencode($value);
        }
    }

    /**
    * Removes a querystring item
    *
    * @param  string $name Name of item
    * @access public
    */
    function removeQueryString($name)
    {
        if (isset($this->querystring[$name])) {
            unset($this->querystring[$name]);
        }
    }

    /**
    * Sets the querystring to literally what you supply
    *
    * @param  string $querystring The querystring data. Should be of the format foo=bar&x=y etc
    * @access public
    */
    function addRawQueryString($querystring)
    {
        $this->querystring = $this->_parseRawQueryString($querystring);
    }

    /**
    * Returns flat querystring
    *
    * @return string Querystring
    * @access public
    */
    function getQueryString()
    {
        if (!empty($this->querystring)) {
            foreach ($this->querystring as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $querystring[] = $this->useBrackets ? sprintf('%s[%s]=%s', $name, $k, $v) : ($name . '=' . $v);
                    }
                } elseif (!is_null($value)) {
                    $querystring[] = $name . '=' . $value;
                } else {
                    $querystring[] = $name;
                }
            }
            $querystring = implode(ini_get('arg_separator.output'), $querystring);
        } else {
            $querystring = '';
        }

        return $querystring;
    }

    /**
    * Parses raw querystring and returns an array of it
    *
    * @param  string  $querystring The querystring to parse
    * @return array                An array of the querystring data
    * @access private
    */
    function _parseRawQuerystring($querystring)
    {
        $parts  = preg_split('/[' . preg_quote(ini_get('arg_separator.input'), '/') . ']/', $querystring, -1, PREG_SPLIT_NO_EMPTY);
        $return = array();

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                $value = substr($part, strpos($part, '=') + 1);
                $key   = substr($part, 0, strpos($part, '='));
            } else {
                $value = null;
                $key   = $part;
            }
            if (substr($key, -2) == '[]') {
                $key = substr($key, 0, -2);
                if (@!is_array($return[$key])) {
                    $return[$key]   = array();
                    $return[$key][] = $value;
                } else {
                    $return[$key][] = $value;
                }
            } elseif (!$this->useBrackets AND !empty($return[$key])) {
                $return[$key]   = (array)$return[$key];
                $return[$key][] = $value;
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
    * Resolves //, ../ and ./ from a path and returns
    * the result. Eg:
    *
    * /foo/bar/../boo.php    => /foo/boo.php
    * /foo/bar/../../boo.php => /boo.php
    * /foo/bar/.././/boo.php => /foo/boo.php
    *
    * This method can also be called statically.
    *
    * @param  string $url URL path to resolve
    * @return string      The result
    */
    function resolvePath($path)
    {
        $path = explode('/', str_replace('//', '/', $path));

        for ($i=0; $i<count($path); $i++) {
            if ($path[$i] == '.') {
                unset($path[$i]);
                $path = array_values($path);
                $i--;

            } elseif ($path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != '') ) ) {
                unset($path[$i]);
                unset($path[$i-1]);
                $path = array_values($path);
                $i -= 2;

            } elseif ($path[$i] == '..' AND $i == 1 AND $path[0] == '') {
                unset($path[$i]);
                $path = array_values($path);
                $i--;

            } else {
                continue;
            }
        }

        return implode('/', $path);
    }

    /**
    * Returns the standard port number for a protocol
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
    * Forces the URL to a particular protocol
    *
    * @param string  $protocol Protocol to force the URL to
    * @param integer $port     Optional port (standard port is used by default)
    */
    function setProtocol($protocol, $port = null)
    {
        $this->protocol = $protocol;
        $this->port = is_null($port) ? $this->getStandardPort() : $port;
    }
    
    function resolveServerVars()
    {
        //  it's apache
        if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {
        
            //  however we're running from cgi, so populate PHP_SELF info from REQUEST_URI
            if (strpos(php_sapi_name(), 'cgi') !== false) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
                
            //  a ? is part of $conf['site']['frontScriptName'] and REQUEST_URI has more info
            } elseif ((strlen($_SERVER['REQUEST_URI']) > strlen($_SERVER['PHP_SELF']) 
                    && strstr($_SERVER['REQUEST_URI'], '?'))) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
            } else {
                //  do nothing, PHP_SELF is valid
            }
        //  it's IIS
        } else {
            if (substr($_SERVER['SCRIPT_NAME'], -1, 1) != substr($conf['site']['frontScriptName'], -1, 1)) {
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
    
    function getFrontScriptName()
    {
        return $this->frontScriptName;   
    }
    
    /**
     * Converts querystring into/se/friendly/format.
     *
     * Returns an array of all elements after the front script name
     * 
     * @access  public
     * @param   $url    Url to be parsed
     * @return  array   $aUriParts  An array of all significant parts of the URL, ie
     *                              from the front controller script name onwards
     */
    function getSignificantSegments($url)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  split elements (remove eventual leading/trailing slashes)
        $aUriParts = explode('/', trim($url, '/'));

        //  step through array and strip until fc element is reached
        foreach ($aUriParts as $elem) {
            if ($elem != $conf['site']['frontScriptName']) {
                array_shift($aUriParts);
            } else {
                break;
            }
        }
        return $aUriParts;
    }
    
    /**
     * Returns true if manager name is the same of module name, ie, index.php/faq/faq/.
     *
     * @param string $url
     * @return boolean
     */
    function containsDuplicates($url)
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
    function isSimplified($url, $sectionName)
    {
        if (!(empty($url))) {
            $aUrlPieces = explode('/', $url);
            $moduleNameUrl = $aUrlPieces[0];
            $aSections =  explode('/', $sectionName);
            $ret = in_array($moduleNameUrl, $aSections) && (SGL_Url::containsDuplicates($sectionName));
        } else {
            $ret = false;
        }
        return $ret;
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
     * Parse string stored in resource_uri field in section table.
     *
     * This will always contain URL elements after the frontScriptName (index.php), never
     * a FQDN, and never simplified names, ie section table must specify module name and 
     * manager name explicitly, even if they are the same, ie user/user
     *
     * @param string $str
     * @return array  A hash containing URL info
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
     * Converts querystring into/se/friendly/format.
     *
     * @access  public
     * @return  void
     * @todo    this data structure should be more similar to the one parsed in 
     *              SGL_Url::parseResourceUri()
     * @todo    use same method for SGL_Url::parseResourceUri()
     * @todo    implement file-based caching or url combinations, simple hashmap
     @ @todo    factor out config loading
     */
    function makeSearchEngineFriendly($aUriParts)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

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
                || (preg_match('/start_debug/', $aParsedUri['moduleName']))) {
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
        
        /////////////////////////////////////////////////////////////////
        //  
        //  config loading below needs to be factored out
        //
        /////////////////////////////////////////////////////////////////
        
        //  we've got module name so load and merge local and global configs
        $aModuleConfig = SGL::getModuleConfig($aParsedUri['moduleName']);
        if ($aModuleConfig) {
            SGL::configMerge($aModuleConfig);
        } else {
            SGL::raiseError('Could not read current module\'s conf.ini file', 
                SGL_ERROR_NOFILE);
        }
        
        //  determine is moduleName is simplified, in other words, the mgr
        //  and mod names should be the same
        if ($aParsedUri['moduleName'] != $aParsedUri['managerName']) {
            if (SGL_Url::mgrNameOmitted($aParsedUri)) {
                array_unshift($aUriParts, $mgrCopy);
                $aParsedUri['managerName'] = $aParsedUri['moduleName'];                
            }
        }
        
        //  catch case where when manger + mod names are the same, and cookies
        //  disabled, sglsessid gets bumped into wrong slot
        if (preg_match('/'.strtolower($conf['cookie']['name']).'/', $aParsedUri['managerName'])) {
            list(,$cookieValue) = split('=', $aParsedUri['managerName']);
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
                $req = & SGL_Request::singleton();
                $aRequestVars = $req->getAll();
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
        //  merge the default request fields with extracted param k/v pairs
        return array_merge($aParsedUri, $aQsParams);
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
    function mgrNameOmitted($aParsedUri)
    {
        $fullMgrName = SGL_Url::getManagerNameFromSimplifiedName(
            $aParsedUri['managerName']);
        
        //  compensate for case-sensitivity
        $corrected = SGL::caseFix($fullMgrName, true);
        $path = SGL_MOD_DIR .'/'. $aParsedUri['moduleName'] . '/classes/' . $corrected . '.php';
        
        //  if the file exists, mgr name is valid and has not been omitted 
        return !file_exists($path);
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
    function makeLink($action = '', $mgr = '', $mod = '', $aList = array(), 
        $params = '', $idx = 0, $output = '')
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

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
        $conf = & $GLOBALS['_SGL']['CONF'];
        $key = array_search($conf['cookie']['name'], $aUrl);
        if ($key !== false) {
            unset($aUrl[$key], $aUrl[$key + 1]);
        }
    }
}
/*
improved URL class for 
- cleaner implementation in constants.php
- works with both tradition and FC querystrings

usage :

//  sort out compat issues
SGL_Url::resolveServerVars();

//  determine current request
$url = new SGL_Url($_SERVER['PHP_SELF']);  // set frontScriptName in const.

$host = $url->getHostName();

//  eg, handles situation where your URL is http://localhost/seagull/trunk/www/index.php
//  ie, hostName = localhost; path = /seagull/trunk/www/
$conf['site']['baseUrl'] = $url->getHostName() . $url->getPath();

- save $url to request registry for later use

- getSignificantSegments() becomes getQueryString()
$string = $url->getQueryString();
$dataStructure = $url->getQueryData();

SGL_Url
(
    [scheme] => https
    [host] => example.com
    [path] => /pls/portal30/PORTAL30.wwpob_page.changetabs/index.php
    [frontScriptName] => index.php
    [raw_query] => p_back_url=http%3A%2F%2Fexample.com%2Fservlet%2Fpage%3F_pageid%3D360%2C366%2C368%2C382%26_dad%3Dportal30%26_schema%3DPORTAL30&foo=bar
    [query] => Array
                (
                    [foo] => bar
                    [baz] => quux
                )
)


//  building SGL URLs
    $url = new SGL_Url();
    
    $url->setModule('publisher');
    $url->setManager('articleview');
    $url->setAction('list');
    $url->addQueryString('frmArticleId', 23);
    $output = $url->toString(SGL_URL_ABS);
    
//  for Flexy output:
makeLink(#self/publisher/articleview/action/view/frmArticleID/item_id#, aPagedData[data])

//  https
makeLink(#self/publisher/articleview/action/view/frmArticleID/item_id#,aPagedData[data],#https#)
-------------------------------------------------^^^^^^^^^^^^^ var name
--------------------------------------------------------------^^^^^^^^ obj prop/array key
-----------------------------------------------------------------------^^^^^^^^^^^^^^^^ collection
----------------------------------------------------------------------------------------^^^^^^^ is https or not

//  working with SGL_Url type, switching FC/traditional implementation at runtime
$url = new SGL_Url($url, $useBrackets = true, new SefUrlStrategy()); // as in Search Engine Friendly

*/
?>