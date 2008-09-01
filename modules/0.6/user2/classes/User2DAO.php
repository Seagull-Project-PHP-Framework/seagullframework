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

    public function updateUserById($userId, $aFields)
    {
        return $this->dbh->autoExecute('usr', $aFields,
            DB_AUTOQUERY_UPDATE, 'usr_id = ' . intval($userId));
    }

    public function updatePasswordByUserId($userId, $password)
    {
        $query = "
            UPDATE usr
            SET    passwd = " . $this->dbh->quoteSmart(md5($password)) . "
            WHERE  usr_id = " . intval($userId) . "
        ";
        return $this->dbh->query($query);
    }

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