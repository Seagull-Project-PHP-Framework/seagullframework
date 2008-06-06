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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Url.php                                                                   |
// +---------------------------------------------------------------------------+
// | Authors:   Richard Heyes <richard at php net>                             |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: Url.php,v 1.32 2005/05/29 21:32:17 demian Exp $
//

#needed for BC output url hander, esp: SGL_URL::makeLink()
require_once SGL_CORE_DIR . '/UrlParser/SefStrategy.php';

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
    * Query string.
    *
    * The querystring is defined as everthing from the frontScriptName
    * to the end of the URI, not including anchors, examples:
    *
    *   - http://example.com/index.php/foo/bar/baz/fluux
    *   -> qs = foo/bar/baz/fluux
    *
    *   - http://example.com/foo/bar/baz/fluux?sess=a&rand=123
    *   -> qs = foo/bar/baz/fluux?sess=a&rand=123
    *
    *   - http://example.com/?sess=a&rand=123
    *   -> qs = sess=a&rand=123
    *
    *   - http://example.com/2006/02/07/my_interesting_blog.html
    *   -> qs - 2006/02/07/my_interesting_blog.html
    * @var array
    */
    var $querystring;
    var $aQueryData;
    var $frontScriptName;
    var $aStrategies = array();
    var $aRes = array();

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
    function __construct($url = null, $useBrackets = true, $parserStrategy = null)
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
        //  setup strategies array
        //  if no strats are provided, use native SEF strategy
        if (is_null($parserStrategy)) {
            $this->aStrategies[] = new SGL_UrlParser_SefStrategy();
        }
        if (!is_array($parserStrategy) && is_a($parserStrategy, 'SGL_UrlParserStrategy')) {
            $this->aStrategies[] = $parserStrategy;

        } elseif (is_array($parserStrategy)) {
            $this->aStrategies = $parserStrategy;

        } else {
            SGL::raiseError('unrecognised url strategy');
        }
        $this->frontScriptName = SGL_Config::get('site.frontScriptName');
    }

    /**
     * Attempts to parse URI elements.
     *
     * @return true on success, PEAR_Error on failure
     */
    function init()
    {
        // Only set defaults if $url is not an absolute URL
        if (!preg_match('/^[a-z0-9]+:\/\//i', $this->url)) {

            // FIXME: get rid of this
            if (is_a($this->aStrategies[0], 'SGL_UrlParser_SimpleStrategy')) {
                $this->aQueryData = $this->parseQueryString();
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
#FIXME: $_SERVER['PHP_SELF'] != $this->path when redir is appended, needs to be cleaned up
# refactor removal of frontScriptName with routine use in switch below
//            if (isset($this->path{0}) && $this->path{0} == '/') {
//                if ($this->frontScriptName != false) {
//                    $frontScriptStartIndex = strpos($this->path, $this->frontScriptName);
//                    $frontScriptEndIndex = $frontScriptStartIndex + strlen($this->frontScriptName);
//                    if ($frontScriptStartIndex) {
//                        $this->path = substr($this->path, 0, $frontScriptStartIndex);
//                    }
//                }
//            }
        }

        // Parse the URI and store the various parts
        if (!is_null($this->url)) {
            //  if we have a relative URI, enforce FQDN as required by parse_url
            $target = (preg_match('/^[a-z0-9]+:\/\//i', $this->url))
                ? $this->url
                : $this->toString();
            $urlinfo = parse_url($target);

            //  if a ? is present in frontScriptName, it gets stripped by calling $_SERVER['PHP_SELF']
            //  and needs to be re-added
            if (preg_match("/\?/", $this->frontScriptName)) {
                $urlinfo['path'] = preg_replace("/index.php/", "index.php?", $urlinfo['path']);
            }

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
                    //  the 'path' is deemed to be everything after the hostname,
                    //  but before the frontScriptName, including initial and final
                    //  slashes, ie: /seagull/trunk/www/
                    if (isset($value{0}) && $value{0} == '/') {
                        if ($this->frontScriptName != false) {
                            $frontScriptStartIndex = strpos($value, $this->frontScriptName);
                            $frontScriptEndIndex = $frontScriptStartIndex + strlen($this->frontScriptName);
                            if (!$frontScriptStartIndex) {

                                //  this is an install and index.php was omitted
                                $this->path = $urlinfo['path'];
                                $this->querystring = @$urlinfo['query'];
                            } else {
                                $this->path = substr($value, 0, $frontScriptStartIndex);
                                $this->querystring = substr($value, $frontScriptEndIndex);
                            }
                        } else {
                            $this->path = dirname($_SERVER['SCRIPT_NAME']) == DIRECTORY_SEPARATOR
                                ? ''
                                : dirname($_SERVER['SCRIPT_NAME']);
                            $this->querystring = str_replace($this->path, '', $urlinfo['path']);
                        }
                    } else {
                        $path = dirname($this->path) == DIRECTORY_SEPARATOR
                            ? ''
                            : dirname($this->path);
                        $this->path = sprintf('%s/%s', $path, $value);
                    }
                    break;

                case 'query':
                    if (isset($urlinfo['query']) && empty($this->querystring)) {
                        $this->querystring = $urlinfo['query'];
                    }
                    $ret = $this->parseQueryString();

                    if (PEAR::isError($ret)) {
                        return $ret;
                    } else {
                        $this->aQueryData = $ret;
                    }
                    //  populate querystring property
                    $this->querystring =    $this->getModuleName() .'/'.
                                            $this->getManagerName() .'/'.
                                            SGL_Array::implodeWithKeys('/', $this->getQueryData(true));
                    break;

                case 'fragment':
                    $this->anchor = $value;
                    break;
                }
            }
            if (!array_key_exists('query', $urlinfo)) {
                $ret = $this->parseQueryString();

                if (PEAR::isError($ret)) {
                    return $ret;
                } else {
                    $this->aQueryData = $ret;
                }
            }
            //  remove strategy objects to streamline for storage
            $this->removeStrategies();
            return true;
        }
        return SGL::raiseError('no URI data', SGL_ERROR_NODATA);
    }


    /**
     * SGL_Url singletons are currently only used for output URLs, so the output
     * format is taken from $conf.
     *
     * @return SGL_Url
     */
    function &singleton()
    {
        static $instance;
        if (!isset($instance)) {
            $parserStrategy = SGL_Config::get('site.outputUrlHandler');
            $class = __CLASS__;
            $instance = new $class(null, true, new $parserStrategy());
        }
        return $instance;
    }

    function getManagerName()
    {
        return @$this->aQueryData['managerName'];
    }

    function getModuleName()
    {
        return @$this->aQueryData['moduleName'];
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

        if (PEAR::isError($aRet)) {
            return $aRet;
        }
        if ($strict) {
            if (isset($aRet['moduleName'])) {
                unset($aRet['moduleName']);
            }
            if (isset($aRet['managerName'])) {
                unset($aRet['managerName']);
            }
        }
        //  zend debug cleanup
        $aZendDebugParams = array('debug_fastfile', 'debug_host', 'debug_new_session',
            'debug_no_cache', 'debug_port', 'debug_stop', 'debug_url', 'send_sess_end',
            'start_debug');
        foreach ($aZendDebugParams as $param) {
            if (isset($aRet[$param])) {
                unset($aRet[$param]);
            }
        }
        //  remove querystring data
        foreach ($aRet as $k => $v) {
            if (stristr($k, '?start_debug')) {
                unset($aRet[$k]);
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

    /**
     * Build array of strategies and executes parsers.
     *
     * @param array $conf
     * @return array
     */
    function parseQueryString()
    {
        foreach ($this->aStrategies as $strategy) {

            //  all strategies will attempt to parse url, overwriting
            //  previous results as they do
            $res = $strategy->parseQueryString($this);
            if (PEAR::isError($res)) {
                return $res;
            } else {
                $this->aRes[] = $res;
            }
        }
        //  reverse order of strats so Classic comes last in array and overrides SEF
        if (SGL_Config::get('site.outputUrlHandler')
                && SGL_Config::get('site.outputUrlHandler') == 'SGL_UrlParser_ClassicStrategy') {
            $tmp = array_reverse($this->aRes);
            $this->aRes = $tmp;
        }
        $ret = call_user_func_array('array_merge', $this->aRes);
        return $ret;
    }

    /**
     * Parses an array of querystring param names and values and returns a
     * key/value hash.
     *
     * @param array $aUriParts  The list of candidate values.
     * @param array $aParsedUri The list of existing valid value, to avoid overwriting
     * @return array            A hash of k/v pairs
     */
    function querystringArrayToHash($aUriParts, $aParsedUri = array())
    {
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
        return $aQsParams;
    }

    function getStrategiesFingerprint($aStrats)
    {
        $aStratNames = array();
        foreach ($aStrats as $strategy) {
            $aStratNames[] = get_class($strategy);
        }
        $fingerprint = implode('', $aStratNames);
        return $fingerprint;
    }

    /**
    * Returns the full URI as a string.
    *
    * @return string Full URI
    * @access public
    */
    function toString()
    {
        $retUrl = $this->protocol . '://'
                   . $this->user . (!empty($this->pass) ? ':' : '')
                   . $this->pass . (!empty($this->user) ? '@' : '')
                   . $this->getHostName() . ($this->port == $this->getStandardPort($this->protocol)
                        ? ''
                        : ':' . $this->port)
                   . $this->getPath();

        //  append front script name if not already present
        if ($this->frontScriptName != false) {
            if (!preg_match("/$this->frontScriptName/", $retUrl)) {
                $retUrl .= $this->getFrontScriptName();
            }
        }

        //  add a trailing slash if one is not present
        $qs  = $this->getQueryString();
        $qs .= !empty($this->anchor) ? '#' . $this->anchor : '';
        if (!empty($qs) && $qs{0} != '/' && substr($retUrl, -1) != '/') {
            $qs = '/' . $qs . (!empty($this->anchor) ? '#' . $this->anchor : '');
        }
        $retUrl .= $qs;
        return $retUrl;
    }

    function makeLink($action = '', $mgr = '', $mod = '', $aList = array(),
        $params = '', $idx = 0, $output = '')
    {
        //  a workaround for 0.4.x style of building SEF URLs
        $url = & SGL_Url::singleton();
        return $url->aStrategies[0]->makeLink(
            $action, $mgr, $mod, $aList, $params, $idx, $output);
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
    function resolveServerVars()
    {
        //  cgi SERVER vars hack causes probs w/quickform
        if (!is_file(SGL_VAR_DIR . '/INSTALL_COMPLETE.php')) {
            return;
        }
        //  it's apache
        if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI'])) {

            //  however we're running from cgi, so populate PHP_SELF info from REQUEST_URI
            if (strpos(php_sapi_name(), 'cgi') !== false) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];

            //  a ? is part of $conf['site']['frontScriptName'] and REQUEST_URI has more info
            } elseif ((strlen($_SERVER['REQUEST_URI']) > strlen($_SERVER['PHP_SELF'])
                    && strstr($_SERVER['REQUEST_URI'], '?')
                    && ! SGL_Config::get('site.setup'))) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];

            // we don't want to have index.php in our url, so REQUEST_URI as more info
            } elseif (!SGL_Config::get('site.frontScriptName')) {
                $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
            } else {
                //  do nothing, PHP_SELF is valid
            }

        //  it's IIS
        } else {
            $frontScriptName = SGL_Config::get('site.frontScriptName')
                ? SGL_Config::get('site.frontScriptName')
                : 'index.php';
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
        if (SGL_Config::get('session.allowedInUri')) {

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
        $key = array_search(SGL_Config::get('cookie.name'), $aUrl);
        if ($key !== false) {
            unset($aUrl[$key], $aUrl[$key + 1]);
        }
    }

    function removeStrategies()
    {
        unset($this->aStrategies);
    }

    /**
     * Returns an array of all elements from the front controller script name onwards,
     * including the frontScriptName.
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

        if ($frontScriptName) {
            //  step through array and strip until fc element is reached
            foreach ($aUriParts as $elem) {
                if ($elem != $frontScriptName) {
                    array_shift($aUriParts);
                } else {
                    break;
                }
            }
        } else {
            $aParts = @parse_url($url);
            if ($aParts && count($aParts)) {
                $pathFromServer = $aParts['path'];

                if (!empty($pathFromServer)) {
                    $a_ = explode('/', $pathFromServer);
                    $aUriParts = SGL_Array::removeBlanks($a_);
                }
            } else {
                $aUriParts = array();
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
}
?>
