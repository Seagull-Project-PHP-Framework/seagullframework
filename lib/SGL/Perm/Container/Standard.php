<?php

class Perm_Standard
{
    var $container;
    
    var $editGroupUrl = '';
    
    function Perm_Standard($options) {}
      
    function &readRights() 
    {
        return array();
    }
       
    function checkRight()
    {
        if(SGL_Session::getAuthLevel() == SGL_ADMIN) {
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