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
// | DetectEnv.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
define('EOL', "\n");

//  dependency types
define('SGL_NEUTRAL', 0);
define('SGL_RECOMMENDED', 1);
define('SGL_REQUIRED', 2);
define('SGL_FORBIDDEN', 3);

require_once dirname(__FILE__) . '/../Request.php';
require_once dirname(__FILE__) . '/../Task.php';

function bool2words($key)
{
    return ($key === true || $key === 1) ? 'Yes' : 'No';
}

function bool2int($key)
{
    return ($key === true || $key === 1) ? 1 : 0;
}

function ini_get2($key)
{
    return (ini_get($key) == '1' || $key === true ? 1 : 0);
}

/**
 * @package Task
 */
class SGL_EnvSummaryTask extends SGL_Task
{
    var $aData = array();
    var $aErrors = array();
    var $aRequirements = array();
    var $title = '';
    var $mandatory = false;

    function render()
    {
        $html = '<table width="70%" border=1>'.EOL;
        $html .= '<th colspan="3">'.$this->title.'</th>'.EOL;

        // check if in "php.ini Settings" portion of environment detection
        if (array_key_exists('register_globals', $this->aData)) {
            $cfg_file_path = (get_cfg_var("cfg_file_path"))
                ? get_cfg_var("cfg_file_path")
                : "<strong>php.ini not available</strong>";
            $html .= '<tr><td colspan="3"><strong>Note:</strong> Your php configuration file (php.ini) is located at: ' . $cfg_file_path . '</td></tr>';

            // check if open_basedir is set and warn user
            $open_basedir = ini_get('open_basedir');
            if (!empty($open_basedir)) {
                $html .= '<tr><td colspan="3"><span style="color: orange; font-weight: bold;">Warning:</span> ' .
                         'This server seems to be using the <strong>open_basedir</strong> php setting to limit ' .
                         'all file operations to the following directory: <strong>' . $open_basedir . '</strong>. ' .
                         'This may cause your installation and application ' .
                         'to work incorrectly.</td></tr>';
            }
        }
        if (!$this->mandatory) {
            $html .= '<tr><td>&nbsp;</td><td><em>Recommended</em></td><td><em>Actual</em></td></tr>'.EOL;
        }
        foreach ($this->aData as $k => $v) {
            $discoveredValue = (is_int($v)) ? bool2words($v) : $v;
            $html .= '<tr>'.EOL;
            $html .= '<td><strong>'.SGL_Inflector::getTitleFromCamelCase($k).'</strong></td>';
            if (is_array($v)) {
                $html .= '<td colspan="2">'.$this->createComboBox($v).'</td>';
            } elseif ($this->mandatory) {
                $html .= '<td colspan="2">'.$this->processDependency($this->aRequirements[$k], @$this->aErrors[$k], $k, $v).$discoveredValue.'</span></td>';
            } else {
                $html .= '<td>'.$this->processRecommended($this->aRequirements[$k]).'</td>';
                $html .= '<td>'.$this->processDependency($this->aRequirements[$k], @$this->aErrors[$k], $k, $v).$discoveredValue.'</span></td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>'.EOL;
        return $html;
    }

    function processDependency($aRequirement, $error, $key, $actual)
    {
        $depType = key($aRequirement);
        $depValue = $aRequirement[$depType];// what value the dep requires

        if ($depType == SGL_REQUIRED) {

            //  exception for php version check
            if (preg_match("/>.*/", $depValue)) {
                $value = substr($depValue, 1);
                if (version_compare($actual, $value, 'g')) {
                    $status = 'green';
                } else {
                    $status = 'red';
                    SGL_Install_Common::errorPush(PEAR::raiseError($error));
                }
            //  else evaluate conventional values
            } else {
                if ($actual == $depValue) {
                    $status = 'green';
                } else {
                    $status = 'red';
                    SGL_Install_Common::errorPush(PEAR::raiseError($error));
                }
            }
        } elseif ($depType == SGL_RECOMMENDED) {
            if ($actual == $depValue) {
                $status = 'green';
            } else {
                $status = 'orange';
            }
        } elseif ($depType == SGL_FORBIDDEN) {
            if ($actual == $depValue) {
                $status = 'green';
            } else {
                $status = 'red';
                SGL_Install_Common::errorPush(PEAR::raiseError($error));
            }
        } else {
            //  neutral, no colour tag
            return '';
        }
        $html = "<span style=\"color:$status\">";
        return $html;
    }

    function processRecommended($aRequirement)
    {
        $depType = key($aRequirement);
        $depValue = $aRequirement[$depType];
        if ($depType == SGL_NEUTRAL) {
            $ret = '--';
        } else {
            $ret = is_int($depValue) ? bool2words($depValue) : $depValue;
        }
        return $ret;
    }

    function createComboBox($aData)
    {
        $html = '<select name="pearPackages" multiple="multiple">';
        foreach ($aData as $option) {
            $html .= "<option value=\"$option\">$option";
        }
        $html .= '</select>';
        return $html;
    }
}

/**
 * @package Task
 */
class SGL_Task_GetLoadedModules extends SGL_EnvSummaryTask
{
    var $title = 'Available Modules';
    var $key = 'loaded_modules';
    var $aRequirements = array(
        'curl' => array(SGL_RECOMMENDED => 1),
        'gd' => array(SGL_RECOMMENDED => 1),
        'iconv' => array(SGL_RECOMMENDED => 1),
        'mysql' => array(SGL_NEUTRAL => 0),
        'mysqli' => array(SGL_NEUTRAL => 0),
        'oci8' => array(SGL_NEUTRAL => 0),
        'odbc' => array(SGL_NEUTRAL => 0),
        'openssl' => array(SGL_RECOMMENDED => 1),
        'pcre' => array(SGL_REQUIRED => 1),
        'pgsql' => array(SGL_NEUTRAL => 0),
        'posix' => array(SGL_RECOMMENDED => 1),
        'session' => array(SGL_REQUIRED => 1),
        'tidy' => array(SGL_RECOMMENDED => 1),
        'zlib' => array(SGL_RECOMMENDED => 1),
        );
    var $aErrors = array(
        'session' => 'You need the session extension to run Seagull',
        'pcre' => 'You need the pcre extension to run Seagull',
        'apc' => 'Problems have been reported running apc, please disable to continue',
    );

    function run()
    {
        if (SGL::isPhp5()) {
            $this->aRequirements['dom'] = array(SGL_RECOMMENDED => 1);
        } else {
            $this->aRequirements['domxml'] = array(SGL_RECOMMENDED => 1);
        }
        foreach ($this->aRequirements as $m => $dep) {
            $this->aData[$m] = bool2int(extension_loaded($m));
        }
        return $this->render($this->aData);
    }
}

/**
 * @package Task
 */
class SGL_Task_GetPhpEnv extends SGL_EnvSummaryTask
{
    var $title = 'PHP Environment';
    var $key = 'php_environment';
    var $mandatory = true;
    var $aRequirements = array(
        'phpVersion' => array(SGL_REQUIRED => '>4.2.3'),
        'operatingSystem' => array(SGL_NEUTRAL => 0),
        'webserverSapi' => array(SGL_NEUTRAL => 0),
        'webserverPort' => array(SGL_NEUTRAL => 0),
        'webserverSoftware' => array(SGL_NEUTRAL => 0),
        'seagullVersion' => array(SGL_NEUTRAL => 0),
    );
    var $aErrors = array(
        'phpVersion' => 'As a minimum you need to be running PHP version 4.3.0 to run Seagull',
        'operatingSystem' => '',
        'webserverSapi' => '',
        'webserverPort' => '',
        'webserverSoftware' => '',
        'seagullVersion' => '',
    );

    function run()
    {
        $this->aData['phpVersion'] = phpversion();
        $this->aData['operatingSystem'] = php_uname('s') .' '. php_uname('r') .', '. php_uname('m');
        $this->aData['webserverSapi'] = php_sapi_name();
        $this->aData['webserverPort'] = $_SERVER['SERVER_PORT'];
        $this->aData['webserverSoftware'] = $_SERVER['SERVER_SOFTWARE'];
        $this->aData['seagullVersion'] = file_get_contents(SGL_PATH . '/VERSION.txt');
        return $this->render($this->aData);
    }
}

/**
 * @package Task
 */
class SGL_Task_GetPhpIniValues extends SGL_EnvSummaryTask
{
    var $title = 'php.ini Settings';
    var $key = 'php.ini_settings';
    var $aRequirements = array(
        'safe_mode' => array(SGL_REQUIRED => 0),
        'register_globals' => array(SGL_RECOMMENDED => 0),
        'magic_quotes_gpc' => array(SGL_RECOMMENDED => 0),
        'magic_quotes_runtime' => array(SGL_RECOMMENDED => 0),
        'session.use_trans_sid' => array(SGL_RECOMMENDED => 0),
        'allow_url_fopen' => array(SGL_RECOMMENDED => 0),
        'file_uploads' => array(SGL_RECOMMENDED => 1),
        'post_max_size' => array(SGL_RECOMMENDED => '10M'),
        'upload_max_filesize' => array(SGL_RECOMMENDED => '10M'),
        );

    var $aErrors = array(
        'safe_mode' => "This software will not work correctly if safe_mode is enabled",
        'memory_limit' => "Please set the option 'memory_limit' in your php.ini to a minimum of 16MB",
        );

    function run()
    {
        $this->aData['safe_mode'] = ini_get2('safe_mode');
        $this->aData['register_globals'] = ini_get2('register_globals');
        $this->aData['magic_quotes_gpc'] = ini_get2('magic_quotes_gpc');
        $this->aData['magic_quotes_runtime'] = ini_get2('magic_quotes_runtime');
        $this->aData['session.use_trans_sid'] = ini_get2('session.use_trans_sid');
        $this->aData['allow_url_fopen'] = ini_get2('allow_url_fopen');
        $this->aData['file_uploads'] = ini_get2('file_uploads');
        $this->aData['post_max_size'] = ini_get('post_max_size');
        $this->aData['upload_max_filesize'] = ini_get('upload_max_filesize');
        if (ini_get('memory_limit')) {
            $this->aRequirements['memory_limit'] = array(SGL_REQUIRED => '>8M');
            $this->aData['memory_limit'] = ini_get('memory_limit');
        }
        return $this->render($this->aData);
    }
}

/**
 * @package Task
 */
class SGL_Task_GetFilesystemInfo extends SGL_EnvSummaryTask
{
    var $title = 'Filesystem info';
    var $key = 'filesystem_info';
    var $mandatory = true;

    var $aRequirements = array(
        'installRoot' => array(SGL_NEUTRAL => 0),
        'varDirExists' => array(SGL_REQUIRED => 1),
        'varDirIsWritable' => array(SGL_REQUIRED => 1),
        'wwwDirIsWritable' => array(SGL_REQUIRED => 1),
    );
    var $aErrors = array(
        'installRoot' => '',
        'varDirExists' => 'It appears you do not have a "var" folder, please create a folder with this name in the root of your Seagull install',
        'varDirIsWritable' => "Your \"var\" dir is not writable by the webserver, to make it writable type the following at the command line: chmod 777 %e",
        'wwwDirIsWritable' => "Your \"www\" dir is not writable by the webserver, to make it writable type the following at the command line: chmod 777 %e",
    );

    function run()
    {
        $this->aData['installRoot'] = SGL_PATH;
        $this->aData['varDirExists'] = bool2int(file_exists(SGL_VAR_DIR));
        $this->aData['varDirIsWritable'] = bool2int(is_writable(SGL_VAR_DIR));
        $this->aData['wwwDirIsWritable'] = bool2int(is_writable(SGL_WEB_ROOT));
        return $this->render($this->aData);
    }
}

/**
 * @package Task
 */
class SGL_Task_GetPearInfo extends SGL_EnvSummaryTask
{
    var $title = 'PEAR Environment';
    var $key = 'pear_environment';
    var $mandatory = true;

    var $aRequirements = array(
        'pearFolderExists' => array(SGL_REQUIRED => 1),
        'pearLibIsLoadable' => array(SGL_REQUIRED => 1),
        'pearPath' => array(SGL_NEUTRAL => 0),
        'pearSystemLibIsLoadable' => array(SGL_REQUIRED => 1),
        'pearRegistryLibIsLoadable' => array(SGL_REQUIRED => 1),
        'pearRegistryIsObject' => array(SGL_REQUIRED => 1),
        'pearBundledPackages' => array(SGL_NEUTRAL => 0),
    );

    function run()
    {
        if (defined('SGL_PEAR_INSTALLED')) {
            $this->aData['pearFolderExists'] = true;
            $this->aData['pearLibIsLoadable'] = true;
            $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
            $this->aData['pearPath'] = @ini_get('include_path');
            $this->aData['pearSystemLibIsLoadable'] = true;
            $this->aData['pearRegistryLibIsLoadable'] = true;
            require_once 'System.php';
            require_once 'PEAR/Registry.php';
            $registry = new PEAR_Registry();
            $this->aData['pearRegistryIsObject'] = bool2int(is_object($registry));
            $this->aData['pearBundledPackages'] = $registry->_listPackages();
        } else {
            $this->aData['pearFolderExists'] = bool2int(file_exists(SGL_LIB_PEAR_DIR));
            $this->aData['pearLibIsLoadable'] = bool2int(include_once SGL_LIB_PEAR_DIR . '/PEAR.php');
            $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
            $ok = @ini_set('include_path',      '.' . $includeSeparator . SGL_LIB_PEAR_DIR);
            $this->aData['pearPath'] = @ini_get('include_path');
            $this->aData['pearSystemLibIsLoadable'] = bool2int(require_once 'System.php');
            $this->aData['pearRegistryLibIsLoadable'] = bool2int(require_once 'PEAR/Registry.php');
            $registry = new PEAR_Registry(SGL_LIB_PEAR_DIR);
            $this->aData['pearRegistryIsObject'] = bool2int(is_object($registry));
            $aPackages = $registry->_listPackages();
            sort($aPackages);
            $this->aData['pearBundledPackages'] = $aPackages;
        }
        return $this->render($this->aData);
    }
}
?>