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

/**
 * Resolve current language and put in current user preferences.
 * Load relevant language translation file.
 *
 * @package Task
 * @author Julien Casanova <julien@soluo.fr>
 */

class SGL2_Filter_SetupLangSupport extends SGL2_DecorateProcess
{
    /**
     * Initialises multi-language support.
     *
     * langCodeCharset still set in prefs for BC, ie
     *  $_SESSION[aPrefs][language] => es-utf-8
     *
     * @param SGL2_Request $input
     * @param SGL2_Response $output
     */
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        //  sets default language for framework, checks for lang param used to set
        //  user lang
        $trans = SGL2_Translation::singleton('array');
        try {
            $trans->loadDefaultDictionaries();
        } catch (Exception $e) {
            throw new Exception($e);
        }
        // save language in settings
        $_SESSION['aPrefs']['language'] = $trans->langCodeCharset;

        // continue chain execution
        $this->_processRequest->process($input, $output);
    }
}
?>