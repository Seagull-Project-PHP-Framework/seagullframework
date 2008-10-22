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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// | Request.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/**
 * Loads Request driver, provides a number of filtering methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 */
class SGL2_Request
{
    const BROWSER   = 1;
    const CLI       = 2;
    const AJAX      = 3;
    const XMLRPC    = 4;
    const AMF       = 5;

    protected $_aClean      = array();
    protected $_aTainted    = array();
    protected $_driver      = null;
    protected $_type;

    public function __construct($type = null)
    {
        if ($this->isEmpty()) {
            $type = (is_null($type))
                ? $this->_resolveType()
                : $type;
            $this->setType($type);
            $strat = 'SGL2_Request_' . $this->_getTypeName();
            if (!SGL2_File::exists(SGL2_Inflector::classToFile($strat))) {
                throw new Exception('Request driver not found');
            }
            $this->_driver = new $strat();
            //error_log('##########   Req type: '.$strat);
            $aData = $this->_driver->init();
            $this->_aTainted = $aData;
            //  data is implicitly trusted for CLI
            if ($this->getType() == self::CLI) {
                $this->_aClean = $aData;
            }
        }
    }

    public function setType($type)
    {
        $this->_type = $type;
    }

    protected function _getTypeName()
    {
        $class = new ReflectionClass(get_class($this));
        $aConstants = $class->getConstants();
        $aConstantsIntIndexed = array_flip($aConstants);
        $const = $aConstantsIntIndexed[$this->_type];
        $name = ucfirst(strtolower($const));
        return $name;
    }

    /*
        $r = new SGL2_Request(SGL2_Request::CLI)
        $type = $r->getType();
        if (SGL2_Registry('request')->getType() == SGL2_Request::CLI) { ...}
    */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Used internally to determine request type before Request strategy instantiated.
     *
     * @return integer
     */
    protected function _resolveType()
    {
        if (PHP_SAPI == 'cli') {
            $ret = self::CLI;

        } elseif (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                        $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $ret = self::AJAX;

        } elseif (isset($_SERVER['CONTENT_TYPE']) &&
            $_SERVER['CONTENT_TYPE'] == 'application/x-amf') {
            $ret = self::AMF;

        } else {
            $ret = self::BROWSER;
        }
        return $ret;
    }

    public function isEmpty()
    {
        return count($this->_aClean) ? false : true;
    }

    /**
     * Retrieves values from Request object.
     *
     * @access  public
     * @param   mixed   $paramName  Request param name
     * @param   boolean $allowTags  If html/php tags are allowed or not
     * @return  mixed               Request param value or null if not exists
     * @todo make additional arg for defalut value
     */
    public function get($key, $allowTags = false)
    {
        if (isset($this->_aClean[$key])) {

            //  don't operate on reference to avoid segfault :-(
            $copy = $this->_aClean[$key];

            //  if html not allowed, run an enhanced strip_tags()
            if (!$allowTags) {
                $clean = SGL2_String::clean($copy);

            //  if html is allowed, at least remove javascript
            } else {
                $clean = SGL2_String::removeJs($copy);
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
        $this->_aClean[$key] = $value;
    }

    protected function __set($key, $value)
    {
        $this->_aClean[$key] = $value;
    }

    protected function __get($key)
    {
        if (isset($this->_aClean[$key])) {
            return $this->_aClean[$key];
        }
    }

    public function add(array $aParams)
    {
        $this->_aClean = array_merge_recursive($this->_aClean, $aParams);
    }

    public function reset()
    {
        unset($this->_aClean);
        $this->_aClean = array();
    }
    /**
     * Return an array of all filtered Request properties.
     *
     * @return array
     */
    public function getClean()
    {
        return $this->_aClean;
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
        return $this->_aClean['moduleName'];
    }

    public function getControllerName()
    {
        if (isset( $this->_aClean['controllerName'])) {
            $ret = $this->_aClean['controllerName'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }

    public function getActionName()
    {
        if ( isset($this->_aClean['action'])) {
            $ret = $this->_aClean['action'];
        } else {
            $ret = 'default';
        }
        return $ret;
    }
}
?>
