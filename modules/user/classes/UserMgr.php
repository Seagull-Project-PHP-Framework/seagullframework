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
// | UserMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: UserMgr.php,v 1.80 2005/06/05 22:59:48 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/RegisterMgr.php';
require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_CORE_DIR . '/HTTP.php';
require_once 'Validate.php';

/**
 * Manages User objects.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Jacob Hanson <jacdx@jacobhanson.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.80 $
 * @since   PHP 4.1
 */
class UserMgr extends RegisterMgr
{
    function UserMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module = 'user';
        $this->pageTitle = 'User Manager';
        $this->template = 'userManager.html';
        $this->da = & DA_User::singleton();
        $this->sortBy = 'usr_id';

        $this->_aActionsMapping = array(  
            'add'                   => array('add'), 
            'insert'                => array('insert', 'redirectToDefault'),
            'edit'                  => array('edit'),
            'update'                => array('update', 'redirectToDefault'),
            'delete'                => array('delete', 'redirectToDefault'),
            'list'                  => array('list'),
            'requestPasswordReset'  => array('requestPasswordReset'),
            'resetPassword'         => array('resetPassword', 'redirectToDefault'),
            'editPerms'             => array('editPerms'),
            'updatePerms'           => array('updatePerms', 'redirectToDefault'),
            'syncToRole'            => array('syncToRole', 'redirectToDefault'),
            'viewLogin'             => array('viewLogin'),
            'truncateLoginTbl'      => array('truncateLoginTbl'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        parent::validate($req, $input);
        $input->action = ($req->get('action')) ? $req->get('action') : 'list';

        //determine action based on which button was pressed
        if ($req->get('delete')) { $input->action = 'delete';}
        if ($req->get('syncToRole')) { $input->action = 'syncToRole';}
        
        $input->masterTemplate  = 'masterMinimal.html';
        $input->aPerms          = $req->get('frmPerms');
        $input->moduleId        = $req->get('frmModuleId');
        $input->from            = ($req->get('pageID'))? $req->get('pageID'):0;
        $input->passwdResetNotify = ($req->get('frmPasswdResetNotify') == 'on') ? 1 : 0;
        $input->user->is_email_public = (isset($input->user->is_email_public)) ? 1 : 0;
        $input->user->is_acct_active = (isset($input->user->is_acct_active)) ? 1 : 0;
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        $input->roleSync        = $req->get('roleSync');
        if ($input->roleSync == 'null') { 
            $input->roleSync = null;
        }
        $input->roleSyncMode    = $req->get('roleSyncMode');
        
        //  Pager's total items value (maintaining it saves a count(*) on each request)
        $input->totalItems      = $req->get('totalItems');
        
        if (!isset($aErrors)) {
            $aErrors = array();
        }

        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  set flag to we can share add/edit templates
        if ($output->action == 'add' || $output->action == 'insert') {
            $output->isAdd = true;
        }

        //  build country/state select boxes unless any of following methods
        $aDisallowedMethods = array(
            'list', 'requestPasswordReset',
            'resetPassword', 'editPerms', 'updatePerms', 'insertImportedUsers');
            
        if (!in_array($output->action, $aDisallowedMethods)) {
            $lang = SGL::getCurrentLang();
            if ($lang != 'en' && $lang != 'de' && $lang != 'it') {
                $lang = 'en';
            }
            include_once SGL_DAT_DIR . '/ary.states.' . $lang . '.php';
            include_once SGL_DAT_DIR . '/ary.countries.' . $lang . '.php';

            $output->states = $states;
            $output->countries = $countries;
            $GLOBALS['_SGL']['COUNTRIES'] = &$countries;
            $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions');
        }
        if (!in_array($output->action, array(
                'requestPasswordReset', 'resetPassword', 
                'editPerms', 'updatePerms'))) {
            $output->aRoles = $this->da->getRoles();
            if ($conf['OrgMgr']['enabled']) {
                $output->aOrgs = $this->da->getOrgs();
            }
        }

        if ($output->action == 'list') {
            $aSyncModes = array();
            $aSyncModes[SGL_ROLESYNC_ADDREMOVE] = SGL_String::translate('complete sync');
            $aSyncModes[SGL_ROLESYNC_REMOVE] = SGL_String::translate('remove extra perms');
            $aSyncModes[SGL_ROLESYNC_ADD] = SGL_String::translate('add missing perms');
            $output->aSyncModes = $aSyncModes;
        }
        $output->isAcctActive = ($output->user->is_acct_active) ? ' checked' : '';
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::_add($input, $output);
        $output->pageTitle = $input->pageTitle . ' :: Add';
    }


    /**
     * Returns a DataObjects user object.
     * 
     * @access private
     * @return object   A DataObjects user object
     */
    function &_createUser()
    {
        return new DataObjects_Usr();
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $oUser = & $this->_createUser();
        $dbh = & $oUser->getDatabaseConnection();
        SGL_DB::setConnection($dbh);
        $dbh->autocommit();
        $oUser->setFrom($input->user);
        $oUser->passwd = md5($input->user->passwd);
        if (@$conf['RegisterMgr']['autoEnable']) {
            $oUser->is_acct_active = 1;
        }
        $oUser->usr_id = $dbh->nextId($conf['table']['user']);
        $oUser->date_created = $oUser->last_updated = SGL::getTime();
        $oUser->created_by = $oUser->updated_by = SGL_HTTP_Session::getUid();
        $success = $oUser->insert();

        //  assign permissions associated with role user belongs to
        //  first get all perms associated with user's role
        $aRolePerms = $this->da->getPermsByRoleId($oUser->role_id);

        //  then assign them to the user_permission table
        $ret = $this->da->addPermsByUserId($aRolePerms, $oUser->usr_id);

        //  assign preferences associated with org user belongs to
        //  first get all prefs associated with user's org
        $aOrgPrefs = $this->da->getUserPrefsByOrgId($oUser->organisation_id, SGL_RET_ID_VALUE);

        //  then assign them to the user_preference table
        $ret = $this->da->addPrefsByUserId($aOrgPrefs, $oUser->usr_id);

        //  check global error stack for any error that might have occurred
        if ($success && !(count($GLOBALS['_SGL']['ERRORS']))) {
            $dbh->commit();
            SGL::raiseMsg('user successfully added');
        } else {
            $dbh->rollback();
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $output->template = 'userAdd.html';
        $oUser = & $this->_createUser();
        $oUser->get($input->userID);
        $output->user = $oUser;
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];

        $oUser = & $this->_createUser();
        $dbh = & $oUser->getDatabaseConnection();
        SGL_DB::setConnection($dbh);
        $dbh->autocommit();
        $oUser->get($input->user->usr_id);
        $oUser->setFrom($input->user);
        $oUser->last_updated = SGL::getTime();
        $oUser->updated_by = SGL_HTTP_Session::getUid();
        $success = $oUser->update();

        //  change perms if role is modified
        if (isset($input->user->role_id_orig) && ($oUser->role_id != $input->user->role_id_orig)) {

            //  first delete old perms
            $ret = $this->da->deletePermsByUserId($oUser->usr_id);

            //  assign permissions associated with role user has been moved to
            //  first get all perms associated with user's new role
            $aRolePerms = $this->da->getPermsByRoleId($oUser->role_id);

            //  then assign them to the user_permission table
            $ret = $this->da->addPermsByUserId($aRolePerms, $oUser->usr_id);
        }

        //  change prefs if org is modified
        if (isset($input->user->organisation_id_orig) 
                && ($oUser->organisation_id  != $input->user->organisation_id_orig)) {

            //  first delete old preferences
            $ret = $this->da->deletePrefsByUserId($oUser->usr_id);

            //  assign preferences associated with org user belongs to
            //  first get all prefs associated with user's org
            $aOrgPrefs = $this->da->getUserPrefsByOrgId($oUser->organisation_id, SGL_RET_ID_VALUE);

            //  then assign them to the user_preference table
            $ret = $this->da->addPrefsByUserId($aOrgPrefs, $oUser->usr_id);
        }

        if ($success && !(count($GLOBALS['_SGL']['ERRORS']))) {
            $dbh->commit();
            SGL::raiseMsg('details successfully updated');
        } else {
            $dbh->rollback();
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'docBlank.html';
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
        $results = array();
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $userId) {
                //  don't allow admin to be deleted
                if ($userId == SGL_ADMIN) {
                    continue;
                }
                $query = "DELETE FROM {$conf['table']['user']} WHERE usr_id=$userId"; 
                if (is_a($dbh->query($query), 'PEAR_Error')) {
                    $results[$userId] = 0; //log result for user
                    continue;
                }
                $results[$userId] = 1;
            }
        } else {
            SGL::raiseError('Incorrect parameter passed to ' . 
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
        }
        //  could eventually display a list of failed/succeeded user ids--just summarize for now
        $results = array_count_values($results);
        $succeeded = array_key_exists(1, $results) ? $results[1] : 0;
        $failed = array_key_exists(0, $results) ? $results[0] : 0;
        
        //  redirect on success
        SGL::raiseMsg("$succeeded user(s) successfully deleted. $failed user(s) failed.");
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //$input->template = 'userManager.html';
        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->pageTitle = $this->pageTitle . ' :: Browse';
        $dbh = & SGL_DB::singleton();

        $allowedSortFields = array('usr_id','username','is_acct_active');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = 'ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY u.usr_id ASC ';
        }

        if ($conf[SGL::caseFix('OrgMgr')]['enabled']) {
            $query = "
                SELECT  u.*, o.name AS org_name, r.name AS role_name
                FROM    {$conf['table']['user']} u, {$conf['table']['organisation']} o, {$conf['table']['role']} r
                WHERE   o.organisation_id = u.organisation_id
                AND     r.role_id = u.role_id " .
                $orderBy_query;
        } else {
            $query = "
                SELECT  u.*, r.name AS role_name
                FROM    {$conf['table']['user']} u, {$conf['table']['role']} r
                WHERE   r.role_id = u.role_id " .
                $orderBy_query;
        }

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
        $output->totalItems = $aPagedData['totalItems'];
        $output->addOnLoadEvent("document.getElementById('frmUserMgrChooser').users.disabled = true");
    }

