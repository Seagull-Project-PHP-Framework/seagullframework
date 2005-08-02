<?php

class Perm_standard
{
    var $container;
    
    var $editGroupUrl = '';
    
    function Perm_standard($options)
    {
        // nothing here
    }
      
    function &readRights() 
    {
        return array();
    }
       
    function checkRight()
    {
        if(Session::getAuthLevel() == SGL_ADMIN) {
            return true;
        } else {
            return false;
        }
    }
    
    function listUsers(&$output)
    {
        // nothing here
    }
        
    
}

?>