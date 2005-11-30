<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2002-2004, Richard Heyes                                    |
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
    function SGL_URL(
        $url = null,
        $useBrackets = true,
        /*SGL_UrlParserStrategy*/ $parserStrategy = null,
        $conf = null)
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

            if (is_a($parserStrategy, 'SGL_UrlParserSimpleStrategy')) {
                $this->aQueryData = $this->parseQueryString($conf);
                return;
            }

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
        if (!is_null($url)) {
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
                        if ($this->frontScriptName != false) {
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
                        } else {
                            $this->path = dirname($_SERVER['SCRIPT_NAME']);
                            $this->querystring = str_replace($this->path, '', $urlinfo['path']);
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
        $aStrategies = array();
        if (is_null($this->parserStrategy)) {
            $aStrategies[] = new SGL_UrlParserSefStrategy();
        }
        if (!is_array($this->parserStrategy) && is_a($this->parserStrategy, 'SGL_UrlParserStrategy')) {
            $aStrategies[] = $this->parserStrategy;

        } elseif (is_array($this->parserStrategy)) {
            $aStrategies = $this->parserStrategy;

        } else {
            $ret = SGL::raiseError('unrecognised url strategy');
        }

        foreach ($aStrategies as $strategy) {

            //  all strategies will attempt to parse url, overwriting
            //  previous results as they do
            $ret = $strategy->parseQueryString($this, $conf);
        }
        return $ret;
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

            // we don't want to have index.php in our url, so REQUEST_URI as more info
            } elseif ($conf['site']['frontScriptName'] == false) {
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
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        if ($conf['site']['sessionInUrl']) {

            //  determine is session propagated in cookies or URL
            $sessionInfo = defined('SID') ? SID : '';
            if (!empty($sessionInfo)) {

                //  determine glue
                $glue = (preg_match("/\?pageID/i", $url)) ? '&amp;' : '?';
                $url .= $glue . $sessionInfo . '&amp;/1/';
            }
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

        if ($frontScriptName != false) {
            //  step through array and strip until fc element is reached
            foreach ($aUriParts as $elem) {
                if ($elem != $frontScriptName) {
                    array_shift($aUriParts);
                } else {
                    break;
                }
            }
        } else {
            $pathFromServer = dirname($_SERVER['SCRIPT_NAME']); //=> /seagull/branches/0.4-bugfix/www
            foreach ($aUriParts as $elem) {
                if (stristr($pathFromServer, $elem)) {
                    array_shift($aUriParts);
                } else {
                    break;
                }
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
?>
