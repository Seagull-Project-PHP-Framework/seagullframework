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
// | ModuleGenerationMgr.php                                                   |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: ModuleGenerationMgr.php,v 1.56 2005/05/31 23:34:23 demian Exp $

require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';

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
        $this->da = &DefaultDAO::singleton();
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
                    $aErrors['moduleName'] = 'please enter module name';
                }
                if (empty($input->createModule->managerName)) {
                    $aErrors['managerName'] = 'please enter manager name';
                }
                //  if module exists, check if manager exists
                if (SGL::moduleIsEnabled($input->createModule->moduleName)) {
                    $aManagers = SGL_Util::getAllManagersPerModule(SGL_MOD_DIR .'/'.
                        $input->createModule->moduleName);
                    if (in_array($input->createModule->managerName, $aManagers)) {
                        $aErrors['managerName'] = 'Manager already exists - please choose another manager name';
                    }
                }
                //  check if writable
                if (!is_writable(SGL_MOD_DIR)) {
                    $aErrors['not_writable'] = 'Please give the webserver write permissions to the modules directory';
                }
            }
        }
        if (!empty($this->conf['db']['prefix'])) {
            SGL::raiseMsg('prefixes not supported');
            $this->validated = false;
        } elseif (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please correct the following errors', false);
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
        $modName = strtolower($input->createModule->moduleName);
        $mgrName = ucfirst($input->createModule->managerName);

        //  strip final 'mgr' if necessary
        if (preg_match('/mgr$/i', $mgrName)) {
            $origMgrName = $mgrName;
            $mgrName = preg_replace("/mgr/i", '', $mgrName);
            $mgrName = strtolower($mgrName);
        }
        //  set mod/mgr details
        $output->moduleName = $modName;
        $output->managerName = strtolower($mgrName);
        $output->ManagerName = ucfirst($mgrName);
        $mgrLongName = (isset($origMgrName))
           ? $origMgrName
           : ucfirst($input->createModule->managerName) . 'Mgr';
        $output->managerLongName = $mgrLongName;

        // rebuild dataobject so that a DO class exists for new table
        if (isset($input->createModule->createCRUD)) {
            require_once SGL_CORE_DIR . '/Task/Install.php';
            $res = SGL_Task_CreateDataObjectEntities::run();

            // check if table exists
            $entity = ucfirst($mgrName);
            $res = file_exists(SGL_ENT_DIR . '/' . $entity . '.php');
            if (!$res) {
                $msg =  'Please generate a table (with the same name as your manager entity, eg, "pizza") '.
                        'in the database first.';
                SGL::raiseMsg($msg, false);
                return false;
            }
        }
        //  build template name
        $firstLetter    = $mgrLongName{0};
        $restOfWord     = substr($mgrLongName, 1);
        $templatePrefix = strtolower($firstLetter).$restOfWord;
        $output->templatePrefix = $templatePrefix;

        //  set author details
        require_once 'DB/DataObject.php';
        $user = DB_DataObject::factory($this->conf['table']['user']);
        $user->get(SGL_Session::getUid());
        $output->authorName = $user->first_name . ' ' . $user->last_name;
        $output->authorEmail = $user->email;

        if (!SGL::moduleIsEnabled($modName)) {
            //  insert module in module table if it's not there
            $ok = $this->_addModule($modName, $mgrLongName);
        }
        //  get details of class to generate, at least field list for now.
        if (isset($input->createModule->createCRUD)) {
            $model = DB_DataObject::factory($mgrName);
            $modelFields = $model->table();
            $output->modelFields = $modelFields;
        } else {
            $output->modelFields = array('foo', 'bar');
        }

        // add table to <servername>.conf.php if it doesn't exist
        $c = &SGL_Config::singleton();
        if (!isset($this->conf['table'][$output->managerName])) {
            $c->set('table', array($output->managerName => $output->managerName));
            $ok = $c->save();
        }
        //  build methods
        list($methods, $aActions, $aTemplates) = $this->_buildMethods($input, $output);
        $output->methods = $methods;
        $output->aActionMapping = $aActions;

        $mgrTemplate = $this->_buildManager($output);

        //  setup directories
        $aDirectories['module']     = SGL_MOD_DIR . '/' . $output->moduleName;
        $aDirectories['classes']    = $aDirectories['module'] . '/classes';
        $aDirectories['data']       = $aDirectories['module'] . '/data';
        $aDirectories['lang']       = $aDirectories['module'] . '/lang';
        $aDirectories['templates']  = $aDirectories['module'] . '/templates';
        $ok = $this->_createDirectories($aDirectories);

        //  write new manager to appropriate module
        $targetMgrName = $aDirectories['classes'] . '/' . $output->ManagerName . 'Mgr.php';
        if (file_exists($targetMgrName)) {
            return SGL::raiseError('A manager with that name already exists');
        } else {
            if (is_writable($aDirectories['classes'])) {
                $success = file_put_contents($targetMgrName, $mgrTemplate);
                //  attempt to get apache user to set 'other' bit as writable, so
                //  you can edit this file
                @chmod($targetMgrName, 0666);
            } else {
                return SGL::raiseError('module\'s classes directory not writable');
            }
        }
        //  create module config
        if (isset($input->createModule->createIniFile)) {
            if (!is_file(SGL_MOD_DIR . '/' . $output->moduleName .'/conf.ini')) {
                $ok = $this->_createModuleConfig($aDirectories, $mgrLongName);
            }  else {
                if (is_writable(SGL_MOD_DIR . '/' . $output->moduleName .'/conf.ini')) {
                    $ok = $this->_updateModuleConfig($aDirectories, $mgrLongName);
                } else {
                    return SGL::raiseError('module\'s conf.ini file is not writable');
                }
            }
        }
        //  create language files
        if (isset($input->createModule->createLangFiles)) {
            if (is_dir($aDirectories['lang'])) {
                if (is_writable($aDirectories['lang'])) {
                    if (!is_file(SGL_MOD_DIR . '/' . $output->moduleName .'/lang/english-iso-8859-15.php')) {
                        $ok = $this->_createLangFiles($aDirectories, $output);
                    }
                } else {
                    return SGL::raiseError('module\'s lang directory not writable');
                }
            } else {
                return SGL::raiseError('module\'s lang directory does not appear to exist');
            }
        }
        //  create default data
        $dataFile = SGL_MOD_DIR . '/' . $output->moduleName .'/data/data.default.my.sql';
        if (!is_file($dataFile)) {
            $ok = $this->_createDefaultDataFile($output, $dataFile);
        }
        //  create templates
        if (isset($input->createModule->createTemplates)) {
            if (is_dir($aDirectories['templates'])) {
                if (is_writable($aDirectories['templates'])) {
                    $ok = $this->_createTemplates($aDirectories, $aTemplates, $output);
                } else {
                    return SGL::raiseError('module\'s templates directory not writable');
                }
            } else {
                return SGL::raiseError('module\'s templates directory does not appear to exist');
            }
        }

        //  add to tableAliases
        $tableAliasIniPath = SGL_MOD_DIR . '/' . $output->moduleName  . '/data/tableAliases.ini';
        $addTable = true;

        //  test existing data
        if (file_exists($tableAliasIniPath)) {
            $aData = parse_ini_file($tableAliasIniPath);
            foreach ($aData as $k => $v) {
                if ($k == $output->managerName) {
                    $addTable = false;
                }
            }
        }

        if ($addTable) {
            //  append new entry
            if (is_file($tableAliasIniPath) && !is_writable($tableAliasIniPath)) {
                return SGL::raiseError('tableAlias.ini file not writable');
            } else {
                $this->_createTableAliasFile($tableAliasIniPath, $output->managerName);
            }
        }

        $shortTags = ini_get('short_open_tag');
        $append = empty($shortTags)
           ? ' However, you currently need to set "short_open_tag" to On for the templates to generate correctly.'
           : '';

        if (!$success) {
            SGL::raiseError('There was a problem creating the files',
                SGL_ERROR_FILEUNWRITABLE);
        } else {
            $uri = SGL_BASE_URL . '/' .$this->conf['site']['frontScriptName'] .'/'.
                $modName .'/'.$output->managerName.'/';
            SGL::raiseMsg('Files for the '.
              $modName .
              ' module successfully created. Don\'t forget to modify the generated list and' .
              " edit templates. You can start using the module at <a href='$uri'>$uri</a>" .
              $append, false, SGL_MESSAGE_INFO);
        }
    }

    function _createDefaultDataFile($output, $dataFile)
    {
        $moduleFriendlyName = ucfirst($output->moduleName);
        $data = <<<EOD
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, '$output->moduleName', '$moduleFriendlyName', 'Generated by ModuleGenerationMgr', '', '48/module_default.png', '$output->authorName', NULL, 'NULL', 'NULL');
EOD;
        $success = file_put_contents($dataFile, $data);
        @chmod($dataFile, 0666);
        return $success;
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

    function _createTemplates($aDirectories, $aTemplates, $output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
        );
        // loop through all possible templates and see if they need to be generated
        foreach ($aTemplates as $template) {
            $fileName = $aDirectories['templates'] . '/' . $template;
            $html = '';

            if (strpos($fileName, 'Edit.html') !== false) {
                foreach ($output->modelFields as $field => $type) {
                    //  omit the table key and foreign keys
                    if (substr($field, -3) != '_id') {
                        $replace['%field%'] = $field;
                        //  strip TIMESTAMP indicator if exists
                        if ($type > DB_DATAOBJECT_MYSQLTIMESTAMP) {
                            $type -= DB_DATAOBJECT_MYSQLTIMESTAMP;
                        }
                        //  strip NOT_NULL indicator if exists
                        if ($type > DB_DATAOBJECT_NOTNULL) {
                            $type -= DB_DATAOBJECT_NOTNULL;
                        }
                        switch (true) {
                        case ($type == DB_DATAOBJECT_BLOB + DB_DATAOBJECT_STR):
                        case ($type == DB_DATAOBJECT_TXT + DB_DATAOBJECT_STR):
                            $templateName = 'text';
                            break;
                        case ($type == DB_DATAOBJECT_BOOL):
                        case ($type == DB_DATAOBJECT_DATE):
                        case ($type == DB_DATAOBJECT_STR):
                        case ($type == DB_DATAOBJECT_INT):
                            $templateName = 'string';
                            break;
                        default:
                            $templateName = false;
                        }
                        if ($templateName) {
                            $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/' . $templateName . '.html.tmpl');
                            $html .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate);
                        }
                    }
                }

                $replace['%field_html%'] = $html;
                $fileTemplate = @file_get_contents(SGL_MOD_DIR . '/default/classes/mgrTemplates/edit.html.tmpl');
                if ($fileTemplate) {
                    $fileTemplate = str_replace(array_keys($replace), array_values($replace), $fileTemplate);
                }
            } elseif (strpos($fileName, 'List.html') !== false) {
                $table_header = '';
                $table_body = '';
                foreach ($output->modelFields as $key => $value) {
                    if (strpos($key, '_id') === false) {
                        $table_header .= '<th>' . $key . '</th>';
                        $table_body   .= '<td nowrap>{aValue[' . $key . ']}</td>';
                    } else {
                        //  don't create columns for foreign key fields
                        if ($key == $output->managerName . '_id') {
                            $table_header .= '<th>Select</th>';
                            $table_body .= '<td align="center"><input type="checkbox" name="frmDelete[]" value="{aValue[' . $output->managerName . '_id]}" /></td>';
                        }
                    }
                }
                $replace['%table_header%'] = $table_header;
                $replace['%table_body%'] = $table_body;

                $fileTemplate = @file_get_contents(SGL_MOD_DIR . '/default/classes/mgrTemplates/list.html.tmpl');
                if ($fileTemplate) {
                    $fileTemplate = str_replace(array_keys($replace), array_values($replace), $fileTemplate);
                }
            }
            if (!is_file($fileName)) {
                $success = file_put_contents($fileName, $fileTemplate);
                if (!$success) {
                    return false;
                }
                @chmod($fileName, 0666);
            }
        }
        return true;
    }

    function _createLangFiles($aDirectories, $output)
    {
        $fileTemplate = "<?php\n\$words=array(\n".
            str_pad("\t'Add entry'                                     ",40)."=> 'Add entry',\n".
            str_pad("\t'edit'                                          ",40)."=> 'edit',\n".
            str_pad("\t'Please modify this view to fit the attributes in your table.'",40)."=> 'Please modify this view to fit the attributes in your table.',\n".
            str_pad("\t'".ucfirst($output->moduleName)." :: Add'       ",40)."=> '".ucfirst($output->moduleName)." :: Add',\n".
            str_pad("\t'".ucfirst($output->moduleName)." :: Edit'      ",40)."=> '".ucfirst($output->moduleName)." :: Edit',\n".
            str_pad("\t'".ucfirst($output->moduleName)." :: List'      ",40)."=> '".ucfirst($output->moduleName)." :: List',\n".
            str_pad("\t'".$output->moduleName." delete successfull'    ",40)."=> '".ucfirst($output->moduleName)." delete successfull',\n".
            str_pad("\t'".$output->moduleName." delete NOT successfull'",40)."=> '".ucfirst($output->moduleName)." delete NOT successfull',\n".
            str_pad("\t'".$output->moduleName." insert successfull'    ",40)."=> '".ucfirst($output->moduleName)." insert successfull',\n".
            str_pad("\t'".$output->moduleName." insert NOT successfull'",40)."=> '".ucfirst($output->moduleName)." insert NOT successfull',\n".
            str_pad("\t'".$output->moduleName." update successfull'    ",40)."=> '".ucfirst($output->moduleName)." update successfull',\n".
            str_pad("\t'".$output->moduleName." update NOT successfull'",40)."=> '".ucfirst($output->moduleName)." update NOT successfull',\n".
            "\n)\n?>\n";

        foreach ($GLOBALS['_SGL']['LANGUAGE'] as $language) {
            $fileName = $aDirectories['module'] . '/lang/' . $language[1] . '.php';
            $success  = file_put_contents($fileName, $fileTemplate);
            @chmod($fileName, 0666);
        }
    }

    function _createModuleConfig($aDirectories, $mgrLongName)
    {
        //  create conf.ini
        $confIniName    = $aDirectories['module'] . '/conf.ini';
        $confTemplate   = '['.$mgrLongName.']' . "\n";
        $confTemplate   .= 'requiresAuth     = false' . "\n";
        $confTemplate   .= 'showUntranslated = false';
        $success        = file_put_contents($confIniName, $confTemplate);
        @chmod($confIniName, 0666);
        return $success;
    }

    function _updateModuleConfig($aDirectories, $mgrLongName)
    {
        //  update conf.ini
        require_once SGL_CORE_DIR . '/Config.php';
        $configFile = $aDirectories['module'] . '/conf.ini';
        $c = new SGL_Config();
        $conf = $c->load($configFile);
        $c->replace($conf);
        $c->set($mgrLongName, array('requiresAuth' => false));
        $c->set($mgrLongName, array('showUntranslated' => false));

        //  write configuration to file
        $success = $c->save($configFile);
        return $success;
    }

    function _createTableAliasFile($tableAliasIniPath, $managerName)
    {
        $h = fopen($tableAliasIniPath, 'w+');
        fwrite($h, $managerName . ' = ' . $managerName);
        fclose($h);
        @chmod($tableAliasIniPath, 0666);
    }

    function _createDirectories($aDirectories)
    {
        if (is_writable(SGL_MOD_DIR)) {
            require_once 'System.php';
            foreach ($aDirectories as $directory){
                //  pass path as array to avoid windows space parsing prob
                if (!file_exists($directory)) {
                    $success = System::mkDir(array('-p', $directory));
                    //  attempt to get apache user to set 'other' bit as writable, so
                    //  you can edit this file
                    @chmod($directory, 0777);
                }
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
            'filters'           => array('SimpleTags', 'Mail'),
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

    function _buildMethods($input, $output)
    {
        //  array: methodName => array (aActionsmapping string, templateName)
        $aPossibleMethods = array(
            'add'   => array("'add'       => array('add'),", $output->managerName.'Edit.html'),
            'insert'=> array("'insert'    => array('insert', 'redirectToDefault'),"),
            'edit'  => array("'edit'      => array('edit'), ", $output->managerName.'Edit.html'),
            'update'=> array("'update'    => array('update', 'redirectToDefault'),"),
            'list'  => array("'list'      => array('list'),", $output->managerName.'List.html'),
            'delete'=> array("'delete'    => array('delete', 'redirectToDefault'),"),
        );
        $aActions = array();
        $aTemplates = array();

        if (!array_key_exists('list', $input->createModule)) {
            $input->createModule->list = 1;
        }
        foreach ($aPossibleMethods as $method => $mapping) {
           //  if checked add to aMethods array
            if (isset($input->createModule->$method)) {
                $aMethods[] = $method;
                $aActions[] = $mapping[0];
                isset($mapping[1])
                    ? $aTemplates[] = $mapping[1]
                    : '';
            }
        }
        $methods = '';
        if (isset($aMethods) && count($aMethods)) {
            $replace = array(
                '%moduleName%' => $output->moduleName,
                '%ModuleName%' => ucfirst($output->moduleName),
                '%mgrName%'    => $output->managerName,
                '%MgrName%'    => $output->managerLongName,
                '%field_list%' => implode(', ', array_keys($output->modelFields)),
                '%crud%'       => isset($input->createModule->createCRUD) ? 'true' : 'false',
            );
            foreach ($aMethods as $method) {
                if (isset($input->createModule->$method)) {

                    // try to read method skeleton
                    $file = SGL_MOD_DIR . '/default/classes/mgrTemplates/' . $method . ".tmpl";
                    $method_template = @file_get_contents($file);
                    $methods .= <<< EOF

    function _cmd_$method(&\$input, &\$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

EOF;
                    if ($method_template) {
                        $method_template = str_replace(array_keys($replace), array_values($replace), $method_template);
                        $methods .= $method_template;
                    }
                    $methods .= <<< EOF
    }

EOF;
                }
            }
        }
        return array($methods, $aActions, $aTemplates);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }
}
?>
