<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Request.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

define('SGL_REQUEST_BROWSER',   1);
define('SGL_REQUEST_CLI',       2);
define('SGL_REQUEST_AJAX',      3);
define('SGL_REQUEST_XMLRPC',    4);
define('SGL_REQUEST_AMF',     	5);

/**
 * Loads Request driver, provides a number of filtering methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 */
class SGL_Request
{
    var $aProps;

    function init()
    {
        if ($this->isEmpty()) {
            $type = $this->getRequestType();
            $typeName = $this->constantToString($type);
            $file = SGL_CORE_DIR . '/Request/' . $typeName . '.php';
            if (!is_file($file)) {
              return PEAR::raiseError('Request driver could not be located',
                  SGL_ERROR_NOFILE);
            }
            require_once $file;
            $class = 'SGL_Request_' . $typeName;
            if (!class_exists($class)) {
              return PEAR::raiseError('Request driver class does not exist',
                  SGL_ERROR_NOCLASS);
            }
            $obj = new $class();
            $ok = $obj->init();

            return PEAR::isError($ok)
                ? $ok
                : $obj;
        }
    }

    function constantToString($constant)
    {
        switch($constant) {
        case SGL_REQUEST_BROWSER:
            $ret = 'Browser';
            break;

        case SGL_REQUEST_CLI:
            $ret = 'Cli';
            break;

        case SGL_REQUEST_AJAX:
            $ret = 'Ajax';
            break;

        case SGL_REQUEST_AMF:
            $ret = 'Amf';
            break;
        }
        return $ret;
    }

    function getRequestType()
    {
        if (SGL::runningFromCLI()) {
            $ret = SGL_REQUEST_CLI;

        } elseif (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                        $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $ret = SGL_REQUEST_AJAX;

        } else if(isset($_SERVER['CONTENT_TYPE']) &&
            $_SERVER['CONTENT_TYPE'] == 'application/x-amf') {
            $ret = SGL_REQUEST_AMF;

        } else {
            $ret = SGL_REQUEST_BROWSER;
        }
        return $ret;
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
    function &singleton($forceNew = false)
    {
        static $instance;

        if (!isset($instance) || $forceNew) {
            $obj = new SGL_Request();
            $instance = $obj->init();
        }
        return $instance;
    }

    function isEmpty()
    {
        return count($this->aProps) ? false : true;
    }

    function getType()
    {
        return $this->type;
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

    function reset()
    {
        unset($this->aProps);
        $this->aProps = array();
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
        if (isset($this->aProps['managerName'])) {
            $ret = $this->aProps['managerName'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }

    function getActionName()
    {
        if (isset($this->aProps['action'])) {
            $ret = $this->aProps['action'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }

    function getUri()
    {
        $uri = '';
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $sglSessionName = $conf['cookie']['name'];

        foreach ($this->aProps as $key => $value) {
            if (is_array($value)) {
                continue;
            }
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
        print_r($this->aProps);
    }
}
?>