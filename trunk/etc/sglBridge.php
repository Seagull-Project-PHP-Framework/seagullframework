<?php
    //  setup seagull environment
    require_once '../constants.php';
    $conf = &$GLOBALS['_SGL']['CONF'];

    //  disable logging and error handling
    $conf['debug']['customErrorHandler'] = false;
    require_once '../init.php';
    require_once SGL_CORE_DIR . '/AppController.php';

    SGL_AppController::init();
    
    ini_set('include_path', ini_get('include_path') . ':' . '/usr/local/lib/php');
?>