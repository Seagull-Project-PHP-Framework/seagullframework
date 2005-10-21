<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file defines PEAR_Delegator, which provides delegation capabilities.
 * 
 * PHP version 5
 * 
 * @package    PEAR_Delegator
 * @author     Michael Witten <lingwitt@yahoo.com>
 * @copyright  2004-2005 Michael Witten
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link       http://pear.php.net/package/PEAR_Delegator
 */

// /* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Michael Witten 4100 85 <LingWitt@yahoo.com>                 |
// |          Martin Jansen <mj@php.net> for overwhelming encouragement   |
// +----------------------------------------------------------------------+
// 4100 85
// $Id$

/**
 * This class is a flag for the forwarding mechanism.
 * 
 * This class serves as a flag and information carrier for the forwarding
 * mechanism. It tells delegators deep in the delegate hierarchy which
 * delegator is to be taken as the caller. Note: This is an internal structure.
 * 
 * @since PHP 5.0.0
 * @author Michael Witten <LingWitt@yahoo.com> 4100 85
 * @see http://pear.php.net/manual/
 * @package PEAR_Delegator
 */
class PEAR_DelegatorInternalForwardingProxy
{
    public $_delegator;
    
    /**
     * Constructs a PEAR_DelegatorInternalForwardingProxy.
     *
     * @param mixed $delegator The delegator that is to be regarded
     *                         as the caller of the delegated method.
     */
    public function __construct($delegator)
    {
        $this->_delegator = $delegator;
    }
    
    /**
     * Gets the delegator that is to be regarded as the initial caller.
     */
    public function getDelegator()
    {
        return $this->_delegator;
    }
}

?>
