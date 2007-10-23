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
     *     params     => action/actionName/k1/v1/k2/v2
     *   )
     */
    private function _resolveOldStyleParams($aParams)
    {
        $aNewParams   = array();
        $aQueryParams = array();
        if (!empty($aParams[0])) {
            $aVars[] = 'action/' . $aParams[0];
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
                $aQueryParams[] = $k . '/' . $v;
            }
        }
        // all params goes here
        if (!empty($aQueryParams)) {
            $aNewParams['params'] = implode('/', $aQueryParams);
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
     * Make link.
     *
     * @todo add https support.
     *
     * @param array $aParams
     *
     * @return string
     */
    public function makeLink($aParams = array())
    {
        // resolve params in old style
        if (isset($aParams[0])) {
            $aParams = $this->_resolveOldStyleParams($aParams);
        }
        // set host without protocol
        if (!isset($aParams['host'])) {
            $aParams['host'] = $this->getBaseUrl();
        }
        $url = $this->_routes->util->urlFor($aParams);
        if ($url[strlen($url) -1] != '/') {
            $url .= '/';
        }
        return $url;
    }

    /**
     * Get Seagull base URL without protocol.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $baseUrl = substr(SGL_BASE_URL, strpos(SGL_BASE_URL, '://') + 3);
        $fcName  = SGL_Config::get('site.frontScriptName');
        if (!empty($fcName)) {
            $baseUrl .= '/' . $fcName;
        }
        return $baseUrl;
    }
}

?>