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
// | Seagull 0.9                                                               |
// +---------------------------------------------------------------------------+
// | FrontController.php                                                       |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: FrontController.php,v 1.49 2005/06/23 19:15:25 demian Exp $

require_once dirname(__FILE__)  . '/../SGL.php';

/**
 * Application controller.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.49 $
 */
class SGL_FrontController
{
    /**
     * Allow SGL_Output with its template methods to be extended.
     *
     * Remember to add your custom include path to the global config, ie a class
     * called FOO_Output will be discovered if it exists in seagull/lib/FOO/Output.php.
     * This means '/path/to/seagull/lib' must be added to
     * $conf['path']['additionalIncludePath'].  The class definition would be:
     *
     *  class FOO_Output extends SGL_Output {}
     *
     */
    function getOutputClass()
    {
        if (SGL_Config::get('site.customOutputClassName')) {
            $className = SGL_Config::get('site.customOutputClassName');
        } else {
            $className = 'SGL_Output';
        }
        return $className;
    }

    /**
     * Main invocation, init tasks plus main process.
     *
     */
    public static function run()
    {
        if (!defined('SGL_INITIALISED')) {
            self::init();
        }
        //  assign request to registry
        $input = SGL_Registry::singleton();
        $req   = SGL_Request::singleton();

        if (PEAR::isError($req)) {
            //  stop with error page
            SGL::displayStaticPage($req->getMessage());
        }
        $input->setRequest($req);

        //  ensure local config loaded and merged
        $c = SGL_Config::singleton();
        $c->ensureModuleConfigLoaded($req->getModuleName());

        $outputClass = self::getOutputClass();
        $output = new $outputClass();

        // test db connection
//SGL_FrontController::testDbConnection($output);

        // run module init tasks
//SGL_Task_InitialiseModules::run();

        // see http://trac.seagullproject.org/wiki/Howto/PragmaticPatterns/InterceptingFilter
        if (!self::customFilterChain($input)) {


            $aFilters = array(
                //  pre-process (order: top down)
                //'SGL_Task_Init',
                'SGL_Filter_StripMagicQuotes',
                'SGL_Filter_DiscoverClientOs',
                'SGL_Filter_ResolveManager',
                'SGL_Filter_CreateSession',
                'SGL_Filter_SetupLangSupport',
                'SGL_Filter_SetupLocale',
                'SGL_Filter_AuthenticateRequest',
                'SGL_Filter_DetectAdminMode',
//                //new SGL_Filter_MaintenanceModeIntercept(
//                //new SGL_Filter_DetectSessionDebug(
//                //new SGL_Filter_SetupPerms(

                //  post-process (order: bottom up)
                'SGL_Filter_BuildHeaders',
                'SGL_Filter_BuildView',
//                //new SGL_Filter_BuildDebugBlock(
//                //new SGL_Filter_SetupBlocks(
//                //new SGL_Filter_SetupNavigation(
                'SGL_Filter_SetupGui',
                'SGL_Filter_BuildOutputData',

                //  target
                'SGL_MainProcess',
                );
            $input->setFilters($aFilters);
        }
        $chain = new SGL_FilterChain($input->getFilters());
        $chain->doFilter($input, $output);
        if (SGL_Config::get('site.outputBuffering')) {
            ob_end_flush();
        }
        echo $output->data;
    }

    function customFilterChain(&$input)
    {
        $req = $input->getRequest();

        switch ($req->getType()) {

        case SGL_REQUEST_BROWSER:
        case SGL_REQUEST_CLI:
            $mgr = SGL_Inflector::getManagerNameFromSimplifiedName(
                $req->getManagerName());
            //  load filters defined by specific manager
            if (SGL_Config::get("$mgr.filterChain")) {
                $aFilters = explode(',', SGL_Config::get("$mgr.filterChain"));
                $input->setFilters($aFilters);
                $ret = true;

            //  load sitewide custom filters
            } elseif (SGL_Config::get('site.filterChain')) {
                $aFilters = explode(',', SGL_Config::get('site.filterChain'));
                $input->setFilters($aFilters);
                $ret = true;
            } else {
                $ret = false;
            }
            break;

        case SGL_REQUEST_AJAX:
            $moduleName = ucfirst($req->getModuleName());
            $providerName = $moduleName . 'AjaxProvider';
            if (SGL_Config::get("$providerName.filterChain")) {
                $aFilters = explode(',', SGL_Config::get("$providerName.filterChain"));
            } else {
                $aFilters = array(
                    'SGL_Task_Init',
                    'SGL_Task_SetupORM',
                    'SGL_Task_CreateSession',
                    'SGL_Task_SetupLangSupport',
                    'SGL_Task_AuthenticateAjaxRequest',
                    'SGL_Task_BuildAjaxHeaders',
                    'SGL_Task_CustomBuildOutputData',
                    'SGL_Task_ExecuteAjaxAction',
                );
            }
            $input->setFilters($aFilters);
            $ret = true;
            break;

        case SGL_REQUEST_AMF:
            $moduleName = ucfirst($req->getModuleName());
            $providerName = $moduleName . 'AmfProvider';
            if (SGL_Config::get("$providerName.filterChain")) {
                $aFilters = explode(',', SGL_Config::get("$providerName.filterChain"));
            } else {
                $aFilters = array(
                    'SGL_Task_Init',
                    'SGL_Task_SetupORM',
                    'SGL_Task_CreateSession',
                    'SGL_Task_SetupLangSupport',
                    'SGL_Task_ExecuteAmfAction',
                );
            }
            $input->setFilters($aFilters);
            $ret = true;
            break;
        }
        return $ret;
    }

