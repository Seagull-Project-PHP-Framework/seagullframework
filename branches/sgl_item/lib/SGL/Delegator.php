<?php
class SGL_Delegator
{
    var $aDelegates = array();
    
    function __call($methodName, $parameters) 
    {
        $delegated = false;
       
        foreach ($this->aDelegates as $delegate) {
            $class   = new ReflectionClass($delegate);
            $methods = $class->getMethods();

            foreach ($methods as $method) {
               if ($methodName == $method->getName()) {
                    $delegated = true;
      
                    return call_user_func_array(
                        array($delegate, $method->getName()), $parameters);
               }
            }
            if (!$delegated) {
                $tmp = debug_backtrace();
                $step = array_pop($tmp);
                die(
                    sprintf(
                        'Fatal error: Call to undefined method %s() in %s on line %s.',
                        $step['function'],
                        $step['file'],
                        $step['line']
                        )
               );
            }
        }
    }
    
    function add($delegate) 
    {
        if (!SGL::isPhp5()) {
            aggregate_methods($this, get_class($delegate));
        }
        $this->aDelegates[] = $delegate;
    }
}
?>    