<?php
class EmailConfirmation extends SGL_Observer
{
    function update($observable)
    {
        //  send email confirmation according to config
        $this->conf = $observable->conf;
        if ($this->conf['RegisterMgr']['sendEmailConfUser']) {
            $bEmailSent = $this->_sendEmail($observable->oUser, $observable->input->moduleName);
            if (!$bEmailSent) {
                return SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
            }
        }
    }

    function _sendEmail($oUser, $moduleName)
    {
        require_once SGL_CORE_DIR . '/Emailer.php';

        $realName = $oUser->first_name . ' ' . $oUser->last_name;
        $recipientName = (trim($realName)) ? $realName : '&lt;no name supplied&gt;';
        $options = array(
                'toEmail'       => $oUser->email,
                'toRealName'    => $recipientName,
                'fromEmail'     => $this->conf['email']['admin'],
                'replyTo'       => $this->conf['email']['admin'],
                'subject'       => 'Thanks for registering at ' . $this->conf['site']['name'],
                'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/' .
                    $moduleName . '/email_registration_thanks.php',
                'username'      => $oUser->username,
                'password'      => $oUser->passwdClear,
        );

        $message = & new SGL_Emailer($options);
        $message->prepare();
        $message->send();

        //  conf to admin
        if ($this->conf['RegisterMgr']['sendEmailConfAdmin']) {
            $options = array(
                    'toEmail'       => $this->conf['email']['admin'],
                    'toRealName'    => 'Admin',
                    'fromEmail'     => $this->conf['email']['admin'],
                    'replyTo'       => $this->conf['email']['admin'],
                    'subject'       => 'New Registration at ' . $this->conf['site']['name'],
                    'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/' .
                        $moduleName . '/email_registration_admin.php',
                    'username'      => $oUser->username,
                    'activationUrl' => SGL_Output::makeUrl('list', 'user', 'user'),
            );
            $notification = & new SGL_Emailer($options);
            $notification->prepare();
            $notification->send();
        }
        //  check error stack
        return (SGL_Error::count()) ? false : true;
    }
}
?>