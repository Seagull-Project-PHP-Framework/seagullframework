<?php
    //  BACKWARDS COMPATIBILITY WITH PHP VERSIONS < 4.2.0
    //  BC for is_a()
    if (!function_exists('is_a')) {
        function is_a($object, $className)
        {
            return ((strtolower($className) == get_class($object))
            || (is_subclass_of($object, $className)));
        }
    }

    //  BC for file_get_contents()
    if (!function_exists('file_get_contents')) {
        function file_get_contents($filename)
        {
            $fd = fopen($filename, "rb");
            $content = fread($fd, filesize($filename));
            fclose($fd);
            return $content;
        }
    }
    //  pre PHP 4.3.x workaround
    if (!defined('__CLASS__')) {
        define('__CLASS__', null);
    }

    //  pre PHP 4.3.x workaround
    if (!defined('__FUNCTION__')) {
        define('__FUNCTION__', null);
    }
?>