<?php
class Uber_Lazy
{
    /**
     *
     *
     * @var Uber_Lazy_Mixin
     */
    private $_mixin;

    /**
     * Final constructor takes over to assure the automated mixin configuration
     * from
     * 
     * var $mixins = array(...);
     * 
     * is loaded on __construct.
     * 
     * If you need to implement your own constructor, use 
     * 
     * function ___construct()
     * {
     *    ....
     * }
     * 
     * Watch out for the 3 underscores "_" in the ___constructor.
     * 
     * This will be called inmediately after the constructor
     *
     */
    public final function __construct()
    {
        $args = func_get_args();
        $this->_mixin = new Uber_Lazy_Mixin($this);
        if (method_exists($this, '___construct')) {
            call_user_func_array(array($this , '___construct'), $args);
        }
    }

    public final function __get($name)
    {
        if (($mixin = $this->_mixin->getMixin($name))) {
            $this->$name = $mixin;
            return $this->$name;
        }
    }
}
?>