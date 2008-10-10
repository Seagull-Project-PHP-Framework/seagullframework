<?php

class SGL_Request_Browser
{
    public function init()
    {
        return $_GET + $_POST;
    }
}
?>
