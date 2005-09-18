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
 * This includes the exceptions thrown.
 */
require_once 'Delegator/DelegatorExceptions.php';

/**
 * This includes highly internal parts.
 */
require_once 'Delegator/DelegatorInternals.php';

/**
 * Base class for objects that allow delegation.
 * 
 * <b>Introduction</b>
 * <br>
 * It is first necessary to discuss the role of delegates in PEAR.
 * With the advent of PHP 5, a whole host of new programming techniques
 * were introduced. Among them were class interfaces. These interfaces
 * provide much of the benefits of multiple inheritance (with regard to
 * methods) without adulterating the inheritance hierarchy. That is, an
 * object can inherit from a parent class as usual, but still possess
 * methods that qualify it as a member of another group of objects, related
 * by certain behavior but still exclusively separate in the hierarchy.
 * The interfaces, however, only define the protocol to which each adopting class
 * must adhere, but they do not define the implementation. This is very
 * useful in many instances, but for some purposes, the implementation
 * remains viritually the same for all adopting parties. This is where
 * delegation enters.
 *
 * A delegate is a class that defines methods which are intended to be
 * called by an adopting object as if they were members of that object.
 * For instance,
 * <code>
 * class Foo extends PEAR_Delegator
 * {
 *     public function _construct()
 *     {
 *         parent::_construct();
 *     }
 *
 *     public function _destruct()
 *     {
 *         parent::_destruct();
 *     }
 * }
 * 
 * $foo = new Foo();
 * $foo->bar() //This results in a runtime error,
 *             //Foo has no such method.
 * 
 * //Now define a delegate
 * class Delegate
 * {
 *     public function _construct()
 *     {
 *         parent::_construct();
 *     }
 *
 *     public function _destruct()
 *     {
 *         parent::_destruct();
 *     }
 *     
 *     public function bar()
 *     {
 *         echo 'bar';
 *     }
 * }
 *
 * foo->addDelegate(new Delegate);  //add the delegate.
 * foo->bar();                      //This will be called as if it 
 *                                  //were a member of Foo.
 * </code>
 *
 * There are two types of delegates: <i>true delegates</i> and <i>false delegates</i>.
 *
 * <b>True Delegates</b>
 * <br>
 * These delegates are designed to be used as delegates. As such, they
 * follow an explicit delegate protocol. To simulate an actual method
 * call, the forwarding mechanism transparently inserts a reference to
 * the calling delgator as a the first argument. Consquently, such a
 * delegate's method signatures must be of the form:
 * <code>
 * accesslevel function functionName($owner, ...);
 * </code>
 * Note:
 * 1. $owner can be any suitable variable name.
 * 2. $owner is unncessary if the method takes no variables and does not
 *    access the delegator.
 * 3. The user of the method need only consider those parameters that
 *    follow the $owner argument.
 * Delegates of this kind are the default. However, the argument <var>true</var>
 * can be passed to explicitly denote the following list as a set of true delegates:
 * <code>
 * $delegator->addDelegate($delegate1, $delegate2, ...);
 * //or
 * $delegator->addDelegate(true, $delegate1, $delegate2, ...);
 * </code>
 *
 * <b>False Delegates</b>
 * <br>
 * These delegates are designed without any intention to be used as delegates.
 * In essence, these are regular objects in themselves that would be useful
 * as delegates. They have no knowledge of delegators and no reason to access
 * a forwarding delegator. Consequently, such a delegate's method signatures are
 * not guaranteed to be of the forme described above. Their signatures can be
 * of any form, but they will not have access to the calling delegator.
 * 
 * Delegates of this kind must be explicitly denoted by passing a <var>false</var>
 * as the preceding argument to a list of false delegates:
 * <code>
 * $delegator->addDelegate(false, $delegate1, $delegate2, ...);
 * </code>
 * 
 * Indeed, a mixed list of delegates can be called:
 * <code>
 * $delegator->addDelegate($delegate1, $delegate2, false, $delegate3, $delegate4, true $delegate1 ...);
 * </code>
 *
 * <b>Mixed Delegates</b>
 * <br>
 * Since delegates can themselves can be delegators, the two kinds of delegates
 * can be mixed. This is achieved by creating a true delegate that has added to it
 * a false delegate. Then, any delegator can make calls to true delegate methods
 * and false delegate methods through the same delegate.\
 * 
 * There are two further categorizations: <i>class delegates</i> and <i>instance delegates</i>.
 * 
 * <b>Class Delegates</b>
 * <br>
 * These delegates are classes whose methods are to be called statically.
 *
 * <b>Instance Delegates</b>
 * <br>
 * These delegates are instances of classes.
 *
 * <b>Delegate Hierarchies</b>
 * <br>
 * One of the benefits of this scheme is the ability to have delegate hierarchies.
 * That is, a delegator could have a delegate that is a delegator, and
 * the PEAR_Delegator class recognizes this by viewing such delegates as subdelegates,
 * treating such subdelegates as subclasses would be treated. This allows for such capabilities
 * as pseudo-overriding:
 * <code>
 * //In our Foo class we could define a bar() method as follows:
 * public function bar()
 * {
 *    $args = func_get_args();
 *    
 *    //Note that it may be better to call $this->getDelegateForMethod()
 *    //instead of $this->hasDelegate(), since the latter may forward
 *    //the call to a delegate that does not respond.
 *    if ($this->hasDelegate())
 *      $this->forwardMethod('bar', $args);
 * 
 *    echo 'foobar';
 * }
 *</code>
 * Now, the delegate's implementation would be called as well. This of course means that
 * you can also completely override the delegate method, and not even call it.
 * 
 * <b>Traditional Delegation</b>
 * <br>
 * In truth, this mode of delegation is unorthodox. The traditional model
 * of delegation is that an object delegates selected methods, calling its
 * own version unless one delegate is present. This feature is, in fact, a
 * subset of the scheme presented here. In otherwords, you can achieve the
 * same effect by pseudo-overriding as described above.
 * 
 * <b>Implementation</b>
 * <br>
 * 1. <b>Performance Impact</b>
 *    In actuality, there should be little extra overhead after the first call to a delegated
 *    method. This is due to a caching scheme: When methods are called upon a 
 *    delegator, the delegator checks an associated array that contains method names as
 *    keys and the proper delegates as values. If the key (method name) is cached
 *    in this manner, then the method is immediatly invoked on the proper delegate. If it
 *    does not exist, then each delegate is searched until one that can respond is found
 *    and this relationship is cached, otherwise, a fatal error is produced. Thus no 
 *    matter how many delegates a class has, all calls after the first should
 *    only have a small latency.
 *    Note: Subdelegators cache their own delegates, so calls are passed down the hiearchy
 *    until the implementing delegate handles the call.
 * 2. <b>Flexibility of Errors</b>.
 *    This is not a trouble at all. In fact, the error output of this class is more direct in
 *    many cases than PHP's own error output. It will give you the file and line of the error
 *    in user code and its messages are modeled after those of PHP.
 * 
 * <b>Terminology</b>
 * <br>
 * 1. <b>owner</b>             : a delegator.
 * 2. <b>subdelegate</b>       : A delegator that is a delegate.
 * 3. <b>native delegate</b>   : A delegate that is an immediate delegate, not a delegate
 *                               of a delegate.
 * 4. <b>class delegate</b>    : A delegate that is simply the class.
 * 5. <b>instance delegate</b> : A delegate that is an instantiated class.
 * 
 * @since PHP 5.0.0
 * @author Michael Witten <LingWitt@yahoo.com> 4100 85
 * @see http://pear.php.net/manual/
 * @package PEAR_Delegator
 */
