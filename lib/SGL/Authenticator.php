<?php
class SGL_Authenticator
{
    #var $_authCredentials = array();
    var $_storage = null;

    function SGL_Authenticator($driver, $options = array())
    {
        $storageClass = 'SGL_Authenticator_Container_' . $driver;
        require_once SGL_CORE_DIR . '/Authenticator/Container/' . $driver . '.php';
        $obj =& new $storageClass($options);
        $this->_storage = $obj;
    }

    function authenticate($username, $clearTextPasswd, $usingChap = false)
    {
        if (!empty($username)) {
            $encryptedPasswd = $this->_storage->fetchData($username, $clearTextPasswd, $usingChap);
            if (!PEAR::isError($encryptedPasswd) && $encryptedPasswd !== false) {
                if ($this->_storage->verifyPassword($clearTextPasswd, $encryptedPasswd)) {
                    return true;
                }
            }
        }
    }

    #function _authenticate($userId, $credentials) {}

    #function isAuthenticated($realm = null) {}

    #function setAuth($userId, $credentials, $realm = null) {}

    #function clearAuth($realm = null) {}
}
?>