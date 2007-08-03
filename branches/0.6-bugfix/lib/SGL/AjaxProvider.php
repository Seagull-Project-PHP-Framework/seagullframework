<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007, Demian Turner                                         |
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
// | AjaxProvider.php                                                          |
// +---------------------------------------------------------------------------+
// | Author: Julien Casanova <julien@soluo.fr>                                 |
// +---------------------------------------------------------------------------+

define('SGL_RESPONSEFORMAT_JSON', 1);
define('SGL_RESPONSEFORMAT_PLAIN', 2);
define('SGL_RESPONSEFORMAT_JAVASCRIPT', 3);
define('SGL_RESPONSEFORMAT_HTML', 4);
define('SGL_RESPONSEFORMAT_XML', 5);

/**
 * Abstract model controller for all the 'ajax provider' classes.
 *
 * @package SGL
 *
 * @author Julien Casanova <julien@soluo.fr>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 *
 * @abstract
 */
class SGL_AjaxProvider
{
    /**
     * Holds configuration
     *
     * @var array
     */
    var $conf = array();

    /**
     * DB abstract layer
     *
     * @var DB resource
     */
    var $dbh = null;

    /**
     * Constant indicating response format.
     *
     * @var integer
     */
    var $responseFormat = SGL_RESPONSEFORMAT_HTML;

    /**
     * Constructor.
     *
     * @access public
     */
    function SGL_AjaxProvider()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $this->dbh = $this->_getDb();
    }

    function &_getDb()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = &SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    /**
     * Main routine of processing ajax requests.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     *
     * @return mixed
     */
    function process(&$input, &$output)
    {
        $req = $input->getRequest();
        $actionName = $req->getActionName();

        // in case some filters already sent response, use it
        if (!empty($input->ajaxRequestMessage)) {
            $ret = $input->ajaxRequestMessage;
        } elseif (!method_exists($this, $actionName)) {
            $ret = 'requested method does not exist';
        } else {

            // by default request is authorised
            $ok = true;

            // only implement on demand
            $providerContainer = ucfirst($req->getModuleName()) . 'AjaxProvider';
            if (!empty($this->conf[$providerContainer]['requiresAuth'])
                    && $this->conf['debug']['authorisationEnabled']) {
                $aMethods = explode(',', $this->conf[$providerContainer]['requiresAuth']);
                $aMethods = array_map('trim', $aMethods);
                if (in_array($actionName, $aMethods)) {
                    $resourseId = $this->getAuthResourceId();
                    $ok = $this->isOwner($resourseId, SGL_Session::getUid());
                }
            }
            $ret = $ok
                ? $this->$actionName()
                : array(
                      'type'    => 'error',
                      'message' => SGL_Output::translate('authorisation failed')
                  );
        }
        return $this->processResponse($ret);
    }

    /**
     * Authorisation routine.
     *
     * @param mixed $resourseId
     * @param integer $userId
     *
     * @return boolean
     *
     * @abstract
     */
    function isOwner($resourseId, $userId)
    {
        return true;
    }

    /**
     * Get resource ID.
     *
     * @return mixed
     *
     * @abstract
     */
    function getAuthResourceId()
    {
        return 'resourceId';
    }

    /**
     * Procces result of ajax method and send appropriate headers.
     *
     * @param mixed $response
     *
     * @return mixed
     */
    function processResponse($response = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_null($response)) {
            return;
        }
        // handle errors
        if (PEAR::isError($response)) {
            $response = SGL_Error::toString($response);
            header('HTTP/1.1 404 Not found');
        }
        // return encoded response with appropriate headers
        switch (strtoupper($this->responseFormat)) {
        case SGL_RESPONSEFORMAT_JSON:
            require_once 'HTML/AJAX/JSON.php';
            $json = new HTML_AJAX_JSON();
            $ret = $json->encode($response);
            header('Content-Type: text/plain');
            break;

        case SGL_RESPONSEFORMAT_HTML:
            $ret = $response;
            header('Content-Type: text/html');
            break;

        case SGL_RESPONSEFORMAT_PLAIN:
            $ret = $response;
            header('Content-Type: text/plain');
            break;

        case SGL_RESPONSEFORMAT_JAVASCRIPT:
            $ret = $response;
            header('Content-Type: text/javascript');
            break;

        case SGL_RESPONSEFORMAT_XML:
            $ret = $response;
            header('Content-Type: text/xml');
            break;

        default:
            $ret = 'You haven\'t defined your response format, '
                . 'see SGL_AjaxProvider::processResponse';
        }
        return $ret;
    }
}
?>