<?php

require_once SGL_CORE_DIR . '/Emailer2.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once 'DB/DataObject.php';
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
        $this->dbh = SGL_DB::singleton();
        $this->da  = UserDAO::singleton();
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

        $oUser = DB_DataObject::factory($this->conf['table']['user']);
        $oUser->setFrom($observable->input->user);

        $oUser->usr_id         = $this->dbh->nextId($this->conf['table']['user']);
        $oUser->role_id        = SGL_MEMBER;
        $oUser->date_created   = SGL_Date::getTime($gmt = true);
        $oUser->last_updated   = SGL_Date::getTime($gmt = true);
        $oUser->created_by     = SGL_ADMIN;
        $oUser->updated_by     = SGL_ADMIN;
        $oUser->is_acct_active = 1;
        $ok = $oUser->insert();

        $observable->input->userId = $oUser->usr_id;

        if ($ok) {
            $aPrefs['language'] = SGL::getCurrentLang() . '-' . SGL::getCurrentCharset();
            $ok = $this->_createPreferences($oUser->usr_id, $aPrefs);
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