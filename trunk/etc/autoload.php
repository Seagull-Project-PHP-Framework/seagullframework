<?php

// paths
$root = dirname(__DIR__);
$sglLibDir = $root .'/lib';
$sglPearDir = $root .'/lib/pear';

// set include_path
$oldIncludePath = ini_get('include_path');
$includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
$includePath = $oldIncludePath . $includeSeparator . $sglLibDir . $includeSeparator . $sglPearDir;

ini_set('include_path', $includePath);

function autoload($className)
{
    $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    require $fileName;
}

spl_autoload_register('autoload');

?>