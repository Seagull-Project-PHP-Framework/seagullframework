<?php

/**
 * Authenticates user.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class AuthenticateUser extends SGL_Observer
{
    public function __construct()
    {
        $this->dbh = SGL_DB::singleton();
    }

    public function update($observable)
    {
        $this->conf = $observable->conf;

        if (SGL_Config::get('cookie.rememberMeEnabled') &&
            !empty($observable->rememberMeIsActive))
        {
            $observable->input->user->rememberme = true;
        }

        $rememberme = !empty($observable->input->user->rememberme) ? true : false;
        $ret        = true;
        $oUser      = $this->_getUser(
            $observable->input->user->username,
            $observable->input->user->password
        );
        if (PEAR::isError($oUser)) {
            $ret = $oUser;
        } elseif (!empty($oUser)) {
            new SGL_Session($oUser->usr_id, $rememberme);
        }
        return $ret;
    }

    private function _getUser($user, $pass)
    {
        $query = '
            SELECT  usr_id, role_id
            FROM    ' . $this->conf['table']['user'] . '
            WHERE   username = ' . $this->dbh->quoteSmart($user) . '
                    AND passwd = \'' . md5($pass) . '\'
                    AND is_acct_active = 1
        ';
        $oUser = $this->dbh->getRow($query);
        return $oUser;
    }
}
?>