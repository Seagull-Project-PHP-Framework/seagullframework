<?php

class SGL_Task_BuildAjaxHeaders extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Error::count()) {
            header('HTTP/1.0 500 text/json');
            header('Cache-Control: no-store, no-cache');
            return;
        }

        if (!headers_sent()) {
            if (!isset($output->responseFormat)) {
                $output->responseFormat = SGL_RESPONSEFORMAT_JSON;
            }

            // return encoded response with appropriate headers
            switch (strtoupper($output->responseFormat)) {
            case SGL_RESPONSEFORMAT_JSON:
                $data = $output->data;
                $output->data = SGL_AjaxProvider::jsonEncode($data);
                header('Content-Type: text/plain');
                break;

            case SGL_RESPONSEFORMAT_HTML:
                header('Content-Type: text/html');
                break;

            case SGL_RESPONSEFORMAT_PLAIN:
                header('Content-Type: text/plain');
                break;

            case SGL_RESPONSEFORMAT_JAVASCRIPT:
                header('Content-Type: text/javascript');
                break;

            case SGL_RESPONSEFORMAT_XML:
                header('Content-Type: text/xml');
                break;

            default:
                header('Content-Type: text/plain');
            }
        }
    }
}
?>