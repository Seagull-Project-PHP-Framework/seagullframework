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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | setup.php                                                                 |
// +---------------------------------------------------------------------------+
// | Authors:                                                                  |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            Gerry Lachac <glachac@tethermedia.com>                         |
// |            Andy Crain <apcrain@fuse.net>                                  |
// +---------------------------------------------------------------------------+
// $Id: setup.php,v 1.5 2005/02/03 11:29:01 demian Exp $

/*
sgl setup
=========
- ability to upload and unzip/tar a packaged module
- file permission handling ideas from FUDforum installer
- more user-friendly error messages from Gallery2
- if no DB detected, prompt to create, otherwise offer to create tables


php ini
=======
- deal with register_globals and set session.use_trans_sid = 0
- allow_url_fopen = Off
- detect and deal with safe_mode
- magic_quotes must be off
- file_uploads ideally enabled

module setup
============
- choose modules and permissions must be created and set at install time
- attempt to
    - uncompress
    - move to correct location
    - apply user perms
    - apply prefs
    - add module's db tables to Config
    - load module's schema + data
    - add 'section' or 'screen' navigation links
    - register module in registry
*/

//  initialise

//  set initial paths according to install type
$pearTest = '@PHP-DIR@';
if ($pearTest != '@' . 'PHP-DIR'. '@') {
    define('SGL_PEAR_INSTALLED', true);
    $rootDir = '@PHP-DIR@/Seagull';
    $varDir = '@DATA-DIR@/Seagull/var';
} else {
    $rootDir = realpath(dirname(__FILE__) . '/..');
    $varDir = realpath(dirname(__FILE__) . '/../var');
}

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

//  are we doing a minimal install?
define('SGL_MINIMAL_INSTALL', (is_file($rootDir . '/MINIMAL_INSTALL.txt'))
    ? true
    : false);

require_once $rootDir . '/lib/SGL/FrontController.php';
require_once $rootDir . '/lib/SGL/Install/Common.php';
SGL_FrontController::init();

session_start();
$_SESSION['ERRORS'] = array();

//  check if requesting auth.txt download
if (isset($_GET['download']) && $_GET['download'] == 1) {
    if (isset($_SESSION['authString'])) {
        header("Content-Type: text/plain");
        header("Content-Length: " . strlen($_SESSION['authString']));
        header("Content-Description: Download AUTH.txt to your computer.");
        header("Content-Disposition: attachment; filename=AUTH.txt");
        print $_SESSION['authString'];
        exit;
    }
}

//  reroute to front controller
if (isset($_GET['start'])) {

    //  remove installer info
    @session_destroy();
    $_SESSION = array();

    //  clear session cookie
    $c = &SGL_Config::singleton();
    $conf = $c->getAll();
    setcookie(  $conf['cookie']['name'], null, 0, $conf['cookie']['path'],
                $conf['cookie']['domain'], $conf['cookie']['secure']);

    $aUrl = array(
        'managerName' => 'default',
        'moduleName'  => 'default',
        'welcome'     => 1
    );
    SGL_HTTP::redirect($aUrl);
}

//  check authorization
if (is_file(SGL_PATH . '/var/INSTALL_COMPLETE.php')
        && empty($_SESSION['valid'])) {

    if (!empty($_POST['frmSetupPassword'])) {
        $aLines = file(SGL_PATH . '/var/INSTALL_COMPLETE.php');
        $secret = trim(substr($aLines[1], 1));
        if ($_POST['frmSetupPassword'] != $secret) {
            $_SESSION['message'] = 'incorrect password';
            header('Location: setup.php');
            exit;
        } else {
            $_SESSION['valid'] = true;
            header('Location: setup.php');
        }
    } else {
        SGL_Install_Common::printHeader();
        SGL_Install_Common::printLoginForm();
        SGL_Install_Common::printFooter();
        exit;
    }
}

