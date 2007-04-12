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
// | Author:   Julien Casanova <julien@soluo.fr>                               |
// +---------------------------------------------------------------------------+

define('SGL_RESPONSEFORMAT_JSON', 1);
define('SGL_RESPONSEFORMAT_PLAIN', 2);
define('SGL_RESPONSEFORMAT_JAVASCRIPT', 3);
define('SGL_RESPONSEFORMAT_HTML', 4);
/**
 * Abstract model controller for all the 'ajax provider' classes.
 *
 * @package SGL
 * @subpackage
 * @author  Julien Casanova <julien@soluo.fr>
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
     * @access  public
     * @return  void
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
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    function processResponse($response = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_null($response)) {
            return;
        }
        // Handle errors
        if (PEAR::isError($response)) {
            $response = array(
                'message'   => $response->getMessage(),
                'debugInfo' => $response->getDebugInfo(),
                'level'     => $response->getCode(),
                'errorType' => SGL_Error::constantToString($response->getCode())
            );
            header('HTTP/1.1 404 Not found');
        }
        // Returned encoded response with appropriate headers
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

        default:
            $ret = 'You haven\'t defined your response format, see SGL_AjaxProvider::processResponse';
        }
        return $ret;
    }
}
?>