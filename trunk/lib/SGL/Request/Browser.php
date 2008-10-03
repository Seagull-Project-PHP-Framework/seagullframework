<?php

class SGL_Request_Browser
{
    function init()
    {
        return $_GET + $_POST;
    }
}
?>
