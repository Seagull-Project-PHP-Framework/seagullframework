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
// | ModuleMgr.php                                                             |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// |            Michael Willemot <michael@sotto.be>                            |
// +---------------------------------------------------------------------------+
// $Id: ModuleMgr.php,v 1.37 2005/06/22 00:32:36 demian Exp $

require_once 'DB/DataObject.php';
require_once 'System.php';
require_once SGL_CORE_DIR . '/Task/Install.php';
require_once SGL_CORE_DIR . '/Sql.php';
require_once SGL_CORE_DIR. '/Install/Common.php';
require_once SGL_MOD_DIR . '/default/classes/DefaultDAO.php';

define('SGL_ICONS_PER_ROW', 3);

/**
 * Manages loading of modules.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class ModuleMgr extends SGL_Manager
{
    function ModuleMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Module Manager';
        $this->template  = 'moduleOverview.html';
        $this->da        = &DefaultDAO::singleton();

        $this->_aActionsMapping = array(
            'add'        => array('add'),
            'detect'     => array('detect'),
            'insert'     => array('insert', 'redirectToDefault'),
            'install'    => array('install', 'redirectToDefault'),
            'edit'       => array('edit'),
            'update'     => array('update', 'redirectToDefault'),
            'delete'     => array('delete', 'redirectToDefault'),
            'uninstall'  => array('uninstall', 'redirectToDefault'),
            'reinstall'  => array('reinstall', 'redirectToDefault'),
            'deregister' => array('deregister', 'redirectToDefault'),
            'list'       => array('list'),
            'overview'   => array('overview'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;

        //  default action is 'overview' unless paging through results,
        //  in which case default is 'list'
        $input->from            = $req->get('pageID');
        $input->totalItems      = $req->get('totalItems');

        $input->action = ($req->get('action')) ? $req->get('action') : 'list';

        if (!is_null($input->from) && $input->action == 'overview') {
            $input->action = 'list';
        }
        $input->aDelete         = $req->get('frmDelete');
        $input->moduleId        = $req->get('frmModuleId');
        $input->moduleName      = $req->get('frmModuleName');
        $aModules               = $req->get('module');
        $input->displayDeRegisteredModules = (!is_null($req->get('displayDeRegisteredModules')))
            ? $req->get('displayDeRegisteredModules')
            : SGL_Session::get('displayDeRegisteredModules');

//        if (count($aModules)) {
//            foreach ($aModules as $k => $module) {
//                $input->module[$k] = (object)$module;
//                $input->module[$k]->is_configurable = (isset($input->module[$k]->is_configurable)) ? 1 : 0;
//            }
//        }

        $input->submitted       = $req->get('submitted');

        //  validate fields
        $aErrors = array();
        if ($input->submitted || in_array($input->action, array('insert', 'update'))) {
            $aFields = array(
                'name' => 'Please, specify a name',
                'title' => 'Please, specify a title',
                'description' => 'Please, specify a description',
                'icon' => 'Please, specify the name of the icon-file'
            );
            if (!empty($input->module)) {
                foreach ($aFields as $field => $errorMsg) {
                    for ($x = 0; $x < count($input->module); $x++) {
                        if (empty($input->module[$x]->$field)) {
                            $aErrors[$x][$field] = $errorMsg;
                            $aErrors[$field] = $errorMsg;
                        }
                    }
                }
            } else {
                $aErrors['name'] = 'no module data supplied';
            }
        }
        //  if errors have occured -
#commented out because multi-module validation not solved
//        if (isset($aErrors) && count($aErrors)) {
//            SGL::raiseMsg('Please fill in the indicated fields');
//            $input->error = $aErrors;
//            $input->template = 'moduleEdit.html';
//            $input->aModules = $input->module;
//            for ($x = 0; $x < count($input->module); $x++) {
//                $input->isConfigurable = ($input->module[$x]->is_configurable) ? 'checked' : '';
//            }
//            $this->validated = false;
//        }

        //put some vars to session
        SGL_Session::set('displayDeRegisteredModules',
            $input->displayDeRegisteredModules);
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->aAdminUris = SGL_Util::getAllModuleDirs($onlyRegistered = false);
        $output->deRegisteredModulesChecked = ($output->displayDeRegisteredModules)
            ? ' checked="checked"' : '';
    }

    function _cmd_syncModules($aModules)
    {
        foreach ($aModules as $module) {
            $aModulesClean[] = $module->name;
        }

        $aSglModules = $this->da->getPackagesByChannel();
        foreach ($aSglModules as $module) {
            if ($module != 'seagull') {
                $aSglModulesClean[] = str_replace('seagull_', '', $module);
            }
        }
        //  determine which PEAR-installed modules are missing from db
        $aRes = array_diff($aSglModulesClean, $aModulesClean);

        if (count($aRes)) {
            foreach ($aRes as $module) {
                $oModule = $this->da->getModuleById();
                $oModule->name = $module;
                $ok = $this->da->addModule($oModule);
                unset($oModule);
            }
        }

    }

//    function _cmd_overview(&$input, &$output)
//    {
//        SGL::logMessage(null, PEAR_LOG_DEBUG);
//
//        $aModules = $this->da->getAllModules();
//        if (!PEAR::isError($aModules)) {
//
//            //  ensure modules installed with pear packager are in db
//            #$this->_syncModules($aModules);
//
//            $ret = array();
//            foreach ($aModules as $k => $oModule) {
//
//                //  split module/manager values out as object properties
//                if (strpos($oModule->admin_uri, '/') !== false) {
//                    list($oModule->module, $oModule->manager) = explode('/', $oModule->admin_uri);
//
//                } elseif (!empty($oModule->admin_uri)) {
//                    $oModule->module = $oModule->admin_uri;
//                    $oModule->manager = '';
//                } else {
//                    $oModule->module = '';
//                    $oModule->manager = '';
//                }
//                $oModule->bgnd = ($oModule->is_configurable) ? 'bgnd' : 'outline';
//                $oModule->breakRow = !((count($ret)+1) % SGL_ICONS_PER_ROW);
//                $ret[] = $oModule;
//            }
//            $output->aModules = $ret;
//        } else {
//            SGL::raiseError('getting module list failed', SGL_ERROR_NODATA);
//        }
//    }

    function _cmd_install(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // retrieve translation settings
        $transContainer = ($this->conf['translation']['container'] == 'db')
            ? 1 : 0;
        $transLanguage = str_replace('_', '-',
            explode(',', $this->conf['translation']['installedLanguages']));

        $data = array(
            'createTables'           => 1,
            'insertSampleData'       => 1,
            'aModuleList'            => array($input->moduleName),
            'moduleInstall'          => true,
            'storeTranslationsInDB'  => $transContainer,
            'installLangs'           => $transLanguage,
            'skipLangTablesCreation' => true
        );
        define('SGL_ADMIN_REBUILD', 1);// rename to HIDE_OUTPUT

        //  if we're installing cms, insert sections to 'page' table
        $installingCms = false;
        if ($input->moduleName == 'cms' || SGL::moduleIsEnabled('cms')) {
            $buildNavTask = 'SGL_Task_BuildNavigation2';
            require_once SGL_MOD_DIR . '/cms/init.php';
            if (!SGL::moduleIsEnabled('cms')) {
                $installingCms = true;
            }
        } else {
            $buildNavTask = 'SGL_Task_BuildNavigation';
        }

        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_InitialiseModules()); // load any init.php file
        $runner->addTask(new SGL_Task_DefineTableAliases());
        $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
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
        $runner->addTask(new SGL_Task_CreateDataObjectEntities());
        $runner->addTask(new SGL_Task_CreateDataObjectLinkFile());
        $runner->addTask(new SGL_Task_SymLinkWwwData());
        $runner->addTask(new SGL_Task_AddTestDataToConfig());
        $ok = $runner->main();

        //  check for errors
        $extraMsg = ($installingCms)
            ? '.  To recreate the navigation for other installed modules, please' .
              ' <a href="'.SGL_Url::makeLink('list', 'maintenance', 'default').'">rebuild</a> your Seagull installation.'
            : '';

        if (SGL_Error::count()) {
            $oError = SGL_Error::getLast();
            $msg = $oError->getMessage();
            $type = SGL_MESSAGE_WARNING;
        } else {
            $msg = 'The ' . $input->moduleName . ' module was successfully installed' . $extraMsg;
            $type = SGL_MESSAGE_INFO;
        }
        SGL::raiseMsg($msg, false, $type);
    }

    function _cmd_uninstall(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  drop tables
        $dbShortname = SGL_Sql::getDbShortnameFromType($this->conf['db']['type']);
        //  get all tables defined in this module's schema
        $oModule = $this->da->getModuleById($input->moduleId);
        //  disallow uninstalling default modules
        if (in_array($oModule->name, SGL_Install_Common::getMinimumModuleList())) {
            SGL::raiseMsg('This is a default module and cannot be uninstalled',
                false, SGL_MESSAGE_ERROR);
        } else {

            $data = array(
                'aModuleList' => array($oModule->name),
                'moduleId' => $oModule->module_id,
                'createTables' => 1,
                'moduleInstall' => true,
                );
            define('SGL_ADMIN_REBUILD', 1);// rename to HIDE_OUTPUT

            $runner = new SGL_TaskRunner();
            $runner->addData($data);
            $runner->addTask(new SGL_Task_DisableForeignKeyChecks());
            $runner->addTask(new SGL_Task_DropTables());
            $runner->addTask(new SGL_Task_RemoveDefaultData());
            $runner->addTask(new SGL_Task_RemoveNavigation());
            $runner->addTask(new SGL_Task_RemoveBlockData());
            $runner->addTask(new SGL_Task_EnableForeignKeyChecks());
            $runner->addTask(new SGL_Task_SyncSequences());
            $runner->addTask(new SGL_Task_UnLinkWwwData());
            $runner->addTask(new SGL_Task_RemoveTestDataFromConfig());
            $runner->addTask(new SGL_Task_DeregisterModule());
            $ok = $runner->main();

            // remove translations
            if ($this->conf['translation']['container'] == 'db') {
                $ok = SGL_Translation::removeTranslations($oModule->name);
            }

            //  add rebuild info if we're uninstalling cms
            $extraMsg = ($oModule->name == 'cms')
                ? '.  To complete the uninstall of the cms module, please' .
                  ' <a href="'.SGL_Url::makeLink('list', 'maintenance', 'default').'">rebuild</a> your Seagull installation.'
                : '';

            SGL::raiseMsg('The ' . $oModule->name . ' module was successfully uninstalled' . $extraMsg,
                false, SGL_MESSAGE_INFO);
        }
    }

    function _cmd_reinstall(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!preg_match("/(mysql|pgsql)/", $this->dbh->phptype)) {
            SGL::raiseMsg('This operation is currently only supported for MySQL or PostgreSQL',
                false, SGL_MESSAGE_INFO);
            return false;
        }

        $dbShortname = SGL_Sql::getDbShortnameFromType($this->conf['db']['type']);
        //  get all tables defined in this module's schema
        $oModule = $this->da->getModuleById($input->moduleId);

        //  disallow uninstalling default modules
        if (in_array($oModule->name, SGL_Install_Common::getMinimumModuleList())) {
            SGL::raiseMsg('This is a default module and cannot be reinstalled',
                false, SGL_MESSAGE_ERROR);
        } else {
            $data = array(
                'createTables'          => 1,
    //            'insertSampleData'      => $input->useSampleData,
                'aModuleList' => array($oModule->name),
                'moduleId' => $oModule->module_id,
                'createTables' => 1,
                'moduleInstall' => true,
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
            $runner->addTask(new SGL_Task_DropTables());
            $runner->addTask(new SGL_Task_RemoveDefaultData());
            $runner->addTask(new SGL_Task_RemoveNavigation());
            $runner->addTask(new SGL_Task_RemoveBlockData());
            $runner->addTask(new SGL_Task_DeregisterModule());
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
            $runner->addTask(new SGL_Task_CreateDataObjectEntities());
            $runner->addTask(new SGL_Task_UnLinkWwwData());
            $runner->addTask(new SGL_Task_SymLinkWwwData());

            $ok = $runner->main();

            if (SGL_Error::count()) {
                $oError = SGL_Error::getLast();
                $msg = $oError->getMessage();
                $type = SGL_MESSAGE_WARNING;
            } else {
                $msg = "'$oModule->name' module reinstalled successfully";
                $type = SGL_MESSAGE_INFO;
            }
            SGL::raiseMsg($msg, false, $type);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $output->action = 'update';
        $output->template  = 'moduleEdit.html';
        $oModule = DB_DataObject::factory($this->conf['table']['module']);
        $oModule->get($input->moduleId);
        $output->aModules = array($oModule);
        $output->isConfigurable = ($oModule->is_configurable) ? ' checked' : '';
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'moduleList.html';
        $newEntry = DB_DataObject::factory($this->conf['table']['module']);
        $newEntry->get($input->moduleId);
        $newEntry->setFrom($input->module[0]);
        $success = $newEntry->update();

        if ($success !== false) {
            SGL::raiseMsg('module successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_deregister(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  de-register module
        $rm = DB_DataObject::factory($this->conf['table']['module']);
        $rm->get($input->moduleId);
        $ok = $rm->delete();

        SGL::raiseMsg('The module was successfully de-registered', false, SGL_MESSAGE_INFO);
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  delete module files
        $msg = 'The module\'s directory does not appear to be writable and therefore
            could not be removed, please give the webserver permissions to write to it';
        $moduleDir =  SGL_MOD_DIR . '/' . $input->moduleName;
        if (is_writable($moduleDir)) {
            $success = System::rm(array('-r', $moduleDir));
        }

        if (isset($success) && $success == true) {
            SGL::raiseMsg('The module was successfully removed', false, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg($msg, false, SGL_MESSAGE_ERROR);
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'moduleList.html';
        $query = "SELECT * FROM {$this->conf['table']['module']} ORDER BY name";
        $aModules = $this->dbh->getAll($query);

        // if there are modules, determine whether installed
        if (count($aModules)) {
            foreach ($aModules as $k => $oModule) {
                $aModules[$k]->isInstalled = $this->_isInstalled($oModule->name);
            }
        }
        if ($input->displayDeRegisteredModules) {
            $aAllModules = SGL_Util::getAllModuleDirs($onlyRegistered = false);
            $aRegisteredModules = SGL_Util::getAllModuleDirs();
            $aDiff = array_diff($aAllModules, $aRegisteredModules);

            $aDeRegisteredModules = array();
            foreach ($aDiff as $modulename) {
                $module = new stdClass();
                $module->name = $module->title = $modulename;
                $module->isInstalled = false;
                $module->description = 'Details available when installed ...';
                $aDeRegisteredModules[] = $module;
            }
            $aModules = array_merge($aModules, $aDeRegisteredModules);
        }
        $output->aModules = $aModules;
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function _isInstalled($moduleName)
    {
        //  get installed tables to compare against
        static $aInstalledTables, $dbShortname;
        if (!isset($aInstalledTables)) {
            $aInstalledTables = $this->dbh->getListOf('tables');
            $dbShortname = SGL_Sql::getDbShortnameFromType($this->conf['db']['type']);
        }
        //  gets all tables defined in this module's schema
        $dataDir = SGL_MOD_DIR . '/' . $moduleName . '/data';
        $schemaFile = $dataDir . '/schema.' . $dbShortname . '.sql';
        $dataFile = $dataDir . '/data.default.' . $dbShortname . '.sql';
        //  Some modules, like export, don't have schema and don't need installing.
        //  is_file($dataDir) is for cases, on delete, where some web-writable files
        //  are deleted, but not all
        if (!is_file($schemaFile)) {
            return is_file($dataFile) ? true : false ;
        }
        $aTablesByModule = SGL_Sql::extractTableNamesFromSchema($schemaFile);
        //  check to see tables in existing db correspond to those specified in schema
        foreach ($aTablesByModule as $tablename) {
            if (!in_array($tablename, $aInstalledTables)) {
                return false;
            }
        }
        return true;
    }
}
?>