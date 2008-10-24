<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// $Id$

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