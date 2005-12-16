<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.5                                                               |
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

        $this->template = 'userPasswordEdit.html';

        $this->_aActionsMapping =  array(
            'edit'      => array('edit'), 
            'update'    => array('update', 'redirectToEdit'), 
            'retrieve'  => array('retrieve', 'redirectToDefault'), 
            'forgot'    => array('forgot'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated        = true;
        $input->masterTemplate  = $this->masterTemplate;
        $input->error           = array();
        $input->pageTitle       = 'Retrieve password';
        $input->action          = ($req->get('action')) ? $req->get('action') : 'forgot';
        $input->passwordOrig    = $req->get('frmPasswordOrig');
        $input->password        = $req->get('frmPassword');
        $input->passwordConfirm = $req->get('frmPasswordConfirm');
        $input->question        = $req->get('frmQuestion');
        $input->answer          = $req->get('frmAnswer');
        $input->passwdResetNotify = ($req->get('frmPasswdResetNotify') == 'on') ? 1 : 0;
        $input->forgotEmail     = $req->get('frmEmail');
        $input->submit          = $req->get('submitted');

        $aErrors = array();

        //  forgot password validation
        if ($input->submit && ($input->action == 'forgot' || $input->action == 'retrieve')) {
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
                $input->template = 'loginForgot.html';
                $this->validated = false;
            }
            unset($v);
        } 
        //  password update validation for AccountMgr
        if ($input->submit && ($input->action == 'edit') || ($input->action == 'update')) {
            $v = & new Validate();
            if (empty($input->passwordOrig)) {
                $aErrors['frmPasswordOrig'] = 'You must enter your original password';
            } else {
                if (!$this->_isOriginalPassword($input->passwordOrig)) {
                    $aErrors['frmPasswordOrig'] = 'You have entered your original password incorrectly';
                }
            }
            if (empty($input->password)) {
                $aErrors['frmPassword'] = 'You must enter a new password';
            } else {
                if (!$v->string($input->password, array('min_length' => 5, 'max_length' => 10 ))) {
                    $aErrors['frmPassword'] = 'Password must be between 5 to 10 characters';
                }
            }
            if (empty($input->passwordConfirm)) {
                $aErrors['frmPasswordConfirm'] = 'Please confirm password';
            } else {
                if ($input->password != $input->passwordConfirm) {
                    $aErrors['frmPasswordConfirm'] = 'Passwords are not the same';
                }
            }
            //  if errors have occured
            if (is_array($aErrors) && count($aErrors)) {
                SGL::raiseMsg('Please fill in the indicated fields');
                $input->error = $aErrors;
                $input->template = 'userPasswordEdit.html';
                $this->validated = false;
            }
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions', false, true);
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'userPasswordEdit.html';
        $output->pageTitle = 'Change Password';
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $oUser = DB_DataObject::factory('Usr');
        $oUser->get(SGL_HTTP_Session::getUid());
        $oUser->passwd = md5($input->password);
        $success = $oUser->update();
        if ($input->passwdResetNotify) {
            $this->sendPassword($oUser, $input->password, $input->moduleName);
        }
        //  redirect on success
        if ($success) {
            SGL::raiseMsg('Password updated successfully');
        } else {
            SGL::raiseError('There was a problem inserting the record', SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _forgot(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'loginForgot.html';
    }

    function _retrieve(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  *
            FROM    " . $this->conf['table']['user'] ."
            WHERE   email = " . $this->dbh->quote($input->forgotEmail) . "
            AND     security_question = " . $input->question. "
            AND     security_answer = '" . $input->answer . "'";
        $userId = $this->dbh->getOne($query);
        if ($userId) {
            $aRet = $this->_resetPassword($userId);
            list($passwd, $oUser) = $aRet;
            $bEmailSent = $this->sendPassword($oUser, $passwd);
            if ($bEmailSent) {
                //  redirect
                SGL::raiseMsg('password emailed out');
                $aParams = array(
                    'moduleName'    => 'default',
                    'managerName'   => 'default',
                    );
                SGL_HTTP::redirect($aParams);
            } else {
                SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
            }
        //  credentials not recognised
        } else {
            //  redirect
            SGL::raiseMsg('email not in system');
        }
    }

    function _resetPassword($userId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $passwd = $oPassword->create();
        $oUser = DB_DataObject::factory('Usr');
        $oUser->get($userId);
        $oUser->passwd = md5($passwd);
        $oUser->update();
        return array($passwd, $oUser);
    }

    function _isOriginalPassword($passwd)
    {
        if (isset($passwd)) {
            $oUser = DB_DataObject::factory('Usr');
            $oUser->get(SGL_HTTP_Session::getUid());
            return md5($passwd) == $oUser->passwd;
        }
    }

    function sendPassword($oUser, $passwd)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        require_once SGL_CORE_DIR . '/Emailer.php';

        $options = array(
                'toEmail'   => $oUser->email,
                'fromEmail' => $this->conf['email']['admin'],
                'replyTo'   => $this->conf['email']['admin'],
                'subject'   => 'Password reminder from ' . $this->conf['site']['name'],
                'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme']
                    . '/user/email_forgot.php',
                'username'  => $oUser->username,
                'password'  => $passwd,
        );
        $message = & new SGL_Emailer($options);
        $ok = $message->prepare();
        return ($ok) ? $message->send() : $ok;
    }
    
    function _redirectToEdit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);  
        SGL_HTTP::redirect(array('action' => 'edit'));
    }
}
?>