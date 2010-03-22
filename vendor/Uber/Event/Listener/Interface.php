<?php
interface Uber_Event_Listener_Interface
{

    public function handleEvent(Uber_Event $e, $data = null);

    public function validate();
}
?>