<?php
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('PS') ? null : define('PS', PATH_SEPARATOR);
final class Uber
{
    private static $_inited = false;

    public static function init()
    {
        if (self::$_inited)
            return;
        require_once (dirname(__FILE__) . DS . 'Uber' . DS . 'Loader.php');
        self::initUberAutoload();
        Uber_Loader::registerAutoload();
        self::$_inited = true;
    }

    public static function initUberAutoload()
    {
        Uber_Loader::registerNamespace('Uber', dirname(__FILE__));
    }
}
?>