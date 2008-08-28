<?php

/**
 * Clean output data object before sending it to the client.
 *
 * @package Task
 */
class SGL_Task_CleanOutputData extends SGL_DecorateProcess
{
    static protected $_aExceptions = array(
        'aCssFiles', 'aHeaders', 'aJavascriptFiles', 'aRawJavascriptFiles',
        'scriptOpen', 'scriptClose',
        'aOnLoadEvents', 'aOnUnloadEvents', 'aOnReadyDomEvents',
        'onLoad', 'onReadyDom', 'onUnload', 'conf', '_aJsExportVars',
        'webRoot', 'currUrl', 'sessID', 'theme', 'imagesDir',
        'isMinimalInstall', 'showExecutionTimes'
    );

    /**
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function process($input, $output)
    {
        $this->processRequest->process($input, $output);

        if (!isset($output->data)) {
            $aProps = array_keys(get_object_vars($output));
            $oData  = new stdClass();
            foreach ($aProps as $prop) {
                if (!in_array($prop, self::$_aExceptions)) {
                    $oData->$prop = $output->$prop;
                }
                unset($output->$prop);
            }
            $output->data = $oData;
        }
    }
}

?>