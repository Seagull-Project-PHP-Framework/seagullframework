<?php
/**
 * XML RPC server.
 *
 * @author Demian Turner <demian@phpkitchen.com>
 * @author James Floyd <jgfloyd@btinternet.com>
 */

$rootDir = dirname(__FILE__) . '/../..';
$varDir = dirname(__FILE__) . '/../../var';

//  check for lib cache
define('SGL_CACHE_LIBS', (is_file($varDir . '/ENABLE_LIBCACHE.txt'))
    ? true
    : false);

require_once $rootDir .'/lib/SGL/FrontController.php';
SGL_FrontController::init();

require_once 'XML/RPC/Server.php';

/**
 * Method to return dispatcher map containing information
 * about defined SGL XML-RPC methods.
 *
 * @return array dispacher map
 *
 * Array
 * (
 *     (
 *         [agencies.getStats] => Array
 *             (
 *                 [function] => SGL_XML_RPC_Server_myFunctionName
 *                 [signature] => Array
 *                     (
 *                         [0] => Array
 *                             (
 *                                 [0] => boolean
 *                                 [1] => int
 *                                 [2] => struct
 *                                 [3] => struct
 *                             )
 *                     )
 *                 [docstring] => Accepts one int parameter, returns true if agency is successfully deactivated.
 *             )
 *     )
 */
function SGL_XML_RPC_getDispatchMap()
{
    $map = array();

    // scan services folder
    require_once 'File/Util.php';
    $rpcModuleDir = SGL_LIB_DIR . '/SGL/XML/RPC/services';
    $aFiles = SGL_Util::listDir($rpcModuleDir, FILE_LIST_FILES, $sort = FILE_SORT_NONE);

    foreach ($aFiles as $file) {
        require_once $rpcModuleDir . '/' . $file;

        // formulate expected method name
        $actionName = substr($file, 0, strlen(substr($file, 0, strrpos($file, '.'))));
        $functionName = 'SGL_XML_RPC_Server_' . $actionName;

        if (function_exists($functionName)) {
            $aliasVar = $actionName . '_alias';
            $signatureVar = $actionName . '_sig';
            $docstringVar = $actionName . '_doc';

            // check required config values are set
            if (isset(${$aliasVar}) && isset(${$signatureVar}) && isset(${$docstringVar})) {

                // Build map values
                $map[${$aliasVar}] = array(
                    "function"  => $functionName,
                    "signature" => ${$signatureVar},
                    "docstring" => ${$docstringVar},
                    );
            } else {
                echo 'SGL_XML_RPC_Server_' . $file . ' required param not defined!!';
            }
        } else {
            echo 'SGL_XML_RPC_Server_' . $file . ' method not defined!!';
        }
    }
    return $map;
}

/**
 * Cache wrapper for SGL_XML_RPC_getDispatchMap method.
 *
 * @return array dispacher map
 */
function SGL_XML_RPC_getDispatchMap_cache()
{
    require_once 'Cache/Lite/Function.php';
    $options = array(
        'cacheDir' => SGL_CACHE_DIR,
        'lifeTime' => 0
    );

    $cache = new Cache_Lite_Function($options);
    return $cache->call('SGL_XML_RPC_getDispatchMap');
}

$map = SGL_XML_RPC_getDispatchMap_cache();
#$map = SGL_XML_RPC_getDispatchMap();
new XML_RPC_Server($map, $serviceNow = 1, $debug = 0);
?>
