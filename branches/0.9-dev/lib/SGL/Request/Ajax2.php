<?php

require_once dirname(__FILE__) . '/Browser2.php';

class SGL_Request_Ajax2 extends SGL_Request_Browser2
{
    public function init()
    {
        parent::init();
        $this->type = SGL_REQUEST_AJAX;
    }
}

?>