    function testDbConnection($output)
    {
        $originalErrorLevel = error_reporting(0);

        // test db connection
        if (defined('SGL_INSTALLED')) {
            $dbh = SGL_DB::singleton();
            if (PEAR::isError($dbh)) {
                // stop with error page
                SGL::displayErrorPage($output);
            }
        }
        error_reporting($originalErrorLevel);
    }


    public static function init()
    {
        self::setupMinimumEnv();
        self::loadRequiredFiles();

        $autoLoad = (is_file(SGL_VAR_DIR  . '/INSTALL_COMPLETE.php'))
            ? true
            : false;
        $c = SGL_Config::singleton($autoLoad);

        $init = new SGL_TaskRunner();
        $init->addData($c->getAll());
        $init->addTask(new SGL_Task_SetupConstantsFinish());
        //$init->addTask(new SGL_Task_EnsurePlaceholderDbPrefixIsNull());
        $init->addTask(new SGL_Task_SetGlobals());
        $init->addTask(new SGL_Task_ModifyIniSettings());
        //$init->addTask(new SGL_Task_SetupPearErrorCallback());
        //$init->addTask(new SGL_Task_SetupCustomErrorHandler());
        $init->addTask(new SGL_Task_SetBaseUrl());
        //$init->addTask(new SGL_Task_RegisterTrustedIPs());
        $init->addTask(new SGL_Task_LoadCustomConfig());
        $init->main();
        define('SGL_INITIALISED', true);
    }

    public static function loadRequiredFiles()
    {
        $coreLibs = dirname(__FILE__);
        $aRequiredFiles = array(
            $coreLibs  . '/Url.php',
            $coreLibs  . '/HTTP.php',
            $coreLibs  . '/Manager.php',
            $coreLibs  . '/Output.php',
            $coreLibs  . '/String.php',
            $coreLibs  . '/Session.php',
            $coreLibs  . '/Util.php',
            $coreLibs  . '/Config.php',
            $coreLibs  . '/ParamHandler.php',
            $coreLibs  . '/Registry.php',
            $coreLibs  . '/Request.php',
            $coreLibs  . '/Inflector.php',
            $coreLibs  . '/Date.php',
            $coreLibs  . '/Array.php',
            $coreLibs  . '/Error.php',
            $coreLibs  . '/Cache.php',
            //$coreLibs  . '/DB.php',
            //$coreLibs  . '/BlockLoader.php',
            $coreLibs  . '/Translation.php',
            $coreLibs  . '/../data/ary.languages.php',
        );
        foreach ($aRequiredFiles as $file) {
            require_once $file;
        }
    }

    public static function setupMinimumEnv()
    {
        $init = new SGL_TaskRunner();
        $init->addTask(new SGL_Task_SetupPaths());
        $init->addTask(new SGL_Task_SetupConstantsStart());
        $init->main();
    }
}

/**
 * Abstract request processor.
 *
 * @abstract
 * @package SGL
 *
 */
class SGL_ProcessRequest
{
    function process(SGL_Registry $input, SGL_Output $output) {}
}

/**
 * Decorator.
 *
 * @abstract
 * @package SGL
 */
class SGL_DecorateProcess extends SGL_ProcessRequest
{
    var $processRequest;

    function SGL_DecorateProcess(/* SGL_ProcessRequest */ $pr)
    {
        $this->processRequest = $pr;
    }
}

/**
 * Core data processing routine.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_MainProcess extends SGL_ProcessRequest
{
    function process($input, $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req  = $input->getRequest();
        $mgr  = $input->get('manager');

        $mgr->validate($req, $input);
        $input->aggregate($output);

        //  process data if valid
        if ($mgr->isValid()) {
            $ok = $mgr->process($input, $output);
            if (SGL_Error::count() && SGL_Session::getRoleId() != SGL_ADMIN
                    && SGL_Config::get('debug.production')) {
                $mgr->handleError(SGL_Error::getLast(), $output);
            }
        }
        SGL_Manager::display($output);
        $mgr->display($output);
    }
}

/**
 * Abstract renderer strategy
 *
 * @abstract
 * @package SGL
 */
class SGL_OutputRendererStrategy
{
    /**
     * Prepare renderer options.
     *
     */
    function initEngine() {}

    /**
     * Abstract render method.
     *
     * @param SGL_View $view
     */
    function render($view) {}
}
?>