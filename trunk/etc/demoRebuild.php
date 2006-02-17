<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | demoRebuild.php                                                           |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/*
    * rebuilds a Seagull install from commandline.
    * Expects to find localhost.conf.php in var dir
    * build a config you're happy with, make a copy called localhost.conf.php

    Usage: $ php etc/demoRebuild.php
*/

//  setup seagull environment
require_once dirname(__FILE__)  . '/../lib/SGL/AppController.php';
require_once dirname(__FILE__)  . '/../lib/SGL/Install/Tasks/Install.php';

class RebuildController extends SGL_AppController
{
    function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_AppController::init();
        }

        //  get config singleton
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        //  get current url object
        $urlHandler = $conf['site']['outputUrlHandler'];
        $url = new SGL_URL($_SERVER['PHP_SELF'], true, new $urlHandler());

        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($url);
        $input->setRequest($req = SGL_Request::singleton());

        $process =  new SGL_Process_Init(
                    new SGL_Process_MinimalSession(
                    new SGL_Rebuild()
                   ));

        $process->process($input);
    }
}

class SGL_Rebuild extends SGL_ProcessRequest
{
    function process(&$input)
    {
        if (!SGL::runningFromCli()) {
            SGL::raiseError('This script can only be run from command line',
                SGL_ERROR_INVALIDCALL, PEAR_ERROR_DIE);
        }

        $data = array(
            'createTables' => 1,
            'insertSampleData' => 1,
            'installAllModules' => 1,
            'adminUserName' => 'admin',
            'adminPassword' => 'admin',
            'adminRealName' => 'Demo Admin',
            'adminEmail' => 'demian@phpkitchen.com',
            );

        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
        $runner->addTask(new SGL_Task_DropDatabase());
        $runner->addTask(new SGL_Task_CreateDatabase());
        $runner->addTask(new SGL_Task_CreateTables());
        $runner->addTask(new SGL_Task_LoadDefaultData());
        $runner->addTask(new SGL_Task_LoadSampleData());
        $runner->addTask(new SGL_Task_CreateConstraints());
        $runner->addTask(new SGL_Task_EnableForeignKeyChecks());
        $runner->addTask(new SGL_Task_VerifyDbSetup());
        $runner->addTask(new SGL_Task_CreateFileSystem());
        $runner->addTask(new SGL_Task_CreateDataObjectEntities());
        $runner->addTask(new SGL_Task_SyncSequences());
        $runner->addTask(new SGL_Task_CreateAdminUser());
        $runner->addTask(new SGL_Task_InstallerCleanup());

        set_time_limit(120);
        $ok = $runner->main();
    }
}

class SGL_Process_MinimalSession extends SGL_DecorateProcess
{
    function process(&$input)
    {
        session_start();
        $_SESSION['uid'] = 1;
        $_SESSION['aPrefs'] = array();

        $this->processRequest->process($input);
    }
}

RebuildController::run();
?>