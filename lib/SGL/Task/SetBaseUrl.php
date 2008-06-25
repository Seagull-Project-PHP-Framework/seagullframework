<?php

/**
 * Routine to discover the base url of the installation.
 *
 * Only gets invoked if user deletes URL in config, or if we're setting up.
 *
 * @package Task
 */
class SGL_Task_SetBaseUrl extends SGL_Task
{
    function run($conf)
    {
        if (!(isset($conf['site']['baseUrl']))) {

            //  defines SGL_BASE_URL constant
            SGL_Task_SetBaseUrlMinimal::run();
        }
    }
}

?>