<?php

/**
 * Core data processing routine.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_Controller_Main extends SGL2_ProcessRequest
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $ctlr = SGL2_Registry::get('controller');

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