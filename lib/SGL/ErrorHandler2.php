<?php

class SGL_LegacyErrorException extends Exception
{
    private $context = null;

    public function __construct($message, $code, $file, $line,
        $context = null)
    {
        parent::__construct($message = null, $code = 0);
        $this->context = $context;
        $this->file = $file;
        $this->line = $line;
    }

}

class SGL_ErrorHandler2
{
    private static $instance = null;

    private function __construct()
    {
        set_error_handler(array(__CLASS__, 'errorHandler'));
    }

    public function __destruct()
    {
        restore_error_handler();
    }

    public static function singleton()
    {
        if (!self::$instance) {
            self::$instance = new SGL_ErrorHandler2();
        }
        return self::$instance;
    }

    public static function errorHandler($message, $code, $file, $line,
        $context = null)
    {
        $legacyErrorException = new SGL_LegacyErrorException($message, $code, $file,
            $line,$context);
        throw $legacyErrorException;
    }
}