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
// | ColorOverlayStrategy.php                                                  |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

/**
 * Strategy for overlaying color across image.
 *
 * @package SGL
 * @author  Dmitri Lakachauskis <dmitri@telenet.lv>
 * @access  public
 */
class SGL_ImageTransform_ColorOverlayStrategy extends SGL_ImageTransformStrategy
{
    function transform()
    {
        $aDefaultParams = array(
            'align'         => 'bottom',
            'size'          => 10,
            'color'         => '#FF0000',
            'trans'         => 0,
            'paddingTop'    => 0,
            'paddingRight'  => 0,
            'paddingBottom' => 0,
            'paddingLeft'   => 0
        );
        $aDefaultParams = array_merge($aDefaultParams, $this->aParams);
        $aDefaultParams = array_merge($aDefaultParams, $this->calculateValues($aDefaultParams));
        $aDefaultParams['trans'] = 100 - $aDefaultParams['trans'];
        return $this->transform->colorOverlay($aDefaultParams);
    }
    
    function calculateValues($aParams)
    {
        // calculate values
        if ('top' == $aParams['align'] || 'bottom' == $aParams['align']) {
            $overlayFromX = 0;
            if ($aParams['paddingLeft']) {
                $overlayFromX = $aParams['paddingLeft'];
            }
            $overlayX = $this->transform->img_x;
            if ($aParams['paddingRight']) {
                $overlayX -= $aParams['paddingRight'] + $overlayFromX;
            }
            $overlayY = $aParams['size'] > $this->transform->img_y
                ? $this->transform->img_y : $aParams['size'];
            if ('top' == $aParams['align']) {
                $overlayFromY = $aParams['paddingTop'];
                if ($overlayFromY > $overlayY) {
                    $overlayFromY = 0;
                }
            } else {
                $overlayFromY = $this->transform->img_y - ($aParams['paddingBottom'] + $aParams['size']);
                if ($overlayFromY < 0) {
                    $overlayFromY = $this->transform->img_y - $aParams['size'];
                }
            }
        } elseif ('left' == $aParams['align'] || 'right' == $aParams['align']) {
            $overlayFromY = 0;
            if ($aParams['paddingTop']) {
                $overlayFromY = $aParams['paddingTop'];
            }
            $overlayY = $this->transform->img_y;
            if ($aParams['paddingBottom']) {
                $overlayY -= $aParams['paddingBottom'] + $overlayFromY;
            }
            $overlayX = $aParams['size'] > $this->transform->img_x
                ? $this->transform->img_x : $aParams['size'];
            if ('left' == $aParams['align']) {
                $overlayFromX = $aParams['paddingLeft'];
                if ($overlayFromX > $overlayX) {
                    $overlayFromX = 0;
                }
            } else {
                $overlayFromX = $this->transform->img_x - ($aParams['paddingRight'] + $aParams['size']);
                if ($overlayFromX < 0) {
                    $overlayFromX = $this->transform->img_x - $aParams['size'];
                }
            }
        }
        return array(
            'startX' => $overlayFromX, 'width'  => $overlayX,
            'startY' => $overlayFromY, 'height' => $overlayY
        );
    }
}
 
?>