<?php
class SGL_Response
{
    /**
     * Response data
     *
     * @var array
     */
    protected $aProps;

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
    protected $data;

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
        $this->aProps[$k] = $v;
    }

    public function add(array $aData)
    {
        foreach ($aData as $k => $v) {
            $this->aProps[$k] = $v;
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
            $this->aProps[$k] = $v;
        }
    }

    protected function __get($k)
    {
        if (isset($this->aProps[$k])) {
            return $this->aProps[$k];
        }
    }

    public function getHeaders()
    {
        return $this->aHeaders;
    }

    public function getBody()
    {
        return $this->aProps;
    }

    public function setBody($body)
    {
        $this->data = $body;
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
        return $this->data;
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
        SGL_Url2::toAbsolute($url);

        //  add a slash if one is not present
        if (substr($url, -1) != '/') {
            $url .= '/';
        }
        //  determine is session propagated in cookies or URL
        SGL_Url2::addSessionInfo($url);

        //  must be absolute URL, ie, string
        header('Location: ' . $url);
        exit;
    }

    /**
     * Used for outputting sub template in master
     *
     * @param string $templateEngine
     */
    public function outputBody($templateEngine = null)
    {
        if (!$this->template) {
            return;
        }
        $this->masterTemplate = $this->template;

#FIXME
        //  considerable hack to workaround recursive Flexy call
        $aData = $this->getBody();
        unset($aData['x'], $aData['_t'], $aData['this']);
        $resp = (object) $aData;

        $view = new SGL_View_HtmlSimple($resp, $templateEngine);
        echo $view->render();

        //  suppress error notices in templates
        //SGL::setNoticeBehaviour(SGL_NOTICES_DISABLED);
    }
}
?>