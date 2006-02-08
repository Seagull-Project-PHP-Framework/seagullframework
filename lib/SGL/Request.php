<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Request.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/**
 * Wraps all $_GET $_POST $_FILES arrays into a Request object, provides a number of filtering methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 */
class SGL_Request
{
    var $aProps;

    /**
     * Sets up a request object.
     *
     * @return SGL_Request
     */
    function SGL_Request()
    {

    }

    function init()
    {
        if ($this->isEmpty()) {
            $res = (!SGL::runningFromCLI()) ? $this->initHttp() : $this->initCli();
        }
        return $res;
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
            $err = $instance->init();            
        }
        return $instance;
    }

    function isEmpty()
    {
        return count($this->aProps) ? false : true;
    }

    function initHttp()
    {
        //  get config singleton
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        //  get current url object
        #$urlHandler = $conf['site']['urlHandler'];
        $cache = & SGL_Cache::singleton();
        $cacheId = md5($_SERVER['PHP_SELF']);

        if ($data = $cache->get($cacheId, 'urls')) {
            $url = unserialize($data);
            SGL::logMessage('url from cache', PEAR_LOG_DEBUG);
        } else {
            require_once dirname(__FILE__) . '/UrlParser/SimpleStrategy.php';
            require_once dirname(__FILE__) . '/UrlParser/AliasStrategy.php';
            require_once dirname(__FILE__) . '/UrlParser/ClassicStrategy.php';

            $aStrats = array(
                new SGL_UrlParser_ClassicStrategy(),
                new SGL_UrlParser_AliasStrategy(),
                new SGL_UrlParser_SefStrategy(),
                );
            $url = new SGL_URL($_SERVER['PHP_SELF'], true, $aStrats);

            $err = $url->init();
            if (PEAR::isError($err)) {
                return $err;
            }
            $data = serialize($url);
            $cache->save($data, $cacheId, 'urls');
            SGL::logMessage('url parsed', PEAR_LOG_DEBUG);
        }
        $aQueryData = $url->getQueryData();
        if (PEAR::isError($aQueryData)) {
            return $aQueryData;
        }
        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($url);

        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_GET, $_FILES, $aQueryData, $_POST);
    }

    function initCli()
    {
        require_once 'Console/Getopt.php';

        $shortOptions = '';
        $longOptions = array('moduleName=', 'managerName=', 'action=');

        $console = new Console_Getopt();
        $arguments = $console->readPHPArgv();
        array_shift($arguments);
        $options = $console->getopt2($arguments, $shortOptions, $longOptions);

        if (!is_array($options) ) {
            die("CLI parameters invalid\n");
        }

        $this->aProps = array();

        /* Take all _valid_ parameters and add them into aProps. */
        while (list($parameter, $value) = each($options[0])) {
            $value[0] = str_replace('--', '', $value[0]);
            $this->aProps[$value[0]] = $value[1];
        }
    }

    function merge($aHash)
    {
        $firstKey = key($aHash);
        if (!array_key_exists($firstKey, $this->aProps)) {
            $this->aProps = array_merge_recursive($this->aProps, $aHash);
        }
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

    function add($aParams)
    {
        $this->aProps = array_merge_recursive($this->aProps, $aParams);
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

    function getUri()
    {
        $uri = '';
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $sglSessionName = $conf['cookie']['name'];

        foreach ($this->aProps as $key => $value) {
            if (!empty($value) && $key != 'lang' && strpos($key, $sglSessionName) === false) {
                $uri .= ($key == 'moduleName' || $key == 'managerName')
                    ? $value . '/'
                    : $key . '/' . $value . '/';
            }
        }

        // remove trailing slash
        $uri = preg_replace('/\/$/','',$uri);

        return $uri;
    }

    function debug()
    {
        $c = &SGL_Config::singleton();
        $c->set('site', array('blocksEnabled' => 0));
        print '<pre>';
        print_r($this->aProps[$key]);
    }
}

?>