<?php

require_once SGL_CORE_DIR . '/Emailer2.php';
require_once SGL_CORE_DIR . '/Delegator.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
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
        $this->da = new SGL_Delegator();
        $this->da->add(User2DAO::singleton());
        $this->da->add(UserDAO::singleton());
    }

    public function update($observable)
    {
        $this->conf = $observable->conf;

        // set pass
        $password                          = $this->_generatePassword();
        $observable->input->user->password = $password;
        $observable->input->user->passwd   = md5($password);

        // clean injection
        $observable->input->user->email = SGL_Emailer2::cleanMailInjection(
            $observable->input->user->email);

        // @todo removeit
        $observable->input->userId = null;

        $aFields = array(
            'username'   => $observable->input->user->username,
            'passwd'     => $observable->input->user->passwd,
            'email'      => $observable->input->user->email,
            'first_name' => $observable->input->user->first_name,
            'last_name'  => $observable->input->user->last_name,
            'created_by' => SGL_ADMIN,
        );
        $userId = $this->da->addUser($aFields);

        if (!PEAR::isError($userId)) {
            $observable->input->userId = $userId;
            $aPrefs['language'] = SGL::getCurrentLang() . '-' . SGL::getCurrentCharset();
            $ok = $this->_createPreferences($userId, $aPrefs);
        }
        return $ok;
    }

    private function _generatePassword()
    {
        $oPassword = new Text_Password();
        return $oPassword->create();
    }

    private function _createPreferences($userId, array $aUserPrefs)
    {
        $aPrefs    = $this->da->getMasterPrefs(SGL_RET_ID_VALUE);
        $aPrefsMap = $this->da->getPrefsMapping();
        foreach ($aUserPrefs as $prefName => $prefValue) {
            $prefId = $aPrefsMap[$prefName];
            $aPrefs[$prefId] = $prefValue;
        }
        if (!PEAR::isError($aPrefs)) {
            $ok = $this->da->addPrefsByUserId($aPrefs, $userId);
        } else {
            $ok = $aPrefs;
        }
        return $ok;
    }
}
?>