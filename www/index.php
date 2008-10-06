<?php

require 'init.php';

$erh = SGL_ErrorHandler2::singleton();
$ech = SGL_ExceptionHandler::singleton();

try {
    $front = new SGL_Controller_FrontBc();
    $front->run();

} catch (Exception $e) {
    print '<pre>'; print_r($e);
}

?>