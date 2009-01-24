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
// | UserDAO.php                                                               |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: UserDAO.php,v 1.14 2005/06/21 23:26:24 demian Exp $

//  role sync constants
define('SGL_ROLESYNC_ADD',              1);
define('SGL_ROLESYNC_REMOVE',           2);
define('SGL_ROLESYNC_ADDREMOVE',        3);
define('SGL_ROLESYNC_VIEWONLY',         4);

/**
 * Data access methods for the user module.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class UserDAO extends SGL_Manager
{
    /**
     * Constructor - set default resources.
     *
     * @return UserDAO
     */
    function UserDAO()
    {
        parent::SGL_Manager();
    }

    /**
     * Returns a singleton UserDAO instance.
     *
     * example usage:
     * $da = & UserDAO::singleton();
     * warning: in order to work correctly, the DA
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  UserDAO reference to UserDAO object
     */
    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new UserDAO();
        }
        return $instance;
    }

    //  //////////////////////////////////////////////////
    //  /////////////////   USERS   //////////////////////
    //  //////////////////////////////////////////////////

    function addUser($oUser)
    {
        SGL_DB::setConnection();
        $this->dbh->autocommit(false);

        $userId = $this->dbh->nextId($this->conf['table']['user']);
        $oUser->usr_id = $userId;
        $ok = $oUser->insert();

        if (!$ok) {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return PEAR::raiseError('Problem inserting user DataObject');
        }
        //  assign permissions associated with role user belongs to
        //  first get all perms associated with user's role
        $aRolePerms = $this->getPermsByRoleId($oUser->role_id);
        if (PEAR::isError($aRolePerms)) {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return $aRolePerms;
        }

        //  then assign them to the user_permission table
        $ok = $this->addPermsByUserId($aRolePerms, $oUser->usr_id);
        if (PEAR::isError($ok)) {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return $ok;
        }
        //  assign preferences associated with org user belongs to
        //  first get all prefs associated with user's org or default
        //  prefs if orgs are disabled
        if (!empty($this->conf['OrgMgr']['enabled'])) {
            $aPrefs = $this->getUserPrefsByOrgId($oUser->organisation_id, SGL_RET_ID_VALUE);
        } else {
            $aPrefs = $this->getMasterPrefs(SGL_RET_ID_VALUE);
        }
        if (PEAR::isError($aPrefs)) {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return $aPrefs;
        }

        //  then assign them to the user_preference table
        $ok = $this->addPrefsByUserId($aPrefs, $oUser->usr_id);
        if (PEAR::isError($ok)) {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return $ok;
        }

        if ($ok && !SGL_Error::count()) {
            $this->dbh->commit();
            $this->dbh->autocommit(true);
            return $userId;
        } else {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return SGL_Error::getLast();
        }
    }

    function updateUser($oUser, $roleIdOrig = null, $orgIdOrig = null)
    {

        SGL_DB::setConnection();
        $this->dbh->autocommit(false);

        $ok = $oUser->update();

        if ($ok === false) {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return PEAR::raiseError('Problem inserting user DataObject');
        }
        //  change perms if role is modified
        if (!is_null($roleIdOrig) && ($oUser->role_id != $roleIdOrig)) {

            //  disallow usr_id(1) admin from changing role
            if ($oUser->usr_id == SGL_ADMIN) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                return PEAR::raiseError('User with ID = 1 cannot change role');
            }


            //  first delete old perms
            $ok = $this->deletePermsByUserId($oUser->usr_id);
            if (PEAR::isError($ok)) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                return $ok;
            }
            //  assign permissions associated with role user has been moved to
            //  first get all perms associated with user's new role
            $aRolePerms = $this->getPermsByRoleId($oUser->role_id);

            //  then assign them to the user_permission table
            $ok = $this->addPermsByUserId($aRolePerms, $oUser->usr_id);
            if (PEAR::isError($ok)) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                return $ok;
            }
        }

        //  change prefs if org is modified
        if (!is_null($orgIdOrig) && ($oUser->organisation_id  != $orgIdOrig)) {

            //  first delete old preferences
            $ok = $this->deletePrefsByUserId($oUser->usr_id);
            if (PEAR::isError($ok)) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                return $ok;
            }
            //  assign preferences associated with org user belongs to
            //  first get all prefs associated with user's org
            $aOrgPrefs = $this->getUserPrefsByOrgId($oUser->organisation_id, SGL_RET_ID_VALUE);

            //  then assign them to the user_preference table
            $ok = $this->addPrefsByUserId($aOrgPrefs, $oUser->usr_id);
            if (PEAR::isError($ok)) {
                $this->dbh->rollback();
                $this->dbh->autocommit(true);
                return $ok;
            }
        }

        if ($ok !== false && !SGL_Error::count()) {
            $this->dbh->commit();
            $this->dbh->autocommit(true);
            return true;
        } else {
            $this->dbh->rollback();
            $this->dbh->autocommit(true);
            return PEAR::raiseError('Problem encountered adding user');
        }
    }

    /**
     * Returns a DataObjects Usr object.
     *
     * @access private
     * @param integer   $id optional user id
     * @return object   A DataObjects user object
     */
    function getUserById($id = null)
    {
        require_once 'DB/DataObject.php';
        $oUser = DB_DataObject::factory($this->conf['table']['user']);
        if (!is_null($id)) {
            $oUser->get($id);
        }
        return $oUser;
    }


    //  //////////////////////////////////////////////////
    //  /////////////////   PERMS   //////////////////////
    //  //////////////////////////////////////////////////

    /**
     * A grouped delete.
     *
     * @param array $aPerms An array of elements of the form <perm_name>^<module_id>
     * @return mixed    True on success, number of errors on failure
     */
    function deleteOrphanedPerms($aPerms)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (count($aPerms)) {
            $this->dbh->autocommit(false);

            foreach ($aPerms as $k => $v) {
                //  undelimit form value into perm name, moduleId
                $p = explode('^', $v);
                $query = "
                    DELETE FROM {$this->conf['table']['permission']}
                    WHERE name='{$p[0]}'
                    AND module_id = {$p[1]}";
                $ok = $this->dbh->query($query);

                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    function getPermsByUser($oUser)
    {
        return $this->getPermsByRoleId($oUser->role_id);
    }

    /**
     * Returns an array of permissions for the given role.
     *
     * @access public
     * @param integer $id   The id of the role to retrieve perms for
     * @return array        An array of permissions
     */
    function getPermsByRoleId($roleId = 0)
    {
        //  no logMessage allowed here
        $query = "  SELECT  permission_id
                    FROM    {$this->conf['table']['role_permission']}
                    WHERE   role_id = " . $roleId;

        $aRolePerms = $this->dbh->getCol($query);
        return $aRolePerms;
    }

    /**
     * Returns assoc array of all perms per given role id.
     *
     * @access  public
     * @param   int     $roleId         id of target role
     * @return  array   $aRolePerms     array of perms returned
     * @see     getPermsNotInRole()
     * @todo    merge with getPermsByRoleId() ?
     */
    function getPermNamesByRoleId($roleId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  rp.permission_id, p.name
            FROM    {$this->conf['table']['role_permission']} rp,
                    {$this->conf['table']['permission']} p
            WHERE   rp.permission_id = p.permission_id
            AND     role_id = $roleId
            ";

        $aRolePerms = $this->dbh->getAssoc($query);
        return $aRolePerms;
    }

    /**
     * Returns an array of permissions by user id.
     *
     * @param integer $userId
     * @return array    An array of permission ids
     */
    function getPermsByUserId($userId = 0)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  permission_id
            FROM    {$this->conf['table']['user_permission']}
            WHERE   usr_id = $userId
                ";
        $aUserPerms = $this->dbh->getCol($query);
        return $aUserPerms;
    }

    /**
     * Returns an assoc array of all perms.
     *
     * @access  public
     * @param   int     $moduleId   only select perms for one module
     * @param   int     $type       return type constant
     * @return  array   $aAllPerms  array of perms returned
     */
    function getPermsByModuleId($moduleId = '', $type = SGL_RET_ID_VALUE)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        switch ($type) {

        case SGL_RET_ARRAY:
            $filter = (!empty($moduleId))
                ? "  AND p.module_id = $moduleId"
                : '';
            $query = "
                SELECT permission_id, p.name, m.name AS module_name, p.module_id
                FROM    {$this->conf['table']['permission']} p,
                        {$this->conf['table']['module']} m
                WHERE p.module_id = m.module_id
                $filter
                ORDER BY name";
            $aAllPerms = $this->dbh->getAll($query, DB_FETCHMODE_ASSOC);
            break;

        case SGL_RET_ID_VALUE:
        default:
            $filter = (!empty($moduleId))
                ? "WHERE  module_id = $moduleId"
                : '';

            $query = "
                SELECT permission_id, name
                FROM {$this->conf['table']['permission']}
                $filter
                ORDER BY name";
            $aAllPerms = $this->dbh->getAssoc($query);
        }
        return $aAllPerms;
    }

    /**
     * Inserts permissions to the user_permission table.
     *
     * @access public
     * @param array $aRolePerms     An array of permission ids
     * @param integer $userId       The id of the user perms are being inserted for
     * @return boolean              True on success, PEAR error on failure
     */
    function addPermsByUserId($aRolePerms, $userId)
    {
        //  no logMessage allowed here
        if (count($aRolePerms)) {
            $this->dbh->autocommit(false);
            foreach ($aRolePerms as $permId) {
                $ok = $this->dbh->query('
                    INSERT INTO ' . $this->conf['table']['user_permission'] . '
                    (user_permission_id, usr_id, permission_id)
                    VALUES (' . $this->dbh->nextId($this->conf['table']['user_permission']) . ', ' . $userId . ", $permId)");
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Adds perms to the master set.
     *
     * Use when adding new modules
     *
     * @param array $aPerms A hash of perms, name => description
     * @param int $moduleId
     * @return boolean              True on success, PEAR error on failure
     */
    function addMasterPerms($aPerms, $moduleId)
    {
        if (count($aPerms)) {
            $this->dbh->autocommit(false);
            foreach ($aPerms as $name => $description) {
                $query = "
                    INSERT INTO {$this->conf['table']['permission']}
                        (permission_id, name, description, module_id)
                    VALUES (". $this->dbh->nextId($this->conf['table']['permission']) .
                        ", '$name', '$description', $moduleId)";
                $ok = $this->dbh->query($query);
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Deletes perms from the master set.
     *
     * Use when removing modules
     *
     * @param array $aPerms An array of perm names
     * @return boolean
     */
    function deleteMasterPerms($aPerms)
    {
        if (count($aPerms)) {
            $this->dbh->autocommit(false);
            foreach ($aPerms as $name) {
                $query = "DELETE FROM {$this->conf['table']['permission']} WHERE name = '$name'";
                $ok = $this->dbh->query($query);
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Deletes permissions for a given user.
     *
     * @access public
     * @param integer $userId       The id of the user perms are being deleted for
     * @return boolean              True on success, PEAR error on failure
     */
    function deletePermsByUserId($userId)
    {
        $query = "DELETE FROM {$this->conf['table']['user_permission']} WHERE usr_id = $userId";
        return $this->dbh->query($query);
    }

    /**
     * Deletes a permission given a user id and the perm id.
     *
     * @access public
     * @param integer $userId       The id of the user perms are being deleted for
     * @param integer $permId       The id of the perm to be deleted
     * @return boolean              True on success, PEAR error on failure
     */
    function deletePermByUserIdAndPermId($userId, $permId)
    {
        $query = "  DELETE FROM {$this->conf['table']['user_permission']}
                    WHERE usr_id = $userId
                    AND permission_id = $permId
        ";
        return $this->dbh->query($query);
    }

    /**
     * Like a 'difference' operation, returns the balance of getPermNamesByRoleId.
     *
     * Returns an assoc array of all users who are not in getPermNamesByRoleId(),
     * builds WHERE clause of role members to exclude,
     * only creates NOT IN clause if role is non-empty
     *
     * @access  public
     * @param   array   $aRolePerms     hash of perms to exclude
     * @return  array   $aOtherPerms    array of perms returned
     * @see     getPermNamesByRoleId()
     */
    function getPermsNotInRole($aRolePerms)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  p.permission_id, p.name
            FROM    {$this->conf['table']['permission']} p";

        if (count($aRolePerms)) {
            $whereClause = '';
            foreach ($aRolePerms as $key => $value) {
                $whereClause .= " $key NOT IN (p.permission_id) AND ";
            }
            $whereClause = substr($whereClause, 0, -4);
            $query .= " WHERE $whereClause";
        }
        $aOtherPerms = $this->dbh->getAssoc($query);
        return $aOtherPerms;
    }

    //  //////////////////////////////////////////////////
    //  /////////////////   PREFS   //////////////////////
    //  //////////////////////////////////////////////////

    /**
     * Returns an array of preferences for the given org.
     *
     * @access public
     * @param integer $orgId    The id of the org to retrieve preferences for
     * @return array            An array of preferences
     */
    function getUserPrefsByOrgId($orgId = 0, $type = SGL_RET_NAME_VALUE)
    {
        //  no logMessage allowed here
        switch ($type) {
        case SGL_RET_ID_VALUE:
            $term = 'op.preference_id';
            break;

        case SGL_RET_NAME_VALUE:
        default:
            $term = 'name';
        }

        $query = "
            SELECT  $term, value
            FROM    {$this->conf['table']['preference']} p,
                    {$this->conf['table']['org_preference']} op
            WHERE   p.preference_id = op.preference_id
            AND     op.organisation_id = " . $orgId;

        $aRes = $this->dbh->getAssoc($query);
        if (!DB::isError($aRes) && count($aRes)) {
            //  return default prefs if none exist for given org id
            return $aRes;
        } elseif ($orgId != 0) {
            return $this->getMasterPrefs($type);
        } else {
            SGL::raiseError('There was a db error, there are no prefs associated with the org',
                SGL_ERROR_NODATA);
        }
    }

    /**
     * Returns an array of preferences by user ID.
     *
     * If no arg is passed, zero is assumed which returns a default set
     * of name/value pref pairs.
     *
     * Theme preference is overriden from config for guests.
     *
     *   Array
     *   (
     *       [sessionTimeout] => 1800
     *       [timezone] => UTC
     *       [theme] => default
     *       [dateFormat] => UK
     *       [language] => fr-iso-8859-1
     *       [resPerPage] => 10
     *       [showExecutionTimes] => 1
     *       [locale] => en_GB
     *   )
     *
     * @access public
     *
     * @param integer $userId
     * @param boolean $addMissing
     *
     * @return array
     */
    function getPrefsByUserId($userId = 0, $addMissing = true)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT name, value
            FROM   {$this->conf['table']['preference']} AS p,
                   {$this->conf['table']['user_preference']} AS up
            WHERE  p.preference_id = up.preference_id
                   AND up.usr_id = " . intval($userId) . "
        ";
        $aUserPrefs = $this->dbh->getAssoc($query);

        // stop script execution on error
        if (PEAR::isError($aUserPrefs, DB_ERROR_NOSUCHTABLE)) {
            $msg = 'You have a Seagull database with no tables ...';
            SGL::raiseError($msg, SGL_ERROR_NODATA, PEAR_ERROR_DIE);
        } elseif (PEAR::isError($aUserPrefs)) {
            $msg = 'Unknown DB error occurred, pls file bug';
            SGL::raiseError($msg, SGL_ERROR_NODATA, PEAR_ERROR_DIE);
        }

        if ($addMissing) {
            $aMasterPrefs = $this->getMasterPrefs();
            foreach ($aMasterPrefs as $prefName => $prefValue) {
            	if (!array_key_exists($prefName, $aUserPrefs)) {
                    $aUserPrefs[$prefName] = $prefValue;
            	}
            }
        }
        if (SGL_Session::getRoleId() == SGL_GUEST) {
            $aUserPrefs['theme'] = SGL_Config::get('site.defaultTheme');
        }
        return $aUserPrefs;
    }

    /**
     * Gets master set of preferences, two return types available.
     *
     * @access  public
     * @param int   $type   Return type
     * @return  array       A hash of preference_id/name => default_value prefs
     */
    function getMasterPrefs($type = SGL_RET_NAME_VALUE)
    {
        //  no logMessage allowed here

        switch ($type) {
        case SGL_RET_ID_VALUE:
            $term = 'preference_id';
            break;

        case SGL_RET_NAME_VALUE:
        default:
            $term = 'name';
        }
        $query = "
            SELECT  $term, default_value
            FROM    {$this->conf['table']['preference']}";
        $aRes = $this->dbh->getAssoc($query);

        //  set default theme from config
        $key = ($type == SGL_RET_NAME_VALUE) ? 'theme' : 3;
        $c = &SGL_Config::singleton();
        $defaultTheme = $c->get(array('site' => 'defaultTheme'));
        $aRes[$key] = $defaultTheme;

        return $aRes;
    }

    /**
     * Get preferences mapping.
     *
     * returns similar:
        Array
        (
            [sessionTimeout] => 1
            [timezone] => 2
            [theme] => 3
            [dateFormat] => 4
            [language] => 5
            [resPerPage] => 6
            [showExecutionTimes] => 7
            [locale] => 8
        )

     *
     * @access  public
     * @return  array   An hash of preference id => name
     */
    function getPrefsMapping()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $query = "
            SELECT  preference_id, name
            FROM    {$this->conf['table']['preference']}";
        $aRes = $this->dbh->getAssoc($query);
        if (!PEAR::isError($aRes)) {
            return array_flip($aRes);
        } else {
            return $aRes;
        }
    }

    /**
     * Syncs the default preferences.
     *
     * @todo error checking, rename to resetPrefs
     */
    function syncDefaultPrefs()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->dbh->autocommit(false);
        $query1 = " DELETE FROM {$this->conf['table']['user_preference']}
                    WHERE usr_id = " . SGL_GUEST;
        $ok = $this->dbh->query($query1);
        if (PEAR::isError($ok)) {
            $this->dbh->rollBack();
            $this->dbh->autocommit(true);
            return $ok;
        }

        //  get master set of prefs
        $aPrefs = $this->getMasterPrefs(SGL_RET_ID_VALUE);
        if (PEAR::isError($aPrefs)) {
            $this->dbh->rollBack();
            $this->dbh->autocommit(true);
            return $aPrefs;
        }

        foreach ($aPrefs as $prefId => $prefValue) {
            $query2 ="
            INSERT INTO {$this->conf['table']['user_preference']}
                (   user_preference_id,
                    usr_id,
                    preference_id,
                    value)
            VALUES(" .
                    $this->dbh->nextId($this->conf['table']['user_preference']) . ', ' .
                    SGL_GUEST . ",
                    $prefId,
                    '$prefValue'
            )";
            $ok = $this->dbh->query($query2);
            if (PEAR::isError($ok)) {
                $this->dbh->rollBack();
                $this->dbh->autocommit(true);
                return $ok;
            }
        }
        $this->dbh->commit();
        $this->dbh->autocommit(true);
        return true;
    }

    /**
     * Inserts preferences to the user_preference table.
     *
     * @access public
     * @param array $aPrefs         An hash of preferences (prefId, prefValue)
     * @param integer $userId       The id of the user prefs are being inserted for
     * @return boolean              True on success, PEAR error on failure
     */
    function addPrefsByUserId($aPrefs, $userId)
    {
        if (count($aPrefs)) {
            $this->dbh->autocommit(false);
            foreach ($aPrefs as $prefId => $prefValue) {
                $ok = $this->dbh->query("
                    INSERT INTO {$this->conf['table']['user_preference']}
                    (user_preference_id, usr_id, preference_id, value)
                    VALUES (" . $this->dbh->nextId($this->conf['table']['user_preference']) . ', ' . $userId . ", $prefId, '$prefValue')");
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Updates user preferences.
     *
     * @access public
     *
     * @param array $aPrefs    hash of pref ID => value
     * @param integer $userId
     *
     * @return boolean
     */
    function updatePrefsByUserId($aPrefs, $userId)
    {
        $this->dbh->autocommit(false);

        $aMapping   = $this->getPrefsMapping();
        $aMapping   = array_flip($aMapping);
        $aUserPrefs = $this->getPrefsByUserId($userId, $addMissing = false);
        foreach ($aPrefs as $prefId => $prefValue) {
            $prefName = $aMapping[$prefId];
            // update preference
            if (array_key_exists($prefName, $aUserPrefs)) {
                $query = "
                    UPDATE {$this->conf['table']['user_preference']}
                    SET    value = " . $this->dbh->quoteSmart($prefValue) . "
                    WHERE  preference_id = " . intval($prefId) . "
                           AND usr_id = " . intval($userId) . "
                ";
            // add missing preference
            } else {
                $nextId = $this->dbh->nextId($this->conf['table']['user_preference']);
                $query = "
                    INSERT INTO {$this->conf['table']['user_preference']}
                    VALUES ($nextId,
                        " . intval($userId) . ",
                        " . intval($prefId) . ",
                        " . $this->dbh->quoteSmart($prefValue) . ")
                ";
            }
            $ok = $this->dbh->query($query);
            if (PEAR::isError($ok)) {
                $this->dbh->rollBack();
                $this->dbh->autocommit(true);
                return $ok;
            }
        }
        $this->dbh->commit();
        $this->dbh->autocommit(true);

        return true;
    }

    /**
     * Updates all preferences for users of a given role.
     *
     * @param string $name
     * @param string $value
     * @param integer $roleId
     * @return boolean
     */
    function updatePrefByRoleId($name, $value, $roleId)
    {
        //  get id for pref
        $aMap = $this->getPrefsMapping();
        $prefId = $aMap[$name];

        //  get list of users with for role
        $aUsers = $this->getUsersByRoleId($roleId);

        //  update all users' prefs
        $this->dbh->autocommit(false);
        $sth = $this->dbh->prepare("
            UPDATE {$this->conf['table']['user_preference']}
            SET value = '$value'
            WHERE preference_id = '$prefId'
            AND usr_id = ?
            ");
        foreach ($aUsers as $userId) {
            $ok = $this->dbh->execute($sth, $userId);

            if (PEAR::isError($ok)) {
                $this->dbh->rollBack();
                $this->dbh->autocommit(true);
                return $ok;
            }
        }
        $this->dbh->commit();
        $this->dbh->autocommit(true);
        return true;
    }

    /**
     * Adds new master preferences.
     *
     * Use when adding new modules
     *
     * @param array $aPrefs A hash of prefId => values
     * @return boolean
     * @TODO check for errors, wrap in transaction
     */
    function addMasterPrefs($aPrefs)
    {
        if (count($aPrefs)) {
            $this->dbh->autocommit(false);
            foreach ($aPrefs as $prefName => $prefValue) {
                $ok = $this->dbh->query("
                    INSERT INTO {$this->conf['table']['preference']}
                    (preference_id, name, default_value)
                    VALUES (" . $this->dbh->nextId($this->conf['table']['preference']) . ",
                    '$prefName', '$prefValue')");
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Updates master preferences.
     *
     * @param array $aPrefs A hash of prefId => values
     * @return boolean
     * @TODO check for errors, wrap in transaction
     */
    function updateMasterPrefs($aPrefs)
    {
        if (count($aPrefs)) {
            $this->dbh->autocommit(false);
            foreach ($aPrefs as $prefName => $prefValue) {
                $ok = $this->dbh->query("
                    UPDATE {$this->conf['table']['preference']}
                    SET default_value = '$prefValue'
                    WHERE name = '$prefName'");
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Complement of addMasterPrefs().
     *
     * @param array $aPrefs An array of pref names
     * @return boolean
     */
    function deleteMasterPrefs($aPrefs)
    {
        if (count($aPrefs)) {
            $this->dbh->autocommit(false);
            foreach ($aPrefs as $pref) {
                $query = "DELETE FROM {$this->conf['table']['preference']} WHERE name = '$pref'";
                $ok = $this->dbh->query($query);
                if (PEAR::isError($ok)) {
                    $this->dbh->rollBack();
                    $this->dbh->autocommit(true);
                    return $ok;
                }
            }
            $this->dbh->commit();
            $this->dbh->autocommit(true);
        }
        return true;
    }

    /**
     * Deletes preferences for a given user.
     *
     * @access public
     * @param integer $userId       The id of the user preferences are being deleted for
     * @return boolean              True on success, PEAR error on failure
     */
    function deletePrefsByUserId($userId)
    {
        $query = "DELETE FROM {$this->conf['table']['user_preference']} WHERE usr_id = " . $userId;
        return $this->dbh->query($query);
    }


    //  //////////////////////////////////////////////////
    //  /////////////////   ROLES   //////////////////////
    //  //////////////////////////////////////////////////


    /**
     * Returns an assoc array of all roles.
     *
     * @access  public
     * @param   boolean $bExcludeGuest  whether admin should be excluded
     * @return  array   $aAllRoles      array of roles returned
     */
    function getRoles($bExcludeRoot = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $whereClause = ($bExcludeRoot) ? ' AND role_id <> ' . SGL_ADMIN : '';

        $query = "
            SELECT role_id, name
            FROM    " . $this->conf['table']['role'] . "
            WHERE  role_id <> " . SGL_GUEST . "
            AND    role_id <> " . SGL_UNASSIGNED .
            $whereClause;
        $aAllRoles = $this->dbh->getAssoc($query);

        //  remove roles that have no perms set
        foreach ($aAllRoles as $roleId => $name) {
            if ($roleId === SGL_ADMIN) {
                continue;
            }
            $query =
                'SELECT COUNT(*) FROM ' . $this->conf['table']['role_permission'] .
                ' WHERE role_id =' . $roleId;
            $count = $this->dbh->getOne($query);
            if ($count < 1) {
                unset($aAllRoles[$roleId]);
            }
        }
        return $aAllRoles;
    }

    function getRoleNameById($id)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT name
            FROM    " . $this->conf['table']['role'] . "
            WHERE  role_id = " . $id;
        return $this->dbh->getOne($query);
    }

    /**
     * Returns a string of all emails per given group.
     *
     * @access  public
     * @param   int     $gid            id of target group
     * @return  string  $emailList      role's emails
     */
    function getEmailsByRole($rid)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  usr_id, email
            FROM    " . $this->conf['table']['user'] . "
            WHERE   role_id = $rid
                ";
        $emailList = implode(';', $this->dbh->getAssoc($query));
        return $emailList;
    }

    /**
     * Returns an array of user ids.
     *
     * @param integer $roleId
     * @return array
     */
    function getUsersByRoleId($roleId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $query = "
            SELECT  usr_id
            FROM    {$this->conf['table']['user']}
            WHERE   role_id = " . $roleId;

        $aRoleUsers = $this->dbh->getCol($query);
        return $aRoleUsers;
    }

    /**
     * Returns an array of user ids.
     *
     * @param integer $orgId
     * @return array
     */
    function getUsersByOrgId($orgId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $query = "
            SELECT  usr_id
            FROM    {$this->conf['table']['user']}
            WHERE   organisation_id = " . $orgId;

        $aOrgUsers = $this->dbh->getCol($query);
        return $aOrgUsers;
    }

    /**
     * Updates role-permission assignments.
     *
     * @access  public
     * @param   array       $aPerms array of perms to add/remove
     * @param   string      $roleId role ID to associate permissions with
     * @param   constant    action  whether to add/remove perm
     * @return  void
     */
    function updateRolePermissionAssocs($aPerms, $roleId, $action)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if ($action == SGL_ROLE_REMOVE) {
            foreach ($aPerms as $permId => $permName) {
                $this->dbh->query('
                    DELETE FROM ' . $this->conf['table']['role_permission'] . "
                    WHERE   permission_id = $permId
                    AND     role_id = $roleId");
            }
        } else {
            //  add perms
            foreach ($aPerms as $permId => $permName) {
                $this->dbh->query('
                    INSERT INTO ' . $this->conf['table']['role_permission'] . "
                        (role_permission_id, role_id, permission_id)
                    VALUES (" . $this->dbh->nextId($this->conf['table']['role_permission']) . ", $roleId, $permId)");
            }
        }
    }


    //  //////////////////////////////////////////////////
    //  /////////////////   ORGS   //////////////////////
    //  //////////////////////////////////////////////////

    /**
     * Returns all organisations.
     *
     * @return array $aAllOrgs
     */
    function getOrgs()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT organisation_id, name
            FROM    " . $this->conf['table']['organisation'];
        $aAllOrgs = $this->dbh->getAssoc($query);
        return $aAllOrgs;
    }

    /**
     * Returns an organisation by org id.
     *
     * @param integer $orgId
     * @return array $aOrg
     */
    function getOrgById($orgId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  *
            FROM    {$this->conf['table']['organisation']}
            WHERE   organisation_id = " . $orgId;

        $aOrg = $this->dbh->getRow($query);
        return $aOrg;
    }

    /**
     * Returns all organisations by role id.
     *
     * @param integer $roleId
     * @return array    An array of org ids
     */
    function getOrgsByRoleId($roleId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $query = "
            SELECT  organisation_id
            FROM    {$this->conf['table']['organisation']}
            WHERE   role_id = " . $roleId;

        $aRoleOrgs = $this->dbh->getCol($query);
        return $aRoleOrgs;
    }

    /**
     * Returns an organisation name by org id.
     *
     * @param integer $orgId
     * @return string $orgName
     */
    function getOrgNameById($orgId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  name
            FROM    {$this->conf['table']['organisation']}
            WHERE   organisation_id = " . $orgId;

        $orgName = $this->dbh->getOne($query);
        return $orgName;
    }

    /**
     * Returns a hash or organisation types.
     *
     * @return array    An array of org id => names
     */
    function getOrgTypes()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT organisation_type_id, name
            FROM {$this->conf['table']['organisation_type']}";
        $aAllTypes = $this->dbh->getAssoc($query);

        //  set the zeroeth element as 'default'
        //  done in code rather than default data
        //  to simplified optional use of 'org types'
        array_unshift($aAllTypes, 'default');
        return $aAllTypes;
    }


    /**
     * Determines if a username is unique.
     *
     * @param string $username
     * @return boolean
     * @todo get rid of DataObject
     */
    function isUniqueUsername($username)
    {
        if (isset($username)) {
            $numRows = $this->dbh->getOne("
                SELECT COUNT(usr_id)
                FROM {$this->conf['table']['user']}
                WHERE username = ".$this->dbh->quoteSmart($username));

            //  return false if any rows found
            $ret = (boolean)$numRows == 0;
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Determines if an email is unique.
     *
     * @param string $email
     * @return boolean
     * @todo get rid of DataObject
     */
    function isUniqueEmail($email)
    {
        if (isset($email)) {
            $numRows = $this->dbh->getOne("
                SELECT COUNT(usr_id)
                FROM {$this->conf['table']['user']}
                WHERE email = ".$this->dbh->quoteSmart($email));

            //  return false if any rows found
            $ret = (boolean)$numRows == 0;
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Returns the datetime of last login.
     *
     * @param integer $userId
     * @return string   Datetime of login
     */
    function getLastLogin($userId = null)
    {
        $id = (is_null($userId)) ? SGL_Session::getUid() : $userId;
        $query = "
            SELECT date_time AS last_login
            FROM  {$this->conf['table']['login']}
            WHERE usr_id = " . $id . '
            ORDER BY date_time DESC';

        //  grab penultimate record
        $res = $this->dbh->limitQuery($query, 1, 1);
        $res->fetchInto($login);
        return $login;
    }

    //OrgPreferenceMgr::_updateAll

    /**
     * Returns UID if valid cookie exists, otherwise null.
     *
     * @param string $username
     * @param string $cookie
     *
     * @return integer
     */
    function getUserIdByCookie($username, $cookie)
    {
        $query = "
            SELECT  u.usr_id
            FROM    {$this->conf['table']['user']} u,
                    {$this->conf['table']['user_cookie']} uc
            WHERE   u.username = " . $this->dbh->quoteSmart($username) . "
                      AND u.is_acct_active = 1
                      AND uc.usr_id = u.usr_id
                      AND uc.cookie_name = " . $this->dbh->quoteSmart($cookie);
        $uid = $this->dbh->getOne($query);
        return $uid;
    }

    /**
     * Deletes all 'remember me' cookies by UID.
     *
     * @param integer $userId
     * @param string  $constraint
     *
     * @return void
     */
    function deleteUserLoginCookiesByUserId($userId, $constraint = '')
    {
        $query = "
            DELETE FROM {$this->conf['table']['user_cookie']}
            WHERE  usr_id = " . intval($userId) . $constraint;
        return $this->dbh->query($query);
    }

    /**
     * Delete login cookie.
     *
     * @param integer $userId
     * @param string  $cookieValue
     *
     * @return void
     */
    function deleteUserLoginCookieByUserId($userId, $cookieValue)
    {
        $constraint = ' AND cookie_name = ' . $this->dbh->quoteSmart($cookieValue);
        return $this->deleteUserLoginCookiesByUserId($userId, $constraint);
    }

    /**
     * Deletes obsolete cookies.
     *
     * @param integer $expirePeriod  validity period in seconds
     *
     * @return void
     */
    function deleteExpiredUserLoginCookies($expirePeriod = null)
    {
        if (empty($expirePeriod)) {
            $expirePeriod = 60 * 60 * 24 * 30 * 12; /* ~ 1 year */
        }
        $startTime  = time() - $expirePeriod;
        $expireDate = strftime("%Y-%m-%d %H:%M:%S", $startTime);

        $query = "
            DELETE FROM {$this->conf['table']['user_cookie']}
            WHERE  login_time < '$expireDate'
        ";
        return $this->dbh->query($query);
    }

    /**
     * Add 'remember me' cookie.
     *
     * @param integer $uid
     * @param string  $cookieValue
     *
     * @return void
     */
    function addUserLoginCookie($uid, $cookieValue)
    {
        $now   = SGL_Date::getTime();
        $query = "
            INSERT INTO {$this->conf['table']['user_cookie']}
            VALUES (
                " . intval($uid) . ",
                " . $this->dbh->quoteSmart($cookieValue) . ",
                '" . $now . "')";
        $ok = $this->dbh->query($query);
        return $ok;
    }
}
?>