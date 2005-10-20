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
// | OrgtypeMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: AJ Tarachanowicz <ajt@localhype.net>                              |
// +---------------------------------------------------------------------------+
// $Id: PreferenceMgr.php,v 1.39 2005/05/17 23:54:53 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
require_once SGL_ENT_DIR . '/Organisation_type.php';
/**
 * Manage Org Types.
 *
 * @package user
 * @author  AJ Tarachanowicz <ajt@localhype.net>
 * @version $Revision: 1.5 $
 * @since   PHP 4.1
 */
class OrgTypeMgr extends SGL_Manager
{
    function OrgTypeMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->module       = 'user';
        $this->pageTitle    = 'OrgType Manager';
        $this->template     = 'orgTypeList.html';
        $this->da           = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'), 
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'), 
            'update'    => array('update', 'redirectToDefault'), 
            'delete'    => array('delete', 'redirectToDefault'), 
            'list'      => array('list'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = 'masterMinimal.html';
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->submit      = $req->get('submitted');
        $input->orgTypes    = (object)$req->get('orgTypes');
        $input->orgTypeId   = ($req->get('frmOrgTypeID')) ? $req->get('frmOrgTypeID') : '';

        if ($input->action == 'update') {
            $input->orgTypeId = $input->orgTypes->organisation_type_id;
        }
                
        $aErrors = array();
        if ($input->submit) {
            if (empty($input->orgTypes->name)) {
                $aErrors['name'] = 'You must enter an organisation type name';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'orgTypeAdd.html';
            $this->validated = false;
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'orgTypeAdd.html';
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $orgType = & new DataObjects_Organisation_type();
        $orgType->setFrom($input->orgTypes);
        $dbh = $orgType->getDatabaseConnection();
        $orgType->organisation_type_id = $dbh->nextId($this->conf['table']['organisation_type']);        
        $success = $orgType->insert();
        if ($success) {
            SGL::raiseMsg('Organisation type saved successfully');
        } else {
            SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
        }        
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'orgTypeAdd.html';
        $output->isEdit = true;
        $orgType = & new DataObjects_Organisation_type();
        $orgType->get($input->orgTypeId);
        $output->orgTypes = $orgType;
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'orgTypeAdd.html';
        $orgType = & new DataObjects_Organisation_type();
        $orgType->get($input->orgTypeId);
        $orgType->setFrom($input->orgTypes);      
        $success = $orgType->update();
        if ($success) {
            SGL::raiseMsg('Organisation type has been updated successfully');
        } else {
            SGL::raiseMsg('No data was updated');
        }
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);        
        $output->orgTypes = $this->da->getOrgTypes();
        $output->addOnLoadEvent("document.getElementById('frmUserMgrChooser').orgs.disabled = true");
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $orgTypeId) {
                $orgTypes = & new DataObjects_Organisation_type();
                $orgTypes->get($orgTypeId);
                $orgTypes->delete();
                unset($orgTypes);
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' . 
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        SGL::raiseMsg('Org type(s) deleted successfully');        
    }
}
?>