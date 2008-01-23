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
// | Captcha.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Steven Stremciuc <steve@freeslacker.net>                        |
// +---------------------------------------------------------------------------+

/**
 * Provides methods for generating/validating a captcha.
 *
 * @package SGL
 * @author  Steven Stremciuc <steve@freeslacker.net>
 */
class SGL_Captcha
{
    /**
     * Returns random ascii art code for use as captcha.
     *
     * @access public
     * @static
     * @return string $captcha   formatted ascii art captcha
     */
    function generateCaptcha()
    {
        $aNumbers = array(
            array('   ###   ','   #   ','  #####  ','  #####  ',' #       ',' ####### ','  #####  ',' ####### ','  #####  ','  #####  '),
            array('  #   #  ','  ##   ',' #     # ',' #     # ',' #    #  ',' #       ',' #     # ',' #    #  ',' #     # ',' #     # '),
            array(' #     # ',' # #   ','       # ','       # ',' #    #  ',' #       ',' #       ','     #   ',' #     # ',' #     # '),
            array(' #     # ','   #   ','  #####  ','  #####  ',' #    #  ',' ######  ',' ######  ','    #    ','  #####  ','  ###### '),
            array(' #     # ','   #   ',' #       ','       # ',' ####### ','       # ',' #     # ','   #     ',' #     # ','       # '),
            array('  #   #  ','   #   ',' #       ',' #     # ','      #  ',' #     # ',' #     # ','   #     ',' #     # ',' #     # '),
            array('   ###   ',' ##### ',' ####### ','  #####  ','      #  ','  #####  ','  #####  ','   #     ','  #####  ','  #####  '),
            );

        // randomly pick 4 numbers
        $aCode = array_rand($aNumbers[0], 4);

        // get code
        $code = '';
        foreach ($aCode as $digit) {
            $code .= $digit;
        }

        // set code in users session
        SGL_Session::set('captcha',$code);

        // turn code into ascii art numbers
        $captcha = '';
        for ($i = 0;$i < 7;$i++) {
            foreach ($aCode as $digit) {
                $captcha .= $aNumbers[$i][$digit];
            }
            $captcha .="\n";
        }

        return $captcha;
    }

    /**
     * Validates input against captcha code in users session.
     *
     * @access public
     * @param   string  $captcha
     * @static
     * @return bool
     */
    function validateCaptcha($captcha = '')
    {
        if (!empty($captcha) && SGL_Session::get('captcha') == $captcha) {
            return true;
        }
        return false;
    }
}
?>
