<?php

require_once 'Horde/Routes/Mapper.php';
require_once 'Horde/Routes/Exception.php';
require_once 'Horde/Routes/Route.php';
require_once 'Horde/Routes/Utils.php';

require_once SGL_CORE_DIR . '/Url2.php';

/**
 * Browser2 request type, which uses Horder_Routes package
 * to resolve query data, instead SGL_Url heavy parsing used by Browser1.
 *
 * @package SGL
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Request_Browser2 extends SGL_Request
{
    /**
     * Resolve query data by connecting to routes.
     *
     * @return void
     */
    public function init()
    {
        if (SGL_Config::get('site.frontScriptName')) {
            $qs = isset($_SERVER['PATH_INFO'])
                ? $_SERVER['PATH_INFO']
                : '/';
        } else {
            $baseUrl       = SGL_Config::get('site.baseUrl');
            list($proto, ) = explode('://', $baseUrl, 2);
            $host          = $_SERVER['HTTP_HOST'];
            $url           = $proto . '://' . $host . $_SERVER['REQUEST_URI'];
            $qs            = urldecode(str_replace($baseUrl, '', $url));

            // we want to be able to resolve routes with question marks at the end,
            // but without adding * to each route path
            $qs            = reset(explode('?', $qs));
        }

        $defModule  = SGL_Config::get('site.defaultModule');
        $defManager = SGL_Config::get('site.defaultManager');
        $defParams  = SGL_Config::get('site.defaultParams');

        // show lang in URL
        $prependLang  = SGL_Config::get('translation.langInUrl');
        $prependRegex = $prependLang ? ':lang/' : '';

        // Connect to custom routes.
        // Custom routes have higher priority, thus connect to them before
        // default Seagull SEO routes.
        $aRoutes = $this->_getCustomRoutes();
        if ($prependRegex) {
            $aRoutes = $this->_prependRegex($aRoutes, $prependRegex);
        } else {

            // fixme
            foreach ($aRoutes as $k => $aRoute) {
                $this->_ignoreRouteModification($aRoute);
                $aRoutes[$k] = $aRoute;
            }
        }

        // create mapper
        $m = new Horde_Routes_Mapper(array(
            'explicit'       => true, // do not connect to Horder defaults
            'controllerScan' => array('SGL_Request_Browser2', 'getAvailableManagers'),
        ));

        foreach ($aRoutes as $aRouteData) {
            call_user_func_array(array($m, 'connect'), $aRouteData);
        }

        // Seagull SEO routes connection
        //   *  all available routes variants are marked with numbers.
        //
        if ($prependLang) {

            // This route fixes problem, when connecting with "/",
            // always resolves to default language
            $m->connect('/', array(
                'moduleName' => $defModule,
            ));

            // Step zero: connect to language
            //   - index.php/ru
            //   - index.php/ru/
            $m->connect($prependRegex, array(
                'moduleName' => $defModule,
                // language is not resolved yet, thus default will be returned
//                'lang'       => SGL::getCurrentLang()
            ));
        }

        // Step one: connect to module
        //   1. index.php
        //   2. index.php/
        //   3. index.php/module
        //   4. index.php/module/
        $m->connect($prependRegex . ':moduleName', array(
            'moduleName' => $defModule,
        ));
        // Step two: connect to module and manager
        //   5. index.php/module/manager
        //   6. index.php/module/manager/
        // NB: we specify :controller variable instead of :managerName
        //     to invoke controller scan, later in the code we rename
        //     contoller -> managerName
        $m->connect($prependRegex . ':moduleName/:controller');
        // Step three: connect to module, manager and parameters
        //   7. index.php/module/manager/and/a/lot/of/params/here
        $m->connect($prependRegex . ':moduleName/:controller/*params');
        // Step four: connect to module and parameters
        //   8. index.php/module/and/a/lot/of/params/here
        $m->connect($prependRegex . ':moduleName/*params');

        $aQueryData = $m->match($qs);
        // resolve default manager
        if (!isset($aQueryData['controller'])) {
            $aQueryData['controller'] = $aQueryData['moduleName'] == $defModule
                ? $defManager
                : $aQueryData['moduleName'];
        }
        // rename controller -> manager
        $aQueryData['managerName'] = $aQueryData['controller'];
        unset($aQueryData['controller']);
        // resolve default params
        if (!isset($aQueryData['params'])) {
            if ($defParams
                    && $aQueryData['moduleName'] == $defModule
                    && $aQueryData['managerName'] == $defManager) {
                $aDefParams = $this->_urlParamStringToArray($defParams);
                $aQueryData = array_merge($aQueryData, $aDefParams);
            }
        // resolve params from 7th or 8th connection
        } else {
            $aParams = $this->_urlParamStringToArray($aQueryData['params']);
            $aQueryData = array_merge($aQueryData, $aParams);

            unset($aQueryData['params']);
        }
        if ($prependLang && !empty($aQueryData['lang'])) {
            $aQueryData['lang'] = $aQueryData['lang'] . '-' .
                // language is not resolved yet, thus default will be returned
                SGL::getCurrentCharset();
//                'utf-8';
        }

        // mapper options
        $m->appendSlash = true;

# remove this hack
        foreach ($m->matchList as $oRoute) {
            $oRoute->encoding = null;
        }

        // SGL_URL2
        $url = new SGL_Url2(new Horde_Routes_Utils($m));
        $url->setRequest($this);

        // assign to registry
        SGL_Registry::singleton()->setCurrentUrl($url);

        // merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_GET, $_FILES, $aQueryData, $_POST);
        $this->type   = SGL_REQUEST_BROWSER;

        return true;
    }

    /**
     * Get list of all available managers. Used as callback for Horde_Routes
     * to generate correct regex.
     *
     * @return array
     */
    public static function getAvailableManagers()
    {
        $aModules  = SGL_Util::getAllModuleDirs();
        if (PEAR::isError($aModules)) {
            return array();
        }
        $aManagers = array();
        foreach ($aModules as $moduleName) {
            $configFile = SGL_MOD_DIR . '/' . $moduleName . '/conf.ini';
            if (file_exists($configFile)) {
                $aDefault  = array(ucfirst($moduleName) . 'Mgr');
                $aSections = array_keys(parse_ini_file($configFile, true));
                $aManagers = array_merge($aManagers, $aSections, $aDefault);
            }
        }
        $aManagers = array_map(array('self', '_getManagerName'), $aManagers);
        $aManagers = array_filter($aManagers, 'trim');
        return $aManagers;
    }

    /**
     * Extract k/v pairs from string.
     *
     * @param string $params
     *
     * @return array
     */
    private function _urlParamStringToArray($params)
    {
        $aParams = explode('/', $params);
        $aRet    = array();
        for ($i = 0, $cnt = count($aParams); $i < $cnt; $i += 2) {
            // only for variables with values
            if (isset($aParams[$i + 1])) {
                $aRet[urldecode($aParams[$i])] = urldecode($aParams[$i + 1]);
            }
        }
        return $aRet;
    }

    /**
     * Get manager name from congif directive. Callback for array_map.
     *
     * @param string $sectionName
     *
     * @return mixed string or null
     */
    private static function _getManagerName($sectionName)
    {
        $ret = null;
        if (substr($sectionName, -3) === 'Mgr') {
            $ret = substr($sectionName, 0, strlen($sectionName) - 3);
            $ret = strtolower($ret);
        }
        return $ret;
    }

    /**
     * Get custom routes array.
     *
     * @return array
     */
    private function _getCustomRoutes()
    {
        $routesFile = SGL_VAR_DIR . '/routes.php';
        if (!file_exists($routesFile)) {
            // copy the default configuration file to the users tmp directory
            if (!copy(SGL_ETC_DIR . '/routes.php.dist', $routesFile)) {
                die('error copying routes file');
            }
            @chmod($routesFile, 0666);
        }
        // no custom routes by default or in case $aRoutes var is not set
        $aRoutes = array();
        // $aRoutes variable should exist
        include $routesFile;
        return $aRoutes;
    }

    /**
     * Prepend regex to routes.
     *
     * @param array $aRoutes
     * @param string $regex
     *
     * @return array
     */
    private function _prependRegex($aRoutes, $regex)
    {
        foreach ($aRoutes as $k => $v) {
            if (self::_ignoreRouteModification($v)) {
                $aRoutes[$k] = $v;
                continue;
            }
            $index = is_string($v[0]) ? 0 : 1;
            $route = $v[$index];
            if ($route[0] == '/' && $regex[strlen($regex)-1] == '/') {
                $aRoutes[$k][$index] = $regex . substr($route, 1);
            } else {
                $aRoutes[$k][$index] = $regex . $aRoutes[$k][$index];
            }
        }
        return $aRoutes;
    }

    /**
     * Looks if custom route option is set to ignore prepending of :lang.
     *
     * @todo make method names more obvious
     *
     * @see self::_prependRegex()
     *
     * @param array $aRoute
     *
     * @return boolean
     */
    private function _ignoreRouteModification(&$aRoute)
    {
        $ret          = false;
        $aRouteParams = end($aRoute);

        // check route preferences
        if (!isset($aRouteParams['moduleName'])
            && isset($aRouteParams['ignore_modification']))
        {
            $ret = true;
            unset($aRoute[count($aRoute)-1]['ignore_modification']);
            if (empty($aRoute[count($aRoute)-1])) {
                array_pop($aRoute);
            }
        }
        return $ret;
    }
}
?>