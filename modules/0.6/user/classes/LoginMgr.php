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
// | LoginMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: LoginMgr.php,v 1.34 2005/06/15 00:50:40 demian Exp $

require_once SGL_CORE_DIR . '/Observer.php';

/**
 * Handles user logins.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.34 $
 */
class LoginMgr extends SGL_Manager
{
    function LoginMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->_aActionsMapping =  array(
            'login'         => array('login'),
            'list'          => array('list'),
            'logout'        => array('logout'),
            'removeCookies' => array('removeCookies', 'redirectToMyAccount')
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  init vars
        $this->validated    = true;
        $input->username    = '';
        $input->password    = '';
        $input->submitted   = '';
        $input->error       = array();
        $input->pageTitle   = 'Login';
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = 'login.html';
        $input->submitted   = $req->get('submitted');
        $input->username    = $req->get('frmUsername');
        $input->password    = $req->get('frmPassword');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->rememberMe  = $req->get('frmExtendedLifetime');

        //  get referer details if present
        $input->redir = $req->get('redir');

        $aErrors = array();
        if ($input->submitted) {
            if ($input->username == '') {
                $aErrors['username'] = 'You must enter a username';
            }
            if ($input->password == '') {
                $aErrors['password'] = 'You must enter a password';
            }
        }
        //  if submitted and there are errors
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->addOnLoadEvent("document.getElementById('frmLogin').frmUsername.focus()");
    }

    function _cmd_login(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $doLogin = new User_DoLogin($input, $output);
        $aObservers = explode(',', $this->conf['LoginMgr']['observers']);
        foreach ($aObservers as $observer) {
            $path = SGL_MOD_DIR . "/user/classes/observers/$observer.php";
            if (is_file($path)) {
                require_once $path;
                $doLogin->attach(new $observer());
            }
        }
        $doLogin->run();
    }

    function _cmd_logout(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $uid = SGL_Session::getUid();
        if ($uid > SGL_GUEST
                && !empty($this->conf['cookie']['rememberMeEnabled'])
                && !empty($_COOKIE['SGL_REMEMBER_ME'])) {
            list(, $cookieValue) = @unserialize($_COOKIE['SGL_REMEMBER_ME']);
            if (!empty($cookieValue)) {
                require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
                $_da = &UserDAO::singleton();
                $_da->deleteUserLoginCookieByUserId($uid, $cookieValue);
            }
        }
        SGL_Session::destroy();
        SGL::raiseMsg('You have been successfully logged out', true, SGL_MESSAGE_INFO);

        //  get default params for logout page
        $aParams = SGL_Config::get('site.logoutTarget')
            ? SGL_Config::getCommandTarget(SGL_Config::get('site.logoutTarget'))
            : $this->getDefaultPageParams();
        SGL_HTTP::redirect($aParams);
    }

    function _cmd_list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($this->conf['tuples']['demoMode']) && $this->conf['tuples']['demoMode'] == true) {
            $output->username = 'admin';
            $output->password = 'admin';
        }
    }

    function _cmd_removeCookies(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $uid = SGL_Session::getUid();
        if ($uid > SGL_GUEST
                && !empty($this->conf['cookie']['rememberMeEnabled'])) {
            $_da = &UserDAO::singleton();
            $_da->deleteUserLoginCookiesByUserId($uid);

            SGL::raiseMsg('Persistent cookies successfully removed',
                true, SGL_MESSAGE_INFO);
        }
    }

    function _cmd_redirectToMyAccount(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aRedir = array(
            'moduleName'  => 'user',
            'managerName' => 'account'
        );
        SGL_HTTP::redirect($aRedir);
    }
}

class User_DoLogin extends SGL_Observable
{
    function User_DoLogin(&$input, &$output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    function &_getDb()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh;
    }

    function run()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->conf = $this->input->getConfig();
        $this->dbh = $this->_getDb();

        if ($res = $this->_doLogin($this->input->username,
                $this->input->password, $this->input->rememberMe)) {

            //  invoke observers
            $this->notify();

            // Get the user id from the current session
            $uid = SGL_Session::getUid();

            // Check for multiple user sessions: allow one only excluding current one.
            if (!empty($this->conf['session']['extended'])) {
                $multiple = SGL_Session::getUserSessionCount($uid, session_id());
                if ($multiple > 0) {
                    if ($this->conf['session']['singleUser']) {
                        SGL_Session::destroyUserSessions($uid, session_id());
                        SGL::raiseMsg('You are allowed to connect from one computer at '.
                            'a time, other sessions were terminated!');
                    } else {
                        // Issue warning only
                        SGL::raiseMsg('You have multiple sessions on this site!');
                    }
                }
            }
            //  if redirect captured
            if (!empty($this->input->redir)) {
                SGL_HTTP::redirect(base64_decode($this->input->redir));
            }
            $type = ($res['role_id'] == SGL_ADMIN) ? 'logonAdminGoto' : 'logonUserGoto';
            $target = !empty($this->conf['LoginMgr'][$type])
                ? SGL_Config::getCommandTarget($this->conf['LoginMgr'][$type])
                : '';
            SGL_HTTP::redirect($target);

        } else {
            SGL::raiseMsg('username/password not recognized');
            SGL::logMessage('login failed', PEAR_LOG_NOTICE);
        }
    }

    function _doLogin($username, $password, $rememberMe)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $query = "
            SELECT  usr_id, role_id
            FROM " . $this->conf['table']['user'] . "
            WHERE   username = " . $this->dbh->quoteSmart($username) . "
            AND     passwd = '" . md5($password) . "'
            AND     is_acct_active = 1";

        $aResult = $this->dbh->getRow($query, DB_FETCHMODE_ASSOC);
        if (is_array($aResult)) {
            $uid = $aResult['usr_id'];

            //  associate new session with authenticated user
            new SGL_Session($uid, $rememberMe);

            return $aResult;

            // else login incorrect
        } else {
            return false;
        }
    }
}
?>
