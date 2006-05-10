<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | FrontController.php                                                       |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: FrontController.php,v 1.49 2005/06/23 19:15:25 demian Exp $

require_once dirname(__FILE__)  . '/../SGL.php';
require_once dirname(__FILE__)  . '/Task/Init.php';


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
     * Main invocation, init tasks plus main process.
     *
     */
    function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        //  assign request to registry
        $input = &SGL_Registry::singleton();
        $req   = &SGL_Request::singleton();

        if (PEAR::isError($req)) {
            //  stop with error page
            SGL::displayStaticPage($req->getMessage());
        }
        $input->setRequest($req);
        $output = &new SGL_Output();

        // see http://trac.seagullproject.org/wiki/Howto/PragmaticPatterns/InterceptingFilter
        if (!SGL_FrontController::customFilterChain($input)) {
            $process =
                //  pre-process (order: top down)
                new SGL_Task_Init(
                new SGL_Task_SetupORM(
                new SGL_Task_StripMagicQuotes(
                new SGL_Task_DiscoverClientOs(
                new SGL_Task_ResolveManager(
                new SGL_Task_CreateSession(
                new SGL_Task_SetupLangSupport(
                new SGL_Task_SetupPerms(
                new SGL_Task_AuthenticateRequest(
                new SGL_Task_SetupLocale(

                //  post-process (order: bottom up)
                new SGL_Task_BuildHeaders(
                new SGL_Task_SetSystemAlert(
                new SGL_Task_BuildView(
                new SGL_Task_SetupBlocks(
                new SGL_Task_SetupNavigation(
                new SGL_Task_SetupGui(
                new SGL_Task_GetPerformanceInfo(
                new SGL_Task_SetupWysiwyg(
                new SGL_Task_BuildOutputData(

                //  target
                new SGL_MainProcess()
                )))))))))))))))))));
            $process->process($input, $output);

        } else {
            require_once dirname(__FILE__)  . '/FilterChain.php';
            $chain = new SGL_FilterChain($input->getFilters());
            $chain->doFilter($input, $output);
        }
        if ($output->conf['site']['outputBuffering']) {
            ob_end_flush();
        }
        echo $output->data;
    }

    function customFilterChain(&$input)
    {
        $conf = $input->getConfig();
        $req = $input->getRequest();
        $mgr = SGL_Inflector::getManagerNameFromSimplifiedName(
            $req->getManagerName());
        if (!empty($conf[$mgr]['filterChain'])) {
            $aFilters = explode(',', $conf[$mgr]['filterChain']);
            $input->setFilters($aFilters);
            $ret = true;
        } elseif (!empty($conf['site']['filterChain'])) {
            $aFilters = explode(',', $conf['site']['filterChain']);
            $input->setFilters($aFilters);
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

    function init()
    {
        SGL_FrontController::setupMinimumEnv();
        SGL_FrontController::loadRequiredFiles();

        $autoLoad = (is_file(SGL_VAR_DIR  . '/INSTALL_COMPLETE.php'))
            ? true
            : false;
        $c = &SGL_Config::singleton($autoLoad);

        $init = new SGL_TaskRunner();
        $init->addData($c->getAll());
        $init->addTask(new SGL_Task_SetupConstantsFinish());
        $init->addTask(new SGL_Task_SetupPearErrorCallback());
        $init->addTask(new SGL_Task_SetupCustomErrorHandler());
        $init->addTask(new SGL_Task_ModifyIniSettings());
        $init->addTask(new SGL_Task_SetBaseUrl());
        $init->addTask(new SGL_Task_SetGlobals());
        $init->addTask(new SGL_Task_RegisterTrustedIPs());
        $init->main();
        define('SGL_INITIALISED', true);
    }

    function loadRequiredFiles()
    {
        $cachedLibs = SGL_VAR_DIR . '/cachedLibs.php';
        if (is_file($cachedLibs) && SGL_CACHE_LIBS) {
            require_once $cachedLibs;
        } else {
            $coreLibs = dirname(__FILE__);
            $aRequiredFiles = array(
                $coreLibs  . '/Url.php',
                $coreLibs  . '/UrlParser/SefStrategy.php',
                $coreLibs  . '/HTTP.php',
                $coreLibs  . '/Manager.php',
                $coreLibs  . '/Output.php',
                $coreLibs  . '/String.php',
                $coreLibs  . '/Task/Process.php',
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
                $coreLibs  . '/DB.php',
                $coreLibs  . '/BlockLoader.php',
                $coreLibs  . '/Translation.php',
                $coreLibs  . '/../data/ary.languages.php',
            );
            $fileCache = '';
            foreach ($aRequiredFiles as $file) {
                require_once $file;

                if (SGL_CACHE_LIBS === true) {
                    // 270kb vs 104kb
                    if ($ok = version_compare(phpversion(), '5.1.2', '>=')) {
                        $fileCache .= php_strip_whitespace($file);
                    } else {
                        $fileCache .= file_get_contents($file);
                    }
                    $fileCache .= "\n";
                }
            }
            if (SGL_CACHE_LIBS === true) {
                $ok = file_put_contents($cachedLibs, $fileCache);
            }
        }
        require_once 'DB.php';
    }

    function setupMinimumEnv()
    {
        $init = new SGL_TaskRunner();
        $init->addTask(new SGL_Task_SetupPaths());
        $init->addTask(new SGL_Task_SetupConstantsStart());
        $init->addTask(new SGL_Task_EnsureBC());
        $init->main();
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
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req  = $input->getRequest();
        $mgr  = $input->get('manager');
        $conf = $mgr->conf;
        $mgr->validate($req, $input);

        $input->aggregate($output);

        //  process data if valid
        if ($mgr->isValid()) {
            $ok = $mgr->process($input, $output);
            if (PEAR::isError($ok)) {
                //  stop with error page
                SGL::displayStaticPage($ok->getMessage());
            }
        }
        $mgr->display($output);
    }
}
?>