<?php

class SGL_Task_ExecuteAjaxAction extends SGL_ProcessRequest
{
    function process(&$input, &$output)
    {
        $req = $input->getRequest();
        $moduleName = $req->getModuleName();
        $method = $req->getActionName();

        $providerFile = SGL_MOD_DIR . '/' .($moduleName) . '/classes/' .
            ucfirst($moduleName) . 'AjaxProvider.php';

        if (!is_file($providerFile)) {
            return PEAR::raiseError('Ajax provider file could not be located',
                SGL_ERROR_NOFILE);
        }
        require_once $providerFile;
        $providerClass = ucfirst($moduleName) . 'AjaxProvider';
        if (!class_exists($providerClass)) {
            return PEAR::raiseError('Ajax provider class does not exist',
                SGL_ERROR_NOCLASS);
        }
        $oProvider = new $providerClass();
        if (method_exists($oProvider, $method)) {
            $response = $oProvider->$method();
            $output->data = $oProvider->processResponse($response);
        } else {
            $output->data = 'requested method does not exist';
        }
    }
}
?>