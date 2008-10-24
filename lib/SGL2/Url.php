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

/**
 * Url class to work with Browser2 request type.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL2_Url
{
    /**
     * @var Horde_Routes_Config
     */
    protected $_routes;

    /**
     * @var array
     */
    protected $_aQueryData;

    /**
     * Constructor.
     *
     * @param array $aQueryData
     */
    public function __construct($aQueryData = array())
    {
        $this->_aQueryData = $aQueryData;
    }

    /**
     * Set routes.
     *
     * @param Horde_Routes_Config $oRoutes
     */
    public function setRoutes(Horde_Routes_Utils $oRoutes)
    {
        $this->_routes = $oRoutes;
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
            $this->_routes->mapper->{$k} = $v;
        }
    }

    /**
     * Format params specified in old SGL2_Output::makeUrl() style
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
    protected function _resolveOldStyleParams($aParams)
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
        // in case of SGL2_Output(#edit#,#user#,##,..)
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
    protected function _makeDefaultParamsArray($aParams)
    {
        $aVars     = array();
        $aKeywords = array('moduleName', 'managerName', 'controller',
            'anchor', 'host');
        if (SGL2_Config::get('translation.langInUrl')) {
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
    protected function _urlIsMatched($url)
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
            // use current module if nothing specified
            if (!isset($aParams['moduleName'])) {
                $aParams['moduleName'] = $this->_aQueryData['moduleName'];
            }
            // use current manager only if
            // 1. we are in same module
            if ($aParams['moduleName'] == $this->_aQueryData['moduleName']
                    // 2. it was not specified
                    && !isset($aParams['managerName'])
                    // 3. moduleName neq managerName
                    && $this->_aQueryData['moduleName'] != $this->_aQueryData['managerName']) {
                $aParams['managerName'] = $this->_aQueryData['managerName'];
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

        // set current language if none specified
        if (SGL2_Config::get('translation.langInUrl') && empty($aParams['lang'])) {
            $aParams['lang'] = 'en';
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
        return $this->makeLink(array_merge($this->_aQueryData, $aQueryData));
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
            $baseUrl = substr(SGL2_BASE_URL, strpos(SGL2_BASE_URL, '://') + 3);
        } else {
            $baseUrl = SGL2_BASE_URL;
        }
        $fcName = SGL2_Config::get('site.frontScriptName');
        if (!empty($fcName) && $includeFc) {
            $baseUrl .= '/' . $fcName;
        }
        return $baseUrl;
    }

    /**
     * Get query string as in SGL2_Url1.
     *
     * @return string
     */
    public function getQueryString()
    {
        $ret = '/';
        foreach ($this->_aQueryData as $k => $v) {
            if (!in_array($k, array('moduleName', 'managerName'))) {
                $ret .= $k . '/';
            }
            $ret .= $v . '/';
        }
        return $ret;
    }
}

?>