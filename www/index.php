<?php
    //  start timer
    $GLOBALS['_SGL']['START_TIME'] = getSystemTime();
    
    //  initialise
    require_once dirname(__FILE__)  . '/../lib/SGL/AppController.php';
    require_once dirname(__FILE__)  . '/../lib/SGL/Registry.php';    
    require_once dirname(__FILE__)  . '/../lib/SGL/Request.php';    
    require_once dirname(__FILE__)  . '/../lib/SGL.php';
    require_once dirname(__FILE__)  . '/../lib/SGL/Config.php';    
    require_once dirname(__FILE__)  . '/../lib/SGL/Tasks/Process.php';    
    require_once dirname(__FILE__)  . '/../lib/SGL/Tasks/Setup.php';    
    require_once dirname(__FILE__)  . '/../lib/SGL/TaskRunner.php';    
    
    // determine if setup needed
    if (!file_exists(dirname(__FILE__)  . '/../var/INSTALL_COMPLETE.php')) {
        header('Location: setup.php');
        exit;
    }

    SGL_AppController::run();
    
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