// load QuickFormController libs
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Next.php';
require_once 'HTML/QuickForm/Action/Back.php';
require_once 'HTML/QuickForm/Action/Display.php';

//  load wizard screens and qf overrides
require_once SGL_PATH . '/lib/SGL/Install/WizardLicenseAgreement.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardSetupAuth.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardDetectEnv.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardTestDbConnection.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardCreateDb.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardCreateAdminUser.php';
require_once SGL_PATH . '/lib/SGL/Install/QuickFormOverride.php';

//  load tasks
require_once SGL_PATH . '/lib/SGL/Task/DetectEnv.php';
require_once SGL_PATH . '/lib/SGL/Task/Install.php';

//  setup temporary logging for Seagull install
$log = "$varDir/install.log";
$ok = @ini_set('error_log', $log);


class ActionProcess extends HTML_QuickForm_Action
{
    function perform(&$page, $actionName)
    {
        $data = $page->controller->exportValues();

        //  is this a rebuild?
        $dbh = & SGL_DB::singleton();
        $res = false;
        if (!PEAR::isError($dbh)) {
            require_once SGL_CORE_DIR . '/Sql.php';
            $table = SGL_Sql::addTablePrefix('module');
            $query = 'SELECT COUNT(*) FROM ' . $table;
            $res = $dbh->getOne($query);
        }

        if (!PEAR::isError($res) && $res > 1) { // it's a re-install
            $data['aModuleList'] = SGL_Install_Common::getModuleList();
            if (count($data['aModuleList'])) {
                foreach ($data['aModuleList'] as $key => $moduleName) {
                    if (!SGL::moduleIsEnabled($moduleName)) {
                        unset($data['aModuleList'][$key]);
                    }
                }
            }
        } else { // a new install
            SGL_Error::pop();
            if (PEAR::isError($dbh)) {
                SGL_Error::pop(); // two errors produced
            }
            $data['aModuleList'] = SGL_Install_Common::getMinimumModuleList();
        }

        //  override with custom settings if they exist
        $data = SGL_Install_Common::overrideDefaultInstallSettings($data);
        $buildNavTask = 'SGL_Task_BuildNavigation';
        if (in_array('cms', $data['aModuleList'])) {
            require_once SGL_MOD_DIR . '/cms/init.php';
            $buildNavTask = 'SGL_Task_BuildNavigation2';
        }
        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_SetTimeout());
        $runner->addTask(new SGL_Task_CreateConfig());
        $runner->addTask(new SGL_Task_LoadCustomConfig());
        $runner->addTask(new SGL_Task_DefineTableAliases());
        $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
        $runner->addTask(new SGL_Task_PrepareInstallationProgressTable());
        $runner->addTask(new SGL_Task_DropTables());
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
        $runner->addTask(new SGL_Task_AddTestDataToConfig());
        $runner->addTask(new SGL_Task_CreateAdminUser());
        $runner->addTask(new SGL_Task_InstallerCleanup());

        $ok = $runner->main();
    }
}

//  start wizard
$wizard = new HTML_QuickForm_Controller('installationWizard');
$wizard->addPage(new WizardLicenseAgreement('page1'));
$wizard->addPage(new WizardSetupAuth('page2'));
$wizard->addPage(new WizardDetectEnv('page3'));
$wizard->addPage(new WizardTestDbConnection('page4'));
$wizard->addPage(new WizardCreateDb('page5'));
$wizard->addPage(new WizardCreateAdminUser('page6'));

// We actually add these handlers here for the sake of example
// They can be automatically loaded and added by the controller
$wizard->addAction('display', new ActionDisplay());
$wizard->addAction('next', new HTML_QuickForm_Action_Next());
$wizard->addAction('back', new HTML_QuickForm_Action_Back());

// This is the action we should always define ourselves
$wizard->addAction('process', new ActionProcess());

$wizard->run();

if (SGL_Install_Common::errorsExist()) {
    SGL_Install_Common::errorPrint();
}
?>
