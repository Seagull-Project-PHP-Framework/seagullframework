<?php
    //  start timer
    $GLOBALS['_SGL']['START_TIME'] = getSystemTime();
    
    //  initialise
    require_once '../init.php';
    require_once SGL_CORE_DIR . '/Controller.php';
    $process = & new SGL_Controller();
    $process->go();
    
    /**
     * Returns systime in ms.
     *
     * @return string   Execution time in milliseconds
     */
    function getSystemTime()
    {
        $time = gettimeofday();    
        $resultTime = $time['sec'] * 1000;
        $resultTime += floor($time['usec'] / 1000);
        return $resultTime;
    }
?>