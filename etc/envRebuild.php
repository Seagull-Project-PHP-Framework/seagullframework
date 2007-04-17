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
// | envRebuild.php                                                            |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/*
    * rebuilds a Seagull install from commandline.
    * Expects to find localhost.conf.php in var dir
    * build a config you're happy with, make a copy called localhost.conf.php or
      symlink localhost.conf.php to the original.

    Usage: $ php etc/envRebuild.php
*/

//  ensure script run as root
$processUser = posix_getpwuid(posix_geteuid());
if ($processUser['name'] !== 'root') {
    die("\nALERT!! You must run this script as root\n\n");
}

//  set initial paths according to install type
$pearTest = '@PHP-DIR@';
if ($pearTest != '@' . 'PHP-DIR'. '@') {
    define('SGL_PEAR_INSTALLED', true);
    $rootDir = '@PHP-DIR@/Seagull';
    $varDir = '@DATA-DIR@/Seagull/var';
} else {
    $rootDir = dirname(__FILE__) . '/..';
    $varDir = dirname(__FILE__) . '/../var';
}

//  setup seagull environment
require_once dirname(__FILE__)  . '/../lib/SGL/FrontController.php';
require_once dirname(__FILE__)  . '/../lib/SGL/Task/Install.php';
require_once dirname(__FILE__)  . '/../lib/SGL/Install/Common.php';

class RebuildController extends SGL_FrontController
{
    function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        //  get config singleton
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        if (!count($conf)) {
            SGL::raiseError('This script can only be run after you have created ' .
            'a valid config file, ie, using the web installer',
                SGL_ERROR_INVALIDCONFIG, PEAR_ERROR_DIE);
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

        $process =  new SGL_Task_Init(
                    new SGL_Task_MinimalSession(
                    new SGL_Rebuild()
                   ));

        $process->process($input, $output);
    }
}

class SGL_Rebuild extends SGL_ProcessRequest
{
    function process(&$input, &$output)
    {
        if (!SGL::runningFromCli()) {
            SGL::raiseError('This script can only be run from command line',
                SGL_ERROR_INVALIDCALL, PEAR_ERROR_DIE);
        }
        //  get config singleton
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  retrieve Install password
        $aLines = file(SGL_PATH . '/var/INSTALL_COMPLETE.php');
        $installPassword = trim(substr($aLines[1], 1));

        //  retrieve translation settings
        $transContainer = ($conf['translation']['container'] == 'db') ? 1 : 0;
        $transLanguage  = str_replace('_','-', explode(',', $conf['translation']['installedLanguages']));

        //  check for custom modules
        $aDefaultData = SGL_Install_Common::overrideDefaultInstallSettings();
        $aModules = !empty($aDefaultData['aModuleList'])
            ? $aDefaultData['aModuleList']
            : SGL_Install_Common::getMinimumModuleList();
        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $oUserDao    = &UserDAO::singleton();
        $aMasterPrefs= $oUserDao->getMasterPrefs();

        $data = array(
            'createTables' => 1,
            'insertSampleData' => 1,
            'adminUserName' => 'admin',
            'adminPassword' => 'admin',
            'adminFirstName' => 'Demo',
            'adminLastName' => 'Admin',
            'adminEmail' => 'demian@phpkitchen.com',
            'aModuleList' => $aModules,
            'serverName' => isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'localhost',
            'installPassword'       => $installPassword,
            'storeTranslationsInDB' => $transContainer,
            'installLangs'          => $transLanguage,
            'aPrefs'                => $aMasterPrefs
            );

        if (SGL::moduleIsEnabled('cms')) {
            require_once SGL_MOD_DIR  . '/cms/classes/NavigationDAO.php';
            require_once SGL_MOD_DIR  . '/cms/init.php';
            $buildNavTask = 'SGL_Task_BuildNavigation2';
        } else {
            require_once SGL_MOD_DIR  . '/navigation/classes/NavigationDAO.php';
            $buildNavTask = 'SGL_Task_BuildNavigation';
        }

        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_SetTimeout());
        $runner->addTask(new SGL_Task_DefineTableAliases());
        $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
        $runner->addTask(new SGL_Task_DropDatabase());
        $runner->addTask(new SGL_Task_CreateDatabase());
        $runner->addTask(new SGL_Task_CreateTables());
        $runner->addTask(new SGL_Task_LoadTranslations());
        $runner->addTask(new SGL_Task_LoadDefaultData());
        $runner->addTask(new SGL_Task_LoadSampleData());
        $runner->addTask(new SGL_Task_LoadCustomData());
        $runner->addTask(new SGL_Task_SyncSequences());
        $runner->addTask(new $buildNavTask());
        $runner->addTask(new SGL_Task_LoadBlockData());
        $runner->addTask(new SGL_Task_CreateConstraints());
        $runner->addTask(new SGL_Task_SyncSequences());
        $runner->addTask(new SGL_Task_EnableForeignKeyChecks());
        $runner->addTask(new SGL_Task_VerifyDbSetup());
        $runner->addTask(new SGL_Task_CreateFileSystem());
        $runner->addTask(new SGL_Task_CreateDataObjectEntities());
        $runner->addTask(new SGL_Task_CreateDataObjectLinkFile());
        $runner->addTask(new SGL_Task_UnLinkWwwData());
        $runner->addTask(new SGL_Task_SymLinkWwwData());
        $runner->addTask(new SGL_Task_CreateAdminUser());
        $runner->addTask(new SGL_Task_CreateMemberUser());
        $runner->addTask(new SGL_Task_EnableDebugBlock());
        $runner->addTask(new SGL_Task_InstallerCleanup());

        $ok = $runner->main();
    }
}

class SGL_Task_MinimalSession extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        session_start();
        $_SESSION['uid'] = 1;
        $_SESSION['aPrefs'] = array();

        $this->processRequest->process($input, $output);
    }
}

RebuildController::run();
?>