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
// | ModuleGenerationMgr.php                                                   |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: ModuleGenerationMgr.php,v 1.56 2005/05/31 23:34:23 demian Exp $

require_once SGL_MOD_DIR  . '/default/classes/DA_Default.php';

/**
 * Provides tools to manage translations and mtce tasks.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 */

class ModuleGenerationMgr extends SGL_Manager
{
    function ModuleGenerationMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Maintenance';
        $this->template     = 'moduleGenerator.html';
        $this->da = &DA_Default::singleton();
        $this->_aActionsMapping =  array(
            'createModule' => array('createModule', 'redirectToDefault'),
            'list'         => array('list'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->submitted   = $req->get('submitted');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->createModule = (object)$req->get('frmCreateModule');

        if ($input->submitted) {

            //  checks for creating modules
            if ($input->action == 'createModule') {
                if (empty($input->createModule->moduleName)) {
                    $aErrors['moduleName'] = SGL_Output::translate('please enter module name');
                }
                if (empty($input->createModule->managerName)) {
                    $aErrors['managerName'] = SGL_Output::translate('please enter manager name');
                }
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function _cmd_createModule(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($this->conf['tuples']['demoMode']) && $this->conf['tuples']['demoMode'] == true) {
            SGL::raiseMsg('Modules cannot be generated in demo mode', false, SGL_MESSAGE_WARNING);
            return false;
        }

        if (!is_writable(SGL_MOD_DIR)) {
            SGL::raiseMsg('Please give the webserver write permissions to the modules directory',
                false, SGL_MESSAGE_WARNING);
            return false;
        }

        $modName = strtolower($input->createModule->moduleName);
        $mgrName = ucfirst($input->createModule->managerName);
        $aActions = '';

        //  strip final 'mgr' if necessary
        if (preg_match('/mgr$/i', $mgrName)) {
            $origMgrName = $mgrName;
            $mgrName = preg_replace("/mgr/i", '', $mgrName);
        }
        //  set mod/mgr details
        $output->moduleName = $modName;

        $output->managerName = $mgrName;
        $mgrLongName = (isset($origMgrName))
            ? $origMgrName
            : ucfirst($input->createModule->managerName) . 'Mgr';

        //  build template name
        $firstLetter = $mgrLongName{0};
        $restOfWord = substr($mgrLongName, 1);
        $templatePrefix = strtolower($firstLetter).$restOfWord;
        $output->templatePrefix = $templatePrefix;

        //  set author details
        require_once 'DB/DataObject.php';
        $user = DB_DataObject::factory($this->conf['table']['user']);
        $user->get(SGL_Session::getUid());
        $output->authorName = $user->first_name . ' ' . $user->last_name;
        $output->authorEmail = $user->email;

        //  insert module in module table if it's not there
        $ok = $this->_addModule($modName, $mgrLongName);

        //  build methods
        list($methods, $aActions) = $this->_buildMethods($input);
        $output->methods = $methods;
        $output->aActionMapping = $aActions;

        $mgrTemplate = $this->_buildManager($output);

        //  setup directories
        $aDirectories['module']     = SGL_MOD_DIR . '/' . $output->moduleName;
        $aDirectories['classes']    = $aDirectories['module'] . '/classes';
        $aDirectories['lang']       = $aDirectories['module'] . '/lang';
        $aDirectories['templates']  = $aDirectories['module'] . '/templates';

        $ok = $this->_createDirectories($aDirectories);

        //  write new manager to appropriate module
        $targetMgrName = $aDirectories['classes'] . '/' . $output->managerName . 'Mgr.php';
        $success = file_put_contents($targetMgrName, $mgrTemplate);

        //  attempt to get apache user to set 'other' bit as writable, so
        //  you can edit this file
        @chmod($targetMgrName, 0666);

        if (isset($input->createModule->createIniFile)){
            $ok = $this->_createModuleConfig($aDirectories, $mgrLongName);
        }
        //  create language files
        if (isset($input->createModule->createLangFiles)){
            $ok = $this->_createLangFiles($aDirectories);
        }
        //  create templates
        if (isset($input->createModule->createTemplates)){
            $ok = $this->_createTemplates($aDirectories, $aTemplates);
        }

        $shortTags = ini_get('short_open_tag');
        $append = empty($shortTags)
            ? ' However, you currently need to set "short_open_tag" to On for the templates to generate correctly.'
            : '';

        if (!$success) {
            SGL::raiseError('There was a problem creating the files',
                SGL_ERROR_FILEUNWRITABLE);
        } else {
            SGL::raiseMsg('Module files successfully created' . $append, false, SGL_MESSAGE_INFO);
        }
    }

    function _addModule($modName, $mgrLongName)
    {
        $module = $this->da->getModuleById();
        $module->whereAdd("name = '$modName'");
        if (!$module->find()) {
            $module->is_configurable    = true;
            $module->name               = $modName;
            $module->title              = $mgrLongName;
            $module->description        = "Generated by ModuleGenerationMgr";
            $module->admin_uri          = $modName . '/' . $modName;
            $module->icon               = 'default.png';

            if (!$this->da->addModule($module)) {
                SGL::raiseError('There was a problem inserting the record in the module table',
                    SGL_ERROR_NOAFFECTEDROWS);
            }
        }
    }

    function _createTemplates($aDirectories, $aTemplates)
    {
        foreach ($aTemplates as $template) {
            $fileName = $aDirectories['templates'].'/'.$templatePrefix.$template;
            $fileTemplate = 'Demo Template: '.$templatePrefix.$template;
            $success = file_put_contents($fileName, $fileTemplate);
            @chmod($fileName, 0666);
        }
    }

    function _createLangFiles($aDirectories)
    {
        $fileTemplate = "<?php\n\$words=array();\n?>";
        foreach ($GLOBALS['_SGL']['LANGUAGE'] as $language) {
            $fileName = $aDirectories['module'] . '/lang/'.$language[1].'.php';
            $success = file_put_contents($fileName, $fileTemplate);
            @chmod($fileName, 0666);
        }
    }


    function _createModuleConfig($aDirectories, $mgrLongName)
    {
        //  create conf.ini
        $confIniName = $aDirectories['module'] . '/conf.ini';
        $confTemplate = '['.$mgrLongName.']'."\n";
        $confTemplate .= 'requiresAuth    = false';
        $success = file_put_contents($confIniName, $confTemplate);
        @chmod($confIniName, 0666);
        return $success;
    }

    function _createDirectories($aDirectories)
    {
        if (is_writable(SGL_MOD_DIR)) {
            require_once 'System.php';

            foreach ($aDirectories as $directory){
                //  pass path as array to avoid widows space parsing prob
                $success = System::mkDir(array('-p', $directory));
                //  attempt to get apache user to set 'other' bit as writable, so
                //  you can edit this file
                @chmod($directory, 0777);
            }
        } else {
            SGL::raiseError('The modules directory does not appear to be writable, please give the
                webserver permissions to write to it', SGL_ERROR_FILEUNWRITABLE);
            return false;
        }
    }

    function _buildManager($output)
    {
                //  initialise template engine
        require_once 'HTML/Template/Flexy.php';
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        $options = array(
            'templateDir'       => SGL_MOD_DIR . '/default/classes/',
            'compileDir'        => SGL_TMP_DIR,
            'forceCompile'      => 1,
            'filters'           => array('SimpleTags','Mail'),
            'compiler'          => 'Regex',
            'flexyIgnore'       => 0,
            'globals'           => true,
            'globalfunctions'   => true,
        );

        $templ = & new HTML_Template_Flexy();
        $templ->compile('ManagerTemplate.html');
        $data = $templ->bufferedOutputObject($output, array());
        $data = preg_replace("/\&amp;/s", '&', $data);
        $mgrTemplate = "<?php\n" . $data . "\n?>";
        return $mgrTemplate;
    }

    function _buildMethods($input)
    {
        //  array: methodName => array (aActionsmapping string, templateName)
        $aPossibleMethods = array(
            'add'   => array("'add'       => array('add'),",'Add.html'),
            'insert'=> array("'insert'    => array('insert', 'redirectToDefault'),"),
            'edit'  => array("'edit'      => array('edit'), ",'Add.html'),
            'update'=> array("'update'    => array('update', 'redirectToDefault'),"),
            'list'  => array("'list'      => array('list'),",'List.html'),
            'delete'=> array("'delete'    => array('delete', 'redirectToDefault'),"),
        );
        foreach ($aPossibleMethods as $method => $mapping) {

            //  if checked add to aMethods array
            if (isset($input->createModule->$method)) {
                $aMethods[] = $method;
                $aActions[] = $mapping[0];
                isset($mapping[1]) ? $aTemplates[] = $mapping[1] : '';
            }
        }
        $methods = '';
        if (isset($aMethods) && count($aMethods)) {
            foreach ($aMethods as $method) {
                if (isset($input->createModule->$method)) {
                    $methods .= <<< EOF

    function _cmd_$method(&\$input, &\$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

EOF;
                }
            }
        }
        return array($methods, $aActions);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }
}
?>
