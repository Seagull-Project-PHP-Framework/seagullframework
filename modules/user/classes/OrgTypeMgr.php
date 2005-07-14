<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | OrgtypeMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004 Demian Turner                                          |
// |                                                                           |
// | Author: AJ Tarachanowicz <ajt@localhype.net>                                  |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This library is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU Library General Public               |
// | License as published by the Free Software Foundation; either              |
// | version 2 of the License, or (at your option) any later version.          |
// |                                                                           |
// | This library is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         |
// | Library General Public License for more details.                          |
// |                                                                           |
// | You should have received a copy of the GNU Library General Public         |
// | License along with this library; if not, write to the Free                |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
// |                                                                           |
// +---------------------------------------------------------------------------+
// $Id: OrgTypeMgr.php,v 1.5 2005/06/23 16:56:14 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
require_once SGL_ENT_DIR . '/Organisation_type.php';
/**
 * Manage Org Types
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
        $orgType->organisation_type_id = $dbh->nextId('organisation_type');        
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
