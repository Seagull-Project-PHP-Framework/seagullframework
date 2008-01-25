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
// | CanvasResizeStrategy.php                                                  |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

/**
 * Strategy for resizing canvas.
 *
 * @package    SGL
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_ImageTransform_CanvasResizeStrategy extends SGL_ImageTransformStrategy
{
    function transform()
    {
        $width  = null;
        $height = null;
        if (isset($this->aParams['width'])) {
            $width = $this->aParams['width'];
        }
        if (isset($this->aParams['height'])) {
            $height = $this->aParams['height'];
        }

        $aSize   = $this->driver->getImageSize();
        $resultX = $aSize[0];
        $resultY = $aSize[1];
        if (isset($width) && $width > $aSize[0]) {
            $resultX = $width;
        }
        if (isset($height) && $height > $aSize[1]) {
            $resultY = $height;
        }
        if ($aSize[0] == $resultX && $aSize[1] == $resultY) {
            // canvas size must be wider, do not apply resizing
            return false;
        }
        $aDefaultParams = array(
            'position' => 'center',
            'color'    => 'white'
        );
        $aParams = array_merge($aDefaultParams, $this->aParams);

        return $this->driver->canvasResize($resultX, $resultY,
            $aParams['position'], $aParams['color']);
    }
}

?>