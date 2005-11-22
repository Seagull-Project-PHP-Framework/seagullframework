<?php

/**
  * A class that allows services to be globally registered, so that they
  * can be accessed by any class that needs them. Also allows Mock Objects
  * to be easily used as replacements for classes during testing.
  */
class SGL_ServiceLocator
{
    var $aServices = array();

    /**
     * A method to return a singleton handle to the service locator class.
     */
    function &singleton()
    {
        static $instance;
        if (!$instance) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    /**
     * A method to register a service with the service locator class.
     *
     * @param string $serviceName The name of the service being registered.
     * @param mixed $oService The object (service) being registered.
     * @return boolean Always returns true.
     */
    function register($serviceName, &$oService)
    {
        $this->aServices[$serviceName] = &$oService;
        return true;
    }

    /**
     * A method to remove a registered service from the service locator class.
     *
     * @param string $serviceName The name of the service being de-registered.
     */
    function remove($serviceName)
    {
        unset($this->aServices[$serviceName]);
    }

    /**
     * A method to return a registered service.
     *
     * @param string $serviceName The name of the service required.
     * @return mixed Either the service object requested, or false if the
     *               requested service was not registered.
     */
    function &get($serviceName)
    {
        if (isset($this->aServices[$serviceName])) {
            return $this->aServices[$serviceName];
        }
        return false;
    }

    /**
     * A method to return a registered service.
     *
     * @param string $serviceName The name of the service required.
     * @return mixed Either the service object requested, or false if the
     *               requested service was not registered.
     * @static
     */
    function &staticGet($serviceName)
    {
        $oServiceLocator = &SGL_ServiceLocator::singleton();
        return $oServiceLocator->get($serviceName);
    }
}

?>
