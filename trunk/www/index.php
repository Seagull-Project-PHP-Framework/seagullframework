<?php

require '../lib/SGL2.php';

$erh = SGL2_ErrorHandler::singleton();
$ech = SGL2_ExceptionHandler::singleton();

try {
    $front = new SGL2_Controller_FrontBc();
    $front->run();

} catch (Exception $e) {
    print '<pre>'; print_r($e);
}

?>