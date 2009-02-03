<?php

require_once SGL_CORE_DIR . '/Emailer2.php';
require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';
require_once 'Text/Password.php';

/**
 * Creates new user.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class CreateUser extends SGL_Observer
{
    public function __construct()
    {
        $this->da = User2DAO::singleton();
    }

    public function update($observable)
    {
        $this->conf = $observable->conf;

        // set pass
        $password = !empty($observable->input->user->password)
            ? $observable->input->user->password
            // generate temporary password
            : $password = $this->_generatePassword();
        $observable->input->user->password = $password;
        $observable->input->user->passwd   = md5($password);

        // clean injection
        $observable->input->user->email = SGL_Emailer2::cleanMailInjection(
            $observable->input->user->email);

        $errorCount = SGL_Error::count();

        $aPrefs = array(
            'language' => SGL::getCurrentLang() . '-' . SGL::getCurrentCharset()
        );
        $aFields = array(
            'username'   => $observable->input->user->username,
            'passwd'     => $observable->input->user->passwd,
            'email'      => $observable->input->user->email,
            'first_name' => $observable->input->user->first_name,
            'last_name'  => $observable->input->user->last_name,
        );
        $userId = $this->da->addUser($aFields);
        $ok     = $this->da->addMasterPrefsByUserId($userId, $aPrefs);

        // we need to commit transaction if no errors happened
        if (SGL_Error::count() == $errorCount) {
            $this->da->dbh->commit();
        }

        $observable->input->userId = $userId;

        return $ok;
    }

    private function _generatePassword()
    {
        $oPassword = new Text_Password();
        return $oPassword->create();
    }
}
?>