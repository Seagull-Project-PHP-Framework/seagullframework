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
// | Authors: Demian Turner <demian@phpkitchen.com>                            |
// |          Gerry Lachac <glachac@tethermedia.com>                           |
// |          Andy Crain <apcrain@fuse.net>                                    |
// +---------------------------------------------------------------------------+

function __autoload($className)
{
    if (!class_exists($className)) {
        $path = str_replace('_', '/', $className);
        $file = $path . '.php';
        // man we have to get rid of Flexy ..
        if ($file == 'HTML/Template/Flexy/Token/Comment.php' ||
            $file == 'HTML/Template/Flexy/Token/Doctype.php' ||
            $file == 'HTML/Template/Flexy/Token/Literal.php' ||
            $file == 'HTML/Template/Flexy/Token/WhiteSpace.php' ||
            $file == 'HTML/Template/Flexy/Token/CloseTag.php' ||
            $file == 'HTML/Template/Flexy/Token/Name.php'
        ) {
            return;
        }
        require $file;
    }
}
//  set initial paths according to install type
$pearTest = '@PHP-DIR@';
if ($pearTest != '@' . 'PHP-DIR'. '@') {
    define('SGL_PEAR_INSTALLED', true);
    $rootDir = '@PHP-DIR@/Seagull';
    $varDir  = '@DATA-DIR@/Seagull/var';
} else {
    $rootDir = realpath(dirname(__FILE__) . '/..');
    $varDir  = realpath(dirname(__FILE__) . '/../var');
}

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

require_once $rootDir .'/lib/SGL/Task.php';
require_once $rootDir .'/lib/SGL/FrontController.php';
require_once $rootDir .'/lib/SGL/Task/SetupPaths.php';
require_once $rootDir . '/lib/SGL/Install/Common.php';
SGL_Task_SetupPaths::run();

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
    $conf = SGL_Config::singleton()->getAll();
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
//require_once SGL_PATH . '/lib/SGL/Install/WizardTestDbConnection.php';
//require_once SGL_PATH . '/lib/SGL/Install/WizardCreateDb.php';
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
//        $dbh = & SGL_DB::singleton();
//        $res = false;
//        if (!PEAR::isError($dbh)) {
//            require_once SGL_CORE_DIR . '/Sql.php';
//            $table = SGL_Sql::addTablePrefix('module');
//            $query = 'SELECT COUNT(*) FROM ' . $table;
//            $res = $dbh->getOne($query);
//        }
//
//        if (!PEAR::isError($res) && $res > 1) { // it's a re-install
//            $data['aModuleList'] = SGL_Install_Common::getModuleList();
//            if (count($data['aModuleList'])) {
//                foreach ($data['aModuleList'] as $key => $moduleName) {
//                    if (!SGL::moduleIsEnabled($moduleName)) {
//                        unset($data['aModuleList'][$key]);
//                    }
//                }
//            }
//        } else { // a new install
//            SGL_Error::pop();
//            if (PEAR::isError($dbh)) {
//                SGL_Error::pop(); // two errors produced
//            }
//            $data['aModuleList'] = SGL_Install_Common::getMinimumModuleList();
//        }
        $data['aModuleList'] = SGL_Install_Common::getMinimumModuleList();
        $data['createTables'] = 1;#REMOVE HACK

        //  override with custom settings if they exist
        $data = SGL_Install_Common::overrideDefaultInstallSettings($data);
//        $buildNavTask = 'SGL_Task_BuildNavigation';
//        if (in_array('cms', $data['aModuleList'])) {
//            require_once SGL_MOD_DIR . '/cms/init.php';
//            $buildNavTask = 'SGL_Task_BuildNavigation2';
//        }
        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_SetTimeout());
        $runner->addTask(new SGL_Task_CreateConfig());
        $runner->addTask(new SGL_Task_LoadCustomConfig());
//        $runner->addTask(new SGL_Task_DefineTableAliases());
//        $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
        $runner->addTask(new SGL_Task_PrepareInstallationProgressTable());
//        $runner->addTask(new SGL_Task_DropTables());
//        $runner->addTask(new SGL_Task_CreateTables());
        $runner->addTask(new SGL_Task_LoadTranslations());
//        $runner->addTask(new SGL_Task_LoadDefaultData());
//        $runner->addTask(new SGL_Task_LoadSampleData());
//        $runner->addTask(new SGL_Task_LoadCustomData());
//        $runner->addTask(new SGL_Task_SyncSequences());
//        $runner->addTask(new $buildNavTask());
//        $runner->addTask(new SGL_Task_LoadBlockData());
//        $runner->addTask(new SGL_Task_CreateConstraints());
//        $runner->addTask(new SGL_Task_SyncSequences());
//        $runner->addTask(new SGL_Task_EnableForeignKeyChecks());

        $runner->addTask(new SGL_Task_VerifyDbSetup());// it prints "launch seagull"
        $runner->addTask(new SGL_Task_CreateFileSystem());
//        $runner->addTask(new SGL_Task_CreateDataObjectEntities());
//        $runner->addTask(new SGL_Task_CreateDataObjectLinkFile());
        $runner->addTask(new SGL_Task_UnLinkWwwData());
        $runner->addTask(new SGL_Task_SymLinkWwwData());
        //$runner->addTask(new SGL_Task_AddTestDataToConfig());
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
//$wizard->addPage(new WizardTestDbConnection('page4'));
//$wizard->addPage(new WizardCreateDb('page5'));
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
