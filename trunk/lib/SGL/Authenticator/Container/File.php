<?php
require_once SGL_CORE_DIR . '/Authenticator/Container.php';

class SGL_Authenticator_Container_File extends SGL_Authenticator_Container
{
    var $options = array();

    /**
     * Constructor of the container class
     *
     * @param  string $filename             path to passwd file
     * @return object Auth_Container_File   new Auth_Container_File object
     */
    function SGL_Authenticator_Container_File($aOptions)
    {
        // Only file is a valid option here
        if (array_key_exists('filename', $aOptions)) {
            $this->pwfile = $aOptions['filename'];
            $this->_parseOptions($aOptions);
        }
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
     * Authenticate an user
     *
     * @param   string  username
     * @param   string  password
     * @return  mixed   boolean|PEAR_Error
     */
    function fetchData($user, $pass)
    {
        #return File_Passwd::staticAuth($this->options['type'], $this->pwfile, $user, $pass);
        $line = $this->_getUserRecordFromFile($this->pwfile, $user);
        if (!$line || PEAR::isError($line)) {
            return $line;
        }
        @list(,$real) = explode(':', $line);
        return $real;
    }

    /**
    * Base method for File_Passwd::staticAuth()
    *
    * Returns a PEAR_Error if:
    *   o file doesn't exist
    *   o file couldn't be opened in read mode
    *   o file couldn't be locked exclusively
    *   o file couldn't be unlocked (only if auth fails)
    *   o file couldn't be closed (only if auth fails)
    *
    * @throws   PEAR_Error
    * @access   protected
    * @return   mixed       line of passwd file containing <var>$id</var>,
    *                       false if <var>$id</var> wasn't found or PEAR_Error
    * @param    string      $file   path to passwd file
    * @param    string      $id     user_id to search for
    * @param    string      $sep    field separator
    */
    function _getUserRecordFromFile($file, $id, $sep = ':')
    {
        $file = realpath($file);
        if (!is_file($file)) {
            return PEAR::raiseError("File '$file' couldn't be found.", 0);
        }
        $aLines = file($file);
        $ret = false;
        if (count($aLines)) {
            $line = trim(substr($aLines[1], 1));
            if ($line) {
                $ret = $line;
            } else {
                $ret = false;
            }
        } else {
            $ret = false;
        }
        return $ret;
    }
}
?>