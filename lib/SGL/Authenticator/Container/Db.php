<?php
require_once SGL_CORE_DIR . '/Authenticator/Container.php';

class SGL_Authenticator_Container_Db extends SGL_Authenticator_Container
{
    var $options = array();

    /**
     * Constructor of the container class
     *
     * @param  string $filename             path to passwd file
     * @return object Auth_Container_File   new Auth_Container_File object
     */
    function SGL_Authenticator_Container_Db($aOptions)
    {
        $this->_setDefaults();
    }

    /**
     * Parse options passed to the container class
     *
     * @access private
     * @param  array
     */
    function _parseOptions($array)
    {
        foreach ($array as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Set some default options
     *
     * @access private
     * @return void
     */
    function _setDefaults()
    {

    }

    /**
     * Authenticate an user
     *
     * @param   string  username
     * @param   string  password
     * @return  mixed   boolean|PEAR_Error
     */
    function fetchData($user, $pass)
    {
        if ($this->verifyPassword($password,
                                  $res[$this->options['passwordcol']],
                                  $this->options['cryptType'])) {

        }
    }
}
?>