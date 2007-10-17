<?php

//require_once SGL_LIB_DIR . '/Horde/Routes/Mapper.php';
//require_once SGL_LIB_DIR . '/Horde/Routes/Exception.php';
//require_once SGL_LIB_DIR . '/Horde/Routes/Route.php';
//require_once SGL_LIB_DIR . '/Horde/Routes/Util.php';
//require_once SGL_LIB_DIR . '/Horde/Routes/Config.php';

require_once 'Horde/Routes/Mapper.php';
require_once 'Horde/Routes/Exception.php';
require_once 'Horde/Routes/Route.php';
require_once 'Horde/Routes/Util.php';
require_once 'Horde/Routes/Config.php';

class SGL_Request_Browser2 extends SGL_Request
{
    function init()
    {
        //  load routes
        if (file_exists(SGL_VAR_DIR . '/routes.php')) {
            include_once SGL_VAR_DIR . '/routes.php';
        } else {
            // copy the default configuration file to the users tmp directory
            if (!copy(SGL_ETC_DIR . '/routes.php.dist', SGL_VAR_DIR . '/routes.php')) {
                die('error copying routes file');
            }
            @chmod(SGL_VAR_DIR . '/routes.php', 0666);
            include_once SGL_VAR_DIR . '/routes.php';
        }


        $aParams = array();
        if (isset($aQueryData[''])) {
            $aParts = explode('/', $aQueryData['']);
            $aTmp = array();
            for ($x = 0; $x < count($aParts); $x++) {
                if ($x % 2) { // if index is odd
                    $aTmp['varValue'] = urldecode($aParts[$x]);
                } else {
                    $aTmp['varName'] = urldecode($aParts[$x]);
                }
                if (count($aTmp) == 2) {
                    $aParams[$aTmp['varName']] = $aTmp['varValue'];
                    $aTmp = array();
                }
            }
            unset($aQueryData['']);
            $aQueryData = array_merge($aQueryData, $aParams);
        }
        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($m);

        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_GET, $_FILES, $aQueryData, $_POST);
        $this->type = SGL_REQUEST_BROWSER;
        return true;
    }
}
?>
