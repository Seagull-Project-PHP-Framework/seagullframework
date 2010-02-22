<?php
/**
 * Core data processing routine.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_CoreProcessor extends SGL_ProcessRequest
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req  = $input->getRequest();
        $mgr  = $input->get('manager');

        $mgr->validate($req, $input);
        $input->aggregate($output);

        //  process data if valid
        if ($mgr->isValid()) {
            $ok = $mgr->process($input, $output);
            if (SGL_Error::count() && SGL_Session::getRoleId() != SGL_ADMIN
                    && SGL_Config::get('debug.production')) {
                $mgr->handleError(SGL_Error::getLast(), $output);
            }
        }
        $mgr->display($output);
    }
}
?>