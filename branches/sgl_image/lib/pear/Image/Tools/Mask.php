<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * This is a driver file contains the Image_Tools_Mask class.
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * The PHP License, version 3.0
 *
 * Copyright (c) 1997-2005 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following url:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category    Images
 * @package     Image_Tools
 * @author      Firman Wandayandi <firman@php.net>
 * @copyright   1997-2005 The PHP Group
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 * @version     CVS: $Id: Mask.php,v 1.1 2005/10/02 17:52:59 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load Image_Tools as the base class.
 */
require_once 'Image/Tools.php';

/**
 * Require for color mixing. Notes: conflict when integrated with
 * Image_Color v1.0.0 (http://pear.php.net/package/Image_Color).
 */
require_once 'Image/Color.php';

// }}}
// {{{ Class: Image_Tools_Mask

/**
 * This class provide masking tool for manipulating an image.
 *
 * @category    Images
 * @package     Image_Tools
 * @author      Firman Wandayandi <firman@php.net>
 * @copyright   1997-2005 The PHP Group
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 * @version     Release: 0.4.0
 */
class Image_Tools_Mask extends Image_Tools
{
    // {{{ Properties

    /**
     * Mask options:
     * <pre>
     * image             mixed  Destination image, a filename or an image string
     *                          data or a GD image resource.
     * mask              mixed  Mask image, a filename or an image string
     *                          data or a GD image resource.
     * sample            mixed  Sample image, a filename or an image string
     *                          data or a GD image resource.
     * mask_color        mixed  Mask color, use string for hexa color format or
     *                          array contains 3 indexes 0 for RGB format
     * unmask_color      mixed  Mask color, use string for hexa color format or
     *                          array contains 3 indexes 0 for RGB format
     * antialias         bool   Flag whether attempt to draw antialias mask
     * antialias_factor  int    Antialias factor, this setting for antialias
     *                          mask
     * </pre>
     *
     * @var array
     * @access protected
     */
    var $options = array(
        'image'             => null,   // Destination image.
        'mask'              => null,     // Mask image.
        'sample'            => null,     // Sample image.
        'mask_color'        => '000000', // Mask color.
        'unmask_color'      => 'ffffff', // Unmask color.
        'antialias'         => true,     // Antialias flag.
        'antialias_factor'  => 16        // Antialias factor.
    );

    /**
     * Available options for Image_Tools_Mask.
     *
     * @var array
     * @access protected
     */
    var $availableOptions = array(
        'image'             => 'mixed',
        'mask'              => 'mixed',
        'sample'            => 'mixed',
        'mask_color'        => 'mixed',
        'unmask_color'      => 'mixed',
        'antialias'         => 'bool',
        'antialias_factor'  => 'int'
    );

    /**
     * There is no public methods in Image_Tool_Mask.
     *
     * @var array
     * @access protected
     */
    var $availableMethods = array();

    /**
     * Image_Tools_Mask API version.
     *
     * @var string
     * @access protected
     */
    var $version = '0.1';

    /**
     * GD image resource for mask image.
     *
     * @var resource
     * @access private
     */
    var $_maskImage;
    
    /**
     * GD image resource for sample image.
     *
     * @var resource
     * @access private
     */
    var $_sampleImage;

    // }}}
    // {{{ _init()

    /**
     * Initialize some internal variables.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access private
     */
    function _init()
    {
        $res = Image_Tools::createImage($this->options['mask']);
        if (PEAR::isError($res)) {
            return $res;
        }
        $this->_maskImage = $res;

        $res = Image_Tools::createImage($this->options['sample']);
        if (PEAR::isError($res)) {
            return $res;
        }
        $this->_sampleImage = $res;

        $res = Image_Tools::createImage($this->options['image']);
        if (PEAR::isError($res)) {
            return $res;
        }
        $this->resultImage = $res;

        return true;
    }

    // }}}
    // {{{ render()

    /**
     * Apply tools to image.
     *
     * This function scan for mask color and closes colors position, grab color
     * at found the position on sample image, then set the pixel color at the same
     * position on destination image.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access private
     * @see Image_Tools_Mask::_getNearestColors()
     */
    function render()
    {
        $res = $this->_init();
        if (PEAR::isError($res)) {
            return $res;
        }

        if (!Image_Tools::isGDImageResource($this->_maskImage)) {
            return PEAR::raiseError('Invalid image resource Image_Tools_Mask::$_maskImage');
        }

        if (!Image_Tools::isGDImageResource($this->_sampleImage)) {
            return PEAR::raiseError('Invalid image resource Image_Tools_Mask::$_sampleImage');
        }

        if (!Image_Tools::isGDImageResource($this->resultImage)) {
            return PEAR::raiseError('Invalid image resource Image_Tools_Mask::$_resultImage');
        }

        $maskWidth = imagesx($this->_maskImage);
        $maskHeight = imagesy($this->_maskImage);

        $sampleWidth = imagesx($this->_sampleImage);
        $sampleHeight = imagesy($this->_sampleImage);

        if ($this->options['antialias']) {
            $closesColors = $this->_getNearestColors();
        } else {
            $closesColors = array($this->options['maskColor']);
        }

        imagealphablending($this->resultImage, true);

        // scan for mask color or closes colors position
        for ($x = 0; $x < $maskWidth; $x++) {
            for ($y = 0; $y < $maskHeight; $y++) {
                if ($x >= $sampleWidth || $y >= $sampleHeight) {
                    continue;
                }

                // grab color at x, y and convert to hex color format
                $index = imagecolorat($this->_maskImage, $x, $y);
                $maskRGBA = imagecolorsforindex($this->_maskImage, $index);
                
                $maskColor = Image_Color::rgb2hex(array_values($maskRGBA));

                // check color in closes colors collection
                if (in_array($maskColor, $closesColors)) {
                    // grab color at x, y from sample image
                    $index = imagecolorat($this->_sampleImage, $x, $y);
                    $sampleRGBA = imagecolorsforindex($this->_sampleImage, $index);

                    // allocate color on destination image
                    $color = imagecolorresolvealpha($this->resultImage,
                                                    $sampleRGBA['red'],
                                                    $sampleRGBA['green'],
                                                    $sampleRGBA['blue'],
                                                    $sampleRGBA['alpha']);
                    
                    // set a pixel color at destination image
                    imagesetpixel($this->resultImage, $x, $y, $color);
                }
            }
        }

        return true;
    }

    // }}}
    // {{{ _getNearestColors

    /**
     * Get nearest colors between mask color and unmask color using
     * antialias factor.
     *
     * @return array Colors range.
     * @access private
     */
    function _getNearestColors()
    {
        $objColor = new Image_Color;
        $objColor->setColors($this->options['mask_color'], $this->options['unmask_color']);
        return $objColor->getRange($this->options['antialias_factor']);
    }

    // }}}
}

// }}}

/*
 * Local variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>