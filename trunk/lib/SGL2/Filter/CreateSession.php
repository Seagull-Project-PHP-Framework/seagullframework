<?php

/**
 * Starts the session.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_Filter_CreateSession extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        SGL2_Registry::set('session', new SGL2_Session());
        $this->processRequest->process($input, $output);
    }
}

?>