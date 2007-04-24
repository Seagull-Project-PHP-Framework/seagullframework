<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | ResizeStrategy.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

/**
 * Strategy for resizing images.
 *
 * @package    seagull
 * @subpackage image
 * @author     Dmitri Lakachauskis <dmitri@telenet.lv>
 */
class SGL_ImageTransform_ResizeStrategy extends SGL_ImageTransformStrategy
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
        $aSize = $this->driver->getImageSize();
        if (isset($width) && isset($height)) {
            if ($aSize[0] > $width || $aSize[1] > $height) {
                return $this->driver->fit($width, $height);
            }
        } elseif (isset($width) && $aSize[0] > $width) {
            return $this->driver->scaleByX($width);
        } elseif (isset($height) && $aSize[1] > $height) {
            return $this->driver->scaleByY($height);
        }
        return true;
    }
}

?>