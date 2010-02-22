<?php

/**
 * Resize image and crop.
 *
 * @package SGL
 * @author ed209
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_ImageTransform_CropStrategy extends SGL_ImageTransformStrategy
{
    function transform()
    {
        // both params must be specified
        if (empty($this->aParams['width']) || empty($this->aParams['height'])) {
            return false;
        }

        $newWidth  = $this->aParams['width'];
        $newHeight = $this->aParams['height'];

        // get size of current image
        list($width, $height) = $this->driver->getImageSize();

        // find sizes
        if ($width != $height) {
            $scaleWidth    = $newWidth / $width;
            $scaleHeight   = $newHeight / $height;
            $percentChange = max($scaleHeight, $scaleWidth);

            /*
            if ($newWidth > $newHeight) {
                $percentChange = $width > $height
                    ? $newWidth / $width
                    : $newHeight / $height;
            } else {
                $percentChange = $width > $height
                    ? $newHeight / $height
                    : $newWidth / $width;
            }
            */

            $scaleWidth  = round($width * $percentChange);
            $scaleHeight = round($height * $percentChange);

            $this->driver->scaleByXY($scaleWidth, $scaleHeight);
        } else {
            if ($newWidth > $newHeight) {
                $scaleSide = $newWidth;
                $method    = 'scaleByX';
            } else {
                $scaleSide = $newHeight;
                $method    = 'scaleByY';
            }
            $this->driver->{$method}($scaleSide);
        }

        // get size of current (transformed) image
        $width  = $this->driver->getCurrentImageWidth();
        $height = $this->driver->getCurrentImageHeight();

        // center
        $newX = round(($width - $newWidth) / 2);
        $newY = round(($height - $newHeight) / 2);

        // crop
        $ret = $this->driver->crop($newWidth, $newHeight, $newX, $newY);
        return $ret;
    }
}

?>