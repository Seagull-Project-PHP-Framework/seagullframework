<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Task.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Abstract task class.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @abstract
 */
class SGL_Task
{
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
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
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
        return PEAR::raiseError('an SGL_Task object was expected');
    }

    function main()
    {
        $ret = array();
        foreach ($this->aTasks as $k => $oTask) {
            $return = $this->aTasks[$k]->run($this->data);
            // log to system tmp dir if we're installing
            if (!defined('SGL_INSTALLED') && !SGL::runningFromCLI()) {
                $err = is_a($return, 'PEAR_Error')
                    ? print_r($return, 1)
                    : 'ok';
                $data = get_class($oTask) .': '. $err;
                error_log($data);
            }
            if (!isset($err) || $err == 'ok') {
                $ret[] = $return;
            }
        }
        return implode('', $ret);
    }
}

?>