<?php

class SGL_TaskRunner
{
   /**
    * collection of Task objects
    * @var array
    */ 
    var $aTasks = array();
    var $data = null;
    
    function addData($data)
    {
        $this->data = $data;
    }
    
   /**
    * Method to register a new Task object in
    * the runner collection of tasks
    *
    * @param object $oTask of type Task
    * @return boolean true on add success false on failure
    * @access public
    */
    function addTask($oTask)
    {
        if (is_a($oTask, 'SGL_Task')) {
            $this->aTasks[] = & $oTask;
            return true;
        }
        return PEAR::raiseError('unexpected object type');
    }
    
    function main()
    {
        $ret = array();
        foreach ($this->aTasks as $oTask) {
            $ret[] = $oTask->run($this->data);                
        }
        return implode('', $ret);
    }
}
?>