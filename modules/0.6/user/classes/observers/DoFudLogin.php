<?php
class DoFudLogin extends SGL_Observer
{
    function update($observable)
    {
        require SGL_MOD_DIR . '/forum/scripts/forum_login.php';

        // Get the user id from the current session
        $uid = SGL_Session::getUid();
        $ok = external_fud_login($uid);
    }
}
?>