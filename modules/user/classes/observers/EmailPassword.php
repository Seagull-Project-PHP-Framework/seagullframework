<?php
require_once SGL_MOD_DIR . '/user/classes/PasswordMgr.php';

class EmailPassword extends SGL_Observer
{
    function update($observable)
    {
        // email password according to form
        if ($observable->input->passwdResetNotify) {
            $bEmailSent = PasswordMgr::sendPassword($observable->oUser, $observable->input->password);
            if (!$bEmailSent) {
                return SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
            }
        }
    }
}
?>
