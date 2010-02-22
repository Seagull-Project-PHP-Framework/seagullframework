<?php

/**
 * @package Task
 */
class SGL_Task_ExecuteAjaxAction2 extends SGL_ProcessRequest
{
    /**
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function process(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $moduleName   = $input->getRequest()->getModuleName();
        $providerFile = SGL_MOD_DIR . "/$moduleName/classes/"
            . ucfirst($moduleName) . 'AjaxProvider.php';

        if (!is_file($providerFile)) {
            SGL::raiseError('Ajax provider file could not be located',
                SGL_ERROR_NOFILE);
        } else {
            require_once $providerFile;
            $providerClass = ucfirst($moduleName) . 'AjaxProvider';
            if (!class_exists($providerClass)) {
                SGL::raiseError('Ajax provider class does not exist',
                    SGL_ERROR_NOCLASS);
            } else {
                self::assignOutputData($output);

                // execute action method
                $oProvider = new $providerClass();
                $ok = $oProvider->process($input, $output);

                $output->responseFormat = $oProvider->responseFormat;
                if (!empty($oProvider->aMsg)) {
                    $output->aMsg = $oProvider->aMsg;
                }
            }
        }
    }

    /**
     * Assign vars.
     *
     * @param SGL_Output $output
     */
    public static function assignOutputData($output)
    {
        // lang data
        $output->currLang     = SGL::getCurrentLang();
        $output->charset      = SGL::getCurrentCharset();
        $output->currFullLang = $_SESSION['aPrefs']['language'];
        $output->langDir      = ($output->currLang == 'ar'
                || $output->currLang == 'he')
            ? 'rtl' : 'ltr';

        // setup theme
        $output->theme = isset($_SESSION['aPrefs']['theme'])
            ? $_SESSION['aPrefs']['theme']
            : SGL_Config::get('site.defaultTheme');

        // setup SGL data
        $output->conf      = SGL_Config::singleton()->getAll();
        $output->webRoot   = SGL_BASE_URL;
        $output->imagesDir = SGL_BASE_URL . '/themes/' . $output->theme . '/images';

        // additional information
        $output->scriptOpen  = "\n<script type='text/javascript'>\n//<![CDATA[\n";
        $output->scriptClose = "\n//]]>\n</script>\n";
    }
}
?>