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

        $req = $input->getRequest();
        $mgr = $input->get('manager');

        $mgr->validate($req, $input);
        $input->aggregate($output);

        $ok = $mgr->process($input, $output);
        if (PEAR::isError($ok)) {
            //  stop with error page
            SGL::displayStaticPage($ok->getMessage());
        }
    }
}
?>