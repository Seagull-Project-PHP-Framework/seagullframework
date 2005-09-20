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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | RoleMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: RoleMgr.php,v 1.34 2005/05/17 23:54:53 demian Exp $

require_once SGL_CORE_DIR . '/Manager.php';
require_once SGL_ENT_DIR . '/Role.php';
require_once SGL_MOD_DIR . '/user/classes/DA_User.php';

/**
 * add to role flag
 */
define('SGL_ROLE_ADD',     1);

/**
 * remove from role flag
 */
define('SGL_ROLE_REMOVE',  2);

/**
 * Manages user roles.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.34 $
 * @since   PHP 4.1
 */
class RoleMgr extends SGL_Manager
{
    function RoleMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'user';
        $this->template     = 'roleManager.html';
        $this->pageTitle    = 'Role Manager';
        $this->da           = & DA_User::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),

            'editPerms' => array('editPerms'), 
            'updatePerms' => array('updatePerms', 'redirectToDefault'),
            'duplicate' => array('duplicate', 'redirectToDefault'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;
        $input->submit          = $req->get('submitted');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'list';
        $input->roleId          = $req->get('frmRoleID');
        $input->role            = (object) $req->get('role');
        $input->aDelete         = $req->get('frmDelete');
        $input->permsToAdd      = $req->get('AddfrmRolePerms');
        $input->permsToRemove   = $req->get('RemovefrmRolePerms');
        $input->totalItems      = $req->get('totalItems');
        $input->sortBy          = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder       = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        $aErrors = array();
        if ($input->submit && $input->action =='insert') {
            if (empty($input->role->name)) {
                $this->validated = false;
                $aErrors['name'] = 'You must enter an role name';
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = ($input->action == 'update') ? 'roleEdit.html' : 'roleAdd.html';
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'roleAdd.html';
        $output->pageTitle = $this->pageTitle . ' :: Add';
        $output->role = & new DataObjects_Role();
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $oRole = & new DataObjects_Role();
        $oRole->setFrom($input->role);
        $dbh = & $oRole->getDatabaseConnection();
        $oRole->role_id = $dbh->nextId($conf['table']['role']);
        $oRole->date_created = $oRole->last_updated = SGL::getTime();
        $oRole->created_by = $oRole->updated_by = SGL_HTTP_Session::getUid();
        $success = $oRole->insert();
        if ($success) {
            SGL::raiseMsg('role successfully added');
        } else {
           SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'roleEdit.html';
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $oRole = & new DataObjects_Role();
        $oRole->get($input->roleId);
        $output->role = $oRole;
    }

    function _update($input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $oRole = & new DataObjects_Role();
        $oRole->get($input->role->role_id);
        $oRole->setFrom($input->role);
        $oRole->last_updated = SGL::getTime();
        $oRole->updated_by = SGL_HTTP_Session::getUid();
        $success = $oRole->update();
        if ($success) {
            SGL::raiseMsg('role successfully updated');
        } else {
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        foreach ($input->aDelete as $roleId) {

            //  disallow deletion of admin and unassigned role
            $msg = 'role successfully deleted';
            if ($roleId == SGL_ADMIN || $roleId == SGL_UNASSIGNED) {
                $msg .= ' but please note, admin/unassigned roles cannot be deleted';
                continue;
            }
            $oRole = & new DataObjects_Role();
            $oRole->get($roleId);
            $oRole->delete();
            unset($oRole);

            //  cleanup
            $this->_deleteCleanup($roleId);
        }
        SGL::raiseMsg($msg);
    }

    /**
     * Updates any users whose role has been deleted to a role status of 
     * SGL_UNASSIGNED (-1).
     *
     * @access  private
     * @param   int     $roleId  id of role to be set to SGL_UNASSIGNED
     * @return  void
     */
    function _deleteCleanup($roleId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  bring global vars into scope
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  select all users and orgs with a given role id
        $aUsers = $this->da->getUsersByRoleId($roleId);

        //  and update users to 'unassigned' role
        if (count($aUsers)) {
            foreach ($aUsers as $userId) {
                $dbh->query('   UPDATE ' . $conf['table']['user'] . '
                                SET role_id = ' . SGL_UNASSIGNED . '
                                WHERE usr_id =' . $userId);

                //  remove all user perms associated with deleted role
                $dbh->query('   DELETE FROM ' . $conf['table']['user_permission'] . '
                                WHERE   usr_id = ' . $userId);
            }
        }

        //  and update orgs to 'unassigned' role
        $aOrgs = $this->da->getOrgsByRoleId($roleId);        
        if (count($aOrgs)) {
            foreach ($aOrgs as $orgId) {
                $dbh->query('   UPDATE ' . $conf['table']['organisation'] . '
                                SET role_id = ' . SGL_UNASSIGNED . '
                                WHERE organisation_id =' . $orgId);
            }
        }
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'roleManager.html';
        $output->pageTitle = $this->pageTitle . ' :: Browse';

        $allowedSortFields = array('role_id','name');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = 'ORDER BY role_id ASC ';
        }

        $query = "  SELECT
                        role_id, name, description, date_created, 
                        created_by, last_updated, updated_by 
                    FROM {$conf['table']['role']} " .$orderBy_query;

        $dbh = & SGL_DB::singleton();
        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => 3,
            'perPage'   => $limit,
            'totalItems'=> $input->totalItems,
        );
        $aPagedData = SGL_DB::getPagedData($dbh, $query, $pagerOptions);
        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->addOnLoadEvent("document.getElementById('frmUserMgrChooser').roles.disabled = true");
    }

    function _duplicate(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();

        //  get role name info
        $name = $dbh->getOne("SELECT name FROM {$conf['table']['role']} WHERE role_id = " . $input->roleId);
        $duplicateName = $name . ' (duplicate)';

        //  insert new role duplicate, wrap in transaction
        $dbh->autocommit();
        $newRoleId = $dbh->nextId($conf['table']['role']);
        $res1 = $dbh->query('INSERT INTO ' . $conf['table']['role'] . "
                            (role_id, name, description)
                            VALUES ($newRoleId, '$duplicateName', 'please enter description')");

        //  insert role perms
        $aRolePerms = $this->da->getPermNamesByRoleId($input->roleId);
        $isError = false;
        if (count($aRolePerms)) {
            foreach ($aRolePerms as $permId => $permName) {
                $res2 = $dbh->query('INSERT INTO ' . $conf['table']['role_permission'] . "
                                     (role_permission_id, role_id, permission_id)
                                     VALUES (" . $dbh->nextId($conf['table']['role_permission']) . ", $newRoleId, $permId)");
            }
            $isError = DB::isError($res2);
        }
        if (DB::isError($res1) || $isError) {
            $dbh->rollback();
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_DBTRANSACTIONFAILURE);
        } else {
            $dbh->commit();
            SGL::raiseMsg('role successfully duplicated');
        }
    }

    function _editPerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'roleEditPerms.html';
        $output->pageTitle = $this->pageTitle . ' :: Permissions';
        $oRole = & new DataObjects_Role();
        $oRole->get($input->roleId);
        $output->role = $oRole;

        //  get set of perms associated with role
        $aRolePerms = $this->da->getPermNamesByRoleId($oRole->role_id);
        asort($aRolePerms);
        $output->aRolePerms = $aRolePerms;

        //  get remaining perms
        $aRemainingPerms = $this->da->getPermsNotInRole($aRolePerms);
        asort($aRemainingPerms);
        $output->aRemainingPerms = $aRemainingPerms;
    }

    function _updatePerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aPermsToAdd     = $this->_parsePermsString($input->permsToAdd);
        $aPermsToRemove  = $this->_parsePermsString($input->permsToRemove);
        if (is_array($aPermsToAdd) && count($aPermsToAdd)) {
            $ret = $this->da->updateRolePermissionAssocs($aPermsToAdd, $input->roleId, SGL_ROLE_ADD);
        }
        if (is_array($aPermsToRemove) && count($aPermsToRemove)) {
            $ret = $this->da->updateRolePermissionAssocs($aPermsToRemove, $input->roleId, SGL_ROLE_REMOVE);
        }
        SGL::raiseMsg('role assignments successfully updated');
    }

    /**
     * Parse string from role changer widget.
     *
     * @access  private
     * @param   string  $sUsers colon-separated string of username_UIDs
     * @return  array   aUsers  hash of uid => username
     */
    function _parsePermsString($sPerms)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $aTmpPerms = split(':', $sPerms);
        if (count($aTmpPerms) > 0) {
            array_pop($aTmpPerms);
            $aPerms = array();
            foreach ($aTmpPerms as $perm) {
                //  chop at caret
                list($permName, $permId) = split('\^', $perm);
                $aPerms[$permId] = $permName;
            }
        } else {
            return false;
        }
        return $aPerms;
    }
}
?>
