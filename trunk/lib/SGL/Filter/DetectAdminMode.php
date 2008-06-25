<?php

/**
 * Set an admin mode to give priviledged session.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_DetectAdminMode extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  set adminMode session if allowed
        $req = SGL_Request::singleton();
        $adminKey = $req->get('adminKey');
        if (SGL_Config::get('site.adminKey') && $adminKey == SGL_Config::get('site.adminKey')) {
            SGL_Session::set('adminMode', true);
        }

        $this->processRequest->process($input, $output);
    }
}

?>