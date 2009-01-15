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
// | Error.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

/**
 * Provides error handling methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Error
{
    /**
     * Returns true if one or more PEAR errors exist on the global stack.
     *
     * @return boolean
     */
    function count()
    {
        return count($GLOBALS['_SGL']['ERRORS']);
    }

    /**
     * Pushes an error onto stack.
     *
     * @param PEAR_Error $oError
     * @return integer   Returns the new number of elements in the array.
     */
    function push($oError)
    {
        return array_push($GLOBALS['_SGL']['ERRORS'], $oError);
    }

    /**
     * Pops last error off stack.
     *
     * @return PEAR_Error
     */
    function pop()
    {
        return array_pop($GLOBALS['_SGL']['ERRORS']);
    }

    /**
     * Returns stack of errors.
     *
     * @return PEAR_Error
     */
    function getAll()
    {
        $aErrors = $GLOBALS['_SGL']['ERRORS'];
        return $aErrors;
    }

    /**
     * Remove first error off stack.
     *
     * @return PEAR_Error
     */
    function shift()
    {
        return array_shift($GLOBALS['_SGL']['ERRORS']);
    }

    function getLast()
    {
        return end($GLOBALS['_SGL']['ERRORS']);
    }

    /**
     * Reset error stack to empty array.
     *
     * @return boolean
     */
    function reset()
    {
        unset($GLOBALS['_SGL']['ERRORS']);
        $GLOBALS['_SGL']['ERRORS'] = array();
        return true;
    }

    function toString($oError)
    {
        $message = $oError->getMessage();
        $debugInfo = $oError->getDebugInfo();
        $level = $oError->getCode();
        $errorType = SGL_Error::constantToString($level);
        $output = <<<EOF
  <strong>MESSAGE</strong>: $message<br />
  <strong>TYPE:</strong> $errorType<br />
  <strong>DEBUG INFO:</strong> $debugInfo<br />
  <strong>CODE:</strong> $level<br />
EOF;
        return $output;
    }

    /**
     * Converts error constants into equivalent strings.
     *
     * @access  public
     * @param   int     $errorCode  error code
     * @return  string              text representing error type
     */
    function constantToString($errorCode)
    {
        $aErrorCodes = array(
            SGL_ERROR_INVALIDARGS       => 'invalid arguments',
            SGL_ERROR_INVALIDCONFIG     => 'invalid config',
            SGL_ERROR_NODATA            => 'no data',
            SGL_ERROR_NOCLASS           => 'no class',
            SGL_ERROR_NOMETHOD          => 'no method',
            SGL_ERROR_NOAFFECTEDROWS    => 'no affected rows',
            SGL_ERROR_NOTSUPPORTED      => 'not supported',
            SGL_ERROR_INVALIDCALL       => 'invalid call',
            SGL_ERROR_INVALIDAUTH       => 'invalid auth',
            SGL_ERROR_EMAILFAILURE      => 'email failure',
            SGL_ERROR_DBFAILURE         => 'db failure',
            SGL_ERROR_DBTRANSACTIONFAILURE => 'db transaction failure',
            SGL_ERROR_BANNEDUSER        => 'banned user',
            SGL_ERROR_NOFILE            => 'no file',
            SGL_ERROR_INVALIDFILEPERMS  => 'invalid file perms',
            SGL_ERROR_INVALIDSESSION    => 'invalid session',
            SGL_ERROR_INVALIDPOST       => 'invalid post',
            SGL_ERROR_INVALIDTRANSLATION => 'invalid translation',
            SGL_ERROR_FILEUNWRITABLE    => 'file unwritable',
            SGL_ERROR_INVALIDREQUEST    => 'invalid request',
            SGL_ERROR_INVALIDTYPE       => 'invalid type',
            SGL_ERROR_RECURSION         => 'recursion',
            SGL_ERROR_RESOURCENOTFOUND  => 'page not found',
        );
        if (in_array($errorCode, array_keys($aErrorCodes))) {
            return strtoupper($aErrorCodes[$errorCode]);

        //  if not within this range, most likely a PEAR::DB error
        } else {
            return 'PEAR';
        }
    }
}
?>