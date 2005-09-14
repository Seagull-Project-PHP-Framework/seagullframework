<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file defines PEAR_Delegator exception classes.
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
 * This includes the base exception class.
 */
require_once 'PEAR/Exception.php';

/**
 * This class is the base class for all delegator exceptions. 
 *
 * @since PHP 5.0.0
 * @author Michael Witten <LingWitt@yahoo.com> 4100 85
 * @see http://pear.php.net/manual/
 * @package PEAR_Delegator
 */
class PEAR_Delegator_Exception extends PEAR_Exception
{
}

/**
 * This class defines the exception that is thrown when an undefined class delegate is referenced.
 *
 * @since PHP 5.0.0
 * @author Michael Witten <LingWitt@yahoo.com> 4100 85
 * @see http://pear.php.net/manual/
 * @package PEAR_Delegator
 */
class PEAR_Delegator_ExceptionDelegateUndefined extends PEAR_Delegator_Exception
{
    /**
     * Constructs a PEAR_Delegator_ExceptionDelegateUndefined.
     *
     * This exception is thrown when adding a static delegate that has not been defined
     *
     * @see PEAR_Delegator::addDelegate()
     * @param mixed  $delegate  This is the undefined delegate. It can be a string.
     * @param string $method    The offending method.
     */
    public function __construct($delegate, $func = 'addDelegate')
    {        
        $backtraceArray = $this->getTrace();
        
        $file   = 'unknown file';
        $line   = 'unknown';
        
        foreach ($backtraceArray as $index => $fileRecord) {
            $function = $fileRecord['function'];
            if (strtolower($function) == strtolower($func)) {                    
                if (array_key_exists('file', $fileRecord)) {
                    $file = $backtraceArray[++$index]['file'];
                    $line = $backtraceArray[$index]['line'];
                }
                
                $class = $fileRecord['class'];
                
                break;
            }
        }
        
        parent::__construct("Class '$delegate' not found in <b>$file</b> on line <b>$line</b>");
    }
}

/**
 * This class defines the exception that is thrown when an undefined method is called.
 *
 * @since PHP 5.0.0
 * @author Michael Witten <LingWitt@yahoo.com> 4100 85
 * @see http://pear.php.net/manual/
 * @package PEAR_Delegator
 */
class PEAR_Delegator_ExceptionMethodUndefined extends PEAR_Delegator_Exception
{
    /**
     * Constructs a PEAR_Delegator_ExceptionMethodUndefined.
     *
     * This exception is thrown when the forwarding mechanism cannot find an implementor
     * for the given method.
     *
     * @see PEAR_Delegator::__call(), PEAR_Delegator::cacheMethod()
     * @param string $method This is the undefined method.
     */
    public function __construct($method)
    {
        $backtraceArray = $this->getTrace();
        
        $file   = 'unknown file';
        $line   = 'unknown';
        
        foreach ($backtraceArray as $fileRecord) {
            $function = $fileRecord['function'];
            if (strtolower($function) == strtolower($method)) {                    
                if (array_key_exists('file', $fileRecord)) {
                    $file = $fileRecord['file'];
                    $line = $fileRecord['line'];
                }
                
                $class = $fileRecord['class'];
                
                break;
            }
        }
        
        parent::__construct("Call to undefined method $class::$function() in <b>$file</b> on line <b>$line</b>");
    }
}
?>
