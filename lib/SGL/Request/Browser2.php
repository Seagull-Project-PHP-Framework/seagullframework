<?php

require_once 'Horde/Routes/Mapper.php';
require_once 'Horde/Routes/Exception.php';
require_once 'Horde/Routes/Route.php';
require_once 'Horde/Routes/Util.php';
require_once 'Horde/Routes/Config.php';

/**
 * Browser2 request type, which uses Horder_Routes package
 * to resolve query data, instead SGL_Url heavy parsing used by Browser1.
 *
 * @todo needs SGL_Url2 to be implemented to complete integration
 *
 * @package SGL
 * @subpackage request
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
        $qs = isset($_SERVER['PATH_INFO'])
            ? $_SERVER['PATH_INFO']
            : '/';

        $defModule  = SGL_Config::get('site.defaultModule');
        $defManager = SGL_Config::get('site.defaultManager');
        $defParams  = SGL_Config::get('site.defaultParams');

        // create mapper
        $m = new Horde_Routes_Mapper(array(
            'explicit'       => true, // do not connect to Horder defaults
            'controllerScan' => array('SGL_Request_Browser2', 'getAvailableManagers'),
        ));

        // Connect to custom routes.
        // Custom routes have higher priority, thus connect to them before
        // default Seagull SEO routes.
        $aRoutes = $this->_getCustomRoutes();
        foreach ($aRoutes as $aRouteData) {
            call_user_func_array(array($m, 'connect'), $aRouteData);
        }

        // Seagull SEO routes connection
        //   *  all available routes variants are marked with numbers.
        //
        // Step one: connect to module
        //   1. index.php
        //   2. index.php/
        //   3. index.php/module
        //   4. index.php/module/
        $m->connect(':moduleName', array(
            'moduleName' => $defModule,
        ));
        // Step two: connect to module and manager
        //   5. index.php/module/manager
        //   6. index.php/module/manager/
        // NB: we specify :controller variable instead of :managerName
        //     to invoke controller scan, later in the code we rename
        //     contoller -> managerName
        $m->connect(':moduleName/:controller');
        // Step three: connect to module, manager and parameters
        //   7. index.php/module/manager/and/a/lot/of/params/here
        $m->connect(':moduleName/:controller/*params');
        // Step four: connect to module and parameters
        //   8. index.php/module/and/a/lot/of/params/here
        $m->connect(':moduleName/*params');

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

        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($m);

        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_GET, $_FILES, $aQueryData, $_POST);
        $this->type = SGL_REQUEST_BROWSER;

        return true;
    }

    /**
     * Get list of all available managers. Used as callback for Horde_Routes
     * to generate correct regex.
     *
     * @return array
     */
    public function getAvailableManagers()
    {
        $aModules  = SGL_Util::getAllModuleDirs();
        $aManagers = array();
        foreach ($aModules as $moduleName) {
            $configFile = SGL_MOD_DIR . '/' . $moduleName . '/conf.ini';
            if (file_exists($configFile)) {
                $aSections = array_keys(parse_ini_file($configFile, true));
                $aManagers = array_merge($aManagers, $aSections);
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
    private function _getManagerName($sectionName)
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
}
?>
