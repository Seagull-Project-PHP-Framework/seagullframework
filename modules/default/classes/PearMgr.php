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
// | PearMgr.php                                                               |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// |            Michael Willemot <michael@sotto.be>                            |
// +---------------------------------------------------------------------------+
// $Id: ModuleMgr.php,v 1.37 2005/06/22 00:32:36 demian Exp $

require_once dirname(__FILE__) . '/../../../lib/SGL/Manager.php';

define('SGL_ICONS_PER_ROW', 3);

/**
 * Manages packages from the PEAR channel.
 *
 * @package default
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.37 $
 */
class PearMgr extends SGL_Manager
{
    function ModuleMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Module Manager';
        $this->template     = 'moduleOverview.html';

        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'insert'    => array('insert', 'redirectToDefault'),
            'edit'      => array('edit'),
            'update'    => array('update', 'redirectToDefault'),
            'delete'    => array('delete', 'redirectToDefault'),
            'list'      => array('list'),
            'listPearPackages'  => array('listPearPackages'),
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

        $input->action = ($req->get('action')) ? $req->get('action') : 'overview';
        if (!is_null($input->from) && $input->action == 'overview') {
            $input->action = 'list';
        }
        $input->aDelete         = $req->get('frmDelete');
        $input->moduleId        = $req->get('frmModuleId');
        $input->module          = (object)$req->get('module');
        $input->module->is_configurable = (isset($input->module->is_configurable)) ? 1 : 0;
        $input->submit          = $req->get('submitted');

