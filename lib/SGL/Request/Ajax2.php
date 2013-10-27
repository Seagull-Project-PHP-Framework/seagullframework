<?php

require_once dirname(__FILE__) . '/Browser2.php';

/**
 * Class SGL_Request_Ajax2
 */
class SGL_Request_Ajax2 extends SGL_Request_Browser2
{
    /**
     * @param null $type
     * @return bool|object|void
     */
    public function init($type = null)
    {
        parent::init();
        $this->type = SGL_REQUEST_AJAX;
    }
}

?>