<?php

define('EOL', "\n");

//  dependency types
define('SGL_NEUTRAL', 0);
define('SGL_RECOMMENDED', 1);
define('SGL_REQUIRED', 2);

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
            
            //  small exception for php version check
            if (preg_match("/>.*/", $depValue)) {
                $comparator = $depValue{0};
                $value = substr($depValue, 1);
                if (version_compare($actual, $value, 'ge')) {
                    $status = 'green';                
                } else {
                    $status = 'red';
                    SGL_Install::errorPush(PEAR::raiseError($error));
                }
            //  else evaluate conventional values
            } else {
                if ($actual == $depValue) {
                    $status = 'green';
                } else {
                    $status = 'red';
                    SGL_Install::errorPush(PEAR::raiseError($error));
                }
            }
        } elseif ($depType == SGL_RECOMMENDED) {
            if ($actual == $depValue) {
                $status = 'green';
            } else {
                $status = 'orange';
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

class SGL_Task_GetLoadedModules extends SGL_EnvSummaryTask
{
    var $title = 'Available Modules';
    var $aRequirements = array(
        'zlib' => array(SGL_RECOMMENDED => 1),
        'iconv' => array(SGL_RECOMMENDED => 1),
        'session' => array(SGL_REQUIRED => 1),
        'gd' => array(SGL_RECOMMENDED => 1),
        'mysql' => array(SGL_NEUTRAL => 0),
        'mysqli' => array(SGL_NEUTRAL => 0),
        'pgsql' => array(SGL_NEUTRAL => 0),
        'oci8' => array(SGL_NEUTRAL => 0),
        'curl' => array(SGL_RECOMMENDED => 1),
        'open_ssl' => array(SGL_RECOMMENDED => 1),
        'pcre' => array(SGL_REQUIRED => 1),
        'posix' => array(SGL_RECOMMENDED => 1),
        'domxml' => array(SGL_RECOMMENDED => 1),
        'tidy' => array(SGL_RECOMMENDED => 1),
        'apc' => array(SGL_RECOMMENDED => 1),
        );
    var $aErrors = array(
        'session' => 'You need the session extension to run Seagull',
        'pcre' => 'You need the prce extension to run Seagull',
    );

    function run()
    {
    	foreach ($this->aRequirements as $m => $dep) {
    		$this->aData[$m] = bool2int(extension_loaded($m));
    	}
    	return $this->render($this->aData);
    }   
}

class SGL_Task_GetPhpEnv extends SGL_EnvSummaryTask
{
    var $title = 'PHP Environment';
    var $mandatory = true;
    var $aRequirements = array(
        'phpVersion' => array(SGL_REQUIRED => '>4.3.0'),
        'operatingSystem' => array(SGL_NEUTRAL => 0),
        'webserverSapi' => array(SGL_NEUTRAL => 0),
        'webSeagullVersion' => array(SGL_NEUTRAL => 0),
    );
    var $aErrors = array(
        'phpVersion' => 'As a minimum you need to be running PHP version 4.3.0 to run Seagull',
        'operatingSystem' => '',
        'webserverSapi' => '',
        'webSeagullVersion' => '',
    );
    
    function run()
    {
        $this->aData['phpVersion'] = phpversion();
        $this->aData['operatingSystem'] = php_uname('s') .' '. php_uname('r') .', '. php_uname('m');
        $this->aData['webserverSapi'] = php_sapi_name();
        $this->aData['webSeagullVersion'] = file_get_contents(SGL_PATH . '/VERSION.txt');
    	return $this->render($this->aData);
    }
}

class SGL_Task_GetPhpIniValues extends SGL_EnvSummaryTask
{
    var $title = 'php.ini Settings';
    var $aRequirements = array(
        'safe_mode' => array(SGL_RECOMMENDED => 0),
        'register_globals' => array(SGL_RECOMMENDED => 0),
        'magic_quotes_gpc' => array(SGL_RECOMMENDED => 0),
        'magic_quotes_runtime' => array(SGL_RECOMMENDED => 0),
        'session.use_trans_sid' => array(SGL_RECOMMENDED => 0),
        'allow_url_fopen' => array(SGL_RECOMMENDED => 0),
        'file_uploads' => array(SGL_RECOMMENDED => 1),
        'post_max_size' => array(SGL_RECOMMENDED => '10MB'),
        'upload_max_filesize' => array(SGL_RECOMMENDED => '10MB'),
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
    	return $this->render($this->aData);
    }
}

class SGL_Task_GetFilesystemInfo extends SGL_EnvSummaryTask
{
    var $title = 'Filesystem info';
    var $mandatory = true;
    
    var $aRequirements = array(
        'installRoot' => array(SGL_NEUTRAL => 0),
        'varDirExists' => array(SGL_REQUIRED => 1),
        'varDirIsWritable' => array(SGL_REQUIRED => 1),
    );
    var $aErrors = array(
        'installRoot' => '',
        'varDirExists' => 'It appears you do not have a "var" folder, please create a folder with this name in the root of your Seagull install',
        'varDirIsWritable' => 'Your "var" dir is not writable by the webserver, please chmod it to 0777',
    );
    
    function run()
    {
        $this->aData['installRoot'] = SGL_PATH;
        $this->aData['varDirExists'] = bool2int(file_exists(SGL_PATH . '/var'));
        $this->aData['varDirIsWritable'] = bool2int(is_writable(SGL_PATH . '/var'));
    	return $this->render($this->aData);
    }   
}

class SGL_Task_GetPearInfo extends SGL_EnvSummaryTask
{
    var $title = 'PEAR Environment';
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
        $this->aData['pearFolderExists'] = bool2int(file_exists(SGL_PATH . '/lib/pear'));
        $this->aData['pearLibIsLoadable'] = bool2int(include_once SGL_PATH . '/lib/pear/PEAR.php');
        
        $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
        $ok = @ini_set('include_path',      '.' . $includeSeparator . SGL_PATH . '/lib/pear');
        $this->aData['pearPath'] = @ini_get('include_path');
        $this->aData['pearSystemLibIsLoadable'] = bool2int(require_once 'System.php');
        $this->aData['pearRegistryLibIsLoadable'] = bool2int(require_once 'PEAR/Registry.php');
        $registry = new PEAR_Registry(SGL_PATH . '/lib/pear');
        $this->aData['pearRegistryIsObject'] = bool2int(is_object($registry));
        $this->aData['pearBundledPackages'] = $registry->_listPackages();
    	return $this->render($this->aData);
    }   
}
?>