        //  validate fields
        $aErrors = array();
        if ($input->submit) {
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
            $input->isConfigurable = ($input->module->is_configurable) ? 'checked' : '';
            $this->validated = false;
        }
    }

    function _listPearPackages(&$input, &$output)
    {

        putenv('PHP_PEAR_INSTALL_DIR='.SGL_LIB_PEAR_DIR);
        putenv('PHP_PEAR_BIN_DIR=/usr/local/bin');
        putenv('PHP_PEAR_PHP_BIN=/usr/local/bin/php');

        $useDHTML = true;

        define('PEAR_Frontend_Web',1);

        if (!isset($_SESSION['_PEAR_Frontend_Web_js'])) {
            $_SESSION['_PEAR_Frontend_Web_js'] = false;
        }
        if (isset($_GET['enableJS']) && $_GET['enableJS'] == 1) {
            $_SESSION['_PEAR_Frontend_Web_js'] = true;
        }
        define('USE_DHTML_PROGRESS', (@$useDHTML && $_SESSION['_PEAR_Frontend_Web_js']));
        if (!isset($pear_user_config)) {
             $pear_user_config = dirname(dirname(__FILE__))."/pear.conf";
        }

        // Include needed files
        require_once 'PEAR/Registry.php';
        require_once 'PEAR/Config.php';
        require_once 'PEAR/Command.php';

        // Init PEAR Installer Code and WebFrontend
        $config  = $GLOBALS['_PEAR_Frontend_Web_config'] = &PEAR_Config::singleton($pear_user_config, '');
        PEAR_Command::setFrontendType("WebSGL");

        $ui = &PEAR_Command::getFrontendObject();

        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayFatalError"));

        // Cient requests an Image/Stylesheet/Javascript
//        if (isset($_GET["css"])) {
//            $ui->outputFrontendFile($_GET["css"], 'css');
//        }
//        if (isset($_GET["js"])) {
//            $ui->outputFrontendFile($_GET["js"], 'js');
//        }
//        if (isset($_GET["img"])) {
//            $ui->outputFrontendFile($_GET["img"], 'image');
//        }

        $verbose = $config->get("verbose");
        $cmdopts = array();
        $opts    = array();
        $params  = array();

        $URL = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        #$dir = substr(dirname(__FILE__), 0, -strlen('PEAR/PEAR')); // strip PEAR/PEAR

        #$_ENV['TMPDIR'] = $_ENV['TEMP'] = $dir.'tmp';
        $_ENV['TMPDIR'] = $_ENV['TEMP'] = SGL_TMP_DIR;

        if (!isset($_GET["command"])) {
            $_GET["command"] = 'list-all';
        }

        // Handle some diffrent Commands
        if (isset($_GET["command"])) {
            switch ($_GET["command"]) {
                case 'install':
                case 'uninstall':
                case 'upgrade':
                    if (USE_DHTML_PROGRESS && isset($_GET['dhtml'])) {
                        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, "displayErrorImg"));
                    }

                    $command = $_GET["command"];
                    $params = array($_GET["pkg"]);
                    $cmd = PEAR_Command::factory($command, $config);
                    $ok = $cmd->run($command, $opts, $params);

                    // success
                    if (USE_DHTML_PROGRESS && isset($_GET['dhtml'])) {
                        echo '<script language="javascript">';
                        if ($_GET["command"] == "uninstall") {
                            printf(' parent.deleteVersion(\'%s\'); ',  $_GET["pkg"]);
                            printf(' parent.displayInstall(\'%s\'); ', $_GET["pkg"]);
                            printf(' parent.hideDelete(\'%s\'); ',     $_GET["pkg"]);
                        } else {
                            printf(' parent.newestVersion(\'%s\'); ',  $_GET["pkg"]);
                            printf(' parent.hideInstall(\'%s\'); ',    $_GET["pkg"]);
                            printf(' parent.displayDelete(\'%s\'); ',  $_GET["pkg"]);
                        }
                        echo '</script>';
                        $html = sprintf('<img src="%s?img=install_ok" border="0">', $_SERVER['PHP_SELF']);
                        echo $js.$html;
                        exit;
                    }

                    if (isset($_GET['redirect']) && $_GET['redirect'] == 'info') {
                        $URL .= '?command=remote-info&pkg='.$_GET["pkg"];
                    } elseif (isset($_GET['redirect']) && $_GET['redirect'] == 'search') {
                        $URL .= '?command=search&userDialogResult=get&0='.$_GET["0"].'&1='.$_GET["1"];
                    } else {
                        $URL .= '?command=list-all&pageID='.$_GET['pageID'].'#'.$_GET["pkg"];
                    }
                    Header("Location: ".$URL);
                    exit;
                case 'remote-info':
                    $command = $_GET["command"];
                    $params = array($_GET["pkg"]);
                    $cmd = PEAR_Command::factory($command, $config);
                    $ok = $cmd->run($command, $opts, $params);

                    exit;
                case 'search':
                    list($name, $description) = $ui->userDialog('search',
                        array('Package Name', 'Package Info'), // Prompts
                        array(), array(), // Types, Defaults
                        'Package Search', 'pkgsearch' // Title, Icon
                        );

                    $command = $_GET["command"];
                    $params = array($name, $description);
                    $cmd = PEAR_Command::factory($command, $config);
                    $ok = $cmd->run($command, $opts, $params);

                    exit;
                case 'config-show':
                    $command = $_GET["command"];
                    $cmd = PEAR_Command::factory($command, $config);
                    $res = $cmd->run($command, $opts, $params);
                    foreach($GLOBALS['_PEAR_Frontend_Web_Config'] as $var => $value) {
                        $command = 'config-set';
                        $params = array($var, $value);
                        $cmd = PEAR_Command::factory($command, $config);
                        $res = $cmd->run($command, $opts, $params);
                    }

                    $URL .= '?command=config-show';
                    header("Location: ".$URL);
                    exit;
                case 'list-all':
                    $command = $_GET["command"];
                    $params = array();
                    if (isset($_GET["mode"]))
                        $opts['mode'] = $_GET["mode"];
                    $cmd = PEAR_Command::factory($command, $config);
                    #$ok = $cmd->run($command, $opts, $params);
                    $data = $cmd->run($command, $opts, $params);
                    foreach ($data['data'] as $array) {
                        foreach ($array as $catName => $v) {
                            print $catName."\n<br />";
                        }
                    }
                    #print_r($data);

                    exit;
                case 'show-last-error':
                    $GLOBALS['_PEAR_Frontend_Web_log'] = $_SESSION['_PEAR_Frontend_Web_LastError_log'];
                    $ui->displayError($_SESSION['_PEAR_Frontend_Web_LastError'], 'Error', 'error', true);
                    exit;
                default:
                    $command = $_GET["command"];
                    $cmd = PEAR_Command::factory($command, $config);
                    $res = $cmd->run($command, $opts, $params);

                    $URL .= '?command='.$_GET["command"];
                    header("Location: ".$URL);
                    exit;
            }
        }
    }
}
?>