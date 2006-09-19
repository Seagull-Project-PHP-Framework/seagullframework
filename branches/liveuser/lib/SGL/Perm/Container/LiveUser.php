<?php

require_once 'LiveUser/Admin.php';

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
          
        $c      = &SGL_Config::singleton();
        $conf   = $c->getAll();        

        $dsn = SGL_DB::getDsn();      
        if ($dsn['phptype'] == 'mysql_SGL') {
            $dsn['phptype'] = 'mysql';
        }

        $dbSingleton = &SGL_DB::singleton(); // get as a copy
        
        // clone it - because we are changing assocMode
        $dbh = clone($dbSingleton);
        
        if (PEAR::isError($dbh)) {
            SGL::raiseError('Cannot connect to DB, check your credentials, exiting ...',
                    SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
        }
      
        $dbh->setFetchMode(DB_FETCHMODE_ASSOC);

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
                    'type'          => 'DB',
                    'loginTimeout'  => $options['sessionTimeout'],
                    'expireTime'    => $options['sessionMaxLifetime'],
                    'idleTime'      => 1800,
                    'dsn'           => $dsn,
                    'allowDuplicateHandles' => false,
                    'authTable'     => 'liveuser_users',
                        'authTableCols' => array(
                            'required' => array(
                                'auth_user_id' => array('type' => 'text',   'name' => 'usr_id'),
                                'handle'       => array('type' => 'text',   'name' => 'username'),
                                'passwd'       => array('type' => 'text',   'name' => 'passwd'),
                            ),
                            'optional' => array(
                                'is_active'      => array('type' => 'boolean', 'name' => 'is_active'),
                                'lastlogin'      => array('type' => 'timestamp', 'name' => 'lastlogin'),
                                'owner_user_id'  => array('type' => 'integer',   'name' => 'owner_user_id'),
                                'owner_group_id' => array('type' => 'integer',   'name' => 'owner_group_id')
                            ),
                            'custom' => array (
                                'name'  => array('type' => 'text',    'name' => 'name'),
                                'email' => array('type' => 'text',    'name' => 'email'),
                            ),
                        ),
                   'storage' => array(
                        'dsn' => $dsn,
                        'alias' => array(
                            'lastlogin' => 'lastlogin',
                            'is_active' => 'is_active',
                        ),
                        'fields' => array(
                            'lastlogin' => 'timestamp',
                            'is_active' => 'boolean',
                        ),
                        'tables' => array(
                            'users' => array(
                                'fields' => array(
                                    'lastlogin' => false,
                                    'is_active' => false,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'permContainer' => array(
                'type'  => 'Complex',                   
                'alias' => array(),
                'storage' => array(
                    'DB' => array(
                        'connection' => $dbh, 
                        'dsn' => $dsn,
                        'prefix' => 'liveuser_',
                        'tables' => array(),
                        'fields' => array(),
                        'force_seq' => false
                    ),
                ),
            ),
            'session'  => array(
                'name'     => $conf['cookie']['name'],
                'varname'  => 'ludata'
            ),
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
        $this->container = &LiveUser_Admin::factory($options, $options['handle'], $options['password']);

        if (!empty($this->container) && $this->container->init()) {
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

        $param['fields'] = array('right_id');
        $param['filters'] = array('perm_user_id' => $userId);
        $param['by_group'] = true;
        $aRights = $this->container->perm->getRights($param);

        // write into session
        $_SESSION['liveuserRights'] = $aRights;
        
        return $aRights;
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

        foreach ($rights as $key => $aValue) {
            $aRight[] = $aValue['right_id'];   
        }        

        $rightIn = implode(',', $aRight);

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