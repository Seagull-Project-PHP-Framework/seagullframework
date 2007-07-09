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
// | ModuleGenerationMgr.php                                                   |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>,                            |
// |         Nasir Iqbal   <nasir@ictinnovations.com>                          |
// +---------------------------------------------------------------------------+
// $Id: ModuleGenerationMgr.php,v 1.56 2005/05/31 23:34:23 demian Exp $

require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';

/**
 * Provides tools to manage translations and mtce tasks.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>, Nasir Iqbal <nasir@ictinnovations.com>
 */

class ModuleGenerationMgr extends SGL_Manager
{
    function ModuleGenerationMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Maintenance';
        $this->masterTemplate = 'master.html';
        $this->template     = 'moduleGenerator.html';
        $this->da = &DefaultDAO::singleton();
        $this->_aActionsMapping =  array(
            'createModule' => array('createModule', 'redirectToDefault'),
            'list'         => array('list'),
            'list2'        => array('list2'),
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

        if (isset($input->createModule->fieldname)) {
            if (isset($input->createModule->selected)) {
                $this->tableData['selected'] = $input->createModule->selected;
            }
            if (isset($input->createModule->fieldname)) {
                $this->tableData['fieldname'] = $input->createModule->fieldname;
            }
            if (isset($input->createModule->tooltip)) {
                $this->tableData['tooltip'] = $input->createModule->tooltip;
            }
            if (isset($input->createModule->name)) {
                $this->tableData['name'] = $input->createModule->name;
            }
            if (isset($input->createModule->order)) {
                $this->tableData['order'] = $input->createModule->order;
            }
            if (isset($input->createModule->datatype)) {
                $this->tableData['datatype'] = $input->createModule->datatype;
            }
            if (isset($input->createModule->required)) {
                $this->tableData['required'] = $input->createModule->required;
            }
            if (isset($input->createModule->defaultdata)) {
                $this->tableData['defaultdata'] = $input->createModule->defaultdata;
            }
            if (isset($input->createModule->fkname)) {
                $this->tableData['fkname'] = $input->createModule->fkname;
            }
            if (isset($input->createModule->fkoutput)) {
                $this->tableData['fkoutput'] = $input->createModule->fkoutput;
            }
            if (isset($input->createModule->searchable)) {
                $this->tableData['searchable'] = $input->createModule->searchable;
            }
            if (isset($input->createModule->orderby)) {
                $this->tableData['orderby'] = $input->createModule->orderby;
            }
        }

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
        $MgrCaption = ucfirst($input->createModule->managerCaption);
        $MgrCaptionSimple = ucfirst($input->createModule->managerCaptionSimple);
        $idField = $input->createModule->idField;

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
        $output->MgrCaption = ucfirst($MgrCaption);
        $output->MgrCaptionSimple = ucfirst($MgrCaptionSimple);
        $output->idField = $idField;
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
        $output->extradata = $this->getExtraData($output);
        $output->validationdata = $this->getValidation($output);

        $mgrTemplate = $this->_buildManager($output);

        //  setup directories
        $aDirectories['module']     = SGL_MOD_DIR . '/' . $output->moduleName;
        $aDirectories['classes']    = $aDirectories['module'] . '/classes';
        $aDirectories['data']       = $aDirectories['module'] . '/data';
        $aDirectories['lang']       = $aDirectories['module'] . '/lang';
        $aDirectories['templates']  = $aDirectories['module'] . '/templates';
        $aDirectories['www']        = $aDirectories['module'] . '/www';
        $aDirectories['js']         = $aDirectories['module'] . '/www/js';
        $aDirectories['css']        = $aDirectories['module'] . '/www/css';
        // remove following two line -- after TODO -- Auto Install
        $aDirectories['publicwww']  = SGL_WEB_ROOT . '/' . $output->moduleName;
        $aDirectories['publiccss']  = SGL_WEB_ROOT . '/' . $output->moduleName . '/css';
        $aDirectories['source']     = $aDirectories['module'] . '/source'; // for rad storage
        $ok = $this->_createDirectories($aDirectories);
        require_once SGL_CORE_DIR . '/File.php';
        $success = @copy(SGL_MOD_DIR . '/default/classes/mgrTemplates/www/css/rad.css', $aDirectories['module'] . '/www/css/rad.css');
        // remove following line -- after TODO -- Auto Install
        $success = @copy(SGL_MOD_DIR . '/default/classes/mgrTemplates/www/css/rad.css', SGL_WEB_ROOT . '/' . $output->moduleName . '/css/rad.css');

        // save source
        $inputSrcPath = SGL_MOD_DIR . '/' . $output->moduleName  . '/source/src_' . $output->managerName . '.php';
            $tempdata = var_export($input->createModule,true);

            $replace['stdClass::__set_state(array'] ='(array';
            $finaldata = str_replace(array_keys($replace), array_values($replace), $tempdata);
        //  Add new entry
        $h = fopen($inputSrcPath, 'w');
        fwrite($h, '<?php ' . "\n" . '$savedData = ' . $finaldata . "\n" . ' ?>' . "\n");
        fclose($h);
        @chmod($$inputSrcPath, 0666);

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
            //if (!is_file(SGL_MOD_DIR . '/' . $output->moduleName .'/conf.ini')) {
                $ok = $this->_createModuleConfig($aDirectories, $mgrLongName, $output);
            //}  else {
                //if (is_writable(SGL_MOD_DIR . '/' . $output->moduleName .'/conf.ini')) {
                    //$ok = $this->_updateModuleConfig($aDirectories, $mgrLongName);
                //} else {
                //    return SGL::raiseError('module\'s conf.ini file is not writable');
                //}
            //} TODO
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

        // add to tableAliases
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
            $module->description        = "Generated by Seagull-RAD Form Genrator";
            $module->admin_uri          = $modName . '/' . $modName;
            $module->icon               = $modName . '.png';

            if (!$this->da->addModule($module)) {
                SGL::raiseError('There was a problem inserting the record in the module table',
                    SGL_ERROR_NOAFFECTEDROWS);
            }
            require_once SGL_CORE_DIR . '/File.php';
            // TODO -- Auto Install www folder
            // $success = SGL_File::copyDir(SGL_MOD_DIR . '/' . $modName . '/www', SGL_WEB_ROOT . '/' . $modName);
        
        }
    }

    function _createTemplates($aDirectories, $aTemplates, $output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%ManagerName%'=> $output->ManagerName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%'=> $output->MgrCaptionSimple,
           '%idField%'    => $output->idField,
        );

        // loop through all possible templates and see if they need to be generated
        foreach ($aTemplates as $template){
            $fileName = $aDirectories['templates'] . '/admin_' . $template;
            $html = '';

            $tabledata = $this->tableData;
            $tableorder = $tabledata['order'];
            asort($tableorder);

            if (strpos($fileName, 'Edit.html') !== false) {
                foreach ($tableorder as $type => $field) {
                    $field = $type;
                    //  omit the table key and foreign keys
                    //if (substr($field, -3) != '_id') {
                    $replace['%field%'] = $field;
                    if (substr($field, -3) == '_id') {
                        $fkdbfromfield = str_replace('_id','',$field);
                    } else {
                        $fkdbfromfield = $field;
                    }

                    switch (true) {
                        case ($tabledata['datatype'][$field]==0):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==1):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==2):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==3):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==4):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==5):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==6):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==7):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==8):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==9):
                            $templateName = 'text';
                            break;
                        case ($tabledata['datatype'][$field]==10):
                            $templateName = 'yesno';
                            break;
                        case ($tabledata['datatype'][$field]==11):
                            $templateName = 'choice';
                            $replace['%fktable%'] = 'a' . ucfirst($fkdbfromfield);
                            $replace['%fkfield%'] = $tabledata['fkname'][$field];
                            break;
                        case ($tabledata['datatype'][$field]==12):
                            $templateName = 'select';
                            $replace['%fktable%'] = 'a' . ucfirst($fkdbfromfield);
                            $replace['%fkfield%'] = $tabledata['fkname'][$field];
                            break;
                        case ($tabledata['datatype'][$field]==13):
                            $templateName = 'multiselect';
                            $replace['%fktable%'] = 'a' . ucfirst($fkdbfromfield);
                            $replace['%fkfield%'] = $tabledata['fkname'][$field];
                            break;
                        case ($tabledata['datatype'][$field]==14):
                            $templateName = 'datepicker';
                            break;
                        case ($tabledata['datatype'][$field]==20):
                            $templateName = 'panelseprator';
                            $replace['%panelseprator%'] = $tabledata['name'][$field];;
                            break;
                        default:
                            $templateName = false;
                        }
                        if ($templateName) {
                            if (isset($tabledata['name'][$field])) {
                                $replace['%caption%'] = $tabledata['name'][$field];
                            }
                            if (isset($tabledata['required'][$field]) && $tabledata['required'][$field]=='1') {
                                $replace['%required%']= @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/required.html.tmpl');
                            } else {
                                $replace['%required%'] = '';
                            }

                            $retvaltooltip = trim($tabledata['tooltip'][$field]);
                            if ($retvaltooltip=='') {
                                $replace['%tooltipspan%'] = '';
                                $replace['%tooltipclass%'] = '';
                            } else {
                                $replace['%tooltipclass%'] = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/tooltipclass.html.tmpl');
                                $replace['%tooltip%'] = $retvaltooltip;
                                $retvalfile = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/tooltip.html.tmpl');
                                $replace['%tooltipspan%'] = str_replace(array_keys($replace), array_values($replace), $retvalfile);
                            }

                             $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/widget/' . $templateName . '.html.tmpl');

                            $html .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate);
                        //}
                    }
                }
                if ($output->checkrequiredyesno) {
                    $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/requiredhelp.html.tmpl');
                    $html .= $fieldTemplate;
                }
                $replace['%field_html%'] = $html;
                $fileTemplate = @file_get_contents(SGL_MOD_DIR . '/default/classes/mgrTemplates/edit.html.tmpl');
                if ($fileTemplate) {
                    $fileTemplate = str_replace(array_keys($replace), array_values($replace), $fileTemplate);
                }
            } elseif (strpos($fileName, 'Search.html') !== false) {
                foreach ($tableorder as $type => $field) {
                  $field = $type;
                  if (isset($tabledata['searchable'][$field]) && $tabledata['searchable'][$field]==1) {
                    $replace['%field%'] = $field;
                    if (substr($field, -3) == '_id') {
                        $fkdbfromfield = str_replace('_id','',$field);
                    } else {
                        $fkdbfromfield = $field;
                    }

                    switch (true) {
                        case ($tabledata['datatype'][$field]==0):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==1):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==2):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==3):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==4):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==5):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==6):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==7):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==8):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==9):
                            $templateName = 'string';
                            break;
                        case ($tabledata['datatype'][$field]==10):
                            $templateName = 'yesno';
                            break;
                        case ($tabledata['datatype'][$field]==11):
                            $templateName = 'choice';
                            break;
                        case ($tabledata['datatype'][$field]==12):
                            $templateName = 'select';
                            $replace['%fktable%'] = 'a' . ucfirst($fkdbfromfield);
                            $replace['%fkfield%'] = $tabledata['fkname'][$field];
                            break;
                        case ($tabledata['datatype'][$field]==13):
                            $templateName = 'multiselect';
                            $replace['%fktable%'] = 'a' . ucfirst($fkdbfromfield);
                            $replace['%fkfield%'] = $tabledata['fkname'][$field];
                            break;
                        case ($tabledata['datatype'][$field]==14):
                            $templateName = 'searchdatepicker';
                            break;
                        default:
                            $templateName = false;
                        }
                        if ($templateName) {
                            if (isset($tabledata['name'][$field])) {
                                $replace['%caption%'] = $tabledata['name'][$field];
                            }
                            $replace['%required%'] = '';
                            $replace['%tooltipspan%'] = '';
                            $replace['%tooltipclass%'] = '';
                            $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/widget/' . $templateName . '.html.tmpl');

                            $html .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate);
                      }
                    }
                }
                $replace['%field_html%'] = $html;
                $fileTemplate = @file_get_contents(SGL_MOD_DIR . '/default/classes/mgrTemplates/search.html.tmpl');
                if ($fileTemplate) {
                    $fileTemplate = str_replace(array_keys($replace), array_values($replace), $fileTemplate);
                }
            } elseif (strpos($fileName, 'List.html') !== false) {
                $table_header = '';
                $table_body = '';
                $countI = 0;
                foreach ($tableorder as $key => $value) {
                    $templateName = false;
                    if ((strpos($key, '_id') === false || $tabledata['datatype'][$key]==12) && isset($tabledata['selected'][$key]) && $tabledata['selected'][$key] == 1) {
                        $templateName = 'table';
                        $replace['%tableheader%'] =  $tabledata['name'][$key];
                        $replace['%tablebody%'] =  $key;
                    } else {
                        //  don't create columns for foreign key fields
                        if ($key == $output->managerName . '_id') {
                            $templateName = 'tablefirst';
                            $replace['%tableheader%'] =  'Select';
                            $replace['%tablebody%'] =  $key;
                        }
                    }
                    if ($templateName) {
                        $countI++;

                        if (isset($tabledata['orderby'][$key]) && $tabledata['orderby'][$key]==1) {
                           $retvalfile = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/' . $templateName . 'headersortby.html.tmpl');
                        } else {
                           $retvalfile = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/' . $templateName . 'header.html.tmpl');
                        }
                        if ($templateName=='tablefirst') {
                            $table_header = str_replace(array_keys($replace), array_values($replace), $retvalfile) . $table_header;
                        } else {
                            $table_header .= str_replace(array_keys($replace), array_values($replace), $retvalfile);
                        }
                        $retvalfile = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/ui/' . $templateName . 'body.html.tmpl');
                        if ($templateName=='tablefirst') {
                            $table_body = str_replace(array_keys($replace), array_values($replace), $retvalfile) . $table_body;
                        } else {
                            $table_body .= str_replace(array_keys($replace), array_values($replace), $retvalfile);
                        }
                    }
                }
                $replace['%noofcolumns%'] = $countI + 1;
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
    }

    function _createLangFiles($aDirectories, $output)
    {
        $fileTemplate = "<?php\n\$words=array(\n".
            str_pad("\t'Add entry'                                     ",40)."=> 'Add entry',\n".
            str_pad("\t'edit'                                          ",40)."=> 'edit',\n".
            str_pad("\t'Please modify this view to fit the attributes in your table.'",40)."=> 'Please modify this view to fit the attributes in your table.',\n".
            str_pad("\t'".ucfirst($output->MgrCaption)." :: Add'       ",40)."=> '".ucfirst($output->MgrCaption)." :: Add',\n".
            str_pad("\t'".ucfirst($output->MgrCaption)." :: Edit'      ",40)."=> '".ucfirst($output->MgrCaption)." :: Edit',\n".
            str_pad("\t'".ucfirst($output->MgrCaption)." :: List'      ",40)."=> '".ucfirst($output->MgrCaption)." :: List',\n".
            str_pad("\t'".ucfirst($output->MgrCaption)." :: Search'    ",40)."=> '".ucfirst($output->MgrCaption)." :: Search',\n".
            str_pad("\t'".$output->MgrCaptionSimple." delete successfull'    ",40)."=> '".ucfirst($output->MgrCaptionSimple)." delete successfull',\n".
            str_pad("\t'".$output->MgrCaptionSimple." delete NOT successfull'",40)."=> '".ucfirst($output->MgrCaptionSimple)." delete NOT successfull',\n".
            str_pad("\t'".$output->MgrCaptionSimple." insert successfull'    ",40)."=> '".ucfirst($output->MgrCaptionSimple)." insert successfull',\n".
            str_pad("\t'".$output->MgrCaptionSimple." insert NOT successfull'",40)."=> '".ucfirst($output->MgrCaptionSimple)." insert NOT successfull',\n".
            str_pad("\t'".$output->MgrCaptionSimple." update successfull'    ",40)."=> '".ucfirst($output->MgrCaptionSimple)." update successfull',\n".
            str_pad("\t'".$output->MgrCaptionSimple." update NOT successfull'",40)."=> '".ucfirst($output->MgrCaptionSimple)." update NOT successfull',\n".
            "\n)\n?>\n";

        foreach ($GLOBALS['_SGL']['LANGUAGE'] as $language) {
            $fileName = $aDirectories['module'] . '/lang/' . $language[1] . '.php';
            $success  = file_put_contents($fileName, $fileTemplate);
            @chmod($fileName, 0666);
        }
    }

    function _createModuleConfig($aDirectories, $mgrLongName, $output)
    {
        //  create conf.ini
        $confIniName    = $aDirectories['module'] . '/conf.ini';
        $tabledata = $this->tableData;
        $tableorder = $tabledata['order'];

        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%mgrLongName%'=> $output->managerLongName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
        );

        $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/conf/moduleconf.tmpl');
        $confTemplate = str_replace(array_keys($replace), array_values($replace), $fieldTemplate);

        foreach ($tableorder as $type => $field) {
                if ($tabledata['defaultdata'][$type]!='') {
                        $confTemplate .= $tabledata['fieldname'][$type] . ' = ' . $tabledata['defaultdata'][$type] . "\n";
                }
        }
        $addTable = true;
        if (file_exists($confIniName)) {
            $aData = parse_ini_file($confIniName,true);
            if (isset($aData[$mgrLongName])) {
                $addTable = false;
            }
        }
        if ($addTable) {
            //  append new entry
            $h = fopen($confIniName, 'a+');
            fwrite($h, $confTemplate . "\n");
            fclose($h);
            @chmod($confIniName, 0666);
        }

        $success = true;
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
            'search'=> array("'search'    => array('search'),", $output->managerName.'Search.html'),
        );
        $aActions = array();
        $aTemplates = array();

        if (!array_key_exists('list', $input->createModule)) {
            $input->createModule->list = 1;
        }
        if (!array_key_exists('search', $input->createModule)) {
            $input->createModule->search = 1;
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
                '%MgrCaption%' => $output->MgrCaption,
                '%ManagerName%'=> $output->MgrCaption,
                '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
                //'%field_list%' => implode(', ', array_keys($output->modelFields)),
                '%crud%'       => $input->createModule->createCRUD ? 'true' : 'false',
            );
            foreach ($aMethods as $method) {
                if (isset($input->createModule->$method)) {

                    // try to read method skeleton
                    $file = SGL_MOD_DIR . '/default/classes/mgrTemplates/' . $method . ".tmpl";
                    $method_template = @file_get_contents($file);
                    if ($method=='add') {
                        $replace['%defaultdata%'] = $this->getDefaultData($output);
                    } elseif ($method=='list') {
                        list($replace['%field_list%'],$replace['%table_list%'],$replace['%orderbyfields%'])=$this->getJoinSelect($output);
                    } elseif ($method=='search') {
                        $replace['%defaultdata%'] = $this->getExtraData($output);
                        $replace['%searchfields%'] = $this->getSearchData($output);
                    }
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

    function getJoinSelect(&$output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%mgrLongName%'=> $output->managerLongName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
        );

        $tabledata = $this->tableData;
        $tableorder = $tabledata['order'];
        asort($tableorder);
        $tempfield = array();

        $j = 0;
        $field = 0;
        $type = 0;
        $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/data/join.tmpl');
        $tablename = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/data/tablename.tmpl');
        $joinselect = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/data/joinselect.tmpl');
        $select = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/data/select.tmpl');

        //table primary key
        $replace['%table%'] = $output->managerName;
        $replace['%unknowntable%'] = str_replace(array_keys($replace), array_values($replace), $tablename);

        $replace['%table%'] = trim($replace['%unknowntable%']);
        $replace['%key%'] = $output->managerName . '_id';
        $tempfield[$field] = str_replace(array_keys($replace), array_values($replace), $select);
        if (isset($tabledata['orderby'][$output->managerName . '_id']) && $tabledata['orderby'][$output->managerName . '_id']==1) {
            $sortfields[$field] = "'" . $output->managerName . "_id'";
        }
        // and all other
        foreach ($tableorder as $type => $field) {
            if (isset($field)) {
                $field = $type;
            }
            if (isset($tabledata['selected'][$field]) && $tabledata['selected'][$field]==1) {
                if ($tabledata['datatype'][$field]==12) {
                    $j++;
                    if (substr($field, -3) == '_id') {
                        $replace['%table%'] = str_replace("_id", '', $field);
                    }
                    $replace['%table1%'] = trim(str_replace(array_keys($replace), array_values($replace), $tablename));
                    $replace['%key1%'] = $tabledata['fkname'][$field];

                    $replace['%table%'] = $output->managerName;
                    $replace['%table2%'] = trim(str_replace(array_keys($replace), array_values($replace), $tablename));
                    $replace['%key2%'] = $field;

                    $temp = str_replace(array_keys($replace), array_values($replace), $fieldTemplate);
                    $replace['%unknowntable%'] = $temp;

                    $replace['%key%'] = $replace['%key1%'];
                    $replace['%table%'] = $replace['%table1%'];
                    $tempfield[$field] = str_replace(array_keys($replace), array_values($replace), $joinselect);

                } else {
                    $replace['%table%'] = $output->managerName;
                    $temp = str_replace(array_keys($replace), array_values($replace), $tablename);
                    $replace['%table%'] = trim($temp);
                    $replace['%key%'] = $field;
                    $tempfield[$field] = str_replace(array_keys($replace), array_values($replace), $select);
                }
                if (isset($tabledata['orderby'][$field]) && $tabledata['orderby'][$field]==1) {
                    $sortfields[$field] = "'" . $field . "'";
                }
            }
        }
        $ftable = $replace['%unknowntable%'];
        $ffield = implode(', ', array_values($tempfield));
        $sfield = implode(', ', array_values($sortfields));
        return array($ffield, $ftable, $sfield);
    }

    function getDefaultData(&$output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%mgrLongName%'=> $output->managerLongName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
        );

        $tabledata = $this->tableData;
        $tableorder = $tabledata['order'];

        asort($tableorder);
        $temp = '';
        foreach ($tableorder as $type => $field) {
            if (!empty($tabledata['defaultdata'][$type])) {
                $replace['%fieldname%'] = $tabledata['fieldname'][$type];
                $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/data/defaultdata.tmpl');
                $temp .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate) . "\n";
            }
        }
        return $temp;
    }

    function getExtraData(&$output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
        );

        $tabledata = $this->tableData;
        $tableorder = $tabledata['order'];

        asort($tableorder);
        $temp = '';
        foreach ($tableorder as $type => $field) {
            $field = $type;
            //  omit the table key and foreign keys
            //if (substr($field, -3) != '_id') {
                $replace['%field%'] = $field;

                switch (true) {

                    case ($tabledata['datatype'][$field]==12):
                        $templateName = 'selectfk';
                        if (substr($field, -3) == '_id') {
                            $replace['%fktable%'] = str_replace("_id", '', $field);
                            $replace['%aFktable%'] = 'a' . ucfirst(str_replace("_id", '', $field));
                        } else {
                            $replace['%fktable%'] = $field;
                            $replace['%aFktable%'] = 'a' . ucfirst($field);
                        }
                        $replace['%fkfield%'] = $tabledata['fkname'][$field];
                        break;
                    case ($tabledata['datatype'][$field]==13):
                        $templateName = 'selectfk';
                        if (substr($field, -3) == '_id') {
                            $replace['%fktable%'] = str_replace("_id", '', $field);
                            $replace['%aFktable%'] = 'a' . ucfirst(str_replace("_id", '', $field));
                        } else {
                            $replace['%fktable%'] = $field;
                            $replace['%aFktable%'] = 'a' . ucfirst($field);
                        }
                        $replace['%fkfield%'] = $tabledata['fkname'][$field];
                        break;
                    default:
                        $templateName = false;
                }
                if ($templateName) {
                    $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/data/' . $templateName . '.tmpl');
                    $temp .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate) . "\n";
                }
            //}
        }
        return $temp;
    }

    function getValidation(&$output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
        );

        $tabledata = $this->tableData;
        $tableorder = $tabledata['order'];

        asort($tableorder);
        $output->checkrequiredyesno = false;
        $temp = '';
        foreach ($tableorder as $type => $field) {
            $field = $type;
            //  omit the table key and foreign keys
            if (substr($field, -3) != '_id') {
                $replace['%field%'] = $field;

                switch (true) {

                    case ($tabledata['datatype'][$field]==0):
                        $templateName = 'texts';
                        break;
                    case ($tabledata['datatype'][$field]==1):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==2):
                        $templateName = 'currency';
                        break;
                    case ($tabledata['datatype'][$field]==3):
                        $templateName = 'currencyp';
                        break;
                    case ($tabledata['datatype'][$field]==4):
                        $templateName = 'number';
                        break;
                    case ($tabledata['datatype'][$field]==5):
                        $templateName = 'numberp';
                        break;
                    case ($tabledata['datatype'][$field]==14):
                        $templateName = 'datepicker';
                        break;
                    default:
                        $templateName = false;
                }
                if ($templateName) {
                    $replace['%caption%'] = $tabledata['name'][$field];
                    $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/validation/' . $templateName . '.tmpl');
                    $replace['%validation%'] = str_replace(array_keys($replace), array_values($replace), $fieldTemplate);
                    if (isset($tabledata['required'][$field]) && $tabledata['required'][$field]==1) {
                        $output->checkrequiredyesno = true;
                        $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/validation/required.tmpl');
                        $temp .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate) . "\n";
                    } else {
                        $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/validation/notrequired.tmpl');
                        $temp .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate) . "\n";
                    }
                }
            }
        }
        return $temp;
    }

    function getSearchData(&$output)
    {
        $replace = array(
           '%moduleName%' => $output->moduleName,
           '%ModuleName%' => ucfirst($output->moduleName),
           '%mgrName%'    => $output->managerName,
           '%MgrName%'    => $output->ManagerName,
           '%MgrCaption%' => $output->MgrCaption,
           '%MgrCaptionSimple%' => $output->MgrCaptionSimple,
        );

        $tabledata = $this->tableData;
        $tableorder = $tabledata['order'];

        asort($tableorder);
        $output->checkrequiredyesno = false;
        $temp = '';

        $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/data/join.tmpl');
        $tablename = @file_get_contents(SGL_MOD_DIR .
                         '/default/classes/mgrTemplates/data/tablename.tmpl');

        foreach ($tableorder as $type => $field) {
            $field = $type;
            //  omit the table key and foreign keys
            if (isset($tabledata['searchable'][$field]) && $tabledata['searchable'][$field]==1) {
                $replace['%field%'] = $field;

                if (substr($field, -3) == '_id') {
                    $replace['%table%'] = str_replace("_id", '', $field);
                } else {
                    $replace['%table%'] = $output->managerName;
                }

                switch (true) {
                    case ($tabledata['datatype'][$field]==0):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==1):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==2):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==3):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==4):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==5):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==6):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==7):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==8):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==9):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==10):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==11):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==12):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==13):
                        $templateName = 'text';
                        break;
                    case ($tabledata['datatype'][$field]==14):
                        $templateName = 'datepicker';
                        break;
                    default:
                        $templateName = false;
                    }
                if ($templateName) {
                    $temptable = str_replace(array_keys($replace), array_values($replace), $tablename);
                    $replace['%table%'] = trim($temptable);
                    $replace['%field%'] = $tabledata['fieldname'][$field];
                    $fieldTemplate = @file_get_contents(SGL_MOD_DIR .
                                '/default/classes/mgrTemplates/search/' . $templateName . '.tmpl');
                    $temp .= str_replace(array_keys($replace), array_values($replace), $fieldTemplate);
                }
            }
        }
        return $temp;
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_list2(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->createModule = $input->createModule;
        $output->createModule->managerCaption = $input->createModule->managerName;
        //$output->createModule->managerCaptionSimple = $input->createModule->managerNameSimple;

        $output->template  = 'tableFieldsList.html';
        $output->pageTitle = 'TestradMgr :: List';

        $aDataType = array(
        0    =>    'Text (any type)',
        1    =>    'Text Name',
        2    =>    'Currency',
        3    =>    'Currency Only Positive',
        4    =>    'Numbers',
        5    =>    'Numbers Only Positive',
        6    =>    'E-Mail',
        7    =>    'Web Site Address',
        8    =>    'File Path',
        9    =>    'Text Area',
        10   =>    'Yes No',
        11   =>    'Choice',
        12   =>    'Select',
        13   =>    'Multi Select',
        14   =>    'Date Picker',
        20   =>    'Span',
        30   =>    'Not Applied',
        );
        $output->aDataType = $aDataType;


        $inputSrcPath = SGL_MOD_DIR . '/' . $input->createModule->moduleName  . '/source/src_' . $input->createModule->managerName . '.php';
        if (file_exists($inputSrcPath)) {
            // add table to conf.php if it doesn't exist
            /* $c = &SGL_Config::singleton($autoLoad = false);
            $conf = $c->load($inputSrcPath);
            $pageData = $conf; */
            include_once($inputSrcPath);
            $pageData = $savedData;
            foreach ($pageData as $key => $value) {
                if (!is_array($value)) {
                    $output->createModule->$key = $value;
                }
            }
            $temparray = $pageData['fieldname'];
            foreach  ($temparray as $key => $value) {
                if (isset($pageData['selected'][$key])) {
                    $output->aPagedData['data'][$key]['selected'] = $pageData['selected'][$key];
                }
                if (isset($pageData['order'][$key])) {
                    $output->aPagedData['data'][$key]['order'] = $pageData['order'][$key];
                }
                if (isset($pageData['fieldname'][$key])) {
                    $output->aPagedData['data'][$key]['fieldname'] = $pageData['fieldname'][$key];
                }
                if (isset($pageData['name'][$key])) {
                    $output->aPagedData['data'][$key]['name'] = $pageData['name'][$key];
                }
                if (isset($pageData['fkoutput'][$key])) {
                    $output->aPagedData['data'][$key]['fkoutput'] = $pageData['fkoutput'][$key];
                }
                if (isset($pageData['searchable'][$key])) {
                    $output->aPagedData['data'][$key]['searchable'] = $pageData['searchable'][$key];
                }
                if (isset($pageData['orderby'][$key])) {
                    $output->aPagedData['data'][$key]['orderby'] = $pageData['orderby'][$key];
                }
                if (isset($pageData['fkname'][$key])) {
                    $output->aPagedData['data'][$key]['fkname'] = $pageData['fkname'][$key];
                }
                if (isset($pageData['datatype'][$key])) {
                    $output->aPagedData['data'][$key]['datatype'] = $pageData['datatype'][$key];
                }
                if (isset($pageData['required'][$key])) {
                    $output->aPagedData['data'][$key]['required'] = $pageData['required'][$key];
                }
                if (isset($pageData['defaultdata'][$key])) {
                    $output->aPagedData['data'][$key]['defaultdata'] = $pageData['defaultdata'][$key];
                }
                if (isset($pageData['tooltip'][$key])) {
                    $output->aPagedData['data'][$key]['tooltip'] = $pageData['tooltip'][$key];
                }
            }

        } else {

            $mgrName = ucfirst($input->createModule->managerName);
            //  strip final 'mgr' if necessary
            if (preg_match('/mgr$/i', $mgrName)) {
                $origMgrName = $mgrName;
                $mgrName = preg_replace("/mgr/i", '', $mgrName);
            $mgrName = strtolower($mgrName);
            }

            //  get details of class to generate, at least field list for now.
            if (isset($input->createModule->createCRUD)) {
                require_once 'DB/DataObject.php';
                $model = DB_DataObject::factory($mgrName);
                $modelFields = $model->table();
            } else {
                $modelFields = array('foo', 'bar');
            }
            $j = 0;
            foreach ($modelFields as $field => $type) {
                //  omit the table key and foreign keys
                if (substr($field, -3) != '_id') {
                    $tableFiled['selected'] = 1;
                } else {
                    $tableFiled['selected'] = 0;
                }
                $tableFiled['order'] = $j;
                $tableFiled['fieldname'] = $field;
                $tableFiled['name'] = $field;
                $tableFiled['fkoutput'] = 0;
                $tableFiled['searchable'] = 0;
                $tableFiled['orderby'] = 0;
                $tableFiled['fkname'] = '';
                $tableFiled['datatype'] = 0;
                $tableFiled['required'] = 0;
                $tableFiled['defaultdata'] = '';
                $tableFiled['tooltip'] = '';

                $output->aPagedData['data'][] = $tableFiled;
                $j++;
                // }
            }
            $tableFiled['selected'] = 0;
            $tableFiled['order'] = -1;
            $tableFiled['name'] = 'Panel';
            $tableFiled['fkoutput'] = 0;
            $tableFiled['searchable'] = 0;
            $tableFiled['orderby'] = 0;
            $tableFiled['fkname'] = '';
            $tableFiled['datatype'] = 30;
            $tableFiled['required'] = 0;
            $tableFiled['defaultdata'] = '';
            $tableFiled['tooltip'] = '';
            $tableFiled['fieldname'] = '__panel1';
            $output->aPagedData['data'][] = $tableFiled;
            $tableFiled['fieldname'] = '__panel2';
            $output->aPagedData['data'][] = $tableFiled;
            $tableFiled['fieldname'] = '__panel3';
            $output->aPagedData['data'][] = $tableFiled;
            $tableFiled['fieldname'] = '__panel4';
            $output->aPagedData['data'][] = $tableFiled;
            $tableFiled['fieldname'] = '__panel5';
            $output->aPagedData['data'][] = $tableFiled;
        } // end of saved data if
    }
}
?>
