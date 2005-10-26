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
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | RegisterMgr.php                                                           |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: RegisterMgr.php,v 1.38 2005/06/05 23:14:43 demian Exp $

require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_MOD_DIR . '/user/classes/LoginMgr.php';
require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
require_once 'Validate.php';

/**
 * Manages User objects.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.38 $
 * @since   PHP 4.1
 */
class RegisterMgr extends SGL_Manager
{
    function RegisterMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'user';
        $this->pageTitle    = 'Register';
        $this->template     = 'userAdd.html';
        $this->da           = & DA_User::singleton();
        
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
        $input->submit      = $req->get('submitted');
        $input->userID      = $req->get('frmUserID');
        $input->aDelete     = $req->get('frmDelete');
        $input->user        = (object)$req->get('user');

        //  get referer details if present
        $input->redir = $req->get('redir');

        $aErrors = array();
        if ($input->submit && ($input->action == 'insert' || $input->action == 'update')) {
            $v = & new Validate();
            if (empty($input->user->username)) {
                $aErrors['username'] = 'You must enter a username';
            } else {
                //  username must be at least 5 chars
                if (!$v->string($input->user->username, array('format' => VALIDATE_NUM . VALIDATE_ALPHA, 'min_length' => 5 ))) {
                    $aErrors['username'] = 'username min length';
                }
            }
            //  only verify password and uniqueness of username on inserts
            if ($input->action != 'update') {
                if (empty($input->user->passwd)) {
                    $aErrors['passwd'] = 'You must enter a password';
                } else {
                    if (!$v->string($input->user->passwd, array('min_length' => 5, 'max_length' => 10 ))) {
                        $aErrors['passwd'] = 'Password must be between 5 to 10 characters';
                    }
                }
                if (empty($input->user->password_confirm)) {
                    $aErrors['password_confirm'] = 'Please confirm password';
                } else {
                    if ($input->user->passwd != $input->user->password_confirm) {
                        $aErrors['password_confirm'] = 'Passwords are not the same';
                    }
                }
                //  username must be unique
                if (!$this->da->isUniqueUsername($input->user->username)) {
                    $aErrors['username'] = 'This username already exist in the DB, please choose another';
                }
                //  username must be unique
                if (!$this->da->isUniqueEmail($input->user->email)) {
                    $aErrors['email'] = 'This email already exist in the DB, please choose another';
                }
            }
            //  end verify inserts
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
            if (empty($input->user->email)) {
                $aErrors['email'] = 'You must enter your email';
            } else {
                if (!$v->email($input->user->email)) {
                    $aErrors['email'] = 'Your email is not correctly formatted';
                }
            }
            if (empty($input->user->security_question)) {
                $aErrors['security_question'] = 'You must choose a security question';
            }
            if (empty($input->user->security_answer)) {
                $aErrors['security_answer'] = 'You must provide a security answer';
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'userAdd.html';
            $this->validated = false;
        }
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
            $lang = SGL::getCurrentLang();

            //  default to english as the data array, countries, etc, only exists in english
            if ($lang != 'en' && $lang != 'de' && $lang != 'it') {
                $lang = 'en';
            }
            include_once SGL_DAT_DIR . '/ary.states.' . $lang . '.php';
            include_once SGL_DAT_DIR . '/ary.countries.' . $lang . '.php';
            $output->states = $states;
            $output->countries = $countries;
            $GLOBALS['_SGL']['COUNTRIES'] = &$countries;
            $GLOBALS['_SGL']['STATES'] = &$states;
            $output->aSecurityQuestions = SGL_String::translate('aSecurityQuestions');
        }
    }

    function _add(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->template = 'userAdd.html';
        $output->user = & new DataObjects_Usr();
        $output->user->password_confirm = (isset($input->user->password_confirm)) ? 
            $input->user->password_confirm : '';
    }

    function _insert(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  get default values for new users
        $defaultRoleId = $conf['RegisterMgr']['defaultRoleId'];
        $defaultOrgId  = $conf['RegisterMgr']['defaultOrgId'];

        //  build new user object
        $oUser = & new DataObjects_Usr();
        $oUser->setFrom($input->user);
        $oUser->passwdClear = $input->user->passwd;
        $oUser->passwd = md5($input->user->passwd);
        if ($conf['RegisterMgr']['autoEnable']) {
            $oUser->is_acct_active = 1;
        }
        $dbh = $oUser->getDatabaseConnection();
        $oUser->usr_id = $dbh->nextId($conf['table']['user']);
        $oUser->role_id = $defaultRoleId;
        $oUser->organisation_id = $defaultOrgId;
        $oUser->date_created = $oUser->last_updated = SGL::getTime();
        $success = $oUser->insert();

        //  assign permissions associated with role user belongs to
        //  first get all perms associated with user's role
        $aRolePerms = $this->da->getPermsByRoleId($defaultRoleId);

        //  then assign them to the user_permission table
        $ret = $this->da->addPermsByUserId($aRolePerms, $oUser->usr_id);
        
        //  assign preferences associated with org user belongs to
        //  first get all prefs associated with user's org or default
        //  prefs if orgs are disabled
        if ($conf['OrgMgr']['enabled']) {
            $aPrefs = $this->da->getUserPrefsByOrgId($oUser->organisation_id, SGL_RET_ID_VALUE);
        } else {
            $aPrefs = $this->da->getMasterPrefs(SGL_RET_ID_VALUE);
        }

        //  then assign them to the user_preference table
        $ret = $this->da->addPrefsByUserId($aPrefs, $oUser->usr_id);
        
        //  handle custom hook if exists
        if (!empty($conf['custom']['hook'])) {
            $params = array('username' => $oUser->username, 'password' => $oUser->passwdClear);
            require_once SGL_MOD_DIR . '/user/classes/' . $conf['custom']['hook'] . '.php';    
            $obj = new $conf['custom']['hook']();
            $ok = $obj->execute($params);
        }

        //  check global error stack for any error that might have occurred
        if ($success && !(count($GLOBALS['_SGL']['ERRORS']))) {
            //  send email confirmation according to config
            if ($conf['RegisterMgr']['sendEmailConfUser']) {
                $bEmailSent = $this->_sendEmail($oUser);
                if (!$bEmailSent) {
                    SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
                }
            }
            //  authenticate user according to settings
            if ($conf['RegisterMgr']['autoLogin']) {
                $input->username = $input->user->username;
                $input->password = $input->user->passwd;
                $oLogin = new LoginMgr();
                $oLogin->_login($input, $output);
            } else {
               SGL::raiseMsg('user successfully registered');
            }
        } else {
            SGL::raiseError('There was a problem inserting the record', 
                SGL_ERROR_NOAFFECTEDROWS);
        }
    }

    function _sendEmail($oUser)
    {
        require_once SGL_CORE_DIR . '/Emailer.php';
        $conf = & $GLOBALS['_SGL']['CONF'];
        $realName = $oUser->first_name . ' ' . $oUser->last_name;
        $recipientName = (trim($realName)) ? $realName : '&lt;no name supplied&gt;';
        $options = array(
                'toEmail'       => $oUser->email,
                'toRealName'    => $recipientName,
                'fromEmail'     => $conf['email']['admin'],
                'replyTo'       => $conf['email']['admin'],
                'subject'       => 'Thanks for registering at ' . $conf['site']['name'],
                'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/' . 
                    $this->module . '/email_registration_thanks.php',
                'username'      => $oUser->username,
                'password'      => $oUser->passwdClear,
        );
                
        $message = & new SGL_Emailer($options);
        $message->prepare();
        $message->send();
        
        //  conf to admin
        if ($conf['RegisterMgr']['sendEmailConfAdmin']) {
            $options = array(
                    'toEmail'       => $conf['email']['admin'],
                    'toRealName'    => 'Admin',
                    'fromEmail'     => $conf['email']['admin'],
                    'replyTo'       => $conf['email']['admin'],
                    'subject'       => 'New Registration at ' . $conf['site']['name'],
                    'template'  => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/' . 
                        $this->module . '/email_registration_admin.php',
                    'username'      => $oUser->username,
                    'activationUrl'      => 'http://seagull.phpkitchen.com/index.php/user/',
            );
            $notification = & new SGL_Emailer($options);
            $notification->prepare();
            $notification->send();         
        }
        //  check error stack
        return (count($GLOBALS['SGL']['ERRORS'])) ? false : true;
    }
}
?>