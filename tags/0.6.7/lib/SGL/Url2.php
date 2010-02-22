<?php

/**
 * Url class to work with Browser2 request type.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_URL2
{
    /**
     * @var SGL_Request
     */
    private $_request;

    /**
     * @var Horde_Routes_Util
     */
    private $_routes;

    /**
     * Constructor.
     *
     * @param Horde_Routes_Util $oRoutes
     */
    public function __construct(Horde_Routes_Utils $oRoutes)
    {
        $this->_routes = $oRoutes;
    }

    /**
     * Set request.
     *
     * @param SGL_Request $oRequest
     */
    public function setRequest(SGL_Request $oRequest)
    {
        $this->_request = $oRequest;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set mapper options.
     *
     * @param array $aOpts
     *
     * @return viod
     */
    public function setMapperOptions($aOpts)
    {
        foreach ($aOpts as $k => $v) {
            $this->_routes->mapper->$k = $v;
        }
    }

    /**
     * Format params specified in old SGL_Output::makeUrl() style
     * to new system.
     *
     * @param array $aParams
     *
     * @return array
     *   Array (
     *     moduleName  => name of module
     *     managerName => name of manager
     *     k1          => v1
     *     k2          => v2
     *   )
     */
    private function _resolveOldStyleParams($aParams)
    {
        $aNewParams = array();
        if (!empty($aParams[0])) {
            $aNewParams['action'] = $aParams[0];
        }
        if (!empty($aParams[1])) {
            $aNewParams['managerName'] = $aParams[1];
        }
        if (!empty($aParams[2])) {
            $aNewParams['moduleName'] = $aParams[2];
        }
        if (!empty($aParams[3]) && isset($aParams[5])) {
            $element = $aParams[3][$aParams[5]];
        }
        if (!empty($aParams[4])) {
            $aVars = explode('||', $aParams[4]);
            foreach ($aVars as $varString) {
                list($k, $v) = explode('|', $varString);
                if (isset($element)) {
                    if (is_object($element)
                        && (isset($element->$v) || @$element->$v))
                    {
                        $v = $element->$v;
                    } elseif (is_array($element) && isset($element[$v])) {
                        $v = $element[$v];
                    }
                }
                $aNewParams[$k] = $v;
            }
        }
        // in case of SGL_Output(#edit#,#user#,##,..)
        if (isset($aNewParams['managerName'])
                && !isset($aNewParams['moduleName'])) {
            $aNewParams['moduleName'] = $aNewParams['managerName'];
        }
        return $aNewParams;
    }

    /**
     * Make array suitable for default Routes.
     *
     * @param array $aParams
     *
     * @return array
     */
    private function _makeDefaultParamsArray($aParams)
    {
        $aVars     = array();
        $aKeywords = array('moduleName', 'managerName', 'controller',
            'anchor', 'host');
        if (SGL_Config::get('translation.langInUrl')) {
            array_push($aKeywords, 'lang');
        }
        foreach ($aParams as $k => $v) {
            if (in_array($k, $aKeywords)) { // skip "keywords"
                continue;
            }
            $aVars[] = $k . '/' . $v;
            unset($aParams[$k]);
        }
        if (!empty($aVars)) {
            $aParams['params'] = implode('/', $aVars);
        }
        return $aParams;
    }

    /**
     * Identify if given URL is ok (i.e. was matched by Horde).
     *
     * @param string $url
     *
     * @return boolean
     */
    private function _urlIsMatched($url)
    {
        return strpos($url, '?') === false;
    }

    /**
     * Make link.
     *
     * @todo add https support.
     *
     * @param array mixed
     *
     * @return string
     */
    public function makeLink($aParams = array())
    {
        if (is_array($aParams)) {
            // resolve params in old style
            if (isset($aParams[0])) {
                $aParams = $this->_resolveOldStyleParams($aParams);
            }
            // set host without protocol
            if (!isset($aParams['host'])) {
                $aParams['host'] = $this->getBaseUrl(true);
            }

            $aQueryData = $this->_request->getAll();

            // use current module if nothing specified
            if (!isset($aParams['moduleName'])) {
                $aParams['moduleName'] = $aQueryData['moduleName'];
            }
            // use current manager only if
            // 1. we are in same module
            if ($aParams['moduleName'] == $aQueryData['moduleName']
                    // 2. it was not specified
                    && !isset($aParams['managerName'])
                    // 3. moduleName neq managerName
                    && $aQueryData['moduleName'] != $aQueryData['managerName']) {
                $aParams['managerName'] = $aQueryData['managerName'];
            }
        // named route
        } else {
            $namedRoute = true;
        }

        // rename managerName -> controller
        if (is_array($aParams) && isset($aParams['managerName'])) {
            if (($aParams['moduleName'] != $aParams['managerName'])
                || !empty($namedRoute))
            {
                $aParams['controller'] = $aParams['managerName'];
            }
            unset($aParams['managerName']);
        }

        if (SGL_Config::get('translation.langInUrl')) {
            // set current language if none specified
            if (empty($aParams['lang'])) {
                $aParams['lang'] = SGL::getCurrentLang();
            // ensure we don't pass encoding
            } elseif (strpos($aParams['lang'], '-') !== false) {
                $aParams['lang'] = reset(explode('-', $aParams['lang']));
            }
        }

        // try to match URL in new style
        $url = $this->_routes->urlFor($aParams);
        // if URL was not matched do it in old style
        if (!$this->_urlIsMatched($url)) {
            $aParams = $this->_makeDefaultParamsArray($aParams);
            $url = $this->_routes->urlFor($aParams);
            $namedRoute = false;
        }

        return empty($namedRoute) ? $url : $this->getBaseUrl() . $url;
    }

    /**
     * Make current link.
     *
     * @param array $aQueryData
     *
     * @return string
     */
    public function makeCurrentLink($aQueryData = array())
    {
        return $this->makeLink(array_merge($this->_request->getAll(), $aQueryData));
    }

    /**
     * Alias for makeCurrentLink().
     *
     * @see self::makeCurrentLink()
     *
     * @return string
     */
    public function toString()
    {
        return $this->makeCurrentLink();
    }

    /**
     * Get Seagull base URL without protocol.
     *
     * @param boolean $skipProtocol
     * @param boolean $includeFc
     *
     * @return string
     */
    public function getBaseUrl($skipProtocol = false, $includeFc = true)
    {
        if ($skipProtocol) {
            $baseUrl = substr(SGL_BASE_URL, strpos(SGL_BASE_URL, '://') + 3);
        } else {
            $baseUrl = SGL_BASE_URL;
        }
        $fcName = SGL_Config::get('site.frontScriptName');
        if (!empty($fcName) && $includeFc) {
            $baseUrl .= '/' . $fcName;
        }
        return $baseUrl;
    }

    /**
     * Get query string as in SGL_Url1.
     *
     * @return string
     *
     * @deprecated
     */
    public function getQueryString()
    {
        $aQueryData = $this->_request->getAll();

        $ret = '/';
        foreach ($aQueryData as $k => $v) {
            if (!in_array($k, array('moduleName', 'managerName'))) {
                $ret .= $k . '/';
            }
            $ret .= $v . '/';
        }
        return $ret;
    }
}
?>