class PEAR_Delegator
{
    /**
     * An associative array with delegate classnames as keys
     * and objects as values.
     * @var array
     */
    public $_delegates      = array();
    
    /**
     * An associative array with false delegate classnames as keys
     * and objects as values.
     * @var array
     */
    public $_delegatesFalse = array();
    
    /**
     * An associative array with delegated methods as keys 
     * and delegate objects as values.
     * @var array
     */
    public $_method_map     = array();
    
    /**
     *
     */
    protected static $_addExtensions = false;
    
    /**
     * Constructs a delegator.
     */
    public function __construct()
    {
        if (self::$_addExtensions) {
            self::addExtensions($this);
        }
    }
    
    /**
     * Destroys a delegator.
     * 
     * When a delegator is destroyed, it automatically removes all of the delegates,
     * so it is unnessary for user code to do so.
     */
    public function __destruct()
    {
        $this->removeAllDelegates();
    }
    
    /**
     * Add extensions to every new delegator.
     *
     * This loads the extensions and gives every new delegator the extensions.
     * Delegators created before this call can be passed in to have the extensions
     * added to them;
     */
     public static function addExtensions()
     {
        if (!self::$_addExtensions) {
            require_once 'Delegator/DelegatorExtensions.php';
            self::$_addExtensions = true;
        }
        
        $args = func_get_args();
        foreach ($args as $delegator) {
            $delegator->addDelegate(PEAR_Delegator_Extensions);
        }
     }
    
