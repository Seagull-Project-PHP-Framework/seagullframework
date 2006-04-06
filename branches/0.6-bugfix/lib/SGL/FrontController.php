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

require_once dirname(__FILE__)  . '/Registry.php';
require_once dirname(__FILE__)  . '/Request.php';
require_once dirname(__FILE__)  . '/Misc.php';
require_once dirname(__FILE__)  . '/../SGL.php';
require_once dirname(__FILE__)  . '/Config.php';
require_once dirname(__FILE__)  . '/Tasks/Init.php';
require_once dirname(__FILE__)  . '/Tasks/Process.php';

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
        $req   = SGL_Request::singleton();

        if (PEAR::isError($req)) {
            //  stop with error page
            SGL::displayStaticPage($req->getMessage());
        }
        $input->setRequest($req);
        $output = &new SGL_Output();

        if (!SGL_FrontController::customFilterChain($input)) {
            $process =
                //  pre-process (order: top down)
                new SGL_Process_Init(
                new SGL_Process_SetupORM(
                new SGL_Process_StripMagicQuotes(
                new SGL_Process_DiscoverClientOs(
                new SGL_Process_ResolveManager(
                new SGL_Process_CreateSession(
                new SGL_Process_SetupLangSupport(
                new SGL_Process_SetupPerms(
                new SGL_Process_AuthenticateRequest(
                new SGL_Process_SetupLocale(

                //  post-process (order: bottom up)
                new SGL_Process_BuildHeaders(
                new SGL_Process_SetSystemAlert(
                new SGL_Process_BuildView(
                new SGL_Process_SetupBlocks(
                new SGL_Process_SetupNavigation(
                new SGL_Process_SetupGui(
                new SGL_Process_GetPerformanceInfo(
                new SGL_Process_SetupWysiwyg(
                new SGL_Process_BuildOutputData(

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
        $init->addTask(new SGL_Task_EnsureBC());
        $init->main();
        define('SGL_INITIALISED', true);
    }

    function setupMinimumEnv()
    {
        $init = new SGL_TaskRunner();
        $init->addTask(new SGL_Task_SetupPaths());
        $init->addTask(new SGL_Task_SetupConstantsStart());
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