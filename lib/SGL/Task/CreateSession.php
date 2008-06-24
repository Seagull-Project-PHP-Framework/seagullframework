<?php

/**
 * Starts the session.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_CreateSession extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $input->set('session', new SGL_Session());
        $this->processRequest->process($input, $output);
    }
}

?>