<?php

#require_once SGL_CORE_DIR . '/ServiceLocator.php';

class SGL_Task
{   
   /**
    * object of type DAL
    * @var object
    */
    var $oDal;
    
    function SGL_Task()
    {
        #$this->oDal = & $this->_getDal();
    }
    
    /**
     * @abstract
     *
     */
    function run()
    {
        return;
    }
    
   /**
    * Method to register Data Access Layer
    * @access private
    */
    function &_getDal()
    {
        $oServiceLocator = &ServiceLocator::instance();
        $oDal = $oServiceLocator->get('dal');
        if (!$oDal) {
            $oDal = &MAX_Dal_Maintenance::singleton();
            $oServiceLocator->register('dal', $oDal);
        }
        return $oDal;
    }
}

?>