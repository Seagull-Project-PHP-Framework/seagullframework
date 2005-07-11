<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Demian Turner                                         |
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
// | LoginMgr.php                                                              |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: LoginMgr.php,v 1.34 2005/06/15 00:50:40 demian Exp $

/**
 * Handles user logins.
 *
 * @package User
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.34 $
 * @since   PHP 4.1
 */
class LoginMgr extends SGL_Manager
{
    function LoginMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module       = 'user';

        $this->_aActionsMapping =  array(
            'login' => array('login'), 
            'list'  => array('list'), 
            'logout' => array('logout'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  init vars
        $this->validated    = true;
        $input->username    = '';
        $input->password    = '';
        $input->submit      = '';
        $input->error       = array();
        $input->pageTitle   = 'Login';
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = 'login.html';
        $input->submit      = $req->get('submitted');
        $input->username    = $req->get('frmUsername');
        $input->password    = $req->get('frmPassword');
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';

        //  get referer details if present
        $input->redir = $req->get('redir');

        $aErrors = array();
        if ($input->submit) {
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
        $output->addOnLoadEvent('document.getElementById("login").frmUsername.focus()');
    }

    function _login(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];
        if ($res = $this->_doLogin($input->username, $input->password)) {

            //  if redirect captured
            if (!empty($input->redir)) {
                SGL_HTTP::redirect(urldecode($input->redir));
            }
            $type = ($res['role_id'] == SGL_ADMIN) ? 'logonAdminGoto' : 'logonUserGoto';
            list($mod, $mgr) = split('\^', $conf['LoginMgr'][$type]);
            $aParams = array(
                'moduleName'    => $mod,
                'managerName'   => $mgr,
                );
            SGL_HTTP::redirect($aParams);

        } else {
            SGL::raiseMsg('username/password not recognized');
            SGL::logMessage('login failed', PEAR_LOG_NOTICE);
        }
    }

    function _logout(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        SGL::clearCache('blocks');
        SGL_HTTP_Session::destroy();
        SGL::raiseMsg('You have been successfully logged out');
        
        //  get logout page
        $conf = & $GLOBALS['_SGL']['CONF'];
        $moduleName = $conf['site']['defaultModule'];
        $managerName = $conf['site']['defaultManager'];
        $defaultParams = $conf['site']['defaultParams'];
        $aDefaultParams = !empty($defaultParams) ? explode('/', $defaultParams) : array();
        
        $aParams = array(
            'moduleName'    => $moduleName,
            'managerName'   => $managerName,
            );
        
        //  convert string into hash and merge with $aParams
        $aRet = array();            
        if ($numElems = count($aDefaultParams)) {
            $aTmp = array();
            for ($x = 0; $x < $numElems; $x++) {
                if ($x % 2) { // if index is odd
                    $aTmp['varValue'] = urldecode($aDefaultParams[$x]);
                } else {
                    // parsing the parameters
                    $aTmp['varName'] = urldecode($aDefaultParams[$x]);
                }
                //  if a name/value pair exists, add it to request
                if (count($aTmp) == 2) {
                    $aRet[$aTmp['varName']] = $aTmp['varValue'];
                    $aTmp = array();                
                }
            }
        }
        $aMergedParams = array_merge($aParams, $aRet);   
        SGL_HTTP::redirect($aMergedParams);
    }

    function _list(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _doLogin($username, $password)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();
        $query = "
            SELECT  usr_id, role_id
            FROM " . $conf['table']['user'] . "
            WHERE   username = " . $dbh->quote($username) . "
            AND     passwd = '" . md5($password) . "'
            AND     is_acct_active = 1";
        $aResult = $dbh->getRow($query, DB_FETCHMODE_ASSOC);
        if (is_array($aResult)) {
            $uid = $aResult['usr_id'];
            $rid = $aResult['role_id'];

            //  record login in db for security
            if (@$conf['LoginMgr']['recordLogin']) {
                include_once SGL_ENT_DIR . '/Login.php';
                $login = & new DataObjects_Login();
                $login->login_id = $dbh->nextId('login');
                $login->usr_id = $uid;
                $login->date_time = SGL::getTime(true);
                $login->remote_ip = $_SERVER['REMOTE_ADDR'];
                $login->insert();
            }
            //  associate new session with authenticated user
            $sess = & new SGL_HTTP_Session($uid);

            return $aResult;

            // else logon incorrect
        } else {
            return false;
        }
    }
}
?>