<?php

/**
 * Assign output vars for template.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Filter_BuildOutputData extends SGL_DecorateProcess
{
    /**
     * Main routine.
     *
     * @access public
     *
     * @param SGL_Request $input
     * @param SGL_Output $output
     */
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);
        self::addOutputData($output);
    }

    /**
     * Adds output vars to SGL_Output object.
     *
     * @access public
     *
     * @param SGL_Output $output
     */
    function addOutputData(&$output)
    {
        // setup login stats
        if (SGL_Session::getRoleId() > SGL_GUEST) {
            $output->loggedOnUser   = SGL_Session::getUsername();
            $output->loggedOnUserID = SGL_Session::getUid();
            $output->loggedOnSince  = strftime("%H:%M:%S", SGL_Session::get('startTime'));
            $output->loggedOnDate   = strftime("%B %d", SGL_Session::get('startTime'));
            $output->isMember       = true;
        }

        // request data
        if (!SGL::runningFromCLI()) {
            $output->remoteIp = $_SERVER['REMOTE_ADDR'];
            $output->currUrl  =
                    SGL_Config::get('site.inputUrlHandlers') == 'Horde_Routes'
                ? self::getCurrentUrlFromRoutes()
                : $_SERVER['PHP_SELF'];
        }

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
            : 'default';
        // check if theme is affected by the current manager
        if (isset($output->manager)) {
            $mgrName = SGL_Inflector::caseFix(get_class($output->manager));
            if (SGL_Config::get("$mgrName.theme")) {
                $output->theme = SGL_Config::get("$mgrName.theme");
            }
        }

        // Setup SGL data
        $c = &SGL_Config::singleton();
        $output->conf             = $c->getAll();
        $output->webRoot          = SGL_BASE_URL;
        $output->imagesDir        = SGL_BASE_URL . '/themes/' . $output->theme . '/images';
        $output->versionAPI       = SGL_SEAGULL_VERSION;
        $output->sessID           = SGL_Session::getId();
        $output->isMinimalInstall = SGL::isMinimalInstall();

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
    function getCurrentUrlFromRoutes()
    {
        $input   = SGL_Registry::singleton();
        $url     = $input->getCurrentUrl();
        $currUrl = $url->toString();
        $baseUrl = $url->getBaseUrl($skipProto = false, $includeFc = false);
        return str_replace($baseUrl, '', $currUrl);
    }
}

?>