<?php
    //  start timer
    $timerStart = getSystemTime();
    
    //  initialise
    require_once '../init.php';
    require_once SGL_CORE_DIR . '/Controller.php';
    $process = & new SGL_Controller();
    $process->go();
    
    //  end timer
    $parseTime = getSystemTime() - $timerStart;
    
    //  output results
    if ($_SESSION['aPrefs']['showExecutionTimes']) {
        echo 'Execution Time = ' .$parseTime. ' ms, ' . $GLOBALS['_SGL']['QUERY_COUNT'] .' queries';
    }
    echo '</div></div></body></html>';
    
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