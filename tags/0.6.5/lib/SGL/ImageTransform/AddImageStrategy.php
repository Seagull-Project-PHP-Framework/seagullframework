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
// | AddImageStrategy.php                                                      |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

/**
 * Strategy for adding image across original one.
 *
 * @package    SGL
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_ImageTransform_AddImageStrategy extends SGL_ImageTransformStrategy
{
    function transform()
    {
        if (count($this->aParams) == 1 && is_int(key($this->aParams))) {
            $this->aParams = array('file' => reset($this->aParams));
        }
        if (!isset($this->aParams['file'])) {
            return SGL::raiseError('file param not specified',
                SGL_ERROR_INVALIDARGS);
        }
        $this->aParams['file'] = SGL_APP_ROOT . '/' . $this->aParams['file'];
        if (!file_exists($this->aParams['file'])) {
            return SGL::raiseError('file not found', SGL_ERROR_NOFILE);
        }
        $aDefaultParams = array(
            'trans'    => 0,
            'alignX'   => 'right',
            'alignY'   => 'bottom',
            'paddingX' => 0,
            'paddingY' => 0
        );
        $aDefaultParams = array_merge($aDefaultParams, $this->aParams);
        $aDefaultParams['trans'] = 100 - $aDefaultParams['trans'];
        return $this->driver->addImage($aDefaultParams);
    }
}

?>