<?php

class SGL_Filter_CleanOutputData extends SGL_DecorateProcess
{
    /**
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function process($input, $output)
    {
        $this->processRequest->process($input, $output);

        $aExceptions = array(
            'aCssFiles', 'aHeaders', 'aJavascriptFiles', 'aRawJavascriptFiles',
            'scriptOpen', 'scriptClose',
            'aOnLoadEvents', 'aOnUnloadEvents', 'aOnReadyDomEvents',
            'onLoad', 'onReadyDom', 'onUnload', 'conf',
            'webRoot', 'currUrl', 'sessID', 'theme', 'imagesDir',
            'isMinimalInstall', 'showExecutionTimes'
        );
        $aProps = array_keys(get_object_vars($output));
        $oData  = new stdClass();
        foreach ($aProps as $prop) {
            if (!in_array($prop, $aExceptions)) {
                $oData->$prop = $output->$prop;
            }
            unset($output->$prop);
        }
        $output->data = $oData;
    }
}

?>