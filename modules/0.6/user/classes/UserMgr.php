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
// | UserMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: UserMgr.php,v 1.80 2005/06/05 22:59:48 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/RegisterMgr.php';
require_once SGL_MOD_DIR  . '/default/classes/DefaultDAO.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_CORE_DIR . '/Delegator.php';

require_once 'Validate.php';

/**
 * Manages User objects.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Jacob Hanson <jacdx@jacobhanson.com>
 * @version $Revision: 1.80 $
 */
class UserMgr extends RegisterMgr
{
    function UserMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'User Manager';
        $this->template = 'userManager.html';
        $this->sortBy = 'usr_id';

        $daUser    = &UserDAO::singleton();
        $daDefault = &DefaultDAO::singleton();
        $this->da = new SGL_Delegator();
        $this->da->add($daUser);
        $this->da->add($daDefault);

        $this->_aActionsMapping = array(
            'add'                   => array('add'),
            'insert'                => array('insert', 'redirectToDefault'),
            'edit'                  => array('edit'),
            'update'                => array('update', 'redirectToDefault'),
            'delete'                => array('delete', 'redirectToDefault'),
            'list'                  => array('list'),
            'requestPasswordReset'  => array('requestPasswordReset'),
            'resetPassword'         => array('resetPassword', 'redirectToDefault'),
            'requestChangeUserStatus'  => array('requestChangeUserStatus'),
            'changeUserStatus'      => array('changeUserStatus', 'redirectToDefault'),
            'editPerms'             => array('editPerms'),
            'updatePerms'           => array('updatePerms', 'redirectToDefault'),
            'syncToRole'            => array('syncToRole', 'redirectToDefault'),
            'viewLogin'             => array('viewLogin'),
            'truncateLoginTbl'      => array('truncateLoginTbl', 'redirectToDefault'),
            'removeExpiredCookies'  => array('removeExpiredCookies', 'redirectToDefault')
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        parent::validate($req, $input);
        $input->action = ($req->get('action')) ? $req->get('action') : 'list';

        //  determine action based on which button was pressed
        if ($req->get('delete')) { $input->action = 'delete';}
        if ($req->get('syncToRole')) { $input->action = 'syncToRole';}

        $input->masterTemplate  = 'masterMinimal.html';
        $input->aPerms          = $req->get('frmPerms');
        $input->moduleId        = $req->get('frmModuleId');
        $input->from            = ($req->get('pageID'))? $req->get('pageID'):0;
        $input->passwdResetNotify = ($req->get('frmPasswdResetNotify') == 'on') ? 1 : 0;
        $input->changeStatusNotify = ($req->get('frmChangeStatusNotify') == 'on') ? 1 : 0;
        $input->user->is_email_public = (isset($input->user->is_email_public)) ? 1 : 0;
        $input->user->is_acct_active = (isset($input->user->is_acct_active)) ? 1 : 0;
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));

        // This will tell HTML_Flexy which key is used to sort data
        $input->{ 'sort_' . $input->sortBy } = true;

        $input->roleSync = $req->get('roleSync');
        if ($input->roleSync == 'null') {
            $input->roleSync = null;
        }
        $input->roleSyncMode = $req->get('roleSyncMode');

        //  Pager's total items value (maintaining it saves a count(*) on each request)
        $input->totalItems = $req->get('totalItems');

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

        //  set flag so we can share add/edit templates
        if ($output->action == 'add' || $output->action == 'insert') {
            $output->isAdd = true;
        }

        //  build country/state select boxes unless any of following methods
        $aDisallowedMethods = array(
            'list', 'requestPasswordReset',
            'resetPassword', 'editPerms', 'updatePerms', 'insertImportedUsers');

        if (!in_array($output->action, $aDisallowedMethods)) {
            $output->states = SGL::loadRegionList('states');
            $output->countries = SGL::loadRegionList('countries');
            $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions', false);
        }
        if (!in_array($output->action, array(
                'requestPasswordReset', 'resetPassword',
                'editPerms', 'updatePerms'))) {
            $output->aRoles = $this->da->getRoles();
            if ($this->conf['OrgMgr']['enabled']) {
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
        $output->isAcctActive = ($output->user->is_acct_active) ? ' checked="checked"' : '';
        $output->addJavascriptFile(array(
            'js/scriptaculous/lib/prototype.js',
            'js/scriptaculous/src/effects.js'
        ));
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        parent::_cmd_add($input, $output);
        $output->pageTitle = $input->pageTitle . ' :: Add';

        //  get default values for new users
        $defaultRoleId = $this->conf['UserMgr']['defaultRoleId'];
        $output->user->role_id = $defaultRoleId;
        $output->user->username_orig = '';
        $output->user->email_orig = '';
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oUser = $this->da->getUserById();
        $oUser->setFrom($input->user);
        $oUser->passwd = md5($input->user->passwd);
        if (@$this->conf['RegisterMgr']['autoEnable']) {
            $oUser->is_acct_active = 1;
        }
        $oUser->date_created = $oUser->last_updated = SGL_Date::getTime();
        $oUser->created_by = $oUser->updated_by = SGL_Session::getUid();
        $success = $this->da->addUser($oUser);

        //  check for errors
        if (!PEAR::isError($success)) {
            SGL::raiseMsg('user successfully added', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Edit';
        $output->template = 'userAdd.html';
        $oUser = $this->da->getUserById($input->userID);
        $output->user = $oUser;
        $output->user->username_orig = $oUser->username;
        $output->user->email_orig = $oUser->email;
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oUser = $this->da->getUserById($input->user->usr_id);
        $oUser->setFrom($input->user);
        $oUser->last_updated = SGL_Date::getTime();
        $oUser->updated_by = SGL_Session::getUid();
        $success = $this->da->updateUser($oUser, $input->user->role_id_orig,
            $input->user->organisation_id_orig);

        if (!PEAR::isError($success)) {
            SGL::raiseMsg('details successfully updated', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_delete(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $results = array();
        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $index => $userId) {

                //  don't allow admin to be deleted
                if ($userId == SGL_ADMIN) {
                    continue;
                }
                $ret = $this->da->deletePrefsByUserId($userId);
                $ret = $this->da->deletePermsByUserId($userId);
                if (!empty($this->conf['cookie']['rememberMeEnabled'])) {
                    $ret = $this->da->deleteUserLoginCookiesByUserId($userId);
                }
                $query = "DELETE FROM {$this->conf['table']['user']} WHERE usr_id=$userId";
                if (is_a($this->dbh->query($query), 'PEAR_Error')) {
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
        if ($succeeded && !$failed) {
            $errorType = SGL_MESSAGE_INFO;
        } elseif (!$succeeded && $failed) {
            $errorType = SGL_MESSAGE_ERROR;
        } else {
            $errorType = SGL_MESSAGE_WARNING;
        }
        //  redirect on success
        SGL::raiseMsg("$succeeded user(s) successfully deleted. $failed user(s) failed.", false, $errorType);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Browse';
        $allowedSortFields = array('usr_id','username','is_acct_active');
        if (      !empty($input->sortBy)
               && !empty($input->sortOrder)
               && in_array($input->sortBy, $allowedSortFields)) {
            $orderBy_query = ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY u.usr_id ASC ';
        }

        if ($this->conf[SGL_Inflector::caseFix('OrgMgr')]['enabled']) {
            $query = "
                SELECT  u.*, o.name AS org_name, r.name AS role_name
                FROM    {$this->conf['table']['user']} u
                LEFT JOIN   {$this->conf['table']['organisation']} o
                    ON u.organisation_id = o.organisation_id
                JOIN {$this->conf['table']['role']} r
                    ON r.role_id = u.role_id
                $orderBy_query";
        } else {
            $query = "
                SELECT  u.*, r.name AS role_name
                FROM    {$this->conf['table']['user']} u, {$this->conf['table']['role']} r
                WHERE   r.role_id = u.role_id
                AND     u.usr_id <> 0
                $orderBy_query";
        }

        $limit = $_SESSION['aPrefs']['resPerPage'];

        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
            'spacesBeforeSeparator' => 0,
            'spacesAfterSeparator'  => 0,
            'curPageSpanPre'        => '<span class="currentPage">',
            'curPageSpanPost'       => '</span>',
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->totalItems = $aPagedData['totalItems'];
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function _cmd_viewLogin(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'userManagerLogins.html';
        $output->pageTitle = $this->pageTitle . ' :: Login Data';

        $allowedSortFields = array('date_time','remote_ip');
        if (  !empty($input->sortBy)
           && !empty($input->sortOrder)
           && in_array($input->sortBy, $allowedSortFields)) {
                $orderBy_query = ' ORDER BY ' . $input->sortBy . ' ' . $input->sortOrder ;
        } else {
            $orderBy_query = ' ORDER BY date_time DESC ';
        }
        if (!empty($input->userID) ) {
            $query = "
                SELECT  date_time, remote_ip, login_id
                FROM    {$this->conf['table']['login']}
                WHERE   usr_id = $input->userID" .
                $orderBy_query;
        } else {
            SGL::raiseError('Incorrect parameter passed to ' .
                __CLASS__ . '::' . __FUNCTION__, SGL_ERROR_INVALIDARGS);
            return false;
        }

        $limit = $_SESSION['aPrefs']['resPerPage'];
        $pagerOptions = array(
            'mode'     => 'Sliding',
            'delta'    => 3,
            'perPage'  => $limit,
            'spacesBeforeSeparator' => 0,
            'spacesAfterSeparator'  => 0,
            'curPageSpanPre'        => '<span class="currentPage">',
            'curPageSpanPost'       => '</span>',
        );
        $aPagedData = SGL_DB::getPagedData($this->dbh, $query, $pagerOptions);

        $output->aPagedData = $aPagedData;
        if (is_array($aPagedData['data']) && count($aPagedData['data'])) {
            $output->pager = ($aPagedData['totalItems'] <= $limit) ? false : true;
        }
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function _cmd_truncateLoginTbl(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (is_array($input->aDelete)) {
            foreach ($input->aDelete as $v){
                $query = "DELETE FROM {$this->conf['table']['login']} WHERE login_id = $v";
                $this->dbh->query($query);
            }
            //  redirect on success
            SGL::raiseMsg('Deleted successfully', true, SGL_MESSAGE_INFO);

        } else {
            SGL::raiseError('Incorrect parameter passed to '.__CLASS__.'::'.__FUNCTION__,
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_requestChangeUserStatus(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Change status';
        $output->template = 'userStatusChange.html';
        $oUser = $this->da->getUserById($input->userID);
        $output->user = $oUser;
    }

    function _cmd_changeUserStatus(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oUser = $this->da->getUserById($input->userID);
        $oUser->is_acct_active = ($oUser->is_acct_active) ? 0 : 1;
        $success = $oUser->update();
        if ($input->changeStatusNotify && $success !== false) {
            $success = $this->_sendStatusNotification($oUser, $oUser->is_acct_active);
        }
        if ($success) {
            SGL::raiseMsg('Status changed successfully', true, SGL_MESSAGE_INFO);
        } else {
            $output->template = 'userManager.html';
            SGL::raiseError('There was a problem modifying the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_requestPasswordReset(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($this->conf['tuples']['demoMode'])
                && $this->conf['tuples']['demoMode'] == true
                && $input->userID == 1) {
            SGL::raiseMsg('Admin password cannot be reset in demo mode',
                false, SGL_MESSAGE_WARNING);
            return false;
        }
        $output->pageTitle = $this->pageTitle . ' :: Reset password';
        $output->template = 'userPasswordReset.html';
        $oUser = $this->da->getUserById($input->userID);
        $output->user = $oUser;
    }

    function _cmd_resetPassword(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $passwd = $oPassword->create();
        $oUser = $this->da->getUserById($input->userID);
        $oUser->passwd = md5($passwd);
        $success = $oUser->update();
        if ($input->passwdResetNotify && $success !== false) {
            require_once SGL_MOD_DIR . '/user/classes/PasswordMgr.php';
            $success = PasswordMgr::sendPassword($oUser, $passwd);
        }
        //  redirect on success
        if ($success) {
            SGL::raiseMsg('Password updated successfully', true, SGL_MESSAGE_INFO);
        } else {
            $output->template = 'userManager.html';
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _cmd_editPerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = $this->pageTitle . ' :: Edit permissions';
        $output->template = 'userPermsEdit.html';

        //  build module filter
        $aModules = $this->da->getModuleHash(SGL_RET_ID_VALUE);
        $output->currentModule = $input->moduleId;

        $aUserPerms = $this->da->getPermsByUserId($input->userID);

        foreach ($aModules as $moduleId => $moduleName) {
            $hAllPerms = $this->da->getPermsByModuleId($moduleId);
            $output->aModules[$moduleId]['id'] = $moduleId;
            $output->aModules[$moduleId]['name'] = $moduleName;
            $output->aModules[$moduleId]['perms'] = SGL_Output::generateCheckboxList(
                $hAllPerms, $aUserPerms, 'frmPerms[]');
            $output->aModulesList = $aModules;
        }
    }

    function _cmd_updatePerms(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'userPermsEdit.html';
        //  delete existing perms
        //  if we're dealing with a single view of all perms
        if (!$input->moduleId) {
            $this->dbh->autocommit(false);

            //  first delete old perms
            $res1 = $this->da->deletePermsByUserId($input->user->usr_id);

            //  then add new perms
            $res2 = $this->da->addPermsByUserId($input->aPerms, $input->user->usr_id);

            if (DB::isError($res1) || DB::isError($res2)) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                SGL::raiseError('There was a problem inserting the record',
                    SGL_ERROR_DBTRANSACTIONFAILURE);
            } else {
                $this->dbh->commit();
                $this->dbh->autocommit(true);
                SGL::raiseMsg('perm successfully updated', true, SGL_MESSAGE_INFO);
            }

        //  else we're dealing with one module's perms
        } else {
            $this->dbh->autocommit(false);

            //  generate list of the superset of perms for given module id
            $aPermsSuperset = $this->da->getPermsByModuleId($input->moduleId);
            foreach ($aPermsSuperset as $permId => $permName) {
                $res1 = $this->da->deletePermByUserIdAndPermId($input->user->usr_id, $permId);
            }
            //  add new module-specific perms
            $res2 = $this->da->addPermsByUserId($input->aPerms, $input->user->usr_id);

            if (DB::isError($res1) || DB::isError($res2)) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                SGL::raiseError('There was a problem inserting the record',
                    SGL_ERROR_DBTRANSACTIONFAILURE);
            } else {
                $this->dbh->commit();
                $this->dbh->autocommit(true);
                SGL::raiseMsg('perm successfully updated', true, SGL_MESSAGE_INFO);
            }
        }
    }

    function _cmd_syncToRole(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $results = $this->syncUsersToRole($input->aDelete, $input->roleSync, $input->roleSyncMode);

        //  could eventually display a list of failed/succeeded user ids--just
        //  summarize for now
        $results = array_count_values($results);
        $succeeded = array_key_exists(1, $results) ? $results[1] : 0;
        $failed = array_key_exists(0, $results) ? $results[0] : 0;
        if ($succeeded && !$failed) {
            $errorType = SGL_MESSAGE_INFO;
        } elseif (!$succeeded && $failed) {
            $errorType = SGL_MESSAGE_ERROR;
        } else {
            $errorType = SGL_MESSAGE_WARNING;
        }
        SGL::raiseMsg("$succeeded user(s) were synched successfully. $failed user(s) failed.",
            false, $errorType);
    }

    function _cmd_removeExpiredCookies(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (!empty($this->conf['cookie']['rememberMeEnabled'])) {
            $this->da->deleteExpiredUserLoginCookies();

            SGL::raiseMsg('Expired persistent cookies successfully removed',
                true, SGL_MESSAGE_INFO);
        }
    }

    function _sendStatusNotification($oUser, $isEnabled)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_CORE_DIR . '/Emailer.php';
        $realName = $oUser->first_name . ' ' . $oUser->last_name;
        $recipientName = (trim($realName)) ? $realName : '&lt;no name supplied&gt;';
        $options = array(
            'toEmail'   => $oUser->email,
            'toRealName' => $recipientName,
            'isEnabled' => $isEnabled,
            'fromEmail' => $this->conf['email']['admin'],
            'replyTo'   => $this->conf['email']['admin'],
            'subject'   => 'Account Status Notification from ' . $this->conf['site']['name'],
            'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme']
                . '/user/email_status_notification.php',
            'username'  => $oUser->username,
        );
        $message = & new SGL_Emailer($options);
        $ok = $message->prepare();
        return ($ok) ? $message->send() : $ok;
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

        //  force user to be an array if it's a single value
        if (!is_array($aUsers)) {
            $aUsers = array($aUsers);
        }
        //  container role(s) perms
        $aRolesPerms = array();

        //  use specified roleId for all users (each user's own roleId is used if
        //  $roleId = null
        $userRoleId = $roleId;

        $results = array();
        foreach ($aUsers as $userId) {
            $this->dbh->autocommit(false); //  a transaction for each user

            //  get user's roleId, if null
            if ($roleId == null) {
                $query = "SELECT role_id FROM {$this->conf['table']['user']} WHERE usr_id={$userId}";
                $userRoleId = $this->dbh->getOne($query);
                if (is_a($userRoleId, 'PEAR_Error')) {
                    $this->dbh->rollback();
                    $this->dbh->autocommit(true);
                    $results[$userId] = 0; //   log result for user
                    continue;
                }
            }

            //  get user's role's perms, if not already loaded
            if (array_key_exists($userRoleId, $aRolesPerms) === false) {

                //  perms for this role haven't been loaded yet
                $aRolesPerms[$userRoleId] = $this->da->getPermsByRoleId($userRoleId);
                if (is_a($aRolesPerms[$userRoleId], 'PEAR_Error')) {
                    $this->dbh->rollback();
                    $this->dbh->autocommit(true);
                    $results[$userId] = 0; //log result for user
                    continue;
                }
            }
            $rolePerms = $aRolesPerms[$userRoleId];

            //  get user's perms
            $userPerms = $this->da->getPermsByUserId($userId);
            if (is_a($userPerms, 'PEAR_Error')) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                $results[$userId] = 0; //log result for user
                continue;
            }

            //  remove extra perms (remove extra or complete sync)
            if ($mode == SGL_ROLESYNC_ADDREMOVE || $mode == SGL_ROLESYNC_REMOVE) {
                $toRemove = array_diff($userPerms, $rolePerms);
                foreach ($toRemove as $k => $permId) {
                    $res = $this->da->deletePermByUserIdAndPermId($userId, $permId);

                    if (is_a($res, 'PEAR_Error')) {
                        $this->dbh->rollback();
                        $this->dbh->autocommit(true);
                        $results[$userId] = 0; //log result for user
                        continue 2;
                    }
                }
            }

            //  add missing perms (add missing or complete sync)
            if ($mode == SGL_ROLESYNC_ADDREMOVE || $mode == SGL_ROLESYNC_ADD) {
                $toAdd = array_diff($rolePerms, $userPerms);

                $res = $this->da->addPermsByUserId($toAdd, $userId);

                if (is_a($res, 'PEAR_Error')) {
                    $this->dbh->rollback();
                    $this->dbh->autocommit(true);
                    $results[$userId] = 0; //log result for user
                    continue;
                }
            }
            //  if we make it here, we're all good (for this user)
            $this->dbh->commit();
            $this->dbh->autocommit(true);
            $results[$userId] = 1;
        }
        return $results;
    }
}
?>
