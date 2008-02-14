<?php
/**
 * Abstract model controller for all the 'manager' classes.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @abstract
 */
class SGL_Observer
{
    function update($observable) {}
}

/**
 * Abstract model controller for all the 'manager' classes.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @abstract
 */
class SGL_Observable
{
    var $aObservers = array();

    function attach($observer)
    {
        $this->aObservers[] = $observer;
    }

    function detach($observer)
    {
        $this->aObservers = array_diff($this->aObservers, array($observer));
    }

    function notify()
    {
        foreach ($this->aObservers as $obs) {
            $returnVal = $obs->update($this);
            if (PEAR::isError($returnVal)) {
                PEAR::raiseError($returnVal->getMessage(), $returnVal->getCode());
            }
        }
    }

    function getStatus() {}
}
?>