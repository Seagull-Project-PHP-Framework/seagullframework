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

class SGL2_Router
{
    public function __construct()
    {
        $this->_init();
    }

    public function route(SGL2_Request $req)
    {
        foreach ($this->_aData as $k => $v) {
            $req->set($k, $v);
        }
    }

    /**
     * Resolve query data by connecting to routes.
     *
     * @return void
     */
    protected function _init()
    {
        if (SGL2_Config::get('site.frontScriptName')) {
            $qs = isset($_SERVER['PATH_INFO'])
                ? $_SERVER['PATH_INFO']
                : '/';
        } else {
            $baseUrl       = SGL2_Config::get('site.baseUrl');
            list($proto, ) = explode('://', $baseUrl, 2);
            $host          = $_SERVER['HTTP_HOST'];
            $url           = $proto . '://' . $host . $_SERVER['REQUEST_URI'];
            $qs            = urldecode(str_replace($baseUrl, '', $url));
        }

        $defModule  = SGL2_Config::get('site.defaultModule');
        $defCtlr    = SGL2_Config::get('site.defaultController');
        $defParams  = SGL2_Config::get('site.defaultParams');

        // show lang in URL
        $prependLang  = SGL2_Config::get('translation.langInUrl');
        $prependRegex = $prependLang ? ':lang/' : '';

        // Connect to custom routes.
        // Custom routes have higher priority, thus connect to them before
        // default Seagull SEO routes.
        $aRoutes = $this->_getCustomRoutes();
        if ($prependRegex) {
            $aRoutes = $this->_prependRegex($aRoutes, $prependRegex);
        }

        // create mapper
        $m = new Horde_Routes_Mapper(array(
            'explicit'       => true, // do not connect to Horder defaults
            'controllerScan' => array('SGL2_Router', '_getAvailableManagers'),
        ));

        foreach ($aRoutes as $aRouteData) {
            call_user_func_array(array($m, 'connect'), $aRouteData);
        }

        // Seagull SEO routes connection
        //   *  all available routes variants are marked with numbers.
        //
        if ($prependLang) {
            // Step zero: connect to language
            //   - index.php/ru
            //   - index.php/ru/
            $m->connect($prependRegex, array(
                'moduleName' => $defModule,
                // language is not resolved yet, thus default will be returned
                'lang'       => 'en',
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
        //     controller -> controllerName
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
                ? $defCtlr
                : $aQueryData['moduleName'];
        }
        // rename controller -> controllerName
        $aQueryData['controllerName'] = $aQueryData['controller'];
        // resolve default params
        if (!isset($aQueryData['params'])) {
            if ($defParams
                    && $aQueryData['moduleName'] == $defModule
                    && $aQueryData['controllerName'] == $defCtlr)
            {
                $aDefParams = $this->_urlParamStringToArray($defParams);
                $aQueryData = array_merge($aQueryData, $aDefParams);
            }
        // resolve params from 7th or 8th connection
        } else {
            $aParams = $this->_urlParamStringToArray($aQueryData['params']);
            $aQueryData = array_merge($aQueryData, $aParams);

            unset($aQueryData['params']);
        }
        if ($prependLang) {
            $aQueryData['lang'] = $aQueryData['lang'] . '-' .
                // language is not resolved yet, thus default will be returned
                'utf-8';
        }
        $this->_aData = $aQueryData;

        // mapper options
        $m->appendSlash = true;

# remove this hack
        foreach ($m->matchList as $oRoute) {
            $oRoute->encoding = null;
        }

        // SGL2_URL
        $url = new SGL2_Url($aQueryData);
        $url->setRoutes(new Horde_Routes_Utils($m));

        // assign to registry
        SGL2_Registry::set('url', $url);

        return true;
    }

    /**
     * Get list of all available managers. Used as callback for Horde_Routes
     * to generate correct regex.
     *
     * This has to be a public method to be recognised as is_callable
     *
     * @return array
     * @todo move to BC plugin
     */
    public static function _getAvailableManagers()
    {
        return array();

        $aModules  = SGL2_Util::getAllModuleDirs();
        $aManagers = array();
        foreach ($aModules as $moduleName) {
            $configFile = SGL2_MOD_DIR . '/' . $moduleName . '/conf.ini';
            if (SGL2_File::exists($configFile)) {
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
    protected function _urlParamStringToArray($params)
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
     * @return mixed string or null
     * @todo move to BC plugin
     *
     */
    protected static function _getManagerName($sectionName)
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
    protected function _getCustomRoutes()
    {
        $routesFile = SGL2_VAR_DIR . '/routes.php';
        if (!SGL2_File::exists($routesFile)) {
            // copy the default configuration file to the users tmp directory
            try {
                copy(SGL2_ETC_DIR . '/routes.php.dist', $routesFile);
            } catch (Exception $e) {
                throw new Exception('error copying routes file, is sgl/var writable?');
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
    protected function _prependRegex($aRoutes, $regex)
    {
        foreach ($aRoutes as $k => $v) {
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
}
?>