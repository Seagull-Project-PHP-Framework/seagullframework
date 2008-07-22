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
// | RegisterMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: RegisterMgr.php,v 1.38 2005/06/05 23:14:43 demian Exp $

require_once SGL_MOD_DIR . '/user/classes/LoginMgr.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_CORE_DIR . '/Observer.php';
require_once SGL_CORE_DIR . '/Emailer.php';
require_once 'Validate.php';
require_once 'DB/DataObject.php';

/**
 * Manages User objects.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.38 $
 */
class RegisterMgr extends SGL_Manager
{
    function RegisterMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Register';
        $this->template     = 'userAdd.html';
        $this->da           = & UserDAO::singleton();

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
        );
    }

    function validate(&$req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->sortBy      = SGL_Util::getSortBy($req->get('frmSortBy'), SGL_SORTBY_USER);
        $input->sortOrder   = SGL_Util::getSortOrder($req->get('frmSortOrder'));
        $input->action      = ($req->get('action')) ? $req->get('action') : 'add';
        $input->submitted   = $req->get('submitted');
        $input->userID      = $req->get('frmUserID');
        $input->aDelete     = $req->get('frmDelete');
        $input->user        = (object)$req->get('user');

        //  get referer details if present
        $input->redir = $req->get('redir');

        $aErrors = array();
        if (($input->submitted && $input->action != 'changeUserStatus')
                || in_array($input->action, array('insert', 'update'))) {
            $v = & new Validate();
            if (empty($input->user->username)) {
                $aErrors['username'] = 'You must enter a username';
            } else {
                //  username must be at least 5 chars
                if (!$v->string($input->user->username, array(
                        'format' => VALIDATE_NUM . VALIDATE_ALPHA, 'min_length' => 5 ))) {
                    $aErrors['username'] = 'username min length';
                }
                //  username must be unique
                $msg = 'This username already exist in the DB, please choose another';
                //      on insert
                if ($input->action == 'insert'
                        && !$this->da->isUniqueUsername($input->user->username)) {
                    $aErrors['username'] = $msg;
                }
                //      on update
                if ($input->action == 'update'
                        && !empty($input->user->username_orig)
                        && $input->user->username_orig != $input->user->username
                        && !$this->da->isUniqueUsername($input->user->username)) {
                    $aErrors['username'] = $msg;
                }
            }
            //  only verify password and uniqueness of username/email on inserts
            if ($input->action != 'update') {
                if (empty($input->user->passwd)) {
                    $aErrors['passwd'] = 'You must enter a password';
                } elseif (!$v->string($input->user->passwd, array('min_length' => 5, 'max_length' => 10 ))) {
                    $aErrors['passwd'] = 'Password must be between 5 to 10 characters';
                }
                if (empty($input->user->password_confirm)) {
                    $aErrors['password_confirm'] = 'Please confirm password';
                } elseif ($input->user->passwd != $input->user->password_confirm) {
                    $aErrors['password_confirm'] = 'Passwords are not the same';
                }
            }
            //  check for data in required fields
            if (empty($input->user->addr_1)) {
                $aErrors['addr_1'] = 'You must enter at least address 1';
            }
            if (empty($input->user->city)) {
                $aErrors['city'] = 'You must enter your city';
            }
            if (empty($input->user->post_code)) {
                $aErrors['post_code'] = 'You must enter your ZIP/Postal Code';
            }
            if (empty($input->user->country)) {
                $aErrors['country'] = 'You must enter your country';
            }

            $emailNotUniqueMsg = 'This email already exist in the DB, please choose another';
            if (empty($input->user->email)) {
                $aErrors['email'] = 'You must enter your email';
            } elseif (!$v->email($input->user->email)) {
                $aErrors['email'] = 'Your email is not correctly formatted';

            //  email must be unique
            } elseif ($input->action == 'insert'
                    && !$this->da->isUniqueEmail($input->user->email)) {
                $aErrors['email'] = $emailNotUniqueMsg;
            } elseif ($input->action == 'update'
                    && !empty($input->user->email_orig)
                    && $input->user->email_orig != $input->user->email
                    && !$this->da->isUniqueEmail($input->user->email)) {
                $aErrors['email'] = $emailNotUniqueMsg;
            }
            if (empty($input->user->security_question)) {
                $aErrors['security_question'] = 'You must choose a security question';
            }
            if (empty($input->user->security_answer)) {
                $aErrors['security_answer'] = 'You must provide a security answer';
            }
            // check for mail header injection
            if (!empty($input->user->email)) {
                $input->user->email =
                    SGL_Emailer::cleanMailInjection($input->user->email);
                $input->user->username =
                    SGL_Emailer::cleanMailInjection($input->user->username);
            }

            //  check for hacks - only admin user can set certain attributes
            if ((SGL_Session::getRoleId() != SGL_ADMIN
                    && count(array_filter(array_flip($req->get('user')), array($this, 'containsDisallowedKeys'))))) {
                $msg = 'Hack attempted by ' .$_SERVER['REMOTE_ADDR'] . ', IP logged';
                if (SGL_Session::getRoleId() > SGL_GUEST) {
                    $msg .= ', user id ' . SGL_Session::getUid();
                }
                SGL_Session::destroy();
                SGL::raiseMsg($msg, false);
                SGL::logMessage($msg, PEAR_LOG_CRIT);

                $input->template = 'error.html';
                $this->validated = false;
                return false;
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'userAdd.html';
            $this->validated = false;
        }

        //  check if reg disabled
        if (!SGL_Config::get('RegisterMgr.enabled')
                && strtolower(get_class($this)) == strtolower(__CLASS__)) {
            SGL::raiseMsg('Registration has been disabled');
            $input->template = 'error.html';
            $this->validated = false;
        }
    }

    function containsDisallowedKeys($var)
    {
        $disAllowedKeys = array('role_id', 'organisation_id', 'is_acct_active');
        return in_array($var, $disAllowedKeys);
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set flag to we can share add/edit templates
        if ($output->action == 'add' || $output->action == 'insert') {
            $output->isAdd = true;
        }

        //  build country/state select boxes unless any of following methods
        $aDisallowedMethods = array('list', 'reset', 'passwdEdit', 'passwdUpdate',
            'requestPasswordReset', 'editPrefs');
        if (!in_array($output->action, $aDisallowedMethods)) {
            $output->states = SGL::loadRegionList('states');
            $output->countries = SGL::loadRegionList('countries');
            $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions');
        }

        $sessId = SGL_Session::getId();
        $output->addJavascriptFile(array(
            'js/scriptaculous/lib/prototype.js',
            'js/scriptaculous/src/effects.js'
        ));
    }

    function _cmd_add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'userAdd.html';
        $output->user = DB_DataObject::factory($this->conf['table']['user']);
        $output->user->password_confirm = (isset($input->user->password_confirm)) ?
            $input->user->password_confirm : '';
    }

    function _cmd_insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $addUser = new User_AddUser($input, $output);
        $aObservers = explode(',', $this->conf['RegisterMgr']['observers']);
        foreach ($aObservers as $observer) {
            $path = SGL_MOD_DIR . "/user/classes/observers/$observer.php";
            if (is_file($path)) {
                require_once $path;
                $addUser->attach(new $observer());
            }
        }
        //  returns id for new user
        $output->uid = $addUser->run();
    }
}

