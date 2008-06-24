<?php

require_once 'DB/DataObject.php';

class Example extends SGL_Manager
{
	var $methodTable;

    function Example()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        
		$this->methodTable = array(
			"isMember" => array(
				"description"	=> "Return true if user is logged",
				"access"		=> "remote",
				"arguments" 	=> array (),
			)
		);

    }
    
    
	function isMember( ) 
	{
		SGL::logMessage(null, PEAR_LOG_DEBUG);
		if ( SGL_Session::getRoleId() == SGL_MEMBER) {
			return true;
		} else {
			return false;
		}
	}
		
}
?>