<?php

require_once 'LiveUser.php';

/**
 * LiveUser perm container
 *
 * @package SGL
 * @author  Radek Maciaszek <radek@gforces.co.uk>
 * @category authentication
 */
class Perm_LiveUser
{
    var $container;
    var $options;
    
    var $initialized = false;
    
    /**
     * Constructor
     *
     * @param array $options Look into method for specification
     */ 
    function Perm_LiveUser($options)
    {
          
        $dbh = &SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];        
        
        $this->options = array(
            'autoInit'       => true,
            'login' => array(
                'force'    => true
             ),
            'logout' => array(
                'destroy'  => true,
             ),
            'authContainers' => array(
                                    array(
                                        'type'          => 'SGLAuth',
                                        'loginTimeout'  => $options['sessionTimeout'],
                                        'expireTime'    => $options['sessionMaxLifetime'],
                                        'idleTime'      => 1800,
                                        'allowDuplicateHandles' => 0,
                                        'passwordEncryptionMode' => 'MD5',
                                        'authTable'     => $options['authTable'],
                                        'authTableCols' => array(
                                            'required'  => array(
                                                'auth_user_id' => array('name' => 'usr_id',   'type' => 'text'),
                                                'handle'       => array('name' => 'username', 'type' => 'text'),
                                                'passwd'       => array('name' => 'passwd',   'type' => 'text'),
                                            ),
                                            'optional' => array(
                                                'lastlogin'    => array('name' => 'lastlogin',      'type' => 'timestamp'),
                                                'is_active'    => array('name' => 'is_acct_active', 'type' => 'boolean')
                                            ),
                                        ),
                                    ),
                                ),
            'permContainer'  => array(
                                    'type'  => 'Complex',
                                    'storage' => array('DB' => array('connection' => $dbh, 'prefix'     => 'liveuser_')),
                                ),
            'session'        => array('name' => $conf['cookie']['name']),
            'handle'         => $options['handle'],
            'password'       => $options['password'],
        );
    }
    
    /**
     * Initialize the liveuser container with $this->options - called only when needed
     *
     */ 
    function init($options = null)
    {
        if ($options === null) {
            $options = &$this->options;
        }
        $this->container = &LiveUser::factory($options, $options['handle'], $options['password']);
        if (!empty($this->container)) {
            $this->initialized = true;
        } else {
            // todo: add error handling
        }
    }
      
    /**
     * Read all user rights and return as array with rights ids
     *
     * @param bool $forceReload  if true rights are reloaded from container, else from session
     * @return array array with right ids
     */ 
    function &readRights($forceReload = false, $userId = null)
    {
        static $rightIds;
        
        if (isset($rightIds) && !$forceReload) {
            return $rightIds;
        }
        if (isset($_SESSION['liveuserRights']) && is_array($_SESSION['liveuserRights']) && !$forceReload) {
            $rightIds = $_SESSION['liveuserRights'];
            return $rightIds;
        }
        if (!$this->initialized) {
            $this->init();
        }
        if ($userId !== null) {
            $this->container->_perm->permUserId = $userId;
        }
        $rightIds = $this->container->_perm->readRights();
        
        // write into session
        $_SESSION['liveuserRights'] = $rightIds;
        
        return $rightIds;
    }
      
    /**
     * Check if user has specific right: by right id or by string which is treat as
     * constant (define name)
     *
     * @param mixed $rightId  rightId or string with right define name
     * @return bool  true if user has a right else false
     */ 
    function checkRight($rightId)
    {
        if (!is_int($rightId)) {
            
            // try retrieve as constant (define name)
            if (defined($rightId)) {
                eval("\$rightId = \$\$rightId");
            } else {
                SGL::raiseError('There is no constant: ' . $rightId,
                    SGL_ERROR_NODATA);
                return false;
            }
        }
        
        $rights = &$this->readRights();
        return isset($rights[$rightId]) ? $rights[$rightId] : false;
    }
    
    /**
     * Get all user perms based on his(er) rights
     *
     * @param mixed $rights  array of user right, if === null - lazy initilization
     * @return array  array of perms
     */ 
    function getPermsByRights($rights = null)
    {
        if ($rights === null) {
            $rights = &$this->readRights();
        }
        if (empty($rights)) {
            return array();
        }
        
        $rightsIds = array_keys($rights);
        $rightIn = implode(',', $rightsIds);
        
        $whereInClause = ' right_id IN (' . $rightIn . ')';
        
        $query = '  SELECT  permission_id 
                    FROM    right_permission
                    WHERE   ' . $whereInClause;
        
        $dbh = & SGL_DB::singleton();
        $aRightPerms = $dbh->getCol($query);
        if (is_a($aRightPerms, 'PEAR_Error')) {
           return SGL::raiseError('There was a problem retrieving perms', 
                SGL_ERROR_NODATA);
		}
        return $aRightPerms;
    }
    
}

?>