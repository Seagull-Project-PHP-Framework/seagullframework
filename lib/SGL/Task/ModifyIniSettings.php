<?php

/**
 * @package Task
 */
class SGL_Task_ModifyIniSettings extends SGL_Task
{
    function run($conf)
    {
        // set php.ini directives
        @ini_set('session.auto_start',          0); //  sessions will fail fail if enabled
        @ini_set('allow_url_fopen',             0); //  this can be quite dangerous if enabled
        if (count($conf)) {
            @ini_set('error_log', SGL_PATH . '/' . $conf['log']['name']);
            if (!empty($conf['log']['ignoreRepeated'])) {
                ini_set('ignore_repeated_errors', true);
                ini_set('ignore_repeated_source', true);
            }
        }
    }
}
?>