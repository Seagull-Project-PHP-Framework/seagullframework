<?php

class SGL2_Request_Browser
{
    public function init()
    {
        return $_GET + $_POST;
    }
}
?>
