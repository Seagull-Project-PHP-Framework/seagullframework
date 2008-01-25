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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | ErrorHandler.php                                                          |
// +---------------------------------------------------------------------------+
// | Authors:   Peter James <petej@shaman.ca>                                  |
// |            Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: ErrorHandler.php,v 1.8 2005/05/28 19:32:12 demian Exp $

//  This class is based on Peter James' class of the same name, for more info
//  see http://php.shaman.ca/

/**
 * Global error handler class, modifies behaviour for PHP errors, not PEAR.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Peter James
 * @version $Revision: 1.8 $
 */
class SGL_ErrorHandler
{
    var $errorType = array();
    var $sourceContextOptions = array();

    /**
     * Constructor.
     *
     * @access  public
     * @return  void
     */
    function SGL_ErrorHandler()
    {
        //  first dimension elements are PHP error types
        //  2nd dimension elements are roughly PEAR Log's equivalents

        //  nb: comment out Notice for equivalent of
        //  error_reporting(E_ALL ^ E_NOTICE);
        $this->errorType = array (
           1   =>  array('Error', 3),
           2   =>  array('Warning', 4),
           4   =>  array('Parsing Error', 3),
           8   =>  array('Notice', 5),
           16  =>  array('Core Error', 3),
           32  =>  array('Core Warning', 4),
           64  =>  array('Compile Error', 3),
           128 =>  array('Compile Warning', 4),
           256 =>  array('User Error', 3),
           512 =>  array('User Warning', 4),
           1024=>  array('User Notice', 5),
           2047=>  array('All', 7)
            );
        $this->sourceContextOptions = array('lines' => 5);
    }

    /**
     * BC hack to assign custom error handler in a method.
     *
     * @access  public
     * @return  void
     */
    function startHandler()
    {
        $GLOBALS['_SGL']['ERROR_HANDLER_OBJECT'] =  & $this;
        $GLOBALS['_SGL']['ERROR_HANDLER_METHOD'] =  'errHandler';

        //  inner function to handle redirection to a class method
        function eh($errNo, $errStr, $file, $line, $context)
        {
            call_user_func(array(
               &$GLOBALS['_SGL']['ERROR_HANDLER_OBJECT'],
                $GLOBALS['_SGL']['ERROR_HANDLER_METHOD']),
                $errNo, $errStr, $file, $line, $context
            );
        }
        //  start handling errors
        set_error_handler('eh');
    }

