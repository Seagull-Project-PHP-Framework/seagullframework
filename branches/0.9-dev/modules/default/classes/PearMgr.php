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
// | PearMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// |            Michael Willemot <michael@sotto.be>                            |
// +---------------------------------------------------------------------------+
// $Id: ModuleMgr.php,v 1.37 2005/06/22 00:32:36 demian Exp $

/**
 * Manages packages from the PEAR channel.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.37 $
 */
class PearMgr extends SGL_Manager
{
    function PearMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'PEAR Manager';
        $this->template     = 'pearList.html';

        $this->_aActionsMapping =  array(
            'list'      => array('list'),
            'doRequest'   => array('doRequest'),
        );
        $this->aChannels = array(
            'pear.phpkitchen.com'   => 'Seagull',
            'pear.php.net'          => 'PEAR',
            'pearified.com'         => 'Pearified',
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->template        = $this->template;

        //  default action is 'overview' unless paging through results,
        //  in which case default is 'list'
        $input->from            = $req->get('pageID');
        $input->totalItems      = $req->get('totalItems');
        $input->action = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete         = $req->get('frmDelete');
        $input->submitted       = $req->get('submitted');

        //  PEAR params
        $input->mode            = $req->get('mode');
        $input->channel         = $req->get('channel');
        $input->command         = $req->get('command');
        $input->pkg             = $this->restoreSlashes($req->get('pkg'));

        //  validate fields
        $aErrors = array();
        if ($input->submitted) {
            $aFields = array(
                'name' => 'Please, specify a name',
                'title' => 'Please, specify a title',
                'description' => 'Please, specify a description',
                'icon' => 'Please, specify the name of the icon-file'
            );
            foreach ($aFields as $field => $errorMsg) {
                if (empty($input->module->$field)) {
                    $aErrors[$field] = $errorMsg;
                }
            }
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'moduleEdit.html';
            $this->validated = false;
        }
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->aChannels = $this->aChannels;
        $output->addOnLoadEvent("switchRowColorOnHover()");
    }

    function restoreSlashes($str)
    {
        return str_replace('^', '/',$str);
    }

    function _cmd_list(&$input, &$output)
    {

    }

