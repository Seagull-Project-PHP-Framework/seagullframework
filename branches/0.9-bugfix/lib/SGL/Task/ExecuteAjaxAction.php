<?php

/**
 * @package Task
 */
class SGL_Task_ExecuteAjaxAction extends SGL_ProcessRequest
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req        = $input->getRequest();
        $moduleName = $req->getModuleName();
        $method     = $req->getActionName();

        $providerFile = SGL_MOD_DIR . '/' .($moduleName) . '/classes/' .
            ucfirst($moduleName) . 'AjaxProvider.php';

        if (!is_file($providerFile)) {
            SGL::raiseError('Ajax provider file could not be located', SGL_ERROR_NOFILE);
        } else {
            require_once $providerFile;
            $providerClass = ucfirst($moduleName) . 'AjaxProvider';
            if (!class_exists($providerClass)) {
                SGL::raiseError('Ajax provider class does not exist', SGL_ERROR_NOCLASS);
            }
        }

        if (SGL_Error::count()) {
            return;
        } else {
            //  execute action method
            $oProvider = new $providerClass();
            $oProvider->process($input, $output);
            //  BC
            if (!empty($oProvider->responseFormat)) {
                $output->responseFormat = $oProvider->responseFormat;
            }
            //  assign messages to output object
            if (count($oProvider->aMsg)) {
                $output->aMsg = $oProvider->aMsg;
            }
        }
    }
}
?>