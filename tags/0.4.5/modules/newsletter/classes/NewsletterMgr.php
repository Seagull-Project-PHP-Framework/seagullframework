<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Rares Benea                                           |
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
// | NewsletterMgr.php                                                         |
// +---------------------------------------------------------------------------+
// | Authors:   Benea Rares <rbenea@bluestardesign.ro>                         |
// |            Alexander J. Tarachanowicz <ajt@localhype.net>                 |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: NewsletterMgr.php,v 1.24 2005/06/12 18:19:18 demian Exp $

require_once SGL_ENT_DIR . '/Newsletter.php';
require_once SGL_ENT_DIR . '/Usr.php';
require_once SGL_CORE_DIR . '/Emailer.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';
require_once 'Validate.php';

/**
 * For distributing 'newsletter' type email to users.
 *
 * @package newsletter
 * @author  Benea Rares <rbenea@bluestardesign.ro>
 * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
 * @version $Revision: 1.24 $
 * @since   PHP 4.1
 */
class NewsletterMgr extends SGL_Manager
{
    function NewsletterMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->module           = 'newsletter';
        $this->pageTitle        = 'Newsletter';
        $this->template         = 'list.html';

        $this->_aActionsMapping =  array(
            'list'        => array('list'),
            'subscribe'   => array('subscribe', 'redirectToDefault'), 
            'unsubscribe' => array('unsubscribe', 'redirectToDefault'), 
            'authorize'   => array('authorize'),
        );
    }


    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->submit      = $req->get('submitted');
        $input->listName    = $req->get('frmListName');
        $input->actionRequest = $req->get('frmRequest');
        $input->actionKey   = $req->get('frmKey');
        $input->submit      = $req->get('submitted');

        $input->validNewsList = $this->_getList();

        if ($req->get('frmUserID')) {
            $input->userID = $req->get('frmUserID');
            $oUser = & new DataObjects_Usr();
            $oUser->get($input->userID);
            $input->email       = $oUser->email;
            $input->name        = $oUser->first_name . $oUser->last_name;                       
        } else {
            $input->email       = $req->get('frmEmail');
            $input->name        = $req->get('frmName');
        }
        
        $aErrors = array();
        if ($input->submit) {
            
            if (empty($input->email)) {
                $aErrors['email'] = 'Please fill in the email field';
            } else {
                $v = & new Validate();
                if (!$v->email($input->email)) {
                    $aErrors['email'] = 'incorrectly formatted email';
                }
            }
            
            if (!empty($input->name) and preg_match("([^\w\s])",$input->name)) {
                $aErrors['name'] = 'Invalid input supplied to list name';
            }
            
            
            if ($input->action == 'subscribe' or $input->action == 'unsubscribe') {
                if (!empty($input->listName) and is_array($input->listName) and count($input->listName) > 0) {
                    foreach ($input->listName as $list) {
                        if (!array_key_exists($list,$input->validNewsList)) {
                            $aErrors['listName'] = 'Invalid input supplied to list name';
                            break;
                        }
                    }
                } else {
                    $aErrors['listName'] = 'Please select at least one newsletter';
                }
            }
            
            if ($input->action == 'authorize') {
               $input->template = 'authorize.html'; 
               if (empty($input->actionKey)) {
                    $aErrors['actionKey'] = 'Please input a valid authorization key';
               } elseif (preg_match("([^\w])",$input->actionKey)) {
                    $aErrors['actionKey'] = 'Please input a valid authorization key';
               }
            }
               
        }
        
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }
    
    
    /**
    * List the subscribe/unsubscribe form
    *
    * @access public
    *
    */
    function _list(&$input, &$output)
    {    
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    
    /**
    * Send subscribe e-mail  and/or add user data in DB
    *
    * @access public
    *
    */
    function _subscribe(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $errorLists = false;

        // Process registration for every list selected
        foreach ($input->listName as $list) {
            $oList = & new DataObjects_Newsletter();
            $oList->email = $input->email;
            $oList->list = $input->validNewsList[$list]['name'];
            $noRows = $oList->find();
            
            // Do not subscribe if already subscribed
            if ($noRows != 0) {
                SGL::logMessage('User: '.$input->email.' tried to resubscribe to list '.
                    $input->validNewsList[$list]['name']);
                $errorLists = true;
                continue;
            }
            
            $dbh = $oList->getDatabaseConnection();
            $oList->newsletter_id = $dbh->nextId($conf['table']['newsletter']);  
            if (!empty($input->name)) {
                $oList->name = $input->name;
            }
            // If emailConfirmation not required - registration active by default
            if ($conf['NewsletterMgr']['emailConfirmation']) {
                $oList->status = 1;
                $oList->action_request = 'subscribe';
                $oList->action_key = $this->_generateKey($input->email);
            } else {
               $oList->status = 0;
            }
            
            $oList->date_created = $oList->last_updated = SGL::getTime();
            $success = $oList->insert();
            if ($success) {
                if ($conf['NewsletterMgr']['emailConfirmation']) {
                    // Send email confirmation
                    $output->emailSiteName = $conf['site']['name'];
                    $output->emailSubject = SGL_String::translate('Action confirmation for newsletter').' '.$input->validNewsList[$list]['name'];
                    $output->emailAction = SGL_String::translate('subscribe');
                    $output->emailName = $input->name ? $input->name : 'User';
                    $output->emailAddress = $input->email;
                    $output->emailList = $input->validNewsList[$list]['name'];
                    $output->emailKey = $oList->action_key;
                    $ret = $this->_send($input, $output);
                    if (!$ret) {
                       SGL::logMessage('Unable to send subscribe message to: '.$input->email);
                    }
                }
            } else {    
                $errorLists = true;
            } 
            unset($oList);
        }
        
        
        if ($errorLists) {
            SGL::raiseMsg('Unable to subscribe you to some lists');
        } else {
            if ($conf['NewsletterMgr']['emailConfirmation']) {
                SGL::raiseMsg('Thank you subscribe email confirmation');
            } else {
                SGL::raiseMsg('Thank you subscribe');
            }
        }
    }
    
    
    /**
    * Send unsubscribe e-mail or remove data from DB
    *
    * @access public
    *
    */
    function _unsubscribe(&$input, &$output) {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $errorLists = false;
        // Process request for every list selected
        foreach($input->listName as $list) {
            $oList = & new DataObjects_Newsletter();
            $oList->email = $input->email;
            $oList->list = $input->validNewsList[$list]['name'];
            $noRows = $oList->find(true);
            
            // Check if user exist
            if ($noRows != 1) {
                SGL::logMessage('Unregistered user: '.$input->email.' tried to unsubscribe to list '.$input->validNewsList[$list]['name']);
                $errorLists = true;
                continue;
            }
            
            if ($conf['NewsletterMgr']['emailConfirmation']) {
                $oList->action_request = 'unsubscribe';
                $oList->action_key = $this->_generateKey($input->email);
                $oList->last_updated = SGL::getTime();
                $success = $oList->update();
                if ($success) {
                    $output->emailSiteName = $conf['site']['name'];
                    $output->emailSubject = SGL_String::translate('Action confirmation for newsletter').' '.$input->validNewsList[$list]['name'];;
                    $output->emailAction = SGL_String::translate('unsubscribe');
                    $output->emailName = $input->name;
                    $output->emailAddress = $input->email;
                    $output->emailList = $input->validNewsList[$list]['name'];
                    $output->emailKey = $oList->action_key;
                    $ret = $this->_send($input, $output);
                    if (!$ret) {
                       SGL::logMessage('Unable to send unsubscribe message to: '.$input->email);
                    }
                } else {    
                    $errorLists = true;
                } 
            } else {
                $success = $oList->delete();
                if (!$success) {
                    $errorLists = true;
                }
            }
            unset($oList);
        }
        
        
        if ($errorLists) {
            SGL::raiseMsg('Unable to unsubscribe you to some lists');
        } else {
            if ($conf['NewsletterMgr']['emailConfirmation']) {
                SGL::raiseMsg('Thank you unsubscribe email confirmation');
            } else {
                SGL::raiseMsg('Thank you unsubscribe');
            }
        }
    }
    
    
    /**
    * e-mail/key pair validation and perform the requested 
    * action (subscribe, unsubscribe, update)
    *
    * @access public
    *
    */
    function _authorize(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'authorize.html';
        
        if (empty($input->actionKey)) {
            return;
        }
        
        $oList = & new DataObjects_Newsletter();
        $oList->email = $input->email;
        $oList->action_key = $input->actionKey;
        $noRows = $oList->find(true);
        
        if ($noRows != 1) {
           SGL::raiseMsg('Invalid e-mail / authorization code pair.');
           return;
        }
        
        // Subscribe
        if ($oList->action_request == 'subscribe') {
            $oList->action_request = '';
            $oList->action_key = '';
            $oList->status = 0;
            $oList->last_updated = SGL::getTime();
            $success = $oList->update();
            if ($success) {
                SGL::raiseMsg('Authorization accepted! Thank you for subscribing to our newsletter.');
                return;   
            }
        }
        
        // Unsubscribe
        if ($oList->action_request == 'unsubscribe') {
            $success = $oList->delete();
            if ($success) {
                SGL::raiseMsg('Authorization accepted! You were unsubscribed from our newsletter.');
                return;
            }
        }
        
        // Update subscription
        if ($oList->action_request == 'update') {
            $oList->action_request = '';
            $oList->action_key = '';
            $oList->last_updated = SGL::getTime();
            $success = $oList->update();
            if ($success) {
                SGL::raiseMsg('Authorization accepted! Thank you for updating your subscription.');
                return;
            }
        }
        
        SGL::raiseMsg('Tehere was an error processing your request.');        
    }
    
  
    /**
    * Send e-mail function
    *
    * @access public
    *
    */
    function _send(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $theme = $_SESSION['aPrefs']['theme'];
        $template = 'email_confim_action.html';
        $output->webRoot          = SGL_BASE_URL;
 
        //  initialise template engine
        $options = &PEAR::getStaticProperty('HTML_Template_Flexy','options');
        $options = array(
            'templateDir'       =>  SGL_THEME_DIR . '/' . $theme . '/' . $this->module . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/' . $this->module . PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/' . $theme . '/default'. PATH_SEPARATOR .
                                    SGL_THEME_DIR . '/default/default',
            'templateDirOrder'  => 'reverse',
            'multiSource'       => true,
            'compileDir'        => SGL_CACHE_DIR . '/tmpl/' . $theme,
            'forceCompile'      => SGL_FLEXY_FORCE_COMPILE,
            'debug'             => SGL_FLEXY_DEBUG,
            'allowPHP'          => SGL_FLEXY_ALLOW_PHP,
            'filters'           => SGL_FLEXY_FILTERS,
            'locale'            => SGL_FLEXY_LOCALE,
            'compiler'          => SGL_FLEXY_COMPILER,
            'valid_functions'   => SGL_FLEXY_VALID_FNS,
            'flexyIgnore'       => SGL_FLEXY_IGNORE,
            'globals'           => true,
            'globalfunctions'   => SGL_FLEXY_GLOBAL_FNS,
        );

        // Configure Flexy to use SGL ModuleOutput Plugin 
        // If an Output.php file exists in module's dir
        $customOutput = SGL_MOD_DIR . '/' . $this->module . '/classes/Output.php';
        if (is_readable($customOutput)) {
            $className = ucfirst($this->module) . 'Output';
            if (isset($options['plugins'])) {
                $options['plugins'] = $options['plugins'] + array($className => $customOutput);
            } else {
                $options['plugins'] = array($className => $customOutput);
            }
        }

        //  suppress notices in templates
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = true;
        $templ = & new HTML_Template_Flexy();
        $templ->compile($template);

        //  if some Flexy 'elements' exist in the output object, send them as
        //  2nd arg to Flexy::bufferedOutputObject()
        $elements = (   isset($output->flexyElements) && 
                        is_array($output->flexyElements))
                ? $output->flexyElements 
                : array();

        $bodyHtml = $templ->bufferedOutputObject($output, $elements);
 
 
        $headers['From'] = $conf['email']['admin'];
        $headers['Subject'] = $output->emailSubject;
        $crlf = SGL_String::getCrlf();
        $mime = & new Mail_mime($crlf);
        $mime->setHTMLBody($bodyHtml);
        $body = $mime->get();
        $hdrs = $mime->headers($headers);
        $mail = & SGL_Emailer::factory();
        $success = $mail->send($output->emailAddress, $hdrs, $body);
   
        if ($success) {
            //  redirect on success
            SGL::raiseMsg('Newsletter sent successfully');
        } else {
            SGL::raiseError('Problem sending email', SGL_ERROR_EMAILFAILURE);
        }
        
        return $success;
    }
    
    
    /**
    * Checks if a valid newsletter list exists
    *
    * @access   private
    * @author   Benea Rares <rbenea@bluestardesign.ro>
    * @param    string  $list   List name, if empty: return an array with all lists
    * @return   array    $ret   'id', 'name', 'descrisption'; False = list not exist
    * 
    */
    function _getList($listName = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        if (preg_match("(\W)",$listName)) {
            SGL::logMessage('Invalid list name: '.$listName);
            return false;
        }
        
        $conf = & $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB :: singleton();
        
        if ($listName == '') {
            $query = "SELECT * FROM {$conf['table']['newsletter']} WHERE status=9";
        } else {
            $query = "SELECT * FROM {$conf['table']['newsletter']} WHERE list='$listName' AND status=9";
        }
        
        $result = $dbh->query($query);
        if (is_a($result, 'PEAR_Error')) {
            return false;
        }
        
        if ($result->numRows() == 0) {
            SGL::logMessage('List does not exist: '.$listName);
            return false;
        } 
        
        if ($result->numRows() == 1) {
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
            $ret = array();
            $ret[] = array(
                'id' => $row['newsletter_id'],
                'name' => $row['list'],
                'description' => $row['name']);
            return $ret;
        }
        
        $ret = array();
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $ret[$row['newsletter_id']] = array(
                'name' => $row['list'],
                'description' => $row['name']);
        }
        
        return $ret;
    }
    
    
    /**
    * Generates a authorization key
    *
    * @access   private
    * @author   Benea Rares <rbenea@bluestardesign.ro>
    * @param    string  $str   Optional noise
    * @return   string  	   Return the key
    * 
    */
    function _generateKey($str = '')
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $str = $str . (string) rand();
        return md5($str);
    }    
    
    /**
     * Retrieves list of news list a user is subscribed to.
     * 
     * @access  public
     * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
     * @param   int     $userID
     * @return  array   $result
     * 
     */
     function getSubscribedLists($userID) 
     {
        SGL::logMessage(null, PEAR_LOG_DEBUG);        
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  get user details        
        $oUser = & new DataObjects_Usr();
        $oUser->get($userID);

        $dbh = SGL_DB::singleton();
        $query = "SELECT * FROM ". $conf['table']['newsletter'] ." WHERE email='". $oUser->email ."' AND status=0"; 
        $result = $dbh->getAssoc($query);

        return $result;        
     }

    /**
     * Retrieves list of news letter a user is not subsribed to.
     * 
     * @access  public
     * @author  Alexander J. Tarachanowicz II <ajt@localhype.net>
     * @param   int     $userID
     * @return  array   $newsLists
     * 
     */     
     function getUnsubscribedLists($userID) 
     {
        SGL::logMessage(null, PEAR_LOG_DEBUG);        
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $newsLists = $this->_getList();
        $subscribedLists = $this->getSubscribedLists($userID);

        foreach ($newsLists as $nKey => $nValues) {
            foreach ($subscribedLists as $sKey => $sValues) {
                if ($nValues['name'] == $sValues->list) {
                    unset($newsLists[$nKey]);
                }   
            }
        }
        return $newsLists;        
     }

}
?>