    function _viewLogin(&$input, &$output){
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'userManagerLogins.html';
        $output->pageTitle = $this->pageTitle . ' :: Login Data';
        $dbh = & SGL_DB::singleton();

        $allowedSortFields = array('date_time','remote_ip');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY date_time ASC ';
        }
        if (!empty($input->userID) ){
            $query = "
                SELECT  date_time, remote_ip, login_id
                FROM    {$conf['table']['login']}
                WHERE   usr_id = $input->userID" .
                $orderBy_query;
        }

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

    }

    function _truncateLoginTbl(&$input, &$output){

        SGL :: logMessage(null, PEAR_LOG_DEBUG);
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];

        if (is_array($input->aDelete)) {
            foreach($input->aDelete as $v){
                $qry = "DELETE FROM {$conf['table']['login']} WHERE login_id = $v";
                $dbh->query($qry); 
            }
        } else {
            SGL :: raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__, SGL_ERROR_NOAFFECTEDROWS);
        }

        //  redirect on success
        SGL :: raiseMsg('Deleted successfully');
        SGL_HTTP :: redirect(array ('action' => 'viewLogin', 'frmUserID' => "{$input->userID}"));

    }
    
    function _requestPasswordReset(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = $this->pageTitle . ' :: Reset password';
        $output->template = 'userPasswordReset.html';
        $oUser = & $this->_createUser();
        $oUser->get($input->userID);
        $output->user = $oUser;
    }

    function _resetPassword(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $passwd = $oPassword->create();
        $oUser = & $this->_createUser();
        $oUser->get($input->userID);
        $oUser->passwd = md5($passwd);
        $success = $oUser->update();
        if ($input->passwdResetNotify && $success) {
            include_once SGL_MOD_DIR . '/user/classes/PasswordMgr.php';
            $success = PasswordMgr::sendPassword($oUser, $passwd);
        }
        //  redirect on success
        if ($success) {
            SGL::raiseMsg('Password updated successfully');
        } else {
            $output->template = 'userManager.html';
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _editPerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_MOD_DIR . '/user/classes/PermissionMgr.php';
        $output->pageTitle = $this->pageTitle . ' :: Edit permissions';
        $output->template = 'userPermsEdit.html';

        //  build module filter
        require_once SGL_MOD_DIR . '/default/classes/ModuleMgr.php';
        $output->aModules = ModuleMgr::retrieveAllModules(SGL_RET_ID_VALUE);
        $output->currentModule = $input->moduleId;

        $aUserPerms = $this->da->getPermsByUserId($input->userID);
        $hAllPerms = $this->da->getPermsByModuleId($input->moduleId);
        $output->permCheckboxes = SGL_Output::generateCheckboxList($hAllPerms, 
            $aUserPerms, 'frmPerms[]');
    }

    function _updatePerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $output->template = 'userPermsEdit.html';

        //  delete existing perms
        $dbh = & SGL_DB::singleton();

        //  if we're dealing with a single view of all perms
        if (!$input->moduleId) {
            $dbh->autocommit();

            //  first delete old perms
            $res1 = $this->da->deletePermsByUserId($input->user->usr_id);

            //  then add new perms
            $res2 = $this->da->addPermsByUserId($input->aPerms, $input->user->usr_id);

            if (DB::isError($res1) || DB::isError($res2)) {
                $dbh->rollback();
                SGL::raiseError('There was a problem inserting the record', 
                    SGL_ERROR_DBTRANSACTIONFAILURE);
            } else {
                $dbh->commit();
                SGL::raiseMsg('perm successfully updated');
            }

        //  else we're dealing with one module's perms
        } else {
            $dbh->autocommit();

            //  generate list of the superset of perms for given module id
            $aPermsSuperset = $this->da->getPermsByModuleId($input->moduleId);
            foreach ($aPermsSuperset as $permId => $permName) {
                $res1 = $this->da->deletePermByUserIdAndPermId($input->user->usr_id, $permId);
            }
            //  add new module-specific perms
            $res2 = $this->da->addPermsByUserId($input->aPerms, $input->user->usr_id);

            if (DB::isError($res1) || DB::isError($res2)) {
                $dbh->rollback();
                SGL::raiseError('There was a problem inserting the record', 
                    SGL_ERROR_DBTRANSACTIONFAILURE);
            } else {
                $dbh->commit();
                SGL::raiseMsg('perm successfully updated');
            }
        }
    }

    function _syncToRole(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $results = $this->syncUsersToRole($input->aDelete, $input->roleSync, $input->roleSyncMode);

        //  could eventually display a list of failed/succeeded user ids--just summarize for now
        $results = array_count_values($results);
        $succeeded = array_key_exists(1, $results) ? $results[1] : 0;
        $failed = array_key_exists(0, $results) ? $results[0] : 0;
        SGL::raiseMsg("$succeeded user(s) were synched successfully. $failed user(s) failed.", false);
    }
    
    /**
    * Syncs user(s) perms to role(s). Can do a complete sync or
    * only add perms that user is missing from role or only delete perms
    * that don't exist in role. If roleId s/xmjn  
    *
    * @author  Jacob Hanson <jacdx@jacobhanson.com>       
    * @copyright Jacob Hanson 2004
    * @access  public
    * @param   array    users   array of user(s) ids
    * @param   integer  roleId  role to assign to users. If null, each user is sync'ed to their existing role
    * @param   integer  mode    mode constant (SGL_ROLESYNC_ADD: only add perms user is missing, 
    * SGL_ROLESYNC_REMOVE: only remove extra perms user has, SGL_ROLESYNC_ADDREMOVE: do both)
    * @return  array    array of results (userId=>true/false)
    */
    function syncUsersToRole($aUsers, $roleId = null, $mode = SGL_ROLESYNC_ADDREMOVE)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $dbh = & SGL_DB::singleton();    
        
        //  force user to be an array if it's a single value
        if (!is_array($aUsers)) {
            $aUsers = array($aUsers);
        }

        //  container role(s) perms
        $aRolesPerms = array();
        
        require_once SGL_MOD_DIR . '/user/classes/PermissionMgr.php';
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        //  use specified roleId for all users (each user's own roleId is used if 
        //  $roleId = null
        $userRoleId = $roleId;            
        
        $results = array();
        foreach ($aUsers as $userId) {
            $dbh->autocommit(); //  a transaction for each user

            //  get user's roleId, if null
            if ($roleId == null) {
                $query = "SELECT role_id FROM {$conf['table']['user']} WHERE usr_id={$userId}";
                $userRoleId = $dbh->getOne($query);
                if (is_a($userRoleId, 'PEAR_Error')) {
                    $dbh->rollback();
                    $results[$userId] = 0; //   log result for user
                    continue;
                }
            }
                
            //  get user's role's perms, if not already loaded
            if (array_key_exists($userRoleId, $aRolesPerms) === false) {

                //  perms for this role haven't been loaded yet    
                $aRolesPerms[$userRoleId] = $this->da->getPermsByRoleId($userRoleId);
                if (is_a($aRolesPerms[$userRoleId], 'PEAR_Error')) {
                    $dbh->rollback();
                    $results[$userId] = 0; //log result for user
                    continue;
                }
            } 
            $rolePerms = $aRolesPerms[$userRoleId];
            
            //  get user's perms                
            $userPerms = $this->da->getPermsByUserId($userId);
            if (is_a($userPerms, 'PEAR_Error')) {
                $dbh->rollback();
                $results[$userId] = 0; //log result for user
                continue;
            }

            //  remove extra perms (remove extra or complete sync)
            if ($mode == SGL_ROLESYNC_ADDREMOVE || $mode == SGL_ROLESYNC_REMOVE) {
                $toRemove = array_diff($userPerms, $rolePerms);
                foreach ($toRemove as $k => $permId) {
                    $res = $this->da->deletePermByUserIdAndPermId($userId, $permId);

                    if (is_a($res, 'PEAR_Error')) {
                        $dbh->rollback();
                        $results[$userId] = 0; //log result for user
                        continue;
                    }
                }
            }
            
            //  add missing perms (add missing or complete sync)
            if ($mode == SGL_ROLESYNC_ADDREMOVE || $mode == SGL_ROLESYNC_ADD) {
                $toAdd = array_diff($rolePerms, $userPerms);

                $res = $this->da->addPermsByUserId($toAdd, $userId);

                if (is_a($res, 'PEAR_Error')) {
                    $dbh->rollback();
                    $results[$userId] = 0; //log result for user
                    continue;
                }
            }
            //  if we make it here, we're all good (for this user)
            $dbh->commit();
            $results[$userId] = 1;
        }
        return $results;
    }
}
?>