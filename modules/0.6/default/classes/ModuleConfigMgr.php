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
// | ModuleConfigMgr.php                                                       |
// +---------------------------------------------------------------------------+
// | Author:    Julien Casanova <julien_casanova@yahoo.fr>                     |
// +---------------------------------------------------------------------------+
// $Id$

require_once 'DB/DataObject.php';
require_once SGL_MOD_DIR . '/default/classes/DefaultDAO.php';

/**
 * Module config manager.
 *
 * @package seagull
 * @subpackage default
 * @author  Julien Casanova <julien_casanova@yahoo.fr>
 * @version $Revision:$
 */
class ModuleConfigMgr extends SGL_Manager
{
    function ModuleConfigMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Module Config Manager';
        $this->template     = 'moduleConfigEdit.html';
        $this->da           = &DefaultDAO::singleton();

        $this->_aActionsMapping =  array(
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault')
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = $this->masterTemplate;
        $input->template        = $this->template;
        $input->module          = (object) $req->get('module');
        $input->moduleNameId    = $req->get('frmModule');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'edit';
        $input->config          = $req->get('config');

        $input->submitted       = $req->get('submitted');

        $aErrors = array();
        if (empty($input->moduleNameId)) {
            $aErrors[] = 'You must select a module to edit';
        } elseif (!SGL::moduleIsEnabled($input->moduleNameId)) {
            $aErrors[] = 'This module is not registered or does not exist';
        } else {
             $input->moduleConfigFile = realpath(SGL_MOD_DIR . '/' . $input->moduleNameId . '/conf.ini');
        }
        //  Validate fields
        if ($input->submitted) {
            $aFields = array(
                'name' => 'Please, specify a name',
                'title' => 'Please, specify a title',
                'description' => 'Please, specify a description'
            );
            if (!empty($input->module)) {
                foreach ($aFields as $field => $errorMsg) {
                    if (empty($input->module->$field)) {
                        $aErrors[$field] = $errorMsg;
                    }
                }
            }
        }

        //  If errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Some errors occured. Please see following message(s)');
            $input->error = $aErrors;
            $input->template = 'moduleConfigEdit.html';
            $this->validated = false;
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'moduleConfigEdit.html';

        // get module to display its properties
        $output->module = $this->da->getModuleByName($input->moduleNameId);

        // then get its config file
        $c = new SGL_Config();
        $config = $c->load($input->moduleConfigFile);

        // Try to identify type of parameters
        $aConfig = array();
        foreach ($config as $section => $aParams) {
            $this->_prepareParamsToEdit($aParams);
            $aConfig[$section] = $aParams;
        }
        $output->config = $aConfig;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!empty($this->conf['tuples']['demoMode'])) {
            SGL::raiseMsg('Module config settings cannot be modified in demo mode',
                false, SGL_MESSAGE_WARNING);
            return false;
        }

        // First update module properties
        $oModule = DB_DataObject::factory($this->conf['table']['module']);
        $oModule->get('name', $input->moduleNameId);
        $oModule->setFrom($input->module);
        $success = $oModule->update();

        if ($success !== false) {
            SGL::raiseMsg('module successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
        // Then update module config parameters
        $c = new SGL_Config();
        $config = $c->load($input->moduleConfigFile);

        $aConfig = array();
        foreach ($input->config as $section => $aParams) {
            $this->_prepareParamsToUpdate($aParams);
            $aConfig[$section] = $aParams;
        }
        //  write configuration to file
        $c->replace($aConfig);
        $ok = $c->save($input->moduleConfigFile);
        if (!is_a($ok, 'PEAR_Error')) {
            SGL::raiseMsg('config info successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem saving your configuration, make sure the conf.ini is writable',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    function _prepareParamsToEdit(&$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        foreach ($aParams as $key => $value) {
            $oParam = new stdClass();
            switch ($key) {

            case 'requiresAuth':
                //  ajax providers accept list of method names
                if ($value != 'true' || $value != 'false' || $value != '1' || $value != '0') {
                    $oParam->type = 'string';
                } else {
                    $oParam->type = 'bool';
                }
                break;
            case 'adminGuiAllowed':
            case 'setHeaders':
            case 'enabled':
            case 'commentsEnabled':
            case 'useAkismet':
            case 'useCaptcha':
            case 'moderationEnabled':
                $oParam->type = 'bool';
                break;
            default:
                $oParam->type = 'string';
            }
            $oParam->value = $value;
            $aParams[$key] = $oParam;
        }
        return $aParams;
    }

    function _prepareParamsToUpdate(&$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        foreach ($aParams as $key => $param) {
            switch ($param['type']) {
            case 'bool':
                $value = ($param['value'] == 1) ? 'true' : 'false';
                break;
            default:
                $value = $param['value'];
            }
            $aParams[$key] = $value;
        }
        return $aParams;
    }

    /**
     * Specific redirect for this Manager.
     *
     * @param object $input
     * @param object $output
     */
    function _cmd_redirectToDefault(&$input, &$output)
    {
        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            $aParams = array(
                'managerName' => 'module'
            );
            SGL_HTTP::redirect($aParams);

        //  else display error with blank template
        } else {
            $output->template = 'error.html';
        }
    }
}
?>
