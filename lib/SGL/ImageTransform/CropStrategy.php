<?php

/**
 * Resize image and crop.
 *
 * @package SGL
 * @subpackage image
 * @author ed209
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_ImageTransform_CropStrategy extends SGL_ImageTransformStrategy
{
    function transform()
    {
        $newWidth  = isset($this->aParams['width'])
            ? $this->aParams['width'] : null;
        $newHeight = isset($this->aParams['height'])
            ? $this->aParams['height'] : null;

        // both params must be specified
        if (empty($newWidth) || empty($newHeight)) {
            return true;
        }

        // crop by default from left top corner
        $newX = 0;
        $newY = 0;

        // get size of current image
        list($width, $height) = $this->driver->getImageSize();

        // find sizes
        if ($width != $height) {
            $percentChange = $width > $height
                ? $newHeight / $height
                : $newWidth / $width;
            $scaleWidth = round($width * $percentChange);
            $scaleHeight = round($height * $percentChange);
        } else {
            $scaleWidth = $newWidth;
            $scaleWidth = $newHeight;
        }

        // resize and crop
        $ret = $this->driver->scaleByXY($scaleWidth, $scaleHeight);
        $ret = $this->driver->crop($newWidth, $newHeight, $newX, $newY);

        return $ret;
    }
}

?>