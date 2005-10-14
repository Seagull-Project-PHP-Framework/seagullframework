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
// | ConfigMgr.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: ConfigMgr.php,v 1.32 2005/06/23 18:21:25 demian Exp $

require_once 'Config.php';
require_once 'Validate.php';

/**
 * To manage administering global config file.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.32 $
 * @since   PHP 4.1
 */
class ConfigMgr extends SGL_Manager
{
    var $aDbTypes;
    var $aLogTypes;
    var $aLogNames;
    var $aMtaBackends;
    var $aCensorModes;
    var $aNavDrivers;

    function ConfigMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
        $this->module = 'default';
        $this->pageTitle = 'Config Manager';
        $this->template = 'configEdit.html';
        $this->aDbTypes = array(
            'mysql_SGL' => 'mysql_SGL',
            'mysql' => 'mysql',
            'pgsql' => 'pgsql',
            'oci8_SGL' => 'oci8',
            'odbc' => 'odbc',
            );
        $this->aLogTypes = array(
            'file' => 'file',
            'mcal' => 'mcal',
            'sql' => 'sql',
            'syslog' => 'syslog',
            );
        $this->aMtaBackends = array(
            'mail' => 'mail() function',
            'smtp' => 'SMTP',
            'sendmail' => 'Sendmail',
            );
        $this->aCensorModes = array(
            0 => 'no censoring',
            1 => 'exact match',
            2 => 'word beginning',
            3 => 'word fragment',
            );
        //  any files where the last 3 letters are 'Nav' in the modules/navigation/classes will be returned
        $this->aNavDrivers = SGL_Util::getAllNavDrivers();
        $this->aSessHandlers = array('file' => 'file', 'database' => 'database');
        $this->aUrlHandlers = array(
            'SGL_UrlParserSefStrategy' => 'Seagull SEF',
            'SGL_UrlParserClassicStrategy' => 'Classic');
        $this->aTemplateEngines = array(
            'flexy' => 'Flexy',
            'smarty' => 'Smarty');
        $this->_aActionsMapping =  array(
            'edit'   => array('edit'), 
            'update' => array('update', 'redirectToDefault'), 
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->aStyleFiles  = SGL_Util::getStyleFiles();
        $this->validated    = true;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = 'masterMinimal.html';
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'edit';
        $input->aDelete     = $req->get('frmDelete');
        $input->submit      = $req->get('submitted');
        $input->conf        = $req->get('conf');

        $aErrors = array();
        if ($input->submit) {
            $v = & new Validate();
            if (empty($input->conf['site']['baseUrl']) || 
                !preg_match('/^https?:\/\/[a-z0-9]+/i', $input->conf['site']['baseUrl'])) {
                $aErrors['baseUrl'] = 'Please enter a valid URI';
            }
            //  filter site name for chars not suited to ini files
            $input->conf['site']['name'] = SGL_String::stripIniFileIllegalChars($input->conf['site']['name']);
            
            // MTA backend & params
            $aBackends = array_keys($this->aMtaBackends);
            if (empty($input->conf['mta']['backend']) ||
                !in_array($input->conf['mta']['backend'], $aBackends)) {
                $aErrors['mtaBackend'] = 'Please choose a valid MTA backend';
            }
            
            //  catch invalid template engine
            if ($input->conf['site']['templateEngine'] == 'smarty') {
                $aErrors['templateEngine'] = 'The Smarty template hooks have not been implemented yet';
            }
            
            //  catch invalid URL handler
            if ($input->conf['site']['urlHandler'] == 'SGL_UrlParserClassicStrategy') {
                $aErrors['urlHandler'] = 'The classic URL handler has not been implemented yet';
            }
            
            switch ($input->conf['mta']['backend']) {
                
            case 'sendmail':
                if (empty($input->conf['mta']['sendmailPath']) ||
                    !file_exists($input->conf['mta']['sendmailPath'])) {
                    $aErrors['sendmailPath'] = 'Please enter a valid path to Sendmail';
                }
                if (empty($input->conf['mta']['sendmailArgs'])) {
                    $aErrors['sendmailArgs'] = 'Please enter valid Sendmail arguments';
                }
                break;
                
            case 'smtp':
                if ($input->conf['mta']['smtpAuth'] == 1) {
                    if (empty($input->conf['mta']['smtpUsername'])) {
                        $aErrors['smtpUsername'] = 'Please enter a valid Username';
                    }
                    if (empty($input->conf['mta']['smtpPassword'])) {
                        $aErrors['smtpPassword'] = 'Please enter a valid Password';
                    }
                }
                break;
            }

            //  session validations
            if (  !empty($input->conf['site']['single_user']) 
                && empty($input->conf['site']['extended_session'])) {
                    $aErrors['single_user'] = 'Single session per user requires extended session';
            }
            if (   !empty($input->conf['site']['extended_session']) 
                && $input->conf['site']['sessionHandler'] != 'database') {
                    $aErrors['extended_session'] = 'Extended session requires database session handling';
            }
        }
        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'configEdit.html';
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        require_once SGL_DAT_DIR . '/ary.logLevels.php';
        $output->conf = $this->conf;
        $output->aDbTypes = $this->aDbTypes;
        $output->aLogTypes = $this->aLogTypes;
        $output->aLogPriorities = $aLogLevels;
        $output->aEmailThresholds = $aLogLevels;
        $output->aMtaBackends = $this->aMtaBackends;
        $output->aCensorModes = $this->aCensorModes;
        $output->aNavDrivers = $this->aNavDrivers;
        $output->aStyleFiles = $this->aStyleFiles;
        $output->aSessHandlers = $this->aSessHandlers;
        $output->aUrlHandlers = $this->aUrlHandlers;
        $output->aTemplateEngines = $this->aTemplateEngines;
    }

    function _edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  add version info which is not available in form        
        $c = &SGL_Config::singleton();
        $c->replace($input->conf);
        $c->set('tuples', array('version' => SGL_SEAGULL_VERSION));
        
        //  write configuration to file
        $ok = $c->save(SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.php');

        if (!is_a($ok, 'PEAR_Error')) {
            SGL::raiseMsg('config info successfully updated');
        } else {
            SGL::raiseError('There was a problem saving your configuration, make sure /var is writable', 
                SGL_ERROR_FILEUNWRITABLE);
        }
    }
}
?>