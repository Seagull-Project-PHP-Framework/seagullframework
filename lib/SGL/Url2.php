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
     * @var Horde_Routes_Config
     */
    private $_routes;

    /**
     * Set routes.
     *
     * @param Horde_Routes_Config $oRoutes
     */
    public function setRoutes(Horde_Routes_Config $oRoutes)
    {
        $this->_routes = $oRoutes;
    }

    /**
     * Format params specified in old SGL_Output::makeUrl() style
     * to new system.
     *
     * @param array $aParams
     *
     * @return array
     *   Array (
     *     moduleName => name of module
     *     controller => name of manager
     *     k1         => v1
     *     k2         => v2
     *   )
     */
    private function _resolveOldStyleParams($aParams)
    {
        $aNewParams = array();
        if (!empty($aParams[0])) {
            $aNewParams['action'] = $aParams[0];
        }
        if (!empty($aParams[1])) {
            $aNewParams['controller'] = $aParams[1];
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
                    if (is_object($element) && isset($element->$v)) {
                        $v = $element->$v;
                    } elseif (is_array($element) && isset($element[$v])) {
                        $v = $element[$v];
                    }
                }
                $aNewParams[$k] = $v;
            }
        }
        // in case of SGL_Output(#edit#,#user#,##,..)
        if (isset($aNewParams['controller'])
                && !isset($aNewParams['moduleName'])) {
            $aNewParams['moduleName'] = $aNewParams['controller'];
        }
        // this allows to skip manager name if it is the same as module name
        if (isset($aNewParams['controller'])
                && isset($aNewParams['moduleName'])
                && $aNewParams['controller'] == $aNewParams['moduleName']) {
            unset($aNewParams['controller']);
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
        $aVars = array();
        foreach ($aParams as $k => $v) {
            // skip "keywords"
            if ($k == 'moduleName' || $k == 'controller'
                    || $k == 'anchor' || $k == 'host') {
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
            // use current module if nothing specified
            if (!isset($aParams['moduleName'])) {
                $aParams['moduleName'] = $this->aQueryData['moduleName'];
            // use current manager if nothing specified
            } elseif (!isset($aParams['controller'])
                    && $this->aQueryData['moduleName'] != $this->aQueryData['managerName']) {
                $aParams['controller'] = $this->aQueryData['managerName'];
            }
        // named route
        } else {
            $namedRoute = true;
        }
        $this->_routes->mapper->appendSlash = true;

        // try to match URL in new style
        $url = $this->_routes->util->urlFor($aParams);
        // if URL was not matched do it in old style
        if (!$this->_urlIsMatched($url)) {
            $aParams = $this->_makeDefaultParamsArray($aParams);
            $url = $this->_routes->util->urlFor($aParams);
            $namedRoute = false;
        }

        return empty($namedRoute) ? $url : $this->getBaseUrl() . $url;
    }

    /**
     * Get Seagull base URL without protocol.
     *
     * @return string
     */
    public function getBaseUrl($skipProtocol = false)
    {
        if ($skipProtocol) {
            $baseUrl = substr(SGL_BASE_URL, strpos(SGL_BASE_URL, '://') + 3);
        } else {
            $baseUrl = SGL_BASE_URL;
        }
        $fcName = SGL_Config::get('site.frontScriptName');
        if (!empty($fcName)) {
            $baseUrl .= '/' . $fcName;
        }
        return $baseUrl;
    }
}

?>