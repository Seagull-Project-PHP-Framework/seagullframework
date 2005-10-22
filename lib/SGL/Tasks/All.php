<?php
require_once SGL_LIB_DIR . '/SGL/Task.php';

class SGL_Task_CheckLoadedModules extends SGL_Task
{
    var $aModules = array();
    
    function run()
    {
    	foreach (array(
    	           'zlib', 'iconv', 'session', 'gd', 'mysql', 'mysqli', 'pgsql', 'oci8',
    	           'curl', 'open_ssl', 'pcre', 'tokenizer', 'posix', 'domxml', 'tidy', 'apc') as $m) {
    		$aModules[$m] = extension_loaded($m);
    	}
    	return $aModules;            
    }   
}