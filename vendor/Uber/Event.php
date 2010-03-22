<?php
abstract class Uber_Event implements Uber_Event_Interface
{
    const INIT = 0;
    const SHUTDOWN = 99;
    private $_cancelled = false;
    private $_subject = null;
    private $_name = null;
    private $_params = array();
    private $_inQueue = false;

    function __construct($oSubject, $eventName, array $params = array())
    {
        $this->_subject = $oSubject;
        $this->_name = $eventName;
        $this->_params = $params;
    }

    public function cancel()
    {
        $this->_cancelled = true;
    }

    public function isCancelled()
    {
        return $this->_cancelled;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function queueEvent()
    {
        $this->_inQueue = true;
    }

    function getParameters()
    {
        return $this->_params;
    }
}
?>