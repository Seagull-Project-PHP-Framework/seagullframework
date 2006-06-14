<?php

require_once 'LiveUser/Admin.php';

define('OPC_DEFAULT_APPLICATION', 1);
define('OPC_DEFAULT_AREA', 1);

/**
 * LiveUsers administration - common class
 *
 * @package liveuser
 * @author  Radek Maciaszek <radek@gforces.co.uk>
 */
class LUAdmin
{

    /**
     * Singleton for admin liveuser
     *
     * @param mixed $initialize user data object or false
     * @static 
     * @return mixed Perm object Only if initialize else false
     */ 
    function &singleton($conf = null)
    {
        static $admin;
        static $staticConf;
        
        if (isset($admin) && isset($staticConf) && ($staticConf === $conf || $conf === null)) {
            return $admin;
        }
        
        $staticConf = &LUAdmin::getConfig();
        
        $admin = &LUAdmin::factory($staticConf);
        $ok = $admin->setAdminPermContainer();
        if (!$ok) {
            return $admin->getErrors();  
        }

        $admin->perm->init($staticConf['permContainer']);
        
        /*
        $logconf = array('mode' => 0666, 'timeFormat' => '%X %x');
        $logger = &Log::factory('file', 'liveuser_test.log', 'ident', $logconf);
        $admin->addErrorLog($logger);
        $GLOBALS['_LIVEUSER_DEBUG'] = true;
        */
        
        return $admin;
    }
    
    /**
     * Factory admin object
     *
     * @param string $type
     * @return mixed Perm object or false on error
     */
    function &factory(&$conf) {
        
        return LiveUser_Admin::factory($conf);
    }
    