    /**
     * Adds delegates to the calling object.
     * 
     * This method takes a list of classnames or objects. If an argument is
     * a classname, then the method determines if it is defined. If it is,
     * the class is added as a static delegate, otherwise a fatal error
     * is raised. If it is an object, it is stored as a delegate; thus,
     * there are two types of delegates: static and dynamic delegates.
     * 
     * @see PEAR_Delegator::setDelegate()
     *      
     * @param mixed $delegate,... This specifies either a classname or an object.
     */
    public function addDelegate($delegate)
    {
        $args           = func_get_args();
        $falseDelegate  = false;
        
        foreach ($args as $delegate)
        {
            if (is_bool($delegate)) {
                $falseDelegate = !$delegate;
                continue;
            }
            
            if (is_string($delegate)) {
                if (!class_exists($delegate)) {
                    $exception = new PEAR_Delegator_ExceptionDelegateUndefined($delegate);
                    die('<b>Fatal error</b>: ' . $exception->getMessage());
                }
                
                $delegateClass = $delegate;
            } else {
                $delegateClass = get_class($delegate);
            }
            
            if ($falseDelegate) {
                $this->_delegatesFalse[$delegateClass] = $delegate;
            }
            
            $this->_delegates[$delegateClass] = $delegate;
        }
    }
    
    /**
     * Sets the delegator's one delegate.
     * 
     * This method takes a classname or an object and makes it the only delegate.
     * In actuality, it removes all of the delegates and then adds the specified
     * delegate. This is useful for using the delegation method for the traditional
     * delegate model.
     *
     * @see PEAR_Delegator::addDelegate()
     * @uses PEAR_Delegator::removeAllDelegates(), PEAR_Delegator::addDelegate()
     * @param mixed $delegate This specifies either a classname or an object.
     */
    
    public function setDelegate($delegate)
     {
         $this->removeAllDelegates();
         if ($delegate) {
             $this->addDelegate($delegate);
         }
     }
    
    /**
     * Gets the associated array of delegate classes => delegates.
     * 
     * Note: Cloning may not work after this.
     * @see PEAR_Delegator::getAllDelegates(), PEAR_Delegator::getDelegate(),
     *      PEAR_Delegator::getDelegateExact(), PEAR_Delegator_Extensions::getDelegateRecursive(),
     *      PEAR_Delegator_Extensions::getDelegateRecursiveExact()
     * @return array A reference to the _delegates array.
     */
    public function &getAllDelegates()
    {
        return $this->_delegates;
    }
    