    function _cmd_doRequest(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $ok = ini_set('max_execution_time', 180);
        putenv('PHP_PEAR_INSTALL_DIR='.SGL_LIB_PEAR_DIR);

        #$useDHTML = true;
        define('PEAR_Frontend_Web',1);

        // Include needed files
        require_once 'PEAR/Registry.php';
        require_once 'PEAR/Config.php';
        require_once 'PEAR/Command.php';

        // Init PEAR Installer Code and WebFrontend
        #$config  = $GLOBALS['_PEAR_Frontend_Web_config'] = &PEAR_Config::singleton();
        $config  = $GLOBALS['_PEAR_Frontend_Web_config'] =
            &PEAR_Config::singleton($this->getPearConfigPath(), $this->getPearConfigPath());
#        $config  = $GLOBALS['_PEAR_Frontend_Web_config'] = &PEAR_Config::singleton('', SGL_MOD_DIR . '/default/pear.conf');

#        $config->set('php_dir', SGL_LIB_PEAR_DIR);
        #$config->set('php_dir', SGL_LIB_PEAR_DIR, $layer='system'); <- this is ignored ??
        //  hence crude hack ; -)
//$GLOBALS['_PEAR_Config_instance']->_registry['system']->statedir = SGL_LIB_PEAR_DIR . '/.registry';
//$GLOBALS['_PEAR_Config_instance']->configuration['system']['php_dir'] = SGL_LIB_PEAR_DIR;
//$GLOBALS['_PEAR_Config_instance']->configuration['system']['doc_dir'] = SGL_TMP_DIR;
//$GLOBALS['_PEAR_Config_instance']->configuration['system']['data_dir'] = SGL_TMP_DIR;
//$GLOBALS['_PEAR_Config_instance']->configuration['system']['test_dir'] = SGL_TMP_DIR;

        $config->set('default_channel', $input->channel);
        $config->set('preferred_state', 'devel');

        PEAR_Command::setFrontendType("WebSGL");
        $ui = &PEAR_Command::getFrontendObject();

        $verbose = $config->get("verbose");
        $cmdopts = array();
        $opts    = array();
        $params  = array();

        $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        #$dir = substr(dirname(__FILE__), 0, -strlen('PEAR/PEAR')); // strip PEAR/PEAR

        #$_ENV['TMPDIR'] = $_ENV['TEMP'] = $dir.'tmp';
        $_ENV['TMPDIR'] = $_ENV['TEMP'] = SGL_TMP_DIR;

        if ($input->command == 'sgl-install' || $input->command == 'sgl-install') {
            if (!is_writable(SGL_MOD_DIR)) {
                SGL::raiseError('your module directory must be writable '.
                    'by the webserver before you attempt this command',
                        SGL_ERROR_FILEUNWRITABLE);
                return;
            }
        }
        if (is_null($input->command)) {
            $input->command  = 'sgl-list-all';
        }
        $params = array();
        if ($input->mode) {
            $opts['mode'] = 'installed';
        }

        #PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));

        $cache = & SGL_Cache::singleton();
        $cacheId = 'pear'.$input->command.$input->mode;

        switch ($input->command) {

        case 'sgl-list-all':
            SGL::logMessage('made it to list-all', PEAR_LOG_DEBUG);
            if ($serialized = $cache->get($cacheId, 'pear')) {
                $data = unserialize($serialized);
                if (PEAR::isError($data)) {
                    return $data;
                }
                SGL::logMessage('pear data from cache', PEAR_LOG_DEBUG);
            } else {
                $cmd = PEAR_Command::factory($input->command, $config);
                $data = $cmd->run($input->command, $opts, $params);
                if (PEAR::isError($data)) {
                    return $data;
                }
                $serialized = serialize($data);
                $cache->save($serialized, $cacheId, 'pear');
                SGL::logMessage('pear data from db', PEAR_LOG_DEBUG);
            }
            break;

        case 'sgl-install':
        case 'sgl-uninstall':
        case 'sgl-upgrade':
            $params = array($input->pkg);
            $cmd = PEAR_Command::factory($input->command, $config);
            if (PEAR::isError($cmd)) {
                return SGL::raiseError('prob with PEAR_Command object');
            }
            ob_start();
            $ok = $cmd->run($input->command, $opts, $params);
            $pearOutput = ob_get_contents();
            ob_end_clean();

            if ($ok) {
                print $pearOutput;#exit;
                $this->_redirectToDefault($input, $output);
            } else {
                print '<pre>';print_r($ok);
            }
            break;
        }

       # foreach ($data['data'] as $aPackages) {
       #     foreach ($aPackages as $aPackage) {
                // [0] name
                // [1] remote version
                // [2] local version
                // [3] desc
                // [4] (array) deps
        #        $result .= $aPackage[0]."\n<br />";
#print '<pre>';print_r($aPackage);
         #   }
        #}
        $output->result = @$data['data'];
#print '<pre>';print_r($aPackage);

    }

    /**
     * Returns path to PEAR config file, and creates file if it doesn't exist.
     *
     * @return string
     */
    function getPearConfigPath()
    {
        if (!is_file(SGL_TMP_DIR . '/pear.conf')) {
            $conf = &PEAR_Config::singleton();

            $conf->set('default_channel', 'pear.php.net');
            $conf->set('http_proxy', SGL_LIB_PEAR_DIR);
            $conf->set('doc_dir', SGL_TMP_DIR);
            $conf->set('php_dir', SGL_LIB_PEAR_DIR);
            $conf->set('web_dir', SGL_WEB_ROOT);
            $conf->set('cache_dir', SGL_TMP_DIR);
            $conf->set('data_dir', SGL_TMP_DIR);
            $conf->set('test_dir', SGL_TMP_DIR);
            $conf->set('preferred_state', 'devel');

//            $conf->set('auto_discover ', '');
//            $conf->set('preferred_mirror', '');
//            $conf->set('remote_config', '');
//            $conf->set('bin_dir', '');
//            $conf->set('ext_dir', '');
//            $conf->set('php_bin', '');
//            $conf->set('cache_ttl', '');
//            $conf->set('umask', '');
//            $conf->set('verbose', '');
//            $conf->set('password', '');
//            $conf->set('sig_bin', '');
//            $conf->set('sig_keydir', '');
//            $conf->set('sig_keyid', '');
//            $conf->set('sig_type', '');
//            $conf->set('username', '');

            $ok = $conf->writeConfigFile(SGL_TMP_DIR . '/pear.conf', $layer = 'user'/*, $data = null*/);
        }

        return SGL_TMP_DIR . '/pear.conf';
    }
}

        // Handle some diffrent Commands
