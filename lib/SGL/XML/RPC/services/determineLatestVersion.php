<?php

$determineLatestVersion_sig = array(array("string"));
$determineLatestVersion_doc = "Requires no parameters, returns a string indicating the latest framework version number.";
$determineLatestVersion_alias = 'framework.determineLatestVersion';

function SGL_XML_RPC_Server_determineLatestVersion($msg)
{
    //  get framework version
    require_once SGL_LIB_DIR . '/SGL/Install/Common.php';
    $ret = SGL_Install_Common::getFrameworkVersion();

    $result = new XML_RPC_Value($ret, "string");
    $return = new XML_RPC_Response($result);

    return $return;
}

?>