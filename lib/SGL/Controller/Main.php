<?php

/**
 * Core data processing routine.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Controller_Main extends SGL_ProcessRequest
{
    public function process(SGL_Request $input, SGL_Response $output)
    {
        $ctlr = SGL_Registry::get('controller');

        //  process data if valid
        if ($ctlr->validate($input)) {
            $ok = $ctlr->process($input, $output);

        } else {
            $output->setMessages($ctlr->getMessages());
        }
        //  copy input to output
        foreach ($input->getClean() as $k => $v) {
            $output->set($k, $v);
        }
        $ctlr->display($output);
    }
}

?>