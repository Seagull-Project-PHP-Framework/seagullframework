<?php

class SGL2_LegacyErrorException extends Exception
{
    private $_context = null;

    public function __construct($message, $code, $file, $line, $context = null)
    {
        parent::__construct($message = null, $code = 0);
        $this->_context = $context;
        $this->file = $file;
        $this->line = $line;
    }

}

class SGL2_ErrorHandler
{
    private static $_instance = null;

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
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function errorHandler($message, $code, $file, $line, $context = null)
    {
        if ($GLOBALS['_SGL']['ERROR_OVERRIDE'] == true) {
            //  if currently handled error is a notice, ignore it
            if (error_reporting() == E_ALL ^ E_NOTICE) {
                return null;
            }
        }
        //  if an @ error suppression operator has been detected (0) return null
        if (error_reporting() == 0) {
            return null;
        }
        $legacyErrorException = new SGL2_LegacyErrorException($message, $code, $file,
            $line, $context);
        throw $legacyErrorException;
    }

    /**
     * Very useful static method when dealing with PEAR libs ;-)
     *
     * @param unknown_type $mode
     */
    public static function setNoticeBehaviour($mode = SGL2_NOTICES_ENABLED)
    {
        $GLOBALS['_SGL']['ERROR_OVERRIDE'] = ($mode) ? false : true;
    }
}