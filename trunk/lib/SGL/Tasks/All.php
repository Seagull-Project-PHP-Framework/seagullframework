<?php
require_once dirname(__FILE__) . '/../Task.php';

function bool2words($key)
{
    return ($key === true || $key === 1) ? 'Yes' : 'No';        
}

function ini_get2($key)
{
    return (ini_get($key) == '1' || $key === true ? 1 : 0);        
}

class SGL_EnvSummaryTask extends SGL_Task 
{
    var $aData = array();
    var $title = '';
    var $mandatory = false;
    
    function render()
    {
        $html = '<table width="70%" border=1>';
        $html .= '<th colspan="3">'.$this->title.'</th>';
        if (!$this->mandatory) {
            $html .= '<tr><td>&nbsp;</td><td>Recommended</td><td>Actual</td></tr>';        
        }
        foreach ($this->aData as $k => $v) {
               //   3 cols: element name, recommended, actual
            $html .= '<tr>';
            $html .= '<td><strong>'.$k.'</strong></td>';               
            if (is_array($v)) {
                $html .= '<td colspan="2">'.$this->createComboBox($v).'</td>';
            } elseif ($this->mandatory) {
                $html .= '<td colspan="2">'.$v.'</td>';
            } else {
                $html .= '<td>'.$v.'</td>';
                $html .= '<td>'.$v.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
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
    
    function run()
    {
    	foreach (array(
	           'zlib', 'iconv', 'session', 'gd', 'mysql', 'mysqli', 'pgsql', 'oci8',
	           'curl', 'open_ssl', 'pcre', 'tokenizer', 'posix', 'domxml', 'tidy', 'apc') as $m) {
    		$this->aData[$m] = bool2words(extension_loaded($m));
    	}
    	return $this->render($this->aData);
    }   
}

class SGL_Task_GetPhpEnv extends SGL_EnvSummaryTask
{
    var $title = 'PHP Environment';
    var $mandatory = true;
    
    function run()
    {
        $this->aData['phpVersion'] = phpversion();
        $this->aData['operatingSystem'] = php_uname('s') .' '. php_uname('r') .', '. php_uname('m');
        $this->aData['webserverSapi'] = php_sapi_name();
    	return $this->render($this->aData);
    }
}

class SGL_Task_GetPhpIniValues extends SGL_EnvSummaryTask
{
    var $title = 'php.ini Settings';
    
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
    
    function run()
    {
        $installRoot = dirname(dirname(dirname(dirname(__FILE__))));
        $this->aData['installRoot'] = $installRoot;
        $this->aData['varDirExists'] = bool2words(file_exists($installRoot . '/var'));
        $this->aData['varDirIsWritable'] = bool2words(is_writable($installRoot . '/var'));
    	return $this->render($this->aData);
    }   
}

class SGL_Task_GetPearInfo extends SGL_EnvSummaryTask
{
    var $title = 'PEAR Environment';
    var $mandatory = true;
    
    function run()
    {
        $installRoot = dirname(dirname(dirname(dirname(__FILE__))));
        $this->aData['pearFolderExists'] = bool2words(file_exists($installRoot . '/lib/pear'));
        $this->aData['pearLibIsLoadable'] = bool2words(include_once $installRoot . '/lib/pear/PEAR.php');
        
        $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
        $this->aData['pearPathSetup'] = @ini_set('include_path',      '.' . $includeSeparator . $installRoot . '/lib/pear');
        $this->aData['pearSystemLibIsLoadable'] = bool2words(require_once 'System.php');
        $this->aData['pearRegistryLibIsLoadable'] = bool2words(require_once 'PEAR/Registry.php');
        $registry = new PEAR_Registry($installRoot . '/lib/pear');
        $this->aData['pearRegistryIsObject'] = bool2words(is_object($registry));
        $this->aData['pearPackages'] = $registry->_listPackages();
    	return $this->render($this->aData);
    }   
}
?>