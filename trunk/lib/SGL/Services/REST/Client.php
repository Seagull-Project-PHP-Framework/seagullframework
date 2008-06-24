<?php

/**
 * uses PEAR error management
 */
require_once 'PEAR.php';

/**
 * uses XML_Unserializer to unserialize the response.
 */
require_once 'XML/Unserializer.php';

/**
 * uses XML_Serializer to read result.
 */
require_once 'XML/Serializer.php';

/**
 * uses HTTP to send the request.
 */
require_once 'HTTP/Request.php';

/**
 * Parent class for implementing a REST client, override and customise.
 *
 */
class SGL_Services_REST_Client
{
   /**
    * URI of the REST API
    *
    * @access  private
    * @var     string
    */
    var $_apiUrl = '';

   /**
    * Username
    *
    * @access  private
    * @var     string
    */
    var $_user   = null;

   /**
    * password
    *
    * @access  private
    * @var     string
    */
    var $_passwd = null;

   /**
    * XML_Unserializer, used to parse the XML
    *
    * @access  private
    * @var     object XML_Unserializer
    */
    var $_us = null;

    var $_clientName = 'SGL_Services_REST_Client';

    var $_aOptions = array();

    var $_entityName = '';

    var $_responseXml = '';

    var $_getRequest = '';

    /**
     * Constructor.
     *
     * @param string $user
     * @param string $passwd
     * @return SGL_Services_REST_Client
     */
    function SGL_Services_REST_Client($aOptions = array(), $user = null, $passwd = null)
    {
        $this->setOptions($aOptions);
        if (!is_null($user) && !is_null($passwd)) {
            $this->_user   = $user;
            $this->_passwd = $passwd;
        }
    }

    function setOptions($aOptions)
    {
        foreach ($aOptions as $k => $option) {
            $this->_aOptions[$k] = $option;
        }
    }

    function getOption($optionName)
    {
        if (isset($this->_aOptions[$optionName])) {
            return $this->_aOptions[$optionName];
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

   /**
    * Auxiliary method to send a request.
    *
    * @access   private
    * @param    string      REST verb
    * @param    array       parameters
    * @return   array|PEAR_Error
    */
    function _getRequest($verb, $params = array())
    {
        $url = sprintf('%s/action/%s', $this->_apiUrl, $verb);
        $string = '';
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                   $string .= $key . '['.$k.']/' .urlencode($v) .'/';
                }
                $value = $string;
            }
            //  remove extra slash if exists
            if (substr($value, -1, 1) == '/') {
                $value = substr($value, 0, (strlen($value) -1));
            }
            $append = (strlen($string))
                ? $value
                : $key . '/' . urlencode($value);
            $string = '';
            $url = $url . '/' . $append;
        }
        $this->_getRequest = $url;
        $request = &new HTTP_Request($url);
        if (!empty($this->_user) && !empty($this->_passwd)) {
            $request->setBasicAuth($this->_user, $this->_passwd);
        }
        $request->addHeader('User-Agent', $this->_clientName);

        $request->sendRequest();
        if ($request->getResponseCode() !== 200) {
            return PEAR::raiseError('Invalid Response Code', $request->getResponseCode());
        }

        $data = $this->_processResponse($request);
        return $data;
    }

    function _postRequest($verb, $params = array(), $oData)
    {
        $url = sprintf('%s/action/%s', $this->_apiUrl, $verb);
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }
            $url = $url . '/' . $key . '/' . urlencode($value);
        }

        $options = array(
            "indent"         => "    ",
            "linebreak"      => "\n",
            "classAsTagName" => true,
            "addDecl" => true,
        );

        $serializer = &new XML_Serializer($options);
        $result = $serializer->serialize($oData);
        if ($result === true ) {
            $xml = $serializer->getSerializedData();
        }

        $request = &new HTTP_Request($url);
        if (!empty($this->_user) && !empty($this->_passwd)) {
            $request->setBasicAuth($this->_user, $this->_passwd);
        }
        $request->addPostData($this->getEntityName(), $xml, $preencoded = false);
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->sendRequest();
        if ($request->getResponseCode() !== 200) {
            return PEAR::raiseError('Invalid Response Code', $request->getResponseCode());
        }

        $data = $this->_processResponse($request);
        return $data;
    }

    function _processResponse($request)
    {
        $xml = $this->_responseXml = $request->getResponseBody();

        if (!is_object($this->_us)) {
            $this->_us = &new XML_Unserializer();
            $this->_us->setOption('tagAsClass', true);
            $this->_us->setOption('ignoreKeys', $this->getKeysToIgnore());
            $this->_us->setOption('complexType', $this->getComplexTypes());
        }

        $result = $this->_us->unserialize($xml);
        if (PEAR::isError($result)) {
            return $result;
        }
        $data = $this->_us->getUnserializedData();
        return $data;
    }

    function getResponseXml()
    {
        return $this->_responseXml;
    }

    function getRawRequest()
    {
        return $this->_getRequest;
    }

    function getKeysToIgnore()
    {
        $aKeys = $this->getOption('ignoreKeys');
        return $aKeys;
    }

    function getComplexTypes()
    {
        $aKeys = $this->getOption('complexType');
        return $aKeys;
    }

    function getEntityName()
    {
        return $this->_entityName;
    }

    function setEntityName($name)
    {
        $this->_entityName = $name;
    }
}