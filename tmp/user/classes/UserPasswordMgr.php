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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | UserPasswordMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: PasswordMgr.php,v 1.26 2005/05/26 22:38:29 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/PasswordMgr.php';
require_once SGL_CORE_DIR . '/Observer.php';

/**
 * Manages passwords.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.26 $
 */
class UserPasswordMgr extends PasswordMgr
{
    function UserPasswordMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::PasswordMgr();

        $this->template = 'userPasswordEdit.html';

        $this->_aActionsMapping =  array(
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToEdit'),
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
        $input->action          = ($req->get('action')) ? $req->get('action') : 'edit';
        $input->passwordOrig    = $req->get('frmPasswordOrig');
        $input->password        = $req->get('frmPassword');
        $input->passwordConfirm = $req->get('frmPasswordConfirm');
        $input->question        = $req->get('frmQuestion');
        $input->answer          = $req->get('frmAnswer');
        $input->passwdResetNotify = ($req->get('frmPasswdResetNotify') == 'on') ? 1 : 0;
        $input->forgotEmail     = $req->get('frmEmail');
        $input->submitted       = $req->get('submitted');

        $aErrors = array();

        //  password update validation for AccountMgr
        if ($input->submitted || ($input->action == 'update')) {
            $v = & new Validate();
            if (empty($input->passwordOrig)) {
                $aErrors['frmPasswordOrig'] = 'You must enter your original password';
            } elseif (!$this->_isOriginalPassword($input->passwordOrig)) {
                $aErrors['frmPasswordOrig'] = 'You have entered your original password incorrectly';
            }
            if (empty($input->password)) {
                $aErrors['frmPassword'] = 'You must enter a new password';
            } elseif (!$v->string($input->password, array('min_length' => 5, 'max_length' => 10 ))) {
                $aErrors['frmPassword'] = 'Password must be between 5 to 10 characters';
            }
            if (empty($input->passwordConfirm)) {
                $aErrors['frmPasswordConfirm'] = 'Please confirm password';
            } elseif ($input->password != $input->passwordConfirm) {
                $aErrors['frmPasswordConfirm'] = 'Passwords are not the same';
            }
            //  if errors have occured
            if (is_array($aErrors) && count($aErrors)) {
                SGL::raiseMsg('Please fill in the indicated fields', true, SGL_MESSAGE_ERROR);
                $input->error = $aErrors;
                $this->validated = false;
            }
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions');
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->pageTitle = 'Change Password';
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  admin cannot change passwd in DEMO mode
        if (isset($this->conf['tuples']['demoMode'])
                && $this->conf['tuples']['demoMode'] == true
                && SGL_Session::getUid() == SGL_ADMIN) {
            SGL::raiseMsg('admin cannot change passwd in DEMO mode', false,
                SGL_MESSAGE_ERROR);
            return false;
        }
        $updateUserPasswd = new User_UpdateUserPassword($input, $output);
        $aObservers = explode(',', $this->conf['UserPasswordMgr']['observers']);
        foreach ($aObservers as $observer) {
            $path = SGL_MOD_DIR . "/user/classes/observers/$observer.php";
            if (is_file($path)) {
                require_once $path;
                $updateUserPasswd->attach(new $observer());
            }
        }
        $updateUserPasswd->run();
    }

    function _isOriginalPassword($passwd)
    {
        if (isset($passwd)) {
            $oUser = DB_DataObject::factory($this->conf['table']['user']);
            $oUser->get(SGL_Session::getUid());
            return md5($passwd) == $oUser->passwd;
        }
    }
}

class User_UpdateUserPassword extends SGL_Observable
{
    function User_UpdateUserPassword(&$input, &$output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    function run()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->conf = $this->input->getConfig();
        $oUser = DB_DataObject::factory($this->conf['table']['user']);
        $oUser->get(SGL_Session::getUid());
        $oUser->passwd = md5($this->input->password);
        $success = $oUser->update();

        //  make user object available to observers
        $this->oUser = $oUser;

        if ($success !== false) {
            //  invoke observers
            $this->notify();
            SGL::raiseMsg('Password updated successfully', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }
}
?>
