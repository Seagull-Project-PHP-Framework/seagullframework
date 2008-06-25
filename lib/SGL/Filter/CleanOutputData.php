<?php

class SGL_Filter_CleanOutputData extends SGL_DecorateProcess
{
    function process($input, $output)
    {
        $this->processRequest->process($input, $output);

        //  catch session timeout
        if (SGL_Error::count()) {
            if (SGL_Error::getLast()->getCode() == SGL_ERROR_INVALIDSESSION) {
                $aMsg = array(
                  'message' => 'session timeout',
                  'type'    => SGL_MESSAGE_ERROR,
                );
                $output->aMsg($aMsg);
            }
        }

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $data = new stdClass();
        $aExceptions = array('aCssFiles', 'aHeaders',
            'aJavascriptFiles', 'aOnLoadEvents', 'aOnUnloadEvents', 'aOnReadyDomEvents',
            'onLoad', 'onReadyDom', 'onUnload', 'conf');
        foreach ($output as $k => $v) {
            if (!in_array($k, $aExceptions)) {
                $data->$k = $v;
            }
            unset($output->$k);
        }
        $output->data = $data;
    }
}

?>