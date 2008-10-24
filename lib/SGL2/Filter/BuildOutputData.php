<?php

/**
 * Assign output vars for template.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL2_Filter_BuildOutputData extends SGL2_DecorateProcess
{
    /**
     * Main routine.
     *
     * @param SGL2_Request $input
     * @param SGL2_Response $output
     */
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $this->_processRequest->process($input, $output);

        $this->_addOutputData($input, $output);
    }

    /**
     * Adds output data to SGL2_Response object.
     *
     * @param SGL2_Response $output
     */
    protected function _addOutputData(SGL2_Request $input, SGL2_Response $output)
    {
        // setup login stats
        if (SGL2_Session::getRoleId() > SGL2_GUEST) {
            $output->loggedOnUser   = SGL2_Session::getUsername();
            $output->loggedOnUserID = SGL2_Session::getUid();
            $output->loggedOnSince  = strftime("%H:%M:%S", SGL2_Session::get('startTime'));
            $output->loggedOnDate   = strftime("%B %d", SGL2_Session::get('startTime'));
            $output->isMember       = true;
        }
        // request data
        if ($input->getType() != SGL2_Request::CLI) {
            $output->remoteIp = $_SERVER['REMOTE_ADDR'];
            $output->currUrl  = $this->_getCurrentUrlFromRoutes();
        }
        // lang data
        $output->currLang     = SGL2_Translation::getDefaultLangCode();
        $output->charset      = SGL2_Translation::getDefaultLangCodeCharset();
        $output->currFullLang = $_SESSION['aPrefs']['language'];
        $output->langDir      = ($output->currLang == 'ar'
                || $output->currLang == 'he')
            ? 'rtl' : 'ltr';

        // setup theme
        $output->theme = isset($_SESSION['aPrefs']['theme'])
            ? $_SESSION['aPrefs']['theme']
            : 'default';

        // Setup SGL data
        $output->webRoot          = SGL2_BASE_URL;
        $output->imagesDir        = SGL2_BASE_URL . '/themes/' . $output->theme . '/images';
        $output->versionApi       = SGL2_SEAGULL_VERSION;
        $output->sessId           = SGL2_Session::getId();

        // Additional information
        $output->scriptOpen         = "\n<script type='text/javascript'>\n//<![CDATA[\n";
        $output->scriptClose        = "\n//]]>\n</script>\n";
        $output->showExecutionTimes = isset($_SESSION['aPrefs']['showExecutionTimes'])
            ? $_SESSION['aPrefs']['showExecutionTimes']
            : 1;
    }

    /**
     * Get current URL in $_SERVER['PHP_SELF'] style.
     *
     * @return string
     */
    protected function _getCurrentUrlFromRoutes()
    {
        $url     = SGL2_Registry::get('url');
        $currUrl = $url->toString();
        $baseUrl = $url->getBaseUrl($skipProto = false, $includeFc = false);
        return str_replace($baseUrl, '', $currUrl);
    }
}

?>