//        if (isset($_GET["command"])) {
//            switch ($_GET["command"]) {
//                case 'install':
//                case 'uninstall':
//                case 'upgrade':
//                    if (USE_DHTML_PROGRESS && isset($_GET['dhtml'])) {
//                        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayErrorImg"));
//                    }
//
//                    $command = $_GET["command"];
//                    $params = array($_GET["pkg"]);
//                    $cmd = PEAR_Command::factory($command, $config);
//                    $ok = $cmd->run($command, $opts, $params);
//
//                    // success
//                    if (USE_DHTML_PROGRESS && isset($_GET['dhtml'])) {
//                        echo '<script language="javascript">';
//                        if ($_GET["command"] == "uninstall") {
//                            printf(' parent.deleteVersion(\'%s\'); ',  $_GET["pkg"]);
//                            printf(' parent.displayInstall(\'%s\'); ', $_GET["pkg"]);
//                            printf(' parent.hideDelete(\'%s\'); ',     $_GET["pkg"]);
//                        } else {
//                            printf(' parent.newestVersion(\'%s\'); ',  $_GET["pkg"]);
//                            printf(' parent.hideInstall(\'%s\'); ',    $_GET["pkg"]);
//                            printf(' parent.displayDelete(\'%s\'); ',  $_GET["pkg"]);
//                        }
//                        echo '</script>';
//                        $html = sprintf('<img src="%s?img=install_ok" border="0">', $_SERVER['PHP_SELF']);
//                        echo $js.$html;
//                        exit;
//                    }
//
//                    if (isset($_GET['redirect']) && $_GET['redirect'] == 'info') {
//                        $URL .= '?command=remote-info&pkg='.$_GET["pkg"];
//                    } elseif (isset($_GET['redirect']) && $_GET['redirect'] == 'search') {
//                        $URL .= '?command=search&userDialogResult=get&0='.$_GET["0"].'&1='.$_GET["1"];
//                    } else {
//                        $URL .= '?command=list-all&pageID='.$_GET['pageID'].'#'.$_GET["pkg"];
//                    }
//                    Header("Location: ".$URL);
//                    exit;
//
//                case 'remote-info':
//                    $command = $_GET["command"];
//                    $params = array($_GET["pkg"]);
//                    $cmd = PEAR_Command::factory($command, $config);
//                    $ok = $cmd->run($command, $opts, $params);
//                    exit;
//
//                case 'search':
//                    list($name, $description) = $ui->userDialog('search',
//                        array('Package Name', 'Package Info'), // Prompts
//                        array(), array(), // Types, Defaults
//                        'Package Search', 'pkgsearch' // Title, Icon
//                        );
//
//                    $command = $_GET["command"];
//                    $params = array($name, $description);
//                    $cmd = PEAR_Command::factory($command, $config);
//                    $ok = $cmd->run($command, $opts, $params);
//                    exit;
//
//                case 'config-show':
//                    $command = $_GET["command"];
//                    $cmd = PEAR_Command::factory($command, $config);
//                    $res = $cmd->run($command, $opts, $params);
//                    foreach($GLOBALS['_PEAR_Frontend_Web_Config'] as $var => $value) {
//                        $command = 'config-set';
//                        $params = array($var, $value);
//                        $cmd = PEAR_Command::factory($command, $config);
//                        $res = $cmd->run($command, $opts, $params);
//                    }
//
//                    $URL .= '?command=config-show';
//                    header("Location: ".$URL);
//                    exit;
//
//
//                case 'show-last-error':
//                    $GLOBALS['_PEAR_Frontend_Web_log'] = $_SESSION['_PEAR_Frontend_Web_LastError_log'];
//                    $ui->displayError($_SESSION['_PEAR_Frontend_Web_LastError'], 'Error', 'error', true);
//                    exit;
//
//                default:
//                    $command = $_GET["command"];
//                    $cmd = PEAR_Command::factory($command, $config);
//                    $res = $cmd->run($command, $opts, $params);
//
//                    $URL .= '?command='.$_GET["command"];
//                    header("Location: ".$URL);
//                    exit;
//            }
//        }
?>