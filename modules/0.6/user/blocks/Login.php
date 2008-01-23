<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | LoginBlock.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Werner M. Krauss <werner.krauss@hallstatt.net>                    |
// +---------------------------------------------------------------------------+

/**
 * User / Login Block.
 *
 * Shows login form if not logged in, user data (username and "session started at")
 * if logged in
 *
 * @package block
 * @author  Werner M. Krauss <werner.krauss@hallstatt.net>
 */
class User_Block_Login
{

    function init($output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->uid = isset($output->loggedOnUserID) ? $output->loggedOnUserID : '';
        $this->username = isset($output->loggedOnUser) ? $output->loggedOnUser : '';
        $this->startTime = isset($output->loggedOnSince) ? $output->loggedOnSince : '';

        return $this->getBlockContent($output);
    }

    function getBlockContent($output)
    {
        if ($this->uid == SGL_GUEST) {
            return $this->getLoginScreen($output);
        } else {
            return $this->getLogoutScreen();
        }
    }

    function getLoginScreen($output)
    {

        if (isset($output->conf['tuples']['demoMode']) && $output->conf['tuples']['demoMode'] == true) {
            $username = 'admin';
            $password = 'admin';
        } else {
            $username = '';
            $password = '';
        }
        $login = '<form method="post" action="'.SGL_Output::makeUrl("login","login","user").'" id="loginBlock">
                    <input name="action" value="login" type="hidden" />
                    <input name="redir" value="" type="hidden" />
                    <span class="error">*&nbsp;</span>'.SGL_String::translate('Username').'
                    <input name="frmUsername" size="15" value="'.$username.'" maxlength="36" type="text" />
                    <span class="error">*&nbsp;</span>'.SGL_String::translate('Password').'
                    <input name="frmPassword" value="'.$password.'" size="15" maxlength="24" type="password" />
                    <p class="alignCenter"><input name="submitted" value="'.SGL_String::translate('Login').'" type="submit" /></p>
                    <p><a href="'.SGL_Url::makeLink('', 'register', 'user').'">'.SGL_String::translate('Not Registered').'</a><br />
                    <a href="'.SGL_Url::makeLink('', 'password', 'user').'">'.SGL_String::translate('Forgot Password').'</a></p>
                    <span class="small"><span class="error">*</span>'.SGL_String::translate('denotes required field').'</span></form>';
        return $login;
    }

    function getLogoutScreen()
    {
        $logout  = SGL_String::translate('user').': '.$this->username.'<br />';
        $logout .= SGL_String::translate('session started at').': '.$this->startTime.'<br />&nbsp;<br>';
        $logout .= '<a href="'.SGL_Url::makeLink('logout', 'login', 'user').'">'.SGL_String::translate('logout').'</a>';
        return $logout;
    }
}
?>