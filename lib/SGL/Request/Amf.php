<?php

class SGL_Request_Amf extends SGL_Request_Ajax
{
    function init()
    {
        parent::init();
        $this->type = SGL_REQUEST_AMF;
        return true;
    }
}
?>