    /**
     * Gets the delegate objects that are instances of the specified class.
     * 
     * This method returns instances of the specified classname as well as 
     * child instances of the specified classnames, including subdelegates,
     * which are native to the caller. That is, if one of the delegates is
     * a delegator and it contains a delegate of the specified type,
     * it will be returned regardless of its own class type.
     * 
     * @see PEAR_Delegator::getAllDelegates(), PEAR_Delegator::getDelegate(),
     *      PEAR_Delegator::getDelegateExact(), PEAR_Delegator_Extensions::getDelegateRecursive(),
     *      PEAR_Delegator_Extensions::getDelegateRecursiveExact()
     * @param class $classname1 This specifies a delegate classname. Any
     *                          number of arguments after this is acceptable.
     * @return array <pre>The result is an 
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
    public function getDelegate($classname1)
    {
        $args = func_get_args();
        
        $result = null;
        
        foreach ($args as $classname) {                
            foreach ($this->_delegates as $delegate) {
                if (PEAR_Delegator::is_a($delegate, $classname)) {
                    $result[$classname][] = $delegate;
                    
                    continue;
                }
            }
        }
                    
        if ($result) {
            return (count($args) > 1) ? $result : $result[$classname1];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the delegate object that is an instance of the specified class.
     * 
     * This method returns classes of the specified type. This does not return
     * subdelegates. That is, it uses only the actual class structures.
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
    public function getDelegateExact($classname1)
    {
        $result = null;
        
        $args = func_get_args();
        foreach ($args as $classname) {
            foreach ($this->_delegates as $delegate) {
                if (PEAR_Delegator::is_aExact($delegate, $classname)) {
                    $result[$classname][] = $delegate;
                }
            }
        }
        
        if ($result) {
            return (count($args) > 1) ? $result : $result[$classname1];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the native delegate objects that respond to a certain method.
     * 
     * The method returns delegates native to the calling delegator, which can
     * respond to the method in question, whether it be defined in the native delegate
     * or in a delegate deeper in the hierarchy.
     * 
     * @see PEAR_Delegator_Extensions::getDelegateForMethodRecursive(), PEAR_Delegator_Extensions::getDelegateForMethodRecursiveExact(),
     *      PEAR_Delegator_Extensions::getDelegateForMethodFirst(), PEAR_Delegator::method_exists()
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
     * Note: If a single classname is passed, a traditional array with numbered
     * elements is returned.
     * </pre>
     */        
    public function getDelegateForMethod($method1)
    {
        $result = array();
        
        $args = func_get_args();
        
        foreach ($args as $method) {
            foreach ($this->_delegates as $delegate) {                
                if (PEAR_Delegator::method_exists($delegate, $method)) {
                    $result[$method][] = $delegate;
                }
            }
        }
        
        if (count($result)) {
            return (count($args) > 1) ? $result : $result[$method1];
        }
        else {
            return null;
        }
    }
    