    /**
     * Enhances PHP's default error handling.
     *
     *  o overrides notices in certain cases
     *  o obeys @muffled errors,
     *  o error logged to selected target
     *  o context info presented for developer
     *  o error data emailed to admin if threshold passed
     *
     * @access  public
     * @param   int     $errNo      PHP's error number
     * @param   string  $errStr     PHP's error message
     * @param   string  $file       filename where error occurred
     * @param   int     $line       line number where error occurred
     * @param   string  $context    contextual info
     * @return  void
     */
    function errHandler($errNo, $errStr, $file, $line, $context)
    {
        //  if an @ error suppression operator has been detected (0) return null
        if (error_reporting() == 0) {
            return null;
        }
        //  or if notices are temporarily being suppressed, return null
        if ($GLOBALS['_SGL']['ERROR_OVERRIDE'] == true) {
            if (error_reporting() == E_ALL ^ E_NOTICE) {
                return null;
            }
        }
        if (in_array($errNo, array_keys($this->errorType))) {
            $c = &SGL_Config::singleton();
            $conf = $c->getAll();
            //  final param is 2nd dimension element from errorType array,
            //  representing PEAR error codes mapped to PHP's

            //  also, error obj is stored in $GLOBALS['_SGL']['ERRORS']
            //  in the logMessage method
            SGL::logMessage($errStr, $file, $line, $this->errorType[$errNo][1]);

            //  if a debug sesssion has been started, or the site in in
            //  development mode, send error info to screen
            if (!$conf['debug']['production'] || SGL_Session::get('adminMode')) {
                $source = $this->_getSourceContext($file, $line);
                //  generate screen debug html
                //  type is 1st dimension element from $errorType array, ie,
                //  PHP error code
                $output = <<<EOF
<hr />
<div id="errorWrapper" class="errorContent">
        <strong>MESSAGE</strong>: $errStr<br />
        <strong>TYPE:</strong> {$this->errorType[$errNo][0]}<br />
        <strong>FILE:</strong> $file<br />
        <strong>LINE:</strong> $line<br />
        <strong>DEBUG INFO:</strong>
        <p>$source</p>
</div>
<hr />
EOF;
                if (SGL::runningFromCLI()) {
                    $output = <<<EOL
MESSAGE: $errStr
TYPE: {$this->errorType[$errNo][0]}
FILE: $file
LINE: $line

 --
EOL;
                }
                if (!empty($conf['log']['showErrors']) && $conf['log']['showErrors'] == true) {
                    echo $output;
                }

            } else {
                //  we're in production mode, suppress any errors from being displayed
                @ini_set('display_errors', 0);
            }
            //  email the error to admin if threshold reached
            if ($this->errorType[$errNo][1] <= SGL_EMAIL_ADMIN_THRESHOLD) {

                //  get extra info
                $dbh = & SGL_DB::singleton();

                $aExtraInfo['callingURL'] = $_SERVER['SCRIPT_NAME'];
                $aExtraInfo['lastSQL'] = isset($dbh->last_query) ?
                    $dbh->last_query : null;
                $aExtraInfo['userID'] = SGL_Session::get('uid');
                $aExtraInfo['clientData']['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER'])
                    ? $_SERVER['HTTP_REFERER']
                    : null;
                $aExtraInfo['clientData']['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT'])
                    ? $_SERVER['HTTP_USER_AGENT']
                    : null;
                $aExtraInfo['clientData']['REMOTE_ADDR'] = isset($_SERVER['REMOTE_ADDR'])
                    ? $_SERVER['REMOTE_ADDR']
                    : null;
                $aExtraInfo['clientData']['SERVER_PORT'] = isset($_SERVER['SERVER_PORT'])
                    ? $_SERVER['SERVER_PORT']
                    : null;

                //  store formatted output
                $info = print_r($aExtraInfo, true);

                //  rebuild error output w/out html
                $crlf = SGL_String::getCrlf();
                $output = $errStr . $crlf .
                    'type: ' . $this->errorType[$errNo][0] . $crlf .
                    'file: ' . $file . $crlf .
                    'line: ' . $line . $crlf . $crlf;
                $message = $output . $info;
                @mail($conf['email']['admin'], 'ERROR OUTPUT FROM ' .
                    $conf['site']['name'], $message);
            }
        }
    }

    /**
     * Provides enhanced error info for developer.
     *
     * Gives 10 lines before and after error occurred, hightlight erroroneous
     * line in red.
     *
     * @access  private
     * @param   string  $file       filename where error occurred
     * @param   int     $line       line number where error occurred
     * @param   string  $context    contextual info
     * @return  string  contextual error info
     */
    function _getSourceContext($file, $line)
    {
        $sourceContext = null;

        //  check that file exists
        if (!is_file($file)) {
            $sourceContext = "Context cannot be shown - ($file) does not exist";

        //  check if line number is valid
        } elseif ((!is_int($line)) || ($line <= 0)) {
            $sourceContext = "Context cannot be shown - ($line) is an invalid line number";
        } else {
            $lines = file($file);

            //  get the source ## core dump in windows, scrap colour highlighting :-(
            //  $source = highlight_file($file, true);
            //  $lines = split("<br />", $source);
            //  get line numbers
            $start = $line - $this->sourceContextOptions['lines'] - 1;
            $finish = $line + $this->sourceContextOptions['lines'];

            //  get lines
            if ($start < 0) {
                $start = 0;
            }

            if ($start >= count($lines)) {
                $start = count($lines) -1;
            }

            for ($i = $start; $i < $finish; $i++) {
                //  highlight line in question
                if ($i == ($line - 1)) {
                    $context_lines[] = '<div class="error"><strong>' . ($i + 1) .
                        "\t" . strip_tags($lines[$line -1]) . '</strong></div>';
                } else {
                    $context_lines[] = '<strong>' . ($i + 1) .
                        "</strong>\t" . @$lines[$i];
                }
            }
            $sourceContext = trim(join("<br />\n", $context_lines)) . "<br />\n";
        }
        return $sourceContext;
    }
}
?>
