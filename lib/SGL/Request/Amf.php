<?php

require_once SGL_CORE_DIR . '/Request/Ajax.php';

class SGL_Request_Amf extends SGL_Request_Ajax
{
    function init()
    {
        parent::init();
        $this->type = SGL_REQUEST_AMF;
        return true;
    }

    function getActionName()
    {
        $container = ucfirst($this->getModuleName()) . 'AmfProvider';
        $ok = preg_match(
            "/$container\.([a-zA-Z_0-9]+)/",
            $GLOBALS['HTTP_RAW_POST_DATA'],
            $aMatches
        );
        return $ok ? $aMatches[1] : 'default';
    }
}
?>
