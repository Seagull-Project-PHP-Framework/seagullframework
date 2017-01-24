<?php

/**
 * User2 data access object.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class User2DAO extends SGL_Manager
{
    /**
     * Returns a singleton User2DAO instance.
     *
     * @return User2DAO
     */
    public static function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    // -------------
    // --- Users ---
    // -------------

    public function getUserById($userId)
    {
        $query = "
            SELECT *
            FROM   usr
            WHERE  usr_id = " . intval($userId) . "
        ";
        return $this->dbh->getRow($query);
    }

    public function getUserIdByUsername($username, $email = null)
    {
        $oUser = $this->getUserByUsername($username, $email);
        return !empty($oUser) ? $oUser->usr_id : false;
    }

    public function getUserByUsername($username, $email = null)
    {
        $constrant = '';
        if (!empty($email)) {
            $constrant = ' AND email = ' . $this->dbh->quoteSmart($email);
        }
        $query = "
            SELECT *
            FROM   usr
            WHERE  username = " . $this->dbh->quoteSmart($username) . "
                   $constrant
        ";
        return $this->dbh->getRow($query);
    }

    public function addUser($aFields, $isAcctActive = true,
        $roleId = SGL_MEMBER, $createdBy = SGL_ADMIN)
    {
        $tableName = $this->conf['table']['user'];

        $aFields['usr_id']         = $this->dbh->nextId($tableName);
        $aFields['is_acct_active'] = $isAcctActive;
        $aFields['role_id']        = $roleId;
        $aFields['date_created']   = SGL_Date::getTime($gmt = true);
        $aFields['created_by']     = $createdBy;
        $aFields['last_updated']   = $aFields['date_created'];
        $aFields['updated_by']     = $aFields['created_by'];

        $ret = $this->dbh->autoExecute($tableName, $aFields, DB_AUTOQUERY_INSERT);
        if (!PEAR::isError($ret)) {
            $ret = $aFields['usr_id'];
        }
        return $ret;
    }

    public function updateUserById($userId, $aFields)
    {
        return $this->dbh->autoExecute('usr', $aFields,
            DB_AUTOQUERY_UPDATE, 'usr_id = ' . intval($userId));
    }

    public function updatePasswordByUserId($userId, $password)
    {
        return $this->updateUserById($userId, array('passwd' => md5($password)));
    }

    public function getUsers()
    {
        $query = "
            SELECT * FROM usr WHERE role_id > " . SGL_GUEST . "
        ";
        return $this->dbh->getAll($query);
    }

    public function findUsersByPattern($q, $includeFirstnameLastname = true)
    {
        $query = "
            SELECT username, email, first_name, last_name
            FROM   usr
            WHERE  username LIKE '%" . $this->dbh->escapeSimple($q) . "%'
        ";
        if ($includeFirstnameLastname) {
            $query .= " OR first_name LIKE '%" . $this->dbh->escapeSimple($q) . "%'";
            $query .= " OR last_name LIKE '%" . $this->dbh->escapeSimple($q) . "%'";
        }
        return $this->dbh->getAll($query);
    }

    // -------------------
    // --- Preferences ---
    // -------------------

    /**
     * Retuns array of master prefs.
     *
     * Array
     * (
     *     [1] => 1800
     *     [2] => UTC
     *     [3] => default
     *     [4] => UK
     *     [5] => ru-utf-8
     *     [6] => 10
     *     [7] => 1
     *     [8] => en_GB
     * )
     *
     * @return array
     */
    public function getMasterPrefs()
    {
        $query = "
            SELECT preference_id, default_value
            FROM   " . SGL_Config::get('table.preference');
        return $this->dbh->getAssoc($query);
    }

    /**
     * Returns assoc array.
     *
     * Array
     * (
     *     [admin theme] => 9
     *     [sessionTimeout] => 1
     *     [timezone] => 2
     *     [theme] => 3
     *     [dateFormat] => 4
     *     [language] => 5
     *     [resPerPage] => 6
     *     [showExecutionTimes] => 7
     *     [locale] => 8
     * )
     *
     * @return array
     */
    public function getPrefsMapping()
    {
        $query = "
            SELECT  preference_id, name
            FROM    " . SGL_Config::get('table.preference');
        $aRet = $this->dbh->getAssoc($query);
        if (!PEAR::isError($aRet)) {
            $aRet = array_flip($aRet);
        }
        return $aRet;
    }

    /**
     * Add preferences to user.
     *
     * @param integer $userId
     * @param array $aPrefs
     *
     * @return boolean
     */
    public function addPrefsByUserId($userId, array $aPrefs)
    {
        $tableName = SGL_Config::get('table.user_preference');
        $ret       = true;
        foreach ($aPrefs as $prefId => $prefValue) {
            $aFields = array(
                'user_preference_id' => $this->dbh->nextId($tableName),
                'usr_id'             => $userId,
                'preference_id'      => $prefId,
                'value'              => $prefValue
            );
            $ret = $this->dbh->autoExecute($tableName, $aFields,
                DB_AUTOQUERY_INSERT);
            if (PEAR::isError($ret)) {
                break;
            }
        }
        return $ret;
    }

    /**
     * Add master preference to user.
     *
     * @param integer $userId
     * @param array $aPrefsOverride
     *
     * @return boolean
     */
    public function addMasterPrefsByUserId($userId, array $aPrefsOverride = array())
    {
        $aPrefs    = $this->getMasterPrefs();
        $aPrefsMap = $this->getPrefsMapping();
        // override master prefs
        foreach ($aPrefsOverride as $prefName => $prefValue) {
            $prefId = $aPrefsMap[$prefName];
            $aPrefs[$prefId] = $prefValue;
        }
        return $this->addPrefsByUserId($userId, $aPrefs);
    }

    public function getPreferencesByUserId($userId)
    {
        $aPrefsMap = $this->getPrefsMapping();
        $aPrefs    = $this->getRawPreferencesByUserId($userId);
        $aRet      = array();
        foreach ($aPrefs as $prefId => $prefValue) {
            $prefName = array_search($prefId, $aPrefsMap);
            if (false !== $prefName) {
                $aRet[$prefName] = $prefValue;
            }
        }
        return $aRet;
    }

    public function getRawPreferencesByUserId($userId)
    {
        $tableName = SGL_Config::get('table.user_preference');
        $query     = "
            SELECT preference_id, value
            FROM   $tableName
            WHERE  usr_id = " . intval($userId) . "
        ";
        return $this->dbh->getAssoc($query);
    }

    public function updatePreferencesByUserId($userId, array $aPrefs)
    {
        $aPrefsMap = $this->getPrefsMapping();
        $aData     = array();
        foreach ($aPrefs as $prefName => $prefValue) {
        	if (isset($aPrefsMap[$prefName])) {
        	    $prefId = $aPrefsMap[$prefName];
        	    $aData[$prefId] = $prefValue;
        	}
        }
        return $this->updateRawPreferencesByUserId($userId, $aData);
    }

    public function updateRawPreferencesByUserId($userId, array $aPrefs)
    {
        $tableName = SGL_Config::get('table.user_preference');
        $ret       = true;
        foreach ($aPrefs as $prefId => $prefValue) {
            $where = "usr_id = " . intval($userId)
                . " AND preference_id = " . intval($prefId);
            $ret = $this->dbh->autoExecute($tableName,
                array('value' => $prefValue), DB_AUTOQUERY_UPDATE, $where);
            if (PEAR::isError($ret)) {
                break;
            }
        }
        return $ret;
    }

    // ---------------------
    // --- Password hash ---
    // ---------------------

    public function getPasswordHashByUserIdAndHash($userId, $hash)
    {
        $query = "
            SELECT *
            FROM   user_passwd_hash
            WHERE  usr_id = " . intval($userId) . "
                   AND hash = " . $this->dbh->quoteSmart($hash) . "
        ";
        return $this->dbh->getRow($query);
    }

    public function addPasswordHash($userId, $hash)
    {
        $aFields = array(
            'user_passwd_hash_id' => $this->dbh->nextId('user_passwd_hash'),
            'usr_id'              => $userId,
            'hash'                => $hash,
            'date_created'        => SGL_Date::getTime($gmt = true)
        );
        $ok = $this->dbh->autoExecute('user_passwd_hash', $aFields,
            DB_AUTOQUERY_INSERT);
        $ret = PEAR::isError($ok)
            ? $ok
            : $aFields['user_passwd_hash_id'];
        return $ret;
    }

    public function deletePasswordHashByUserId($userId)
    {
        $query = "
            DELETE FROM user_passwd_hash
            WHERE  usr_id = " . intval($userId) . "
        ";
        return $this->dbh->query($query);
    }

    // -------------
    // --- Media ---
    // -------------

    public function getProfileImageByUserId($userId)
    {
        $query = "
            SELECT    m.*
            FROM      media AS m,
                      usr AS u,
                      media_type AS mt
            WHERE     mt.name = 'profile'
                      AND mt.media_type_id = m.media_type_id
                      AND m.fk_id = u.usr_id
                      AND u.usr_id = " . intval($userId) . "
            ORDER BY  m.date_created DESC
        ";
        $query = $this->dbh->modifyLimitQuery($query, 0, 1);
        return $this->dbh->getRow($query);
    }

    // -----------------
    // --- Addresses ---
    // -----------------

    public function getAddressByUserId($userId, $addressType = 'home')
    {
        $constraint = '';
        if (!empty($addressType)) {
            $constraint = ' AND ua.address_type = ' . $this->dbh->quoteSmart($addressType);
        }
        $query = "
            SELECT a.*
            FROM   `user-address` ua
            INNER JOIN `address` a ON ua.address_id = a.address_id
            WHERE
                ua.usr_id = " . intval($userId) . "
                $constraint
        ";
        return $this->dbh->getRow($query);
    }

    public function getAddressesByUserIdAndType($userId, $aAddressType = array())
    {
        if (!is_array($aAddressType)) {
            $aAddressType = (array)$aAddressType;
        }

        $constraint = '';
        if (!empty($aAddressType)) {
            $aTmp = array();
            foreach ($aAddressType as $addressType) {
                $aTmp[] = 'ua.address_type = ' . $this->dbh->quoteSmart($addressType);
            }
            if (!empty($aTmp)) {
                $constraint = ' AND ('.implode(' OR ', $aTmp).')';
            }
        }

        $query = "
            SELECT a.*
            FROM   `user-address` ua
            INNER JOIN `address` a ON ua.address_id = a.address_id
            WHERE
                ua.usr_id = " . intval($userId) . "
                $constraint
        ";
        return $this->dbh->getAssoc($query);
    }

    public function addAddress($userId, $aFields, $addressType = 'home')
    {
        $aAllowedFields = array('address1', 'address2', 'city', 'state',
            'post_code', 'country');
        foreach (array_keys($aFields) as $k) {
            if (!in_array($k, $aAllowedFields)) {
                unset($aFields[$k]);
            }
        }
        $aFields['address_id'] = $this->dbh->nextId('address');
        $success = $this->dbh->autoExecute('address', $aFields,
            DB_AUTOQUERY_INSERT);

        if (PEAR::isError($success)) {
            return $success;
        }

        $assocFields = array(
            'usr_id'     => $userId,
            'address_id' => $aFields['address_id'],
        );

        if (!empty($addressType)) {
            $assocFields['address_type'] = $addressType;
        }

        $success = $this->dbh->autoExecute('`user-address`', $assocFields,
            DB_AUTOQUERY_INSERT);

        if (PEAR::isError($success)) {
            return $success;
        }

        return $aFields['address_id'];
    }

    public function updateAddressById($addressId, $aFields)
    {
        $aAllowedFields = array('address1', 'address2', 'city', 'state',
            'post_code', 'country');
        foreach (array_keys($aFields) as $k) {
            if (!in_array($k, $aAllowedFields)) {
                unset($aFields[$k]);
            }
        }
        return $this->dbh->autoExecute('address', $aFields,
            DB_AUTOQUERY_UPDATE, 'address_id = ' . intval($addressId));
    }
}
?>