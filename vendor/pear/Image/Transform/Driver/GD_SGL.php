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
// | GD_SGL.php                                                                |
// +---------------------------------------------------------------------------+
// | Author: Dmitri Lakachauskis <dmitri@telenet.lv>                           |
// +---------------------------------------------------------------------------+

require_once 'Image/Transform/Driver/GD.php';

/**
 * @todo addBorder(), canvasResize(), colorOverlay() should be consolidated
 *       into one method, thus appropriate strategy could call it with
 *       needed set of params.
 *
 * Extends some functionality of original Image_Transform GD driver.
 *
 * @package    seagull
 * @subpackage image
 * @author     Dmitri Lakachaukis <dmitri@telenet.lv>
 */
class Image_Transform_Driver_GD_SGL extends Image_Transform_Driver_GD
{
    /**
     * Add border to image handle.
     *
     * @access public
     * @param  int     $borderWidth  width of border in pixels
     * @param  mixed   $color        color as name, hex or rgb array
     * @return boolean
     */
    function addBorder($borderWidth, $color = array(0, 0, 0))
    {
        $this->new_x = $this->img_x + 2 * $borderWidth;
        $this->new_y = $this->img_y + 2 * $borderWidth;
        $trueColor = true;
        // creates image for temporary processing
        $newImg = $this->_createImage($this->new_x, $this->new_y, $trueColor);

        $options = array('pencilColor' => $color);
        $color   = $this->_getColor('pencilColor', $options);

        // apply background color
        $c = imagecolorresolve($newImg, $color[0], $color[1], $color[2]);
        imagefill($newImg, 0, 0, $c);

        ImageCopy($newImg, $this->imageHandle, $borderWidth, $borderWidth,
            0, 0, $this->img_x, $this->img_y);
        $this->imageHandle = $newImg;
        $this->resized = true;

        // new image size (with border)
        $this->img_x = $this->new_x;
        $this->img_y = $this->new_y;

        return true;
    }

    /**
     * Create new image size of $x on $y with background color of $color
     * and place loaded image at $position.
     *
     * @access public
     * @param  int     $x         canvas width
     * @param  int     $y         canvas height
     * @param  string  $position  where to place original image on canvas,
     *                            only "center" position is supported for now
     * @param  mixed   $color     color as name, hex or rgb array
     * @return boolean
     */
    function canvasResize($x, $y, $position = 'center',
        $color = array(255, 255, 255))
    {
        $this->new_x = $x;
        $this->new_y = $y;
        $trueColor = true;
        // creates image for temporary processing
        $newImg = $this->_createImage($this->new_x, $this->new_y, $trueColor);

        // resolve canvas color
        $options = array('canvasColor' => $color);
        $color   = $this->_getColor('canvasColor', $options);

        // get color index (closest one if none)
        //$c = imagecolorresolve($this->imageHandle, $color[0],
        //    $color[1], $color[2]);
        $c = imagecolorallocate($newImg, $color[0], $color[1], $color[2]);
        imagefill($newImg, 0, 0, $c);

        $startX = 0;
        $startY = 0;
        if ('center' == $position) {
            $startX = round(($this->new_x - $this->img_x) / 2);
            $startY = round(($this->new_y - $this->img_y) / 2);
        }

        ImageCopy($newImg, $this->imageHandle, $startX, $startY,
            0, 0, $this->img_x, $this->img_y);
        $this->imageHandle = $newImg;
        $this->resized = true;

        // new image size (size of canvas)
        $this->img_x = $this->new_x;
        $this->img_y = $this->new_y;

        return true;
    }

    /**
     * Overlay color accross original image.
     *
     * @access public
     * @param  array   $aParams  keys: startX, startY, width, height,
     *                                 trans, color
     * @return boolean
     */
    function colorOverlay($aParams)
    {
        // creates truecolor image for temporary processing
        $overlayImg = $this->_createImage($aParams['width'],
            $aParams['height'], $trueColor = true);

        // resolve color
        $options = array('canvasColor' => $aParams['color']);
        $color   = $this->_getColor('canvasColor', $options);

        $c = imagecolorresolve($overlayImg, $color[0], $color[1], $color[2]);
        imagefill($overlayImg, 0, 0, $c);

        // copy overlayed image over
        imagecopymerge($this->imageHandle, $overlayImg,
            $aParams['startX'], $aParams['startY'], 0, 0,
            $aParams['width'], $aParams['height'], $aParams['trans']);
        $this->resized = true;

        return true;
    }

    /**
     * Load custom image file and return it's properties.
     *
     * @access public
     * @param  string $image  filename
     * @return array          image params
     */
    function loadFile($image)
    {
        $originalImage = $this->image;

        $success = $this->load($image);
        if (PEAR::isError($success)) {
            return $success;
        }

        // result properties
        $retHandler = $this->imageHandle;
        $retType    = $this->type;
        $retImgX    = $this->img_x;
        $retImgY    = $this->img_y;

        $this->imageHandle = null; // otherwise image resource will be destroyed
        $this->load($originalImage); // reload original image

        return array($retHandler, $retType, $retImgX, $retImgY);
    }

    /**
     * Add image accross original image.
     *
     * @access public
     * @param  array   $aParams  keys: alignX, alignY, paddingX, paddingY, file
     * @return boolean
     */
    function addImage($aParams)
    {
        // load image
        $imgParams = $this->loadFile($aParams['file']);
        if (PEAR::isError($imgParams)) {
            return $imgParams;
        }
        list($imgHandler, $imgType, $imgX, $imgY) = $imgParams;

        if ('left' == $aParams['alignX']) {
            $imageFromX = $aParams['paddingX'];
        } elseif ('right' == $aParams['alignX']) {
            $imageFromX = $this->img_x - ($aParams['paddingX'] + $imgX);
        }
        if ('top' == $aParams['alignY']) {
            $imageFromY = $aParams['paddingY'];
        } elseif ('bottom' == $aParams['alignY']) {
            $imageFromY = $this->img_y - ($aParams['paddingY'] + $imgY);
        }

        // copy image over
        imagecopyresampled($this->imageHandle, $imgHandler,
            $imageFromX, $imageFromY, 0, 0, $imgX, $imgY, $imgX, $imgY);
        //imagecopymerge($this->imageHandle, $imageHandler,
        //    $imageFromX, $imageFromY, 0, 0, $imageX, $imageY, $trans);
        $this->resized = true;

        return true;
    }

    function getCurrentImageWidth()
    {
        if (is_resource($this->imageHandle)) {
            $ret = imagesx($this->imageHandle);
        } else {
            $ret = false;
        }
        return $ret;
    }

    function getCurrentImageHeight()
    {
        if (is_resource($this->imageHandle)) {
            $ret = imagesy($this->imageHandle);
        } else {
            $ret = false;
        }
        return $ret;
    }

    function _createImage($width = -1, $height = -1, $trueColor = null)
    {
        $new_img = parent::_createImage($width, $height, $trueColor);
        if ($this->getImageType() == 'png') {
            imagealphablending($new_img, false);
        }
        return $new_img;
    }

    function save($filename, $type = '', $quality = null)
    {
        if ($this->getImageType() == 'png') {
            imagesavealpha($this->imageHandle, true);
        }
        return parent::save($filename, $type, $quality);
    }
}

?>