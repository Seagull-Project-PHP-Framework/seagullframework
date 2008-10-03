<?php

/**
 * Starts the session.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Filter_CreateSession extends SGL_DecorateProcess
{
    function process(SGL_Request $input, SGL_Response $output)
    {
        SGL_Registry::set('session', new SGL_Session());
        $this->processRequest->process($input, $output);
    }
}

?>