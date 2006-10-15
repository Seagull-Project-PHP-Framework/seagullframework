<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// {{{ Header

/**
 * This is a driver file contains the Image_Tools_Border class.
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
 * @category Images
 * @package Image_Tools
 * @author Firman Wandayandi <firman@php.net>
 * @copyright 1997-2005 The PHP Group
 * @license http://www.php.net/license/3_0.txt
 *          The PHP License, version 3.0
 * @version CVS: $Id: Border.php,v 1.4 2005/10/02 17:52:59 firman Exp $
 */

// }}}
// {{{ Dependencies

/**
 * Load Image_Tools as the base class.
 */
require_once 'Image/Tools.php';

/**
 * Load Image_Color for color handling.
 */
require_once 'Image/Color.php';

// }}}
// {{{ Class: Image_Tools_Border

/**
 * This class provide border creation on an image.
 *
 * @category Images
 * @package Image_Tools
 * @author Firman Wandayandi <firman@php.net>
 * @copyright Copyright (c) 2004-2005 Firman Wandayandi
 * @license http://www.php.net/license/3_0.txt
 *          The PHP License, version 3.0
 * @version Release: 0.4.0
 */
class Image_Tools_Border extends Image_Tools
{
    // {{{ Properties

    /**
     * Border options:
     * <pre>
     * image   mixed   Destination image, a filename or an image string
     *                 data or a GD image resource.
     * </pre>
     *
     * @var array
     * @access protected
     */
    var $options = array(
        'image' => null    // An image.
    );

    /**
     * Available options for Image_Tools_Border.
     *
     * @var array
     * @access protected
     */
    var $availableOptions = array(
        'image' => 'mixed'
    );

    /**
     * Available methods for Image_Tool_Border (only public methods).
     *
     * @var array
     * @access protected
     */
    var $availableMethods = array(
        'rounded' => array(
            'radius'        => 'integer',
            'background'    => 'mixed',
            'antiAlias'     => 'integer'
        ),
        'bevel' => array(
            'size'          => 'integer',
            'highlight'     => 'mixed',
            'shadow'        => 'mixed'
        )
    );

    /**
     * Image_Tools_Border API version.
     *
     * @var string
     * @access protected
     */
    var $version = '0.1';

    /**
     * Image width.
     *
     * @var int
     * @access private
     */
    var $_iWidth = 0;

    /**
     * Image height.
     *
     * @var int
     * @access private
     */
    var $_iHeight = 0;

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
        $res = Image_Tools::createImage($this->options['image']);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->resultImage = $res;
        $this->_iWidth = imagesx($this->resultImage);
        $this->_iHeight = imagesy($this->resultImage);

        return true;
    }

    // }}}
    // {{{ rounded()

    /**
     * Make an image be a rounded edge.
     *
     * @param int   $radius optional Radius size.
     * @param mixed $background Background color.
     * @param int   $antiAlias Anti-alias factor.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     */
    function rounded($radius = 3, $background = 'FFFFFF', $antiAlias = 3)
    {
        $res = $this->_init();
        if (PEAR::isError($res)) {
            return $res;
        }

        if (!Image_Tools::isGDImageResource($this->resultImage)) {
            return PEAR::raiseError('Invalid image resource Image_Tools_Mask::$_resultImage');
        }

        $background = Image_Color::hex2rgb($background);
        $antiAlias = min(3, $antiAlias);

        $iDot = imagecreate(1, 1);
        imagecolorallocate($iDot, $background[0], $background[1], $background[2]);

        for ($i = 0 - $radius; $i <= $radius; $i++) {
            $y = $i < 0 ? $i + $radius - 1 : $this->_iHeight - ($radius - $i);
            for ($j = 0 - $radius; $j <= $radius; $j++) {
                $x = $j < 0 ? $j + $radius - 1 : $this->_iWidth - ($radius - $j);
                if ($i != 0 || $j != 0) {
                    $distance = round(sqrt(($i * $i) + ($j * $j)));
                    $opacity = $distance < $radius - $antiAlias ?
                               0 : max(0, 100 - (($radius - $distance) * 33));
                    $opacity = $distance > $radius ? 100 : $opacity;
                    imagecopymerge($this->resultImage, $iDot, $x, $y, 0, 0, 1, 1, $opacity);
                }
            }
        }

        imagedestroy($iDot);

        return true;
    }

    // }}}
    // {{{ bevel()

    /**
     * Make an image bevel border.
     *
     * @param int $size Border size.
     * @param mixed $highlight Highlight color.
     * @param mixed $shadow Shadow color.
     *
     * @return bool|PEAR_Error TRUE on success or PEAR_Error on failure.
     * @access public
     */
    function bevel($size = 8, $highlight = 'FFFFFF', $shadow = '000000')
    {
        $res = $this->_init();
        if (PEAR::isError($res)) {
            return $res;
        }

        if (!Image_Tools::isGDImageResource($this->resultImage)) {
            return PEAR::raiseError('Invalid image resource Image_Tools_Mask::$_resultImage');
        }

        $highlight = Image_Color::hex2rgb($highlight);
        $shadow = Image_Color::hex2rgb($shadow);

        // Create an image resource for highlight.
        $iLight = imagecreate($this->_iWidth, $this->_iHeight);
        imagecolorallocate($iLight, $highlight[0], $highlight[1], $highlight[2]);

        // Create an image resource for shadow.
        $iShadow = imagecreate(1, 1);
        imagecolorallocate($iShadow, $shadow[0], $shadow[1], $shadow[2]);

        for ($j = 0; $j < $size; $j++) {
            $opacity =  100 - (($j + 1) * (100 / $size));
            imagecopymerge($this->resultImage, $iLight, $j, $j,
                           0, 0, 1, $this->_iHeight - (2 * $j), $opacity);
            imagecopymerge($this->resultImage, $iLight, $j - 1, $j - 1,
                           0, 0, $this->_iWidth - (2 * $j), 1, $opacity);
            imagecopymerge($this->resultImage, $iShadow, $this->_iWidth - ($j + 1), $j,
                           0, 0, 1, $this->_iHeight - (2 * $j), max(0, $opacity - 10));
            imagecopymerge($this->resultImage, $iShadow, $j, $this->_iHeight - ($j + 1),
                           0, 0, $this->_iWidth - (2 * $j), 1, max(0, $opacity - 10));
        }

        // Free highlight and shadow image resources.
        imagedestroy($iLight);
        imagedestroy($iShadow);

        return true;
    }

    // }}}
    // {{{ render()

    /**
     * This method is useless, use directly call for specific border style
     * method.
     *
     * @return bool always TRUE.
     * @access protected
     */
    function render()
    {
        return true;
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