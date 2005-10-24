<?php
require_once dirname(__FILE__) . '/../Task.php';

function bool2int($key)
{
    return ($key === true or $key === 1 ? 1 : 0);        
}

function ini_get2($key)
{
    return (ini_get($key) == '1' || $key === true ? 1 : 0);        
}

class SGL_Task_GetLoadedModules extends SGL_Task
{
    var $aModules = array();
    
    function run()
    {
    	foreach (array(
    	           'zlib', 'iconv', 'session', 'gd', 'mysql', 'mysqli', 'pgsql', 'oci8',
    	           'curl', 'open_ssl', 'pcre', 'tokenizer', 'posix', 'domxml', 'tidy', 'apc') as $m) {
    		$this->aModules[$m] = extension_loaded($m);
    	}
    	return $this->aModules;            
    }   
}

class SGL_Task_GetPhpEnv extends SGL_Task
{
    var $aEnv = array();
    
    function run()
    {
        $this->aEnv['phpVersion'] = phpversion();
        $this->aEnv['operatingSystem'] = php_uname('s') .' '. php_uname('r') .', '. php_uname('m');
        $this->aEnv['webserverSapi'] = php_sapi_name();
    	return $this->aEnv;
    }   
}

class SGL_Task_GetPhpIniValues extends SGL_Task
{
    var $aValues = array();
    
    function run()
    {
        $this->aValues['safe_mode'] = ini_get2('safe_mode');
        $this->aValues['register_globals'] = ini_get2('register_globals');
        $this->aValues['magic_quotes_gpc'] = ini_get2('magic_quotes_gpc');
        $this->aValues['magic_quotes_runtime'] = ini_get2('magic_quotes_runtime');
        $this->aValues['session.use_trans_sid'] = ini_get2('session.use_trans_sid');
        $this->aValues['allow_url_fopen'] = ini_get2('allow_url_fopen');
        $this->aValues['file_uploads'] = ini_get2('file_uploads');
        $this->aValues['post_max_size'] = ini_get('post_max_size');
        $this->aValues['upload_max_filesize'] = ini_get('upload_max_filesize');
    	return $this->aValues;
    }   
}

class SGL_Task_GetFilesystemInfo extends SGL_Task
{
    var $aPerms = array();
    
    function run()
    {
        $installRoot = dirname(dirname(dirname(dirname(__FILE__))));
        $this->aPerms['installRoot'] = $installRoot;
        $this->aPerms['varDirExists'] = bool2int(file_exists($installRoot . '/var'));
        $this->aPerms['varDirIsWritable'] = bool2int(is_writable($installRoot . '/var'));
    	return $this->aPerms;
    }   
}

class SGL_Task_GetPearInfo extends SGL_Task
{
    var $aPearInfo = array();
    
    function run()
    {
        $installRoot = dirname(dirname(dirname(dirname(__FILE__))));
        $this->aPearInfo['pearFolderExists'] = bool2int(file_exists($installRoot . '/lib/pear'));
        $this->aPearInfo['pearLibIsLoadable'] = bool2int(include_once $installRoot . '/lib/pear/PEAR.php');
        
        $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
        $this->aPearInfo['pearPathSetup'] = @ini_set('include_path',      '.' . $includeSeparator . $installRoot . '/lib/pear');
        $this->aPearInfo['pearSystemLibIsLoadable'] = bool2int(require_once 'System.php');
        $this->aPearInfo['pearRegistryLibIsLoadable'] = bool2int(require_once 'PEAR/Registry.php');
        $registry = new PEAR_Registry($installRoot . '/lib/pear');
        $this->aPearInfo['pearRegistryIsObject'] = bool2int(is_object($registry));
        $this->aPearInfo['pearPackages'] = $registry->_listPackages();
    	return $this->aPearInfo;
    }   
}