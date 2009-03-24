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
// | MaintenanceMgr.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: MaintenanceMgr.php,v 1.56 2005/05/31 23:34:23 demian Exp $

require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';

/**
 * Provides tools preform maintenance tasks.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.56 $
 * @since   PHP 4.1
 */
class MaintenanceMgr extends SGL_Manager
{
    function MaintenanceMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Maintenance';
        $this->template  = 'maintenance.html';
        $this->redirect  = true;
        $this->da        = &DefaultDAO::singleton();

        $this->_aActionsMapping = array(
            'dbgen'              => array('dbgen'),
            'rebuildSequences'   => array('rebuildSequences'),
            'rebuildSeagull'     => array('rebuildSeagull'),
            'clearCache'         => array('clearCache'),
            'checkLatestVersion' => array('checkLatestVersion', 'redirectToDefault'),
            'list'               => array('list'),
            'deleteConfigs'      => array('deleteConfigs', 'redirectToDefault')
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template       = $this->template;
        $input->submitted      = $req->get('submitted');
        $input->action         = ($req->get('action')) ? $req->get('action') : 'list';
        $input->cache          = ($req->get('frmCache')) ? $req->get('frmCache') : array();
        $input->useSampleData  = ($req->get('frmSampleData')) ? 1 : 0;

        $input->aModules = $req->get('aModules');

        if ($input->submitted) {
            if ($req->get('action') == '' || $req->get('action') == 'list') {
                $aErrors['noSelection'] = SGL_Output::translate('please specify an option');
            }
            if ($input->action == 'clearCache' && !count($input->cache)) {
                $aErrors['nothingChecked'] = SGL_Output::translate('please check at least one box');
            }
            if ($input->action == 'deleteConfigs' && empty($input->aModules)) {
                $aErrors['nothingChecked'] = SGL_Output::translate('please check at least one box');
            }
         }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->aModules = SGL_Util::getAllModuleDirs();
    }

    //  regenerate dataobject entity files
    function _cmd_dbgen(&$input, &$output)
    {
        require_once SGL_CORE_DIR . '/Task/Install.php';
        require_once SGL_CORE_DIR . '/Sql.php';

        //  First regenerate entities files
        $resEntities = SGL_Task_CreateDataObjectEntities::run();
        //  Then regenerate links file
        $data['aModuleList'] = SGL_Util::getAllModuleDirs($onlyRegistered = true);
        $resLinks = SGL_Task_CreateDataObjectLinkFile::run($data);
        SGL::raiseMsg('Data Objects rebuilt successfully', true, SGL_MESSAGE_INFO);
    }

    function _cmd_checkLatestVersion(&$input, &$output)
    {
        require_once SGL_CORE_DIR . '/Install/Common.php';
        $localVersion = SGL_Install_Common::getFrameworkVersion();

        require_once SGL_CORE_DIR . '/XML/RPC/Remote.php';
        $config = SGL_MOD_DIR . '/default/xmlrpc_conf.ini';
        $remote = new SGL_XML_RPC_Remote($config);
        $remoteVersion = $remote->call('framework.determineLatestVersion');

        if (PEAR::isError($remoteVersion)) {
            SGL::raiseError('remote interface problem');
        } else {
            $res = version_compare($localVersion, $remoteVersion);
            $msgType = SGL_MESSAGE_ERROR;
            if ($res < 0) {
                $msg = 'There is a newer version available: ' . $remoteVersion . ', please upgrade '.
                '<a href="http://seagull.phpkitchen.com/index.php/publisher/articleview/frmArticleID/12/staticId/20/">here</a>';
                $msgType = SGL_MESSAGE_WARNING;
            } else {
                $msg = "Your current version, $localVersion, is up to date";
                $msgType = SGL_MESSAGE_INFO;
            }
            SGL::raiseMsg($msg, false, $msgType);
        }
    }

    function _cmd_rebuildSequences(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_CORE_DIR . '/Task/Install.php';
        $res = SGL_Task_SyncSequences::run();
        if (PEAR::isError($res)) {
            return $res;
        } else {
            SGL::raiseMsg('Sequences rebuilt successfully', true, SGL_MESSAGE_INFO);
        }
    }

    function _cmd_rebuildSeagull(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!preg_match("/(mysql|pgsql)/", $this->dbh->phptype)) {
            SGL::raiseMsg('This operation is currently only supported for MySQL or PostgreSQL',
                false, SGL_MESSAGE_INFO);
            return false;
        }
        if (!empty($this->conf['debug']['production'])) {
            SGL::raiseMsg('This operation is not supported in production mode',
                false, SGL_MESSAGE_INFO);
            return false;
        }
        require_once SGL_CORE_DIR . '/Task/Install.php';

        //  retrieve Install password
        $aLines = file(SGL_PATH . '/var/INSTALL_COMPLETE.php');
        $installPassword = trim(substr($aLines[1], 1));

        //  retrieve translation settings
        $transContainer = ($this->conf['translation']['container'] == 'db') ? 1 : 0;
        $transLanguage  = str_replace('_','-', explode(',', $this->conf['translation']['installedLanguages']));

        //  retrieve admin user
        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $oUserDao    = &UserDAO::singleton();
        $aAdminUserIds = $oUserDao->getUsersByRoleId(SGL_ADMIN);
        $oAdmin      = $oUserDao->getUserById($aAdminUserIds[0]); // get first admin user
        $aMasterPrefs= $oUserDao->getMasterPrefs();