    /**
     * Determines whether or not the calling object adopts a particular delegate.
     *
     * This returns the availability of a delegate not including subdelegates.
     * Note: This should be used for delegates that don't use hierarchies.
     *
     * @see PEAR_Delegator::hasDelegate()
     * @param class $specifier This specifies a delegate classname.
     * @return bool If the calling object has adopted the specifed classname
     */
    public function hasDelegateExact($specifier = null)
    {
        if (array_key_exists($specifier, $this->_delegates)) {
            return true;
        }
        
        foreach ($this->_delegates as $delegate) {
            if (PEAR_Delegator::is_aExact($delegate, $specifier)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Determines whether or not the calling object adopts a particular delegate.
     *
     * This returns the availability of a delegate, including subdelegates.
     *
     * @see PEAR_Delegator::hasDelegateExact(), PEAR_Delegator::is_a();
     * @param mixed $specifier This specifies a delegate classname or object. If
     *                         $delegate is a string, then it adheres to the tests
     *                         of getDelegate(). If $delegate is an object, the _delegates 
     *                         array is searched for the object. If $specifier is null,
     *                         then this returns whether or not the caller has any delegates.
     * @return bool If the calling object has adopted the specifed class name
     */
    public function hasDelegate($specifier = null)
    {
        if ($specifier == null) {
            return (count($this->_delegates)) ? true : false;
        } elseif (is_string($specifier)) {
            $specifier = $specifier;
            
            if (array_key_exists($specifier, $this->_delegates)) {
                return true;
            }
            
            foreach ($this->_delegates as $delegate) {
                if (PEAR_Delegator::is_aExact($delegate, $specifier)) {
                    return true;
                }
                
                if (is_object($delegate) && ($delegate instanceof PEAR_Delegator && $delegate->hasDelegate($specifier))) {
                    return true;
                }
            }
        } else {
            foreach ($this->_delegates as $delegate) {
                if ($delegate === $specifier) {
                    return true;
                }
                
                if (is_object($delegate) && ($delegate instanceof PEAR_Delegator && $delegate->hasDelegate($specifier))) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Removes all delegates.
     *
     * This completely cleans the calling object of any delegates.
     *
     * @see PEAR_Delegator::removeDelegate(), PEAR_Delegator_Extensions::removeDelegateRecursiveExact()
     */
    public function removeAllDelegates()
    {
        unset($this->_method_map);
        unset($this->_delegates);
        unset($this->_delegatesFalse);
        
        $this->_method_map      = array();
        $this->_delegates       = array();
        $this->_delegatesFalse  = array();
    }
    
    /**
     * Removes the unwanted entries from _method_map.
     *
     * This method cleans the _method_map array when a call to removeDelegate*()
     * is made.
     *
     * @param object $filterDelegate Specifies the delegate instance, whose information is
     *                             is to be removed.
     * @return array The method map without the $filterdelegate
     */
    public function filterMethodMapWithDelegate($filterDelegate)
    {
        $result = array();
        
        $method_map_keys    = array_keys($this->_method_map);
        $method_map_values  = array_values($this->_method_map);
        
        for ($i = 0, $count = count($method_map_values); $i < $count; $i++) {
            $delegate = $method_map_values[$i];
            
            if ($delegate === $filterDelegate) {
                continue;
            }
            
            $result[$method_map_keys[$i]] = $delegate;
        }
        
        return $result;
    }

   /**
     * Removes the specified delegate.
     *
     * Takes a list of delegate classnames and delegate objects and removes them
     * from the calling object.
     *
     * @param mixed $specifier,... Specifies the delegate, whose information is
     *                           is to be removed. If it is a string, then it
     *                           adheres to the tests of getDelegate(). If it
     *                           is an object, then it searches the for that
     *                           delegate to remove.
     * @see PEAR_Delegator::removeAllDelegates(), PEAR_Delegator_Extensions::removeDelegateRecursiveExact()
     * @uses PEAR_Delegator::getDelegate(), PEAR_Delegator::filterMethodMapWithDelegate()
     */
    public function removeDelegate($specifier)
    {
        $args = func_get_args();
        
        $delegates = call_user_func_array(array($this, 'getDelegate'), $args);
        
        foreach ($delegates as $delegateArray) {
            foreach ($delegateArray as $delegate) {
                $key = is_string($delegate) ? $delegate : get_class($delegate);
                unset($this->_delegates[$key]);
                unset($this->_delegatesFalse[$key]);
                $this->_method_map = $this->filterMethodMapWithDelegate($delegate);
            }
        }
    }
    
    /**
     * Determines if a class or instance object is of the given type.
     *
     * This method is analogous to the is_a() method of PHP. However,
     * it handles classes too.
     *
     * @see PEAR_Delegator::is_a()
     * @param mixed $specifier Specifies the delegate with either
     *                           a class or instantiated object.
     * @param class $classname The classname type to check against.
     * @return  bool    true if it is, false if it is not.
     */
    public function is_aExact($specifier, $classname)
    {
        if (is_string($specifier)) {
            if ((new $specifier) instanceof $classname) {
                return true;
            }
        }
        elseif ($specifier instanceof $classname) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determines if a class or instance object is of the given type.
     *
     * This method is an extension of the is_aExact() method. It also
     * handles subdelegates, so it returns true if a delegator
     * is passed in and has a delegate of type $classname, whether
     * or not the delegator is of type $classname.
     *
     * @uses PEAR_Delegator::hasDelegate()
     * @param mixed $specifier Specifies the delegate with either
     *                           a class or instantiated object.
     * @param class $classname The classname type to check against.
     * @return  bool    true if it is, false if it is not.
     */
    public function is_a($specifier, $classname)
    {
        if (PEAR_Delegator::is_aExact($specifier, $classname)) {
            return true;
        }
        elseif (is_object($specifier) && (($specifier instanceof PEAR_Delegator) && $specifier->hasDelegate($classname))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determines if a class or instance object responds to a method.
     *
     * This method is analogous to the method_exists() method of PHP. However,
     * it handles classes too.
     *
     * @see PEAR_Delegator::method_exists()
     * @param mixed $specifier Specifies the delegate with either
     *                           a class or instantiated object.
     * @param class $classname The method to look for.
     * @return  bool    true if it is, false if it is not.
     */
    
    public function method_existsExact($specifier, $method)
    {
        if (is_string($specifier)) {
            if (is_callable(array($specifier, $method))) {
                return true;
            }
        } elseif (method_exists($specifier, $method)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determines if a class or instance object responds to a method.
     *
     * This method is an extension of the method_existsExact() method.
     * It also handles subdelegates, so it returns true if a delegator
     * is passed in and has a delegate that can implement $method,
     * whether or not the delegator can implement the $method.
     *
     * @see PEAR_Delegator::method_existsExact()
     * @param mixed $specifier Specifies the delegate with either
     *                           a class or instantiated object.
     * @param class $classname The method to look for.
     * @return  bool    true if it is, false if it is not.
     */
    public function method_exists($specifier, $method)
    {
        if (PEAR_Delegator::method_existsExact($specifier, $method)) {
            return true;
        } elseif (is_object($specifier) && ($specifier instanceof PEAR_Delegator)) {
            foreach ($specifier->_delegates as $delegate) {
                if (PEAR_Delegator::method_exists($delegate, $method)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Finds whether or not the object or one of its delegates implements a method
     *
     * Finds whether or not the calling object can perform the given method name.
     * The calling object can perform the given method if it or one of its delegates
     * can do so. This means that it also searches delegates that are themselves
     * delegators.
     *
     * @see PEAR_Delegator::method_exists()
     * @uses PEAR_Delegator::method_exists()
     * @param string $method The method name that is to be searched for availability.
     * @return  bool    true if it does, false if it does not.
     */

    public function respondsToMethod($method)
    {
        return PEAR_Delegator::method_exists($this, $method);
    }
    
    /**
     * Stores the relationship between method names and delegates.
     *
     * Takes a method name, searches for the delegate that can handle
     * it, and stores the relationship in the _method_map array. This
     * method is called when the __call() method reveives an unrecognized
     * method. This caching of methods speeds up delegation. If the method
     * cannot be handled by any of the adopted delegates, then an Exception
     * is thrown.
     *
     * Note: This caches the first responding delegate.
     *
     * This is an internal method and should not be invoked.
     *
     * @param string $method The method name that is to be cached. This must
     *                       be a lowercase string.
     * @throws PEAR_Delegator_ExceptionNoDelegateForMethod If the method 
     */
    protected function cacheMethod($method)
    {
        $delegates = $this->getDelegateForMethod($method);
                    
        if ($delegates && ($delegate = $delegates[0])) {
            $this->_method_map[$method] = $delegate;
            return;
        }
        
        throw new PEAR_Delegator_ExceptionMethodUndefined($method);
    }
    
    /**
     * This can be used by delegators for pseudo-method-overriding a method.
     *
     * This is the public interface to the __call() method, and it allows
     * for a method to be forwarded to the delegation system, so that
     * pseudo-method-overriding can occur.
     *
     * @see PEAR_Delegator::__call()
     * @param string $method See the PHP documentation.
     * @param string $args See the PHP documentation
     */
     public function forwardMethod($method, $args = array())
     {
         return $this->__call($method, $args);
     }
    
    /**
     * Processes unrecognized method signatures.
     *
     * This checks the _method_map array for a cached relationship
     * between the method and any delegate. If one exists, the method
     * is immediately called and the result returned. If it does not,
     * then it calls the cacheMethod() method to find and cache the
     * method, after which is calls the unrecognized method on the
     * proper delegate or kills the PHP with an error.
     *
     * This is an internal method and should not be invoked.
     *
     * @see PEAR_Delegator::forwardMethod(), PEAR_Delegator::cacheMethod()
     * @param string $method See the PHP documentation.
     * @param string $args See the PHP documentation.
     * @return mixed the result of the called method.
     * @uses PEAR_Delegator::cacheMethod()
     */
    public function __call($method, $args = array(null))
    {
        //It is necessary to convert the string to lowercase since PHP doesn't 
        //differentiate between case when calling methods. This distills the
        //situation to one case.
        $method = strtolower($method);
        
        if (!array_key_exists($method, $this->_method_map)) {
            try {
                $this->cacheMethod($method);
            } catch(PEAR_Delegator_ExceptionMethodUndefined $exception) {
                die('<b>Fatal error</b>: ' . $exception->getMessage());
            }
        }
        
        $forwardingDelegator = $args[0];
                
        if (!(is_object($forwardingDelegator)) || !($forwardingDelegator instanceof PEAR_DelegatorInternalForwardingProxy)) {
            $forwardingDelegator = new PEAR_DelegatorInternalForwardingProxy($this);
            $args = array_merge(array($forwardingDelegator), $args);
        }
        
        $delegate       = $this->_method_map[$method];
                
        if (($delegate instanceof PEAR_Delegator) && !(PEAR_Delegator::method_existsExact($delegate, $method))) {
            return $delegate->forwardMethod($method, $args);
        }
                
        if ((!array_key_exists(is_string($delegate) ? $delegate : get_class($delegate), $this->_delegatesFalse))) {
            $args[0] = $forwardingDelegator->getDelegator();
        } else {
            unset($args[0]);
        }
                
        return call_user_func_array(array($delegate, $method), $args);
    }
}
?>
