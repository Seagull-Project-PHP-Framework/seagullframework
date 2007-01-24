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
// | BugMgr.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// $Id: BugMgr.php,v 1.4 2005/06/23 18:21:25 demian Exp $
 
require_once SGL_CORE_DIR . '/Emailer.php';
require_once 'Validate.php';

class BugMgr extends SGL_Manager
{
    var $_clientInfo = array();
    var $_serverInfo = array();
    var $aSeverityTypes = array();
    
    function BugMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module      = 'default';
        $this->pageTitle   = 'Bug Report';
        $this->template    = 'bugReport.html';

        $this->_aActionsMapping =  array(
            'list'  => array('list'), 
            'send'  => array('send', 'redirectToDefault'), 
        );
        
        $this->_clientInfo = $this->getClientInfo();
        $this->_serverInfo = $this->getServerInfo();
        
        $this->aSeverityTypes = array(
            'not categorized' => 'not categorized',
            'critical' => 'critical',
            'major' => 'major',
            'critical' => 'critical',
            'minor' => 'minor',
            'enhancement' => 'enhancement',
            'question' => 'question',
            'feature request' => 'feature request',
            'support request' => 'support request',
            );
    }
    
    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = null;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = 'masterMinimal.html';
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submit      = $req->get('submitted');
        $input->bug         = (object)$req->get('bug');
        
        $aErrors = array();
        if ($input->submit) {
            $v = & new Validate();
            if (empty($input->bug->reporter_email)) {
                $aErrors['reporter_email'] = 'You must enter your email';
            } else {
                if (!$v->email($input->bug->reporter_email)) {
                    $aErrors['reporter_email'] = 'Your email is not correctly formatted';
                }
            }
            if (empty($input->bug->description)) {
                $aErrors['description'] = 'You must fill in your description';
            }
            if (empty($input->bug->comment)) {
                $aErrors['comment'] = 'You must fill in your comment';
            }            
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }
    
    function _list(&$input, &$output)
    {
        if (SGL_HTTP_Session::getUserType() != SGL_GUEST) {                        
            $user = $this->getCurrentUserInfo();
            $bug = new stdClass();
            $bug->reporter_first_name = $user->first_name;
            $bug->reporter_last_name = $user->last_name;
            $bug->reporter_email = $user->email;

            //  and send populated bug object to output
            $output->bug = $bug;
        }      
    }
    
    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->environment = $this->buildEnvironmentReport();
        $output->aSeverityTypes = $this->aSeverityTypes;          
    }
    
    function _send(&$input, &$output)
    {
        $ok = $this->sendEmail($input->bug);
        if (!PEAR::isError($ok)) {
            SGL::raiseMsg('email submitted successfully');
        } else {
            SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
        }
    }

    function getServerInfo()
    {
        $aServerInfo = array();
        //  get db info
        $lastQuery = $this->dbh->last_query;
        $aServerInfo['lastSql'] = isset($this->dbh->last_query) ? 
            $this->dbh->last_query : null;
        $aServerInfo['phpSapi'] = php_sapi_name();
        $aServerInfo['phpOs'] = PHP_OS;
        $aServerInfo['dbType'] = $this->conf['db']['type'];
        $aServerInfo['phpVersion'] = PHP_VERSION;
        $aServerInfo['serverPort'] = $_SERVER['SERVER_PORT'];
        $aServerInfo['serverSoftware'] = $_SERVER['SERVER_SOFTWARE'];   
        $aServerInfo['sglVersion'] = SGL_SEAGULL_VERSION;
        return $aServerInfo;
    }
    
    
    function getClientInfo()
    {
        $aclientInfo = array();
        $aclientInfo['callingURL'] = $_SERVER['SCRIPT_NAME'];
        $aclientInfo['httpReferer'] = @$_SERVER['HTTP_REFERER'];
        $aclientInfo['httpUserAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $aclientInfo['remoteAddr'] = $_SERVER['REMOTE_ADDR'];
        return $aclientInfo;
    }
    
    function getCurrentUserInfo()
    {
        //  instantiate new User entity
        require_once SGL_ENT_DIR . '/Usr.php';        
        $user = & new DataObjects_Usr();    
        $user->get(SGL_HTTP_Session::getUid());
        return $user;
    }
    
    function buildEnvironmentReport()
    {
        $html = '';
        $data = array_merge($this->_clientInfo, $this->_serverInfo);
        foreach ($data as $k => $v) {
            $html .= "[$k] => $v \n";   
        }
        return $html;
    }
    
    function sendEmail($oData)
    {
        $body = "The following bug report was submitted: \n\n";
        
        $report = new BugReport($oData);
        
        $options = array(
            'toEmail'       => 'bugs@phpkitchen.com',
            'toRealName'    => 'Seagull Maintainer',
            'fromEmail'     => $report->getEmail(),
            'fromRealName'  => $report->getName(),
            'replyTo'       => 'seagull@phpkitchen.com',
            'subject'       => '[Seagull Bug report]',
            'body'          => $report->toString(),
            'template'      => SGL_THEME_DIR . '/' . $_SESSION['aPrefs']['theme'] . '/' . 
                    $this->module . '/emailBugReport.php',
            'siteUrl'       => SGL_BASE_URL,
            'siteName'      => 'Seagull',
        );
        $email = new SGL_Emailer($options);
        $ret = $email->prepare();
        if (!($ret)) {
            return PEAR::raiseError('Problem building email'); 
        } else {
            return $email->send();
        }
    }
}

class BugReport
{   
    var $reporter_first_name;
    var $reporter_last_name;
    
    function BugReport($oData)
    {
        foreach ($oData as $k => $v) {
            $this->$k = $v;   
        }
    }
    
    function getEmail()
    {
        return isset($this->reporter_email) ? $this->reporter_email : 'anonymous';
    }
    
    function getName()
    {
        return isset($this->reporter_first_name) 
            ? $this->reporter_first_name .' '. $this->reporter_last_name 
            : 'BugReporter';
        
    }
    
    function toString()
    {
        $str = '';
        $data = get_object_vars($this);
        foreach ($data as $k => $v) {
            $str .= "[$k] => $v \n";   
        }
        return $str;
    }
}
?>