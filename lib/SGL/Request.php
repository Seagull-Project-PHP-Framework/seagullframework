<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
define('SGL_REQUEST_AMF',       5);

/**
 * Loads Request driver, provides a number of filtering methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 */
class SGL_Request
{
    protected $aProps;
    protected $_aTainted;
    private static $instance;

    public function __construct($type = null)
    {
        if ($this->isEmpty()) {
            $type = (is_null($type))
                ? $this->_getRequestType()
                : $type;
            $typeName = $this->_constantToString($type);
            $strat = 'SGL_Request_' . $typeName;
            $obj = new $strat();
            error_log('##########   Req type: '.$strat);
            $this->_aTainted = $obj->init();
        }
    }

    protected function _constantToString($constant)
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
            if (SGL_Config::get('site.inputUrlHandlers') == 'Horde_Routes') {
                $ret = 'Ajax2';
            }
            break;

        case SGL_REQUEST_AMF:
            $ret = 'Amf';
            break;
        }
        return $ret;
    }

    /**
     * Used internally to determine request type before Request strategy instantiated.
     *
     * @return integer
     */
    protected function _getRequestType()
    {
        $ret = SGL_REQUEST_BROWSER;
        return $ret;
    }


    public function isEmpty()
    {
        return count($this->aProps) ? false : true;
    }

#FIXME // get object
    /**
     * Returns constant representing request type.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieves values from Request object.
     *
     * @access  public
     * @param   mixed   $paramName  Request param name
     * @param   boolean $allowTags  If html/php tags are allowed or not
     * @return  mixed               Request param value or null if not exists
     */
    public function get($key, $allowTags = false)
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

            return $clean;

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
    public function set($key, $value)
    {
        $this->aProps[$key] = $value;
    }

    protected function __set($key, $value)
    {
        $this->aProps[$key] = $value;
    }

    protected function __get($key)
    {
        if (isset($this->aProps[$key])) {
            return $this->aProps[$key];
        }
    }

    public function add(array $aParams)
    {
        $this->aProps = array_merge_recursive($this->aProps, $aParams);
    }

    public function reset()
    {
        unset($this->aProps);
        $this->aProps = array();
    }
    /**
     * Return an array of all filtered Request properties.
     *
     * @return array
     */
    public function getClean()
    {
        return $this->aProps;
    }

    /**
     * Return an array of all tainted (raw) Request properties.
     *
     * @return array
     */
    public function getTainted()
    {
        return $this->_aTainted;
    }


    public function getModuleName()
    {
        return $this->aProps['moduleName'];
    }

    public function getManagerName()
    {
        if (isset( $this->aProps['managerName'])) {
            $ret = $this->aProps['managerName'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }

    public function getActionName()
    {
        if ( isset($this->aProps['action'])) {
            $ret = $this->aProps['action'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     * @todo what's this?
     */
    public function getName()
    {
        if (isset( $this->aProps['controller'])) {
            $ret = $this->aProps['controller'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }
}
?>
