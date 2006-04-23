<?php

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
    function run($data = null)
    {
        return;
    }

   /**
    * Example ...
    * @access private
    */
    function &_getDal()
    {
        $oServiceLocator = &ServiceLocator::instance();
        $oDal = $oServiceLocator->get('dal');
        if (!$oDal) {
            $oDal = &DA_FooBar::singleton();
            $oServiceLocator->register('dal', $oDal);
        }
        return $oDal;
    }
}

/**
 * Used for building and running a task list.
 *
 */
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