    /**
     * Build config for LiveUser_Admin package
     *
     * @return array  Array config
     */
    function &getConfig() {
        
        $conf = &$GLOBALS['_SGL']['CONF'];

        // seagull specific mysql container
#FIXME remove
       
        $dsn = SGL_DB::getDsn();
        
        if ($dsn['phptype'] == 'mysql_SGL') {
            $dsn['phptype'] = 'mysql';
        }
        
        $dbSingleton = &SGL_DB::singleton(); // get as a copy
        
        // clone it - because we are changing assocMode
        $db = clone($dbSingleton);
        
        if (PEAR::isError($db)) {
            SGL::raiseError('Cannot connect to DB, check your credentials, exiting ...',
                    SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
        }
        
      
        $db->setFetchMode(DB_FETCHMODE_ASSOC);
        
        $luConf =
            array(
                'autoInit' => false,
                'session'  => array(
                    'name'     => $conf['cookie']['name'],
                    'varname'  => 'ludata'
                ),
                'login' => array(
                    'force'    => false,
                ),
                'logout' => array(
                    'destroy'  => true,
                ),
                'authContainers' => array( 'DB' => 
                    array(
                        'type'          => 'DB',
//                        'name'          => 'DB_Local',
                        'loginTimeout'  => 0,
                        'expireTime'    => 3600,
                        'idleTime'      => 1800,
                        'dsn'           => $dsn,
                        'allowDuplicateHandles' => false,
                        'authTable'     => 'liveuser_users',
                            'authTableCols' => array(
                                'required' => array(
                                    'auth_user_id' => array('type' => 'text',   'name' => 'auth_user_id'),
                                    'handle'       => array('type' => 'text',   'name' => 'handle'),
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
                            'dsn' => $dsn,
                            'prefix' => 'liveuser_',
                            'tables' => array(),
                            'fields' => array(),
                             'force_seq' => false
                        ),
                    ),
                ),
            ); 
        
        return $luConf;
    }
    
    /**
     * Convert string into constant representation (upper letters and replace some letters)
     *
     * @access  public
     * @param   string  $str  String to change
     * @static 
     * @return  string   $str  Changed string (constant representation)
     */
    function convertToConstant($str)
    {
        $str = str_replace(' ', '_', $str);
        return strtoupper($str);
    }
    
    /**
     * Convert whatever object (or array) to string representation
     *
     * @access  public
     * @param   mixed  $error Object|array|whatever else
     * @static 
     * @return  string   $str  string representation
     */
    function errorToString($error)
    {
        ob_start();
        print_r($error);
        $str = ob_get_clean();
        
        return $str;
    }
    
    /**
     * Raise error message and redirect to list page
     *
     * @access  public
     * @static 
     */
    function noRecordRedirect()
    {
        SGL::raiseMsg('There is not record with such id');
        SGL_HTTP::redirect('', array('action' => 'list')); 
    }
    
    /**
     * Parse string from changer widget.
     *
     * @access  public
     * @param   string  $sWidgetData colon-separated string of username_UIDs
     * @static 
     * @return  array   $aData  hash of key => name
     */
    function parseWidgetString($sWidgetData)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        $aTmpData = split(':', $sWidgetData);
        if (count($aTmpData) > 0) {
            array_pop($aTmpData);
            $aData = array();
            foreach ($aTmpData as $row) {
                //  chop at caret
                list($name, $key) = split('\^', $row);
                $aData[$key] = $name;
            }
        } else {
            return false;
        }
        return $aData;
    }
    
    
    /**
     * Retrieve groups for specific user
     *
     * @access  public
     * @param   int     $userId user_id
     * @static 
     * @return  array   $aGroups  groups associated to user with userId
     */
    function getGroupsByUserId($userId)
    {
        $userId = (int) $userId;
        
        if(empty($userId)) {
            SGL::raiseError('Bad user id', SGL_ERROR_INVALIDARGS);
            return array();
        }
        
        $query = "
                SELECT  lgu.group_id, lt.name
                FROM    liveuser_groups lg, liveuser_groupusers lgu 
                LEFT JOIN liveuser_translations lt ON lt.section_id = lg.group_id 
                WHERE   lg.group_id = lgu.group_id
                AND lt.section_type = ".LIVEUSER_SECTION_GROUP."
                AND lgu.perm_user_id = " . $userId . "
                ORDER BY lt.name";
        
        $dbh = &SGL_DB::singleton();
        
        
        $aGroups = $dbh->getAssoc($query);        
              
        return $aGroups;
    }
    
    /**
     * Retrieve all rights
     *
     * @access  public
     * @static 
     * @return  array  $aRights  all system rights
     */
    function getAllRightsAsArray()
    {
        $query = '  SELECT
                        liveuser_rights.right_id AS right_id,
                        liveuser_rights.right_define_name AS right_define_name,
                        liveuser_applications.application_define_name AS application_define_name,
                        liveuser_areas.area_define_name AS area_define_name
                    FROM
                        liveuser_rights,
                        liveuser_areas,
                        liveuser_applications
                    WHERE
                        liveuser_rights.area_id = liveuser_areas.area_id
                        AND liveuser_areas.application_id = liveuser_applications.application_id';
        
        $dbh = &SGL_DB::singleton();
        
        $aRights = $dbh->getAssoc($query);
        return $aRights;
    }
    
    /**
     * Rebuild file containing all constants
     *
     * @access  public
     * @static 
     * @return  bool  true on success else false
     */
    function rebuildRightsConstants()
    {
        $rights = LUAdmin::getAllRightsAsArray();
        
        $brline = "\n";
        $phpStr = '<?php' . $brline;
        // build string with constants
        foreach ($rights as $right) {
            $phpStr .= "define('$right->right_define_name', $right->right_id);" . $brline;
        }
        $phpStr .= '?>';
        
        // save it into defaults location
        $conf = &$GLOBALS['_SGL']['CONF'];
        $targetConstantFileName = $conf['permission']['constantsFile'];
        $success = file_put_contents($targetConstantFileName, $phpStr);

        if (!$success) {
            SGL::raiseError('There was a problem creating the liveuser constants file', 
                SGL_ERROR_FILEUNWRITABLE);
            
            return false;
            
        } else {
            return true;
        }
    }
    
    /**
     * Prints last LU Error
     *
     * @access  public
     * @static 
     * @return  bool  true on success else false
     */    

    function raiseError(&$obj) {
        $LUErrors = $obj->perm->stack->getErrors();        
        $message =  $LUErrors[0]['message'] . "\n" .
                    "Error in " . $LUErrors[0]['context']['file'] . " on line " .
                    $LUErrors[0]['context']['line'];
        SGL::raiseError($message,LU_ERROR_ADMIN);
        
    }

}
?>