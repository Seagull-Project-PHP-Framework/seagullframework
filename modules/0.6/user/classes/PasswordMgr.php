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
// | PasswordMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: PasswordMgr.php,v 1.26 2005/05/26 22:38:29 demian Exp $

require_once 'Validate.php';
require_once 'DB/DataObject.php';

/**
 * Manages passwords.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.26 $
 */
class PasswordMgr extends SGL_Manager
{
    function PasswordMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->template = 'loginForgot.html';

        $this->_aActionsMapping =  array(
            'retrieve'  => array('retrieve', 'redirectToDefault'),
            'forgot'    => array('forgot'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->masterTemplate  = $this->masterTemplate;
        $input->template        = $this->template;
        $input->error           = array();
        $input->pageTitle       = 'Retrieve password';
        $input->action          = ($req->get('action')) ? $req->get('action') : 'forgot';
        $input->passwordOrig    = $req->get('frmPasswordOrig');
        $input->password        = $req->get('frmPassword');
        $input->passwordConfirm = $req->get('frmPasswordConfirm');
        $input->question        = $req->get('frmQuestion');
        $input->answer          = $req->get('frmAnswer');
        $input->forgotEmail     = $req->get('frmEmail');
        $input->submitted       = $req->get('submitted');

        $aErrors = array();

        //  forgot password validation
        if ($input->submitted && ($input->action == 'forgot' || $input->action == 'retrieve')) {
            $v = & new Validate();
            if (empty($input->forgotEmail)) {
                $aErrors['frmEmail'] = 'You must enter your email';
            } else {
                if (!$v->email($input->forgotEmail)) {
                    $aErrors['frmEmail'] = 'Your email is not correctly formatted';
                }
            }
            if (empty($input->question)) {
                $aErrors['frmQuestion'] = 'You must choose a security question';
            }
            if (empty($input->answer)) {
                $aErrors['frmAnswer'] = 'You must provide a security answer';
            }
            //  if errors have occured
            if (is_array($aErrors) && count($aErrors)) {
                SGL::raiseMsg('Please fill in the indicated fields');
                $input->error = $aErrors;
                $this->validated = false;
            }
            unset($v);
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions');
    }

    function _cmd_forgot(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_retrieve(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  *
            FROM    " . $this->conf['table']['user'] ."
            WHERE   email = " . $this->dbh->quoteSmart($input->forgotEmail) . "
            AND     security_question = " . $input->question. "
            AND     security_answer = " . $this->dbh->quoteSmart($input->answer);
        $userId = $this->dbh->getOne($query);
        if ($userId) {
            $aRet = $this->_resetPassword($userId);
            list($passwd, $oUser) = $aRet;
            $bEmailSent = $this->sendPassword($oUser, $passwd);
            if ($bEmailSent) {
                SGL::raiseMsg('password emailed out', true, SGL_MESSAGE_INFO);
            } else {
                SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
            }
        //  credentials not recognised
        } else {
            SGL::raiseMsg('email not in system');
        }
    }

    function _cmd_redirectToEdit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  if no errors have occured, redirect
        if (!SGL_Error::count()) {
            SGL_HTTP::redirect(array('action' => 'edit'));

        //  else display error with blank template
        } else {
            $output->template = 'error.html';
        }
    }

    function _resetPassword($userId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $passwd = $oPassword->create();
        $oUser = DB_DataObject::factory($this->conf['table']['user']);
        $oUser->get($userId);
        $oUser->passwd = md5($passwd);
        $oUser->update();
        return array($passwd, $oUser);
    }

    /**
     * Wrapper for emailing password.
     *
     * @static
     * @param object $oUser
     * @param string $passwd
     * @return boolean
     */
    function sendPassword($oUser, $passwd)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_CORE_DIR . '/Emailer.php';

        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $options = array(
            'toEmail'   => $oUser->email,
            'fromEmail' => $conf['email']['admin'],
            'replyTo'   => $conf['email']['admin'],
            'subject'   => 'Password reminder from ' . $conf['site']['name'],
            'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme']
                . '/user/email_forgot.php',
            'username'  => $oUser->username,
            'password'  => $passwd,
        );
        $message = & new SGL_Emailer($options);
        $ok = $message->prepare();
        return ($ok) ? $message->send() : $ok;
    }
}
?>
