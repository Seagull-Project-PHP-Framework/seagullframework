<?php

$getAllModules_sig = array(array("array"));
$getAllModules_doc = "Requires no parameters, returns an array of all seagull modules.";
$getAllModules_alias = 'framework.getAllModules';

function SGL_XML_RPC_Server_getAllModules($msg)
{
    require_once SGL_LIB_DIR . '/SGL/Util.php';
    $aModules = SGL_Util::getAllModuleDirs($onlyRegistered = false);
    sort($aModules);

    $ret = array();
    foreach ($aModules as $module) {
        $ret[] = new XML_RPC_Value($module, "string");
    }
    $result = new XML_RPC_Value($ret, "array");
    $return = new XML_RPC_Response($result);

    return $return;
}

?>