<?php

function __autoload($className)
{
    if (!class_exists($className)) {
        $path = str_replace('_', '/', $className);
        $file = $path . '.php';
        include $file;
    }
}
$sglPath = realpath(dirname(__FILE__).'/..');
$libPath = realpath(dirname(__FILE__).'/../lib');

define('SGL_PATH', $sglPath);
set_include_path($libPath.PATH_SEPARATOR.get_include_path());

$erh = SGL_ErrorHandler2::singleton();
$ech = SGL_ExceptionHandler::singleton();

?>