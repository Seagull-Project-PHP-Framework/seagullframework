<?php

/**
 * @package Task
 */
class SGL_Task_SetGlobals extends SGL_Task
{
    function run($data)
    {
        $GLOBALS['_SGL']['BANNED_IPS'] =        array();
        $GLOBALS['_SGL']['ERRORS'] =            array();
        $GLOBALS['_SGL']['QUERY_COUNT'] =       0;
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] =    false;
        $GLOBALS['_SGL']['CHARSET'] =           '';
    }
}
?>