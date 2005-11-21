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
    - move to correct locatin
    - apply user perms
    - apply prefs
    - add module's db tables to Config
    - load module's schema + data
    - add 'section' or 'screen' navigation links
    - register module in registry
*/

//  initialise
session_start();
require_once dirname(__FILE__) . '/../lib/SGL/Install.php';
require_once dirname(__FILE__) . '/../lib/SGL/Task.php';
require_once dirname(__FILE__) . '/../lib/SGL/Config.php';
require_once dirname(__FILE__) . '/../lib/SGL/Url.php';
require_once dirname(__FILE__) . '/../lib/SGL/TaskRunner.php';
require_once dirname(__FILE__) . '/../lib/SGL/Tasks/Setup.php';
require_once dirname(__FILE__) . '/../lib/SGL/Tasks/Install.php';

$init = new SGL_TaskRunner();
$init->addTask(new SGL_Task_SetupPaths());
$init->addTask(new SGL_Task_SetupConstants());
$init->addTask(new SGL_Task_SetBaseUrlMinimal());
$init->addTask(new SGL_Task_SetGlobals());
$init->main();

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

    header('Location: '.SGL_BASE_URL.'/index.php');
    exit;
}

//  check authorization
if (file_exists(SGL_PATH . '/var/INSTALL_COMPLETE.php')
        && empty($_SESSION['valid'])) {

    if (!empty($_POST['frmPassword'])) {
        $aLines = file(SGL_PATH . '/var/INSTALL_COMPLETE.php');
        $secret = trim(substr($aLines[1], 1));
        if ($_POST['frmPassword'] != $secret) {
            $_SESSION['message'] = 'incorrect password';
            header('Location: setup.php');
            exit;
        } else {
            $_SESSION['valid'] = true;
            header('Location: setup.php');
        }
    } else {
        SGL_Install::printHeader();
        SGL_Install::printLoginForm();
        SGL_Install::printFooter();
        exit;
    }
}

// load QuickFormController libs
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Next.php';
require_once 'HTML/QuickForm/Action/Back.php';
require_once 'HTML/QuickForm/Action/Display.php';

require_once 'DB.php';

//  load SGL libs
require_once SGL_PATH . '/lib/SGL/DB.php';
require_once SGL_PATH . '/lib/SGL/Config.php';

//  load wizard screens
require_once SGL_PATH . '/lib/SGL/Install/WizardLicenseAgreement.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardDetectEnv.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardTestDbConnection.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardCreateDb.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardCreateAdminUser.php';

//  load tasks
require_once SGL_PATH . '/lib/SGL/Tasks/DetectEnv.php';

class ActionProcess extends HTML_QuickForm_Action
{
    function perform(&$page, $actionName)
    {
        $data = $page->controller->exportValues();

        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_CreateConfig());
        $runner->addTask(new SGL_Task_DefineTableAliases());
        $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
        $runner->addTask(new SGL_Task_CreateTables());
        $runner->addTask(new SGL_Task_LoadDefaultData());
        $runner->addTask(new SGL_Task_CreateConstraints());
        $runner->addTask(new SGL_Task_EnableForeignKeyChecks());
        $runner->addTask(new SGL_Task_VerifyDbSetup());
        $runner->addTask(new SGL_Task_CreateFileSystem());
        $runner->addTask(new SGL_Task_CreateDataObjectEntities());
        $runner->addTask(new SGL_Task_SyncSequences());
        $runner->addTask(new SGL_Task_CreateAdminUser());
        $runner->addTask(new SGL_Task_InstallerCleanup());

        set_time_limit(60);
        $ok = $runner->main();
    }
}

//  start wizard
$wizard =& new HTML_QuickForm_Controller('installationWizard');
$wizard->addPage(new WizardLicenseAgreement('page1'));
$wizard->addPage(new WizardDetectEnv('page2'));
$wizard->addPage(new WizardTestDbConnection('page3'));
$wizard->addPage(new WizardCreateDb('page4'));
$wizard->addPage(new WizardCreateAdminUser('page5'));

// We actually add these handlers here for the sake of example
// They can be automatically loaded and added by the controller
$wizard->addAction('display', new ActionDisplay());
$wizard->addAction('next', new HTML_QuickForm_Action_Next());
$wizard->addAction('back', new HTML_QuickForm_Action_Back());

// This is the action we should always define ourselves
$wizard->addAction('process', new ActionProcess());

$wizard->run();

if (SGL_Install::errorsExist()) {
    SGL_Install::errorPrint();
}
?>