        $data = array(
            'createTables'          => 1,
            'insertSampleData'      => $input->useSampleData,

            // admin data
            'adminUserName'         => $oAdmin->username,
            'adminPassword'         => $oAdmin->passwd,
            'adminFirstName'        => $oAdmin->first_name,
            'adminLastName'         => $oAdmin->last_name,
            'adminEmail'            => $oAdmin->email,
            'adminPasswordIsHash'   => true,
            'aModuleList'           => SGL_Util::getAllModuleDirs($onlyRegistered = true),
            'serverName'            => SGL_SERVER_NAME,
            'installPassword'       => $installPassword,
            'storeTranslationsInDB' => $transContainer,
            'installLangs'          => $transLanguage,
            'aPrefs'                => $aMasterPrefs
            );

        define('SGL_ADMIN_REBUILD', 1);

        $buildNavTask = SGL::moduleIsEnabled('cms')
            ? 'SGL_Task_BuildNavigation2'
            : 'SGL_Task_BuildNavigation';

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

        //  check for custom tasks in config
        if (!empty($this->conf['site']['customRebuildTasks'])) {
            $aTasks = explode(',', $this->conf['site']['customRebuildTasks']);
            foreach ($aTasks as $task) {
                // attempt to load task if one is missing
                if (!class_exists($task)) {
                    $path = str_replace('_', '/', $task);
                    if (file_exists(SGL_LIB_DIR . '/' . $path . '.php')) {
                        require_once SGL_LIB_DIR . '/' . $path . '.php';
                    }
                }
                if (class_exists($task)) {
                    $runner->addTask(new $task());
                }
            }
        }

        $ok = $runner->main();

        // reload local config
        $c = &SGL_Config::singleton();
        // we want to reload default module
        $c->set('localConfig', array('moduleName' => 'non existent module'));
        $c->ensureModuleConfigLoaded('default');

        if (SGL_Error::count()) {
            $oError = SGL_Error::getLast();
            $msg = $oError->getMessage();
            $type = SGL_MESSAGE_WARNING;
        } else {
            $msg = 'Environment rebuilt successfully';
            $type = SGL_MESSAGE_INFO;
        }
        SGL::raiseMsg($msg, false, $type);
    }

    function _cmd_clearCache(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $msg = '';
        if (array_key_exists('templates', $input->cache)) {
            require_once 'System.php';
            $tmplDir = SGL_CACHE_DIR . "/tmpl/";
            $aDirs = System::find(array($tmplDir, "-maxdepth", 1));
            //  exclude last element found which is the containing tmpl folder itself
            array_pop($aDirs);
            foreach ($aDirs as $dir) {
                $aFiles = System::find(array($dir, "-name", "*"));
                //  exclude last element found which is the theme folder
                array_pop($aFiles);
                if (!@System::rm($aFiles)) {
                    SGL::raiseError('There was a problem deleting the files',
                        SGL_ERROR_FILEUNWRITABLE);
                } else {
                    SGL::raiseMsg('Cache files successfully deleted', true, SGL_MESSAGE_INFO);
                }
            }
        }
        if (array_key_exists('translations', $input->cache)) {
            if (SGL_Translation::clearCache() === false) {
                SGL::raiseError('There was a problem deleting the files',
                    SGL_ERROR_FILEUNWRITABLE);
            } else {
                SGL::raiseMsg('Cache files successfully deleted', true, SGL_MESSAGE_INFO);
            }
        }
        // drop lib cache
        if (!empty($input->cache['lib'])) {
            if (file_exists(SGL_VAR_DIR . '/cachedLibs.php')) {
                unlink(SGL_VAR_DIR . '/cachedLibs.php');
            }
        }
        // drop js cache
        if (!empty($input->cache['js'])) {
            require_once 'System.php';
            $tmpDir = SGL_VAR_DIR . '/tmp';
            $aFiles = System::find("$tmpDir -name *js_*");
            if (!@System::rm($aFiles)) {
                SGL::raiseError('There was a problem deleting the files',
                    SGL_ERROR_FILEUNWRITABLE);
            } else {
                SGL::raiseMsg('Cache files successfully deleted', true, SGL_MESSAGE_INFO);
            }
        }
        if (count($input->cache) > 0) {
            $success = true;
            foreach ($input->cache as $group => $v) {
                $result = SGL_Cache::clear($group);
                $success = $success && $result;
            }
            if ($success === false) { //  let's see what happens with Cache_Lite's return type
                SGL::raiseError('There was a problem deleting the files',
                    SGL_ERROR_FILEUNWRITABLE);
            } else {
                SGL::raiseMsg('Cache files successfully deleted', true, SGL_MESSAGE_INFO);
            }
        }
    }

    function _cmd_deleteConfigs(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        foreach ($input->aModules as $moduleName => $foo) {
            $fileName = SGL_VAR_DIR . '/config/' . $moduleName . '.ini';
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
        SGL::raiseMsg('Cached configs successfully deleted',
            true, SGL_MESSAGE_INFO);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_redirectToDefault(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            if (!($this->redirect)) {
                return;
            } else {
                SGL_HTTP::redirect();
            }

        //  else display error with blank template
        } else {
            $output->template = 'error.html';
        }
    }
}
?>
