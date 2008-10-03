<?php

/**
 * @package Task
 */
class SGL_Task_LoadCustomConfig extends SGL_Task
{
    function run($conf)
    {
        if (!empty($conf['path']['pathToCustomConfigFile'])) {
            if (is_file($conf['path']['pathToCustomConfigFile'])) {
                require_once realpath($conf['path']['pathToCustomConfigFile']);
            }
        }
    }
}

?>