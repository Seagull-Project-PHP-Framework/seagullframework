<?php

/**
 * Generates key.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class GenerateHashKey extends SGL_Observer
{
    public function __construct()
    {
        $this->da = User2DAO::singleton();
    }

    public function update($observable)
    {
        $userId = $observable->input->userId;
//        $ip     = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
//        $agent  = isset($_SERVER['HTTP_USER_AGENT'])
//            ? $_SERVER['HTTP_USER_AGENT'] : '';
//        $lang   = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
//            ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        $salt   = SGL_Config::get('PasswordRecoveryMgr.salt');
        $hash   = md5(uniqid($userId . $salt, true));

        $ok = $this->da->deletePasswordHashByUserId($userId);
        $ok = $this->da->addPasswordHash($userId, $hash);

        $observable->input->hash = $hash;

        return $ok;
    }
}
?>