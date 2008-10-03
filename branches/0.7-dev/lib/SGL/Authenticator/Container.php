<?php
class SGL_Authenticator_Container
{

    function fetchData($username, $password)
    {
    }

    /**
     * Crypt and verfiy the entered password
     *
     * @param  string Entered password
     * @param  string Password from the data container (usually this password
     *                is already encrypted.
     * @param  string Type of algorithm with which the password from
     *                the container has been crypted. (md5, crypt etc.)
     *                Defaults to "md5".
     * @return bool   True, if the passwords match
     */
    function verifyPassword($password1, $password2, $cryptType = 'md5')
    {
        switch ($cryptType) {
        case 'crypt' :
            return ( crypt($password1, $password2) == $password2 );
            break;
        case 'none' :
        case '' :
            return ($password1 == $password2);
            break;
        case 'md5' :
            return (md5($password1) == $password2);
            break;
        default :
            if (function_exists($cryptType)) {
                return ($cryptType($password1) == $password2);
            } elseif (method_exists($this,$cryptType)) {
                return ($this->$cryptType($password1) == $password2);
            } else {
                return false;
            }
            break;
        }
    }

    /**
      * Returns the crypt current crypt type of the container
      *
      * @return string
      */
    function getCryptType()
    {
        return false;
    }
}
?>