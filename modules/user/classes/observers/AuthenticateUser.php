<?php
class AuthenticateUser extends SGL_Observer
{
    function update($observable)
    {
        //  authenticate user according to settings
        $this->conf = $observable->conf;
        if ($this->conf['RegisterMgr']['autoLogin']) {
            $observable->input->username = $observable->input->user->username;
            $observable->input->password = $observable->input->user->passwd;
            $oLogin = new LoginMgr();
            $oLogin->_cmd_login($observable->input, $observable->output);
        }
    }
}
?>