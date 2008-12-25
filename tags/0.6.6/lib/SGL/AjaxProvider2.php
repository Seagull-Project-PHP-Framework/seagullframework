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
// | Seagull 0.9                                                               |
// +---------------------------------------------------------------------------+
// | AjaxProvider2.php                                                         |
// +---------------------------------------------------------------------------+
// | Author: Julien Casanova <julien@soluo.fr>                                 |
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// | Author: Dmitri Lakachauskis <lakiboy83@gmail.com>                         |
// +---------------------------------------------------------------------------+

require_once SGL_CORE_DIR . '/Delegator.php';

if (!defined('SGL_RESPONSEFORMAT_JSON')) {
    define('SGL_RESPONSEFORMAT_JSON',       1);
    define('SGL_RESPONSEFORMAT_PLAIN',      2);
    define('SGL_RESPONSEFORMAT_JAVASCRIPT', 3);
    define('SGL_RESPONSEFORMAT_HTML',       4);
    define('SGL_RESPONSEFORMAT_XML',        5);
}

/**
 * Abstract model controller for all the 'ajax provider' classes.
 *
 * @package SGL
 */
abstract class SGL_AjaxProvider2
{
    /**
     * Holds configuration.
     *
     * @var array
     */
    public $conf = array();

    /**
     * DB abstraction layer
     *
     * @var DB resource
     */
    public $dbh;

    /**
     * SGL_Request.
     *
     * @var SGL_Request
     */
    public $req;

    /**
     * Data access object.
     *
     * @var SGL_Delegator
     */
    public $da;

    /**
     * Constant indicating response format.
     *
     * @var integer
     */
    public $responseFormat = SGL_RESPONSEFORMAT_JSON;

    /**
     * Message information passed back to client.
     *
     * @var array
     */
    public $aMsg = array();

    /**
     * Current module.
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * Current action.
     *
     * @var string
     */
    protected $_actionName;

    /**
     * Constructor.
     */
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->conf = SGL_Config::singleton()->getAll();
        $this->req  = SGL_Registry::singleton()->getRequest();
        $this->dbh  = $this->_getDb();
        $this->da   = new SGL_Delegator();

        $moduleName        = str_replace('AjaxProvider', '', get_class($this));
        $this->_moduleName = strtolower($moduleName);
        $this->_actionName = $this->req->getActionName();
    }

    private function _getDb()
    {
        $locator = SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    /**
     * Main workflow.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     *
     * @return void
     */
    public function process(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (method_exists($this, $this->_actionName)) {
            $ok = true; // by default request is authorised

            $container = ucfirst($this->_moduleName . 'AjaxProvider');
            if (SGL_Config::get("$container.requiresAuth")
                && SGL_Config::get('debug.authorisationEnabled'))
            {
                $aMethods = explode(',', SGL_Config::get("$container.requiresAuth"));
                $aMethods = array_map('trim', $aMethods);
                if (in_array($this->_actionName, $aMethods)) {
                    $ok = $this->_isOwner(SGL_Session::getUid());
                }
            }
            if (PEAR::isError($ok)) {
                $ret = $ok;
            } elseif (!$ok) {
                $ret = SGL::raiseError('authorisation failed',
                    SGL_ERROR_INVALIDAUTHORISATION);
            } else {
                $ret = $this->{$this->_actionName}($input, $output);
                if (SGL_Error::count()) {
                    $ret = SGL_Error::getLast();
                }
            }
        } else {
            $ret = SGL::raiseError('requested method does not exist');
        }
        return $ret;
    }

    /**
     * Ensure the current user can perform requested action.
     *
     * @param integer $requestedUserId
     *
     * @return boolean
     */
    abstract protected function _isOwner($requestedUserId);

    /**
     * Raises notification messages intended for end user.
     *
     * @param array mixed     string $message['message']
     *                        int    $message['type']    SGL_MESSAGE_ERROR
     *                                                   SGL_MESSAGE_INFO
     *                                                   SGL_MESSAGE_WARNING
     * @param boolean $translate
     * @param boolean $persist
     */
    protected function _raiseMsg($aMessage, $translate = false, $persist = false)
    {
        if (!is_array($aMessage)) {
            $aMessage = array('message' => $aMessage);
        }
        if ($translate) {
            $aMessage['message'] = SGL_Output::tr($aMessage['message']);
        }
        if (!isset($aMessage['type'])) {
            $aMessage['type'] = SGL_MESSAGE_INFO;
        }
        if ($persist) {
            // propagate for next request
            SGL_Session::set('message', $aMessage['message']);
            SGL_Session::set('messageType', $aMessage['type']);
        }
        $aMessage['persist'] = $persist;
        $this->aMsg = $aMessage;
    }

    /**
     * Encode data to JSON.
     *
     * @param mixed $data
     *
     * @return string
     */
    public static function jsonEncode($data)
    {
        return json_encode($data);
    }

    /**
     * Get formatted error.
     *
     * @param PEAR_Error $oError
     *
     * @return array
     */
    public static function handleError($oError)
    {
        $aResponse = array(
            'message'   => $oError->getMessage(),
            'debugInfo' => $oError->getDebugInfo(),
            'level'     => $oError->getCode(),
            'errorType' => SGL_Error::constantToString($oError->getCode())
        );
        $ret = self::jsonEncode($aResponse);
        return $ret;
    }

    /**
     * Render template.
     *
     * @param SGL_Output $output
     * @param mixed $aParams
     *
     * @return string
     *
     * @todo do we really need to clone output object?
     */
    protected function _renderTemplate($output, $aParams)
    {
        if (!is_array($aParams)) {
            $aParams = array('masterTemplate' => $aParams);
        }
        $o = clone $output;
        $o->moduleName = $this->_moduleName;
        foreach ($aParams as $k => $v) {
            $o->$k = $v;
        }
        $view = new SGL_HtmlSimpleView($o);
        return $view->render();
    }
}
?>