<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file defines PEAR_Delegator extensions, which are added to any delegator with the addExtensions() method.
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
 * This class provides a static delegate that adds methods to a PEAR_Delegator instance
 *
 * @since PHP 5.0.0
 * @author Michael Witten <LingWitt@yahoo.com> 4100 85
 * @see http://pear.php.net/manual/
 * @package PEAR_Delegator
 */
class PEAR_Delegator_Extensions
{   
    /**
     * Gets the delegates that is of the specified class.
     * 
     * This method returns instances of the specified classname as well as 
     * child instances of the specified classname, including subdelegates, 
     * which are anywhere in the delegate hierarchy. This method is provided, 
     * because it may be useful, but its use is discouraged, as objects should 
     * logically only have access to their native delegates.
     * 
     * @see PEAR_Delegator::getAllDelegates(), PEAR_Delegator::getDelegate(),
     *      PEAR_Delegator_Extensions::getDelegateRecursive(),
     *      PEAR_Delegator_Extensions::getDelegateRecursiveExact()
     * @param class $classname1 This specifies a delegate classname. Any
     *                          number of arguments after this is acceptable.
     * @return array <pre> The result is an 
     * associative array of the form:
     * Array
     * (
     *     [classname1] = Array
     *                    (
     *                        delegate11
     *                        delegate12
     *                        ...
     *                    )
     *     [classnamei] = Array
     *                    (
     *                        delegate1i
     *                        delegate1i
     *                        ...
     *                    )
     *     ...
     * )
     * Note: If a single classname is passed, a traditional array with numbered
     * elements is returned.
     * </pre>
     */
    public function getDelegateRecursive($owner, $classname1)
    {
        $args = func_get_args();
        unset($args[0]);
        
        $result = array();
        
        foreach ($args as $arg) {                
            foreach ($owner->_delegates as $delegate) {
                if (is_object($delegate) && $delegate instanceof PEAR_Delegator) {
                    if ($others = PEAR_Delegator_Extensions::getDelegateRecursive($delegate, $arg)) {
                        $others = array($arg => $others);
                        
                        $result = array_merge_recursive($result, $others);
                        
                        $result[$arg][] = $delegate;
                        
                        continue;
                    }
                }
                
                if (PEAR_Delegator::is_aExact($delegate, $arg)) {
                    $result[$arg][] = $delegate;
                }
            }
        }
                    
        if (count($result)) {
            return (count($args) > 1) ? $result : $result[$classname1];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the delegate that is of the specified class.
     * 
     * This method returns classes of the specified type. This does not return
     * subdelegates. This method is provided, because it may be useful, but its 
     * use is discouraged, as objects should logically only have access to their
     * native delegates.
     * 
     * @see PEAR_Delegator::getAllDelegates(), PEAR_Delegator::getDelegate(),
     *      PEAR_Delegator_Extensions::getDelegateRecursive(),
     *      PEAR_Delegator_Extensions::getDelegateRecursiveExact()
     * @param class $classname1 This specifies a delegate classname. Any
     *                          number of arguments after this is acceptable.
     * @return array <pre> The result is an 
     * associative array of the form:
     * Array
     * (
     *     [classname1] = Array
     *                    (
     *                        delegate11
     *                        delegate12
     *                        ...
     *                    )
     *     [classnamei] = Array
     *                    (
     *                        delegate1i
     *                        delegate1i
     *                        ...
     *                    )
     *     ...
     * )
     * Note: If a single classname is passed, a traditional array with numbered
     * elements is returned.
     * </pre>
     */
    public function getDelegateRecursiveExact($owner, $classname1)
    {
        $result = array();
        
        $args = func_get_args();
        unset($args[0]);
        foreach ($args as $classname) {                
            if ($native = $owner->getDelegateExact($owner, $classname)) {
                $native = array($classname => $native);
                $result = array_merge_recursive($result, $native);
                
                continue;
            }
                        
            foreach ($owner->_delegates as $delegate) {                    
                if (is_object($delegate) && ($delegate instanceof PEAR_Delegator) && ($delegateNative = PEAR_Delegator_Extensions::getDelegateRecursiveExact($delegate, $classname))) {
                    $delegateNative = array($classname => $delegateNative);
                    $result = array_merge_recursive($result, $delegateNative);
                }
            }
        }
                                
        if (count($result)) {
            return (count($args) > 1) ? $result : $result[$classname1];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the delegate objects that respond to a certain method.
     * 
     * This method returns delegates native to the calling delegator as well
     * as delegates of delegate owning delegates (it's recursive). This method is
     * provided, because it may be useful, but its use is discouraged, as objects
     * should logically only have access to their native delegates.
     * 
     * @see PEAR_Delegator_Extensions::getDelegateForMethodRecursiveExact()
     * @uses PEAR_Delegator::method_exists()
     * @param string $method1,... This specifies the method for whose responder is searched.
     * @return array <pre>The result is an 
     * associative array of the form:
     * Array
     * (
     *     [method1] = Array
     *                    (
     *                        delegate11
     *                        delegate12
     *                        ...
     *                    )
     *     [methodi] = Array
     *                    (
     *                        delegate1i
     *                        delegate1i
     *                        ...
     *                    )
     *     ...
     * )
     * Note: If a single method is passed, a traditional array with numbered
     * elements is returned.
     * </pre>
     */        
    public function getDelegateForMethodRecursive($owner, $method1)
    {
        $result = array();
        
        $args = func_get_args();
        unset($args[0]);
        
        foreach ($args as $method) {
            foreach ($owner->_delegates as $delegate) {                
                if (is_object($delegate) && $delegate instanceof PEAR_Delegator) {
                    if ($others = PEAR_Delegator_Extensions::getDelegateForMethodRecursive($delegate, $method)) {
                        $result = array_merge_recursive($result, array($method => $others));
                                                    
                        $result[$method][] = $delegate;
                      
                        
                        continue;
                    }
                }
                
                if (PEAR_Delegator::method_exists($delegate, $method)) {
                    $result[$method][] = $delegate;
                }
            }
        }
                    
        if (count($result)) {
            return (count($args) > 1) ? $result : $result[$method1];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the delegate object (native or otherwise) that implements the method in question.
     * 
     * This method returns the delegate in the delegate hierarchy 
     * which actually implements the method. This method is provided, because it may be useful,
     * but its use is discouraged, as objects should logically only have access to their native
     * delegates.
     * 
     * @see PEAR_Delegator_Extensions::getDelegateForMethodRecursive()
     * @param string $method1,... This specifies the method for whose implementor is searched.
     * @return array <pre> An array of the form:
     * Array
     * (
     *     [method1] = Array
     *                    (
     *                        delegate11
     *                        delegate12
     *                        ...
     *                    )
     *     [methodi] = Array
     *                    (
     *                        delegate1i
     *                        delegate1i
     *                        ...
     *                    )
     *     ...
     * )
     * Note: If a single method is passed, a traditional array with numbered
     * elements is returned.
     * </pre>
     */
    public function getDelegateForMethodRecursiveExact($owner, $method1)
    {
        $result = array();
        
        $args = func_get_args();
        unset($args[0]);
        foreach ($args as $method) {
            foreach ($owner->_delegates as $delegate) {
                if (PEAR_Delegator::method_existsExact($delegate, $method)) {
                    $result[$method][] = $delegate;
                }
                
                if (is_object($delegate) && $delegate instanceof PEAR_Delegator) {
                    if ($match = PEAR_Delegator_Extensions::getDelegateForMethodRecursiveExact($delegate, $method)) {
                        $result = array_merge_recursive($result, array($method => $match));
                    }
                }
            }
        }
        
        if (count($result)) {
            return (count($args) > 1) ? $result : $result[$method1];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the first native delegate that responds to a certain method.
     * 
     * The method returns the first delegate native to the calling delegator, which can
     * respond to each method in question. Moreover, this method returns delegates that
     * actually implement methods over those that inherit them from delegates.
     * 
     * @see PEAR_Delegator_Extensions::getDelegateForMethodRecursive(),
     *      PEAR_Delegator_Extensions::getDelegateForMethodRecursiveExact()
     * @uses PEAR_Delegator::method_exists()
     * @param string $method,... This specifies the method for whose responder is searched.
     * @return array <pre>The result is an 
     * associative array of the form:
     * Array
     * (
     *     [method1] = $delegate
     *     [methodi] = $delegatei
     *     ...
     * )
     * Note: If a single classname is passed, the actual object is returned.
     * </pre>
     */
    
    public function getDelegateForMethodFirst($owner, $method)
    {
        $args = func_get_args();
        unset($args[0]);
        $delegates = call_user_func_array(array($owner, 'getDelegateForMethod'), $args);
        
        if ($delegates) {
            $delegateArrayImplementors      = array();
            $delegateArrayNonimplementors   = array();
            
            if (count($args) > 1) {   
                foreach ($delegates as $method => $delegateArray) {
                    foreach ($delegateArray as $delegate) {
                        if (is_string($delegate) || method_exists($delegate, $method)) {
                            $delegateArrayImplementors[] = $delegate;
                        }
                        else {
                            $delegateArrayNonimplementors[] = $delegate;
                        }
                    }
                    
                    $delegateArray = array_merge($delegateArrayImplementors, $delegateArrayNonimplementors);
                    
                    $delegates[$method] = $delegateArray[0];
                }
            }
            else {
                foreach ($delegates as $delegate) {
                    if (is_string($delegate) || method_exists($delegate, $method)) {
                        $delegateArrayImplementors[] = $delegate;
                    }
                    else {
                        $delegateArrayNonimplementors[] = $delegate;
                    }
                }
                
                $delegates = array_merge($delegateArrayImplementors, $delegateArrayNonimplementors);
                
                $delegates = $delegates[0];
            }
        }
                                            
        return $delegates;
    }
    
    /**
     * Removes the specified delegate recursively
     *
     * Only exact delegates are removed, as it is otherwise superfluous.
     *
     * @see PEAR_Delegator::removeAllDelegates(), PEAR_Delegator::removeDelegate()
     * @param mixed $specifier,... Specifies the delegate, whose information is
     *                           is to be removed. If it is a string, then it
     *                           adheres to the tests of getDelegateExact(). If it
     *                           is an object, then it searches the for that
     *                           delegate to remove.
     * @uses filterMethodMapWithDelegate()
     */
    public function removeDelegateRecursiveExact($owner, $specifier)
    {
        $args = func_get_args();
        unset($args[0]);
        
        foreach ($args as $arg) {
            foreach ($owner->_delegates as $delegate) {
                if (is_object($arg)) {
                    if ($delegate === $arg) {
                        unset($owner->_delegates[get_class($delegate)]);
                        $owner->_method_map = $owner->filterMethodMapWithDelegate($delegate);
                    }
                } else {
                    if (PEAR_Delegator::is_aExact($delegate, $arg)) {
                        unset($owner->_delegates[is_string($delegate) ? $delegate : get_class($delegate)]);
                        $owner->_method_map = $owner->filterMethodMapWithDelegate($delegate);
                    }
                }
                
                if ($delegate instanceof PEAR_Delegator) {
                    PEAR_Delegator_Extensions::removeDelegateRecursiveExact($delegate, $arg);
                }
            }
        }
    }
    
    /**
     * Removes an entry from the _method_map array.
     * 
     * This method is for removing entries in the _method_map array.
     * it simply tests for the entry for existence and removes it.
     * 
     * This is an internal method.
     *
     * @see PEAR_Delegator::cacheMethod()
     * @param string $method This method must be in lowercase.
     * @return bool If entry exists true; false otherwise.
     */
    protected function uncacheMethod($owner, $method)
    {
        $ok = false;
        if ($ok = isset($owner->_method_map[$method]))
            unset($owner->_method_map[$method]);
        
        return $ok;
    }
}
?>
