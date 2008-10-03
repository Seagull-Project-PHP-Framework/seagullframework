<?php

require 'init.php';

//$erh = SGL_ErrorHandler2::singleton();
$ech = SGL_ExceptionHandler::singleton();

// determine if setup needed
if (!is_file($varDir . '/INSTALL_COMPLETE.php')) {
    $protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
        ? 'https'
        : 'http';
    $webRoot = $protocol . '://'. $_SERVER['HTTP_HOST'] .
        str_replace('\\','/',(dirname($_SERVER['SCRIPT_NAME']))) . '/setup.php';
    header('Location: '.$webRoot);
    exit;
} else {
    define('SGL_INSTALLED', true);
}


try {
    SGL_FrontController::run();
} catch (Exception $e) {
    print '<pre>'; print_r($e);
}
?>