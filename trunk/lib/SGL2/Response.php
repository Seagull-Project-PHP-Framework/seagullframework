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
// $Id$


class SGL2_Response
{
    /**
     * Response data
     *
     * @var array
     */
    protected $_aProps;

    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $_code;

    /**
     * Stores output string to be returned to user
     *
     * @var string
     */
    protected $_data;

    /**
     * List of messages to be returned to user
     *
     * @var array
     */
    protected $_aMessages;

    /**
     * HTTP headers
     *
     * @var array
     */
    protected $aHeaders = array();

    public function set($k, $v)
    {
        $this->_aProps[$k] = $v;
    }

    public function add(array $aData)
    {
        foreach ($aData as $k => $v) {
            $this->_aProps[$k] = $v;
        }
    }

    public function setMessages(array $aMessages)
    {
        $this->_aMessages = $aMessages;
    }


    /**
     * If object attribute does not exist, magically set it to data array
     *
     * @param unknown_type $k
     * @param unknown_type $v
     */
    protected function __set($k, $v)
    {
        if (!isset($this->$k)) {
            $this->_aProps[$k] = $v;
        }
    }

    protected function __get($k)
    {
        if (isset($this->_aProps[$k])) {
            return $this->_aProps[$k];
        }
    }

    public function getHeaders()
    {
        return $this->aHeaders;
    }

    public function getBody()
    {
        return $this->_aProps;
    }

    public function setBody($body)
    {
        $this->_data = $body;
    }

    public function addHeader($header)
    {
        if (!in_array($header, $this->aHeaders)) {
            $this->aHeaders[] = $header;
        }
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    protected function __toString()
    {
        return $this->_data;
    }

    /**
     * Used for outputting template in layout
     *
     * @param string $templateEngine
     */
    public function outputBody($templateEngine = null)
    {
        if (!$this->template) {
            return;
        }
        $this->layout = $this->template;

#FIXME
        //  considerable hack to workaround recursive Flexy call
        $aData = $this->getBody();
        unset($aData['x'], $aData['_t'], $aData['this']);
        $resp = (object) $aData;

        $view = new SGL2_View_HtmlSimple($resp, $templateEngine);
        echo $view->render();
    }

    /**
     * Wrapper for PHP header() redirects.
     *
     * Simplified version of Wolfram's HTTP_Header class
     *
     * @param   mixed   $url    target URL
     * @return  void
     * @todo incomplete
     */
    function redirect($url = '')
    {
        //  check for absolute uri as specified in RFC 2616
        SGL2_Url::toAbsolute($url);

        //  add a slash if one is not present
        if (substr($url, -1) != '/') {
            $url .= '/';
        }
        //  determine is session propagated in cookies or URL
        SGL2_Url::addSessionInfo($url);

        //  must be absolute URL, ie, string
        header('Location: ' . $url);
        exit;
    }
}
?>