<?php

//	BC for < PHP 5.3.x
if (!class_exists('SplQueue')) {
	require dirname(dirname(__FILE__)) .'/bc/splqueue/spldoublylinkedlist.php';
	require dirname(dirname(__FILE__)) .'/bc/splqueue/splqueue.php';	
}



class Uber_Event_Dispatcher
{
    protected $_listeners = array();
    protected $_globalListeners = null;
    private $_queue = null;
    private static $_instance = null;

    private function __construct()
    {
        $this->_queue = new SplQueue();
        //$this->_queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
        $this->_globalListeners = new Uber_Event_Listener_Collection();
    }

    public static function getInstance()
    {
        if (! isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function addEventListener($eventName, Uber_Event_Listener_Interface $listener)
    {
        if (! array_key_exists($eventName, $this->_listeners)) {
            $this->_listeners[$eventName] = new Uber_Event_Listener_Collection();
        }
        $col = $this->_listeners[$eventName];
        $col->addListener($listener);
        // check the event queue
        foreach ($this->_queue as $event) {
            $this->_propagate($event, $data = null, false);
        }
    }

    public function removeEventListener($eventName, Uber_Event_Listener_Interface $listener)
    {
        if (! array_key_exists($eventName, $this->_listeners)) {
            return null;
        }
        $col = $this->_listeners[$eventName];
        $ok = $col->removeListener($listener);
    }

    public function getListenerCount($eventName = null)
    {
        if (is_null($eventName)) {
            //  count all listeners for all events
        }
        if (! isset($this->_listeners[$eventName])) {
            return false;
        }
        return count($this->_listeners[$eventName]);
    }

    public function addGlobalListener(Uber_Event_Listener_Interface $listener)
    {
        $this->_globalListeners->addListener($listener);

        foreach ($this->_queue as $event) {
            $this->_propagate($event, $data = null, false);
        }
    }

    /**
     * not implemented yet
     *
     * @return unknown_type
     */
    public function removeGlobalListener(Uber_Event_Listener_Interface $listener)
    {
        $col = $this->_globalListeners;
        $ok = $col->removeListener($listener);
        return $ok;
    }

    public function triggerEvent(Uber_Event $e, $data = null, $enQueue = false)
    {
        return $this->_propagate($e, $data, $enQueue);
    }

    protected function _propagate(Uber_Event $e, $data, $enQueue)
    {
        if (array_key_exists($e->getName(), $this->_listeners)) {
            $col = $this->_listeners[$e->getName()];
            $col->propagate($e, $data);
        }
        if ($e->isCancelled()) {
            return $e;
        }
        $this->_globalListeners->propagate($e, $data);
        if ($e->isCancelled() || $enQueue == false) {
            return $e;
        }
        $this->_queue[] = $e;
        return $e;
    }

    public function getEventListeners($eventName)
    {
        if (array_key_exists($eventName, $this->_listeners)) {
            return $this->_listeners[$eventName];
        }
        return new Uber_Event_Listener_Collection();
    }

    /**
     *
     * @return unknown_type
     */
    public function reset()
    {
        $this->_listeners = array();
        $this->_globalListeners = new Uber_Event_Listener_Collection();
        $this->_queue = new SplQueue();
        $this->_queue->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }
}
?>