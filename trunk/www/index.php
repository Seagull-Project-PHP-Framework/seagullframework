<?php

require 'init.php';

try {
    $front = new SGL_Controller_FrontBc();
    $front->run();

} catch (Exception $e) {
    print '<pre>'; print_r($e);
}
?>