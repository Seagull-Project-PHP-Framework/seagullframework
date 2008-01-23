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
// | OrgtypeMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: AJ Tarachanowicz <ajt@localhype.net>                              |
// +---------------------------------------------------------------------------+
// $Id: PreferenceMgr.php,v 1.39 2005/05/17 23:54:53 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once 'DB/DataObject.php';

/**
 * Manage Org Types.
 *
 * @package user
 * @author  AJ Tarachanowicz <ajt@localhype.net>
 * @version $Revision: 1.5 $
 */
class OrgTypeMgr extends SGL_Manager
{
    function OrgTypeMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'OrgType Manager';
        $this->template     = 'orgTypeList.html';
        $this->da           = & UserDAO::singleton();

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
        $input->submitted   = $req->get('submitted');
        $input->orgTypes    = (object)$req->get('orgTypes');
        $input->orgTypeId   = ($req->get('frmOrgTypeID')) ? $req->get('frmOrgTypeID') : '';

        if ($input->action == 'update') {
            $input->orgTypeId = $input->orgTypes->organisation_type_id;
        }

        $aErrors = array();
        if ($input->submitted || in_array($input->action, array('insert', 'update'))) {
            if (empty($input->orgTypes->name)) {
                $aErrors['name'] = 'You must enter an organisation type name';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            if ($input->action == 'update') {
                $input->isEdit = true;
            }
            $input->template = 'orgTypeEdit.html';
            $this->validated = false;
        }
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'orgTypeEdit.html';
        $output->pageTitle .= ' :: Add';
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        SGL_DB::setConnection();
        $orgType = DB_DataObject::factory($this->conf['table']['organisation_type']);
        $orgType->setFrom($input->orgTypes);
        $orgType->organisation_type_id = $this->dbh->nextId($this->conf['table']['organisation_type']);
        $success = $orgType->insert();
        if ($success) {
            SGL::raiseMsg('Organisation type saved successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'orgTypeEdit.html';
        $output->isEdit = true;
        $orgType = DB_DataObject::factory($this->conf['table']['organisation_type']);
        $orgType->get($input->orgTypeId);
        $output->orgTypes = $orgType;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'orgTypeEdit.html';
        $orgType = DB_DataObject::factory($this->conf['table']['organisation_type']);
        $orgType->get($input->orgTypeId);
        $orgType->setFrom($input->orgTypes);
        $success = $orgType->update();
        if ($success) {
            SGL::raiseMsg('Organisation type has been updated successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseMsg('No data was updated', true, SGL_MESSAGE_WARNING);
        }
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->orgTypes = $this->da->getOrgTypes();
        $output->addOnLoadEvent("document.getElementById('frmUserMgrChooser').orgs.disabled = true");
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $orgTypeId) {
                $orgTypes = DB_DataObject::factory($this->conf['table']['organisation_type']);
                $orgTypes->get($orgTypeId);
                $orgTypes->delete();
                unset($orgTypes);
            }
            SGL::raiseMsg('Org type(s) deleted successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . __CLASS__ . '::' .
                __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
    }
}
?>
