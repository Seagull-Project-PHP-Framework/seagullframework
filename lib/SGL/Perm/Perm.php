<?php

require_once 'DB/DataObject.php';

/**
 * Class for user authentication and authorisation
 *
 * @package SGL
 * @author  Radek Maciaszek <radek@maciaszek.pl>
 * @version $Revision: 1.10 $
 * @since   PHP 4.1
 */
class SGL_Perm
{

    /**
     * Singleton for user dataobject
     *
     * @param mixed $initialize user data object or false
     * @static 
     * @return mixed Perm object Only if initialize else false
     */ 
    function singleton($type = 'default')
    {
        static $perm;
        
        if($type == 'default') {
            $c = &SGL_Config::singlton();
            $this->conf = $c->getAll();
            $type = $this->conf['authentication']['permissionPackage'];
        }
        if(!isset($perm[$type])) {
            $perm[$type] =& SGL_Perm::factoryPerm($type);
        }
        return $perm[$type]; 
    }
    
    /**
     * Factory permission layer
     *
     * @param string $type
     * @return mixed Perm object or false on error
     */
    function &factoryPerm($type) {

        switch (strtolower($type)) {
        case 'liveuser':
            $type = 'LiveUser';
            break;
        case 'standard':
        default:
            $type = 'Standard';
            break;
        }

        $class = 'Perm_' . $type;
        $classfile = '/Perm/Container/' . $type . '.php';

        /*
         * Attempt to include our version of the named class, but don't treat
         * a failure as fatal.  The caller may have already included their own
         * version of the named class.
         */
        @include_once SGL_CORE_DIR . $classfile;

        
        $user = &SGL_Perm::singletonUsr();

        $conf = &$GLOBALS['_SGL']['CONF'];
        /*
        if (!isset($_SESSION['prefs'])) {
            $_SESSION['prefs'] = PreferenceMgr::getPrefsByUid(Session::getUid());
        }
        */

        // options for permission package   
        $options['handle']              = $user->username;
        $options['password']            = $user->passwd;
        $options['sessionTimeout']      = $_SESSION['aPrefs']['sessionTimeout'];
        $options['sessionMaxLifetime']  = $conf['site']['sessionMaxLifetime'];
        $options['authTable']           = $conf['table']['user'];

        // If class exists, return a new instance of it.
        if (class_exists($class)) {
            return new $class($options);
        }

        return false;
    }
    
    /**
     * Check any right for any permission container
     *
     * @param int $right_id
     * @param string $type Type of permission container
     * @return int
     */
    function checkRight($right_id, $type = 'default') {
        $perm =& SGL_Perm::singleton($type);
        
        if($perm) {
            return $perm->checkRight($right_id);
        } else {
            return false;
        }
    }
    
    /*
     * Singleton for user dataobject
     *
     * @param mixed $initialize user data object or false
     * @return object Only if initialize == false
     */
    function &singletonUsr($uid = -1) {
        static $user;
        
        if(!empty($uid) && is_object($uid)) {
            // set new $user object
            $user = $uid;
        }
        
        if($uid <= 0) {
            $uid = SGL_Session::getUid();
        }
        
        if(!$user) {
            if($uid > 0) {
                $user = & DB_DataObject::factory('usr');
                $user->get($uid);
                return $user;
            } else {
                $user = false;
                return $user;
            }
        } else {
            return $user;
        }
    }
    
}

?>