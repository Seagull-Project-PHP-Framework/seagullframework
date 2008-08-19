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
        $constrant = '';
        if (!empty($email)) {
            $constrant = ' AND email = ' . $this->dbh->quoteSmart($email);
        }
        $query = "
            SELECT usr_id
            FROM   usr
            WHERE  username = " . $this->dbh->quoteSmart($username) . "
                   $constrant
        ";
        return $this->dbh->getOne($query);
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
}
?>