class User_AddUser extends SGL_Observable
{
    function User_AddUser(&$input, &$output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    function run()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get default values for new users
        $this->conf = $this->input->getConfig();
        $defaultRoleId = $this->conf['RegisterMgr']['defaultRoleId'];
        $defaultOrgId  = $this->conf['RegisterMgr']['defaultOrgId'];

        $da = & UserDAO::singleton();
        $oUser = $da->getUserById();
        $oUser->setFrom($this->input->user);
        $oUser->passwdClear = $this->input->user->passwd;
        $oUser->passwd = md5($this->input->user->passwd);

        if ($this->conf['RegisterMgr']['autoEnable']) {
            $oUser->is_acct_active = 1;
        }
        $oUser->role_id = $defaultRoleId;
        $oUser->organisation_id = $defaultOrgId;
        $oUser->date_created = $oUser->last_updated = SGL_Date::getTime();
        $success = $da->addUser($oUser);

        //  make user object available to observers
        $this->oUser = $oUser;

        if ($success) {
            //  set user id for use in observers
            $this->oUser->usr_id = $success;
            //  invoke observers
            $this->notify();
            $ret = $success;
            SGL::raiseMsg('user successfully registered', true, SGL_MESSAGE_INFO);
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
            $ret = false;
        }
        return $ret;
    }
}
?>
