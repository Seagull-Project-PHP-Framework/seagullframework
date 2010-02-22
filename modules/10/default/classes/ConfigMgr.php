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
 */
class ConfigMgr extends SGL_Manager
{
    var $aDbTypes;
    var $aLogTypes;
    var $aLogNames;
    var $aMtaBackends;
    var $aMtaAuthentication;
    var $aCensorModes;
    var $aNavDrivers;
    var $aNavRenderers;
    var $aTranslationContainers;

    function ConfigMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Config Manager';
        $this->template = 'configEdit.html';
        $this->aDbTypes = array(
            'mysql_SGL' => 'mysql_SGL',
            'mysqli_SGL' => 'mysqli_SGL',
            'mysql' => 'mysql',
            'mysqli' => 'mysqli',
            'pgsql' => 'pgsql',
            'oci8_SGL' => 'oci8',
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
        $this->aMtaAuthentication = array(
            '0' => 'no',
            'LOGIN' => 'LOGIN',
            'PLAIN' => 'PLAIN',
            'CRAM-MD5' => 'CRAM-MD5',
            'DIGEST-MD5' => 'DIGEST-MD5',
            );
        $this->aCensorModes = array(
            0 => 'no censoring',
            1 => 'exact match',
            2 => 'word beginning',
            3 => 'word fragment',
            );
        $this->aDbDoDebugLevels = range(0, 5);
        $this->aMysqlEngines = array(
            0            => 'server default',
            'myisam'     => 'MyISAM',
            'innodb'     => 'InnoDB',
            'ndbcluster' => 'MySQL Cluster'
        );

        //  any files where the last 3 letters are 'Nav' in the modules/navigation/classes will be returned
        $navDir = SGL_MOD_DIR . '/navigation/classes';
        $this->aNavDrivers   = SGL_Util::getAllClassesFromFolder($navDir, '.*Driver');
        $this->aNavRenderers = SGL_Util::getAllClassesFromFolder($navDir, '.*Renderer');
        $this->aSessHandlers = array('file' => 'file', 'database' => 'database');
        $stratDir = SGL_CORE_DIR . '/UrlParser';
        $aUrlHandlers = SGL_Util::getAllClassesFromFolder($stratDir);
        foreach ($aUrlHandlers as $k => $v) {
            require_once $stratDir . '/' . $k . '.php';
            $stratName = 'SGL_UrlParser_' . $k;
            $oStrategy = new $stratName();
            if ($oStrategy->makeLink('', '', '', array(), '', 0, null)) {
                $this->aUrlHandlers[$stratName] = str_replace('Strategy', '', $v);
            }
        }
        $this->aTemplateEngines = array(
            'flexy'   => 'Flexy',
            'savant2' => 'Savant2',
            'smarty'  => 'Smarty');
        $this->_aActionsMapping =  array(
            'edit'   => array('edit'),
            'update' => array('update', 'redirectToDefault'),
        );
        $this->aTranslationContainers = array('file' => 'file', 'db' => 'database');

		$this->aWysiwygEditor = array('fckeditor' => 'fckeditor', 'xinha' => 'xinha', 'htmlarea' => 'htmlarea', 'tinyfck' => 'tinyfck');
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated    = true;
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = 'masterMinimal.html';
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'edit';
        $input->aDelete     = $req->get('frmDelete');
        $input->submitted   = $req->get('submitted');
        $input->conf        = $req->get('conf');
        $input->displayTab  = 'generalSiteOptions';

        $aErrors = array();
        if ($input->submitted) {
            $v = & new Validate();
            if (empty($input->conf['site']['baseUrl']) ||
                !preg_match('/^https?:\/\/[a-z0-9]+/i', $input->conf['site']['baseUrl'])) {
                $aErrors['baseUrl'] = 'Please enter a valid URI';
            }

            if (empty($input->conf['site']['masterTemplate'])) {
                $aErrors['masterTemplate'] = 'Please enter a valid template name';
            }

            if (empty($input->conf['site']['masterLayout'])) {
                $input->conf['site']['masterLayout'] = 'layout-navtop-3col.css';
                //$aErrors['masterLayout'] = 'Please enter a valid layout name';
            }

            //  paths
            if (empty($input->conf['path']['webRoot'])) {
                $aErrors['webRoot'] = 'Please enter a valid path';
                // unset() because we use isset() in lib/SGL/Task/Init.php to check this variable
                unset($input->conf['path']['webRoot']);
            }

            if (empty($input->conf['path']['installRoot'])) {
                $aErrors['installRoot'] = 'Please enter a valid path';
            }

            // MTA backend & params
            $aBackends = array_keys($this->aMtaBackends);
            if (empty($input->conf['mta']['backend']) ||
                !in_array($input->conf['mta']['backend'], $aBackends)) {
                $aErrors['mtaBackend'] = 'Please choose a valid MTA backend';
                $input->displayTab = 'mtaOptions';
            }

            switch ($input->conf['mta']['backend']) {

            case 'sendmail':
                if (empty($input->conf['mta']['sendmailPath']) ||
                    !is_file($input->conf['mta']['sendmailPath'])) {
                    $aErrors['sendmailPath'] = 'Please enter a valid path to Sendmail';
                    $input->displayTab = 'mtaOptions';
                }
                if (empty($input->conf['mta']['sendmailArgs'])) {
                    $aErrors['sendmailArgs'] = 'Please enter valid Sendmail arguments';
                    $input->displayTab = 'mtaOptions';
                }
                break;

            case 'smtp':
                $aAuthentication = array_keys($this->aMtaAuthentication);
                if (!in_array($input->conf['mta']['smtpAuth'], $aAuthentication)) {
                    $aErrors['mtaBackend'] = 'Please choose a valid authentication method';
                    $input->displayTab = 'mtaOptions';
                }
                if ($input->conf['mta']['smtpAuth'] != 0) {
                    if (empty($input->conf['mta']['smtpUsername'])) {
                        $aErrors['smtpUsername'] = 'Please enter a valid Username';
                        $input->displayTab = 'mtaOptions';
                    }
                    if (empty($input->conf['mta']['smtpPassword'])) {
                        $aErrors['smtpPassword'] = 'Please enter a valid Password';
                        $input->displayTab = 'mtaOptions';
                    }
                }
                break;
            }
            //  session validations
            if (  !empty($input->conf['session']['singleUser'])
                && empty($input->conf['session']['extended'])) {
                    $aErrors['singleUser'] = 'Single session per user requires extended session';
                    $input->displayTab = 'sessionOptions';
            }
            if (   !empty($input->conf['session']['extended'])
                && $input->conf['session']['handler'] != 'database') {
                    $aErrors['extendedSession'] = 'Extended session requires database session handling';
                    $input->displayTab = 'sessionOptions';
            }

            // table prefix
            if (!empty($input->conf['db']['prefix'])) {
                $pattern = '/^[a-zA-Z]([a-zA-Z0-9]+)?_?$/';
                if (!preg_match($pattern, $input->conf['db']['prefix'])) {
                    $aErrors['db']['prefix'] = 'Only letters and digits are ' .
                        'allowed, first symbol must be a letter, last symbol ' .
                        'can be an underscore';
                    $input->displayTab = 'databaseOptions';
                }
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
        $output->aDbTypes           = $this->aDbTypes;
        $output->aLogTypes          = $this->aLogTypes;
        $output->aLogPriorities     = $aLogLevels;
        $output->aEmailThresholds   = $aLogLevels;
        $output->aMtaBackends       = $this->aMtaBackends;
        $output->aMtaAuthentication = $this->aMtaAuthentication;
        $output->aCensorModes       = $this->aCensorModes;
        $output->aNavDrivers        = $this->aNavDrivers;
        $output->aNavRenderers      = $this->aNavRenderers;
        $output->aSessHandlers      = $this->aSessHandlers;
        $output->aUrlHandlers       = $this->aUrlHandlers;
        $output->aTemplateEngines       = $this->aTemplateEngines;
        $output->aTranslationContainers = $this->aTranslationContainers;
        $output->aDbDoDebugLevels = $this->aDbDoDebugLevels;
        $output->aMysqlEngines    = $this->aMysqlEngines;
        $output->aMysqlEngines[0] = SGL_Output::translate($output->aMysqlEngines[0]);
		$output->aWysiwygEditor = $this->aWysiwygEditor;
        //  retrieve installed languages
        if ($this->conf['translation']['container'] == 'db') {
            $output->aInstalledLangs = $this->trans->getLangs();
        } else {
            $output->aInstalledLangs = SGL_Util::getLangsDescriptionMap(array(),
                SGL_LANG_ID_TRANS2);
        }
        $output->addOnLoadEvent("showSelectedOptions('configuration','$output->displayTab')");

        //  disable translation options depending on the selected translation container.
        $output->addOnLoadEvent("toggleTransElements()");
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($this->conf['tuples']['demoMode']) && $this->conf['tuples']['demoMode'] == true) {
            SGL::raiseMsg('Config settings cannot be modified in demo mode',
                false, SGL_MESSAGE_WARNING);
            return false;
        }
        //  lib cache is enabled by setting file flag in seagull/var
        $cacheFileFlag = SGL_VAR_DIR . '/ENABLE_LIBCACHE.txt';
        $cachedLibsFile = SGL_VAR_DIR . '/cachedLibs.php';
        if ($input->conf['cache']['libCacheEnabled']) {
            if (!is_file($cacheFileFlag)) {
                $ok = touch($cacheFileFlag);
            }
        } else {
            if (is_file($cacheFileFlag)) {
                $ok = unlink($cacheFileFlag);
            }
            if (is_file($cachedLibsFile)) {
                $ok = unlink($cachedLibsFile);
            }
        }
        //  add version info which is not available in form
        $c = &SGL_Config::singleton();
        $dbType = $c->get(array('db' => 'type')); // get db type before merge
        $c->merge($input->conf);
        $c->set('tuples', array('version' => SGL_SEAGULL_VERSION));
        //  write configuration to file
        $ok = $c->save();

        if (!is_a($ok, 'PEAR_Error')) {
            //  check if db type has changed
            if ($dbType != $input->conf['db']['type']) {
                //  we need to remove DB service, or SyncSequences will
                //  use it and think the db type is the old one and run the
                //  wrong sync code.
                $locator = & SGL_ServiceLocator::singleton();
                $locator->remove('DB');
                //  rebuild sequences
                require_once SGL_CORE_DIR . '/Task/Install.php';
                $res = SGL_Task_SyncSequences::run();
            }

            if (isset($res) && PEAR::isError($res)) {
                SGL::raiseMsg('config info successfully updated but failed syncing sequences',
                    true, SGL_MESSAGE_WARNING);
            } else {
                SGL::raiseMsg('config info successfully updated', true, SGL_MESSAGE_INFO);
            }
        } else {
            SGL::raiseError('There was a problem saving your configuration, make sure /var is writable',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }
}
?>
