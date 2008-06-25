<?php

/**
 * @package Task
 */
class SGL_Task_StripMagicQuotes extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = $input->getRequest();
        SGL_String::dispelMagicQuotes($req->aProps);
        $input->setRequest($req);

        $this->processRequest->process($input, $output);
    }
}

?>