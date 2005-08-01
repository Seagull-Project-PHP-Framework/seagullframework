<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A framework for authentication and authorization in PHP applications
 *
 * LiveUser is an authentication/permission framework designed
 * to be flexible and easily extendable.
 *
 * Since it is impossible to have a
 * "one size fits all" it takes a container
 * approach which should enable it to
 * be versatile enough to meet most needs.
 *
 * PHP version 4 and 5
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston,
 * MA  02111-1307  USA
 *
 *
 * @category authentication
 * @package  LiveUser
 * @author  Markus Wolff <wolff@21st.de>
 * @author Helgi �ormar �orbj�rnsson <dufuz@php.net>
 * @author  Lukas Smith <smith@backendmedia.com>
 * @author Arnaud Limbourg <arnaud@php.net>
 * @author   Pierre-Alain Joye  <pajoye@php.net>
 * @author  Bjoern Kraus <krausbn@php.net>
 * @copyright 2002-2005 Markus Wolff
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version CVS: $Id: LiveUser.php,v 1.90 2005/07/19 11:20:16 lsmith Exp $
 * @link http://pear.php.net/LiveUser
 */

/**
 * Include PEAR_ErrorStack
 * and Event_Dispatcher classes
 */
require_once 'PEAR/ErrorStack.php';
require_once 'Event/Dispatcher.php';
require_once 'PEAR.php';

/**#@+
 * Error related constants definition
 *
 * @var integer
 */
define('LIVEUSER_ERROR',                        -1);
define('LIVEUSER_ERROR_NOT_SUPPORTED',          -2);
define('LIVEUSER_ERROR_CONFIG',                 -3);
define('LIVEUSER_ERROR_MISSING_DEPS',           -4);
define('LIVEUSER_ERROR_COOKIE',                 -7);
define('LIVEUSER_ERROR_MISSING_FILE',           -8);
define('LIVEUSER_ERROR_FAILED_INSTANTIATION',   -9);
define('LIVEUSER_ERROR_INIT_ERROR',            -10);
define('LIVEUSER_ERROR_MISSING_CLASS',         -11);
define('LIVEUSER_ERROR_WRONG_CREDENTIALS',     -12);
define('LIVEUSER_ERROR_UNKNOWN_EVENT',         -13);
define('LIVEUSER_ERROR_NOT_CALLABLE',          -14);
/**#@-*/

/**#@+
 * Statuses of the current object.
 *
 * @var integer
 */
define('LIVEUSER_STATUS_OK',              1);
define('LIVEUSER_STATUS_IDLED',          -1);
define('LIVEUSER_STATUS_EXPIRED',        -2);
define('LIVEUSER_STATUS_ISINACTIVE',     -3);
define('LIVEUSER_STATUS_PERMINITERROR',  -4);
define('LIVEUSER_STATUS_AUTHINITERROR',  -5);
define('LIVEUSER_STATUS_UNKNOWN',        -6);
define('LIVEUSER_STATUS_AUTHNOTFOUND',   -7);
define('LIVEUSER_STATUS_LOGGEDOUT',      -8);
define('LIVEUSER_STATUS_AUTHFAILED',     -9);
define('LIVEUSER_STATUS_UNFROZEN',      -10);
/**#@-*/

/**
 * The higest possible right level
 *
 * @var integer
 */
define('LIVEUSER_MAX_LEVEL', 3);

/**#@+
 * Usertypes
 *
 * @var integer
 */
/**
 * lowest user type id
 */
define('LIVEUSER_ANONYMOUS_TYPE_ID',   0);
/**
 * lowest user type id
 */
// higest user type id
define('LIVEUSER_USER_TYPE_ID',        1);
/**
 * lowest admin type id
 */
define('LIVEUSER_ADMIN_TYPE_ID',       2);
define('LIVEUSER_AREAADMIN_TYPE_ID',   3);
define('LIVEUSER_SUPERADMIN_TYPE_ID',  4);
/**
 * higest admin type id
 */
define('LIVEUSER_MASTERADMIN_TYPE_ID', 5);
/**#@-*/

/**#@+
 * Section types
 *
 * @var integer
 */
define('LIVEUSER_SECTION_APPLICATION',  1);
define('LIVEUSER_SECTION_AREA',         2);
define('LIVEUSER_SECTION_GROUP',        3);
define('LIVEUSER_SECTION_RIGHT',        4);
/**#@-*/

// 60 * 60 * 24 == number of seconds in a day
define('LIVEUSER_DAY_SECONDS', 86400);

// 60 * 60 * 24 * 365 * 30 == number of seconds between 1970 and about 2000
define('LIVEUSER_COOKIE_DELETE_TIME', 946080000);

/**
 * Debug global. When set to true the
 * error stack will be printed to
 * a separate window using the Win implementation
 * of PEAR::Log (PEAR::ErrorStack has built in
 * support).
 *
 * @var boolean
 */
$GLOBALS['_LIVEUSER_DEBUG'] = false;

/**
 * This is a manager class for a user login system using the LiveUser
 * class. It creates a LiveUser object, takes care of the whole login
 * process and stores the LiveUser object in a session.
 *
 * You can also configure this class to try to connect to more than
 * one server that can store user information - each server requiring
 * a different backend class.
 *
 * An example would be to create a login
 * system for a live website that first queries the local database and
 * if the requested user is not found, it tries to find it in your
 * company's LDAP server. It means you don't have to create several
 * user accounts for your employees so that they can access closed
 * sections of your website - everyone can use one account.
 *
 * NOTE: No browser output may be made before using this class, because
 * it will try to send HTTP headers such as cookies and redirects.
 *
 * Requirements:
 * - Should run on PHP version 4.2.0 (required for PEAR_Errorstack or higher,
 *   tested only from 4.2.1 onwards
 *
 * Thanks to:
 * Bjoern Schotte, Kristian Koehntopp, Antonio Guerra
 *
 * @category authentication
 * @package  LiveUser
 * @author   Markus Wolff       <wolff@21st.de>
 * @author   Bjoern Kraus       <krausbn@php.net>
 * @author   Lukas Smith        <smith@backendmedia.com>
 * @author   Pierre-Alain Joye  <pajoye@php.net>
 * @author   Arnaud Limbourg    <arnaud@php.net>
 * @copyright 2002-2005 Markus Wolff
 * @license http://www.gnu.org/licenses/lgpl.txt
 * @version Release: @package_version@
 * @link http://pear.php.net/LiveUser
 */
class LiveUser
{
    /**
     * LiveUser options set in the configuration file.
     *
     * @var     array
     * @access  private
     */
    var $_options = array(
        'autoInit' => false,
        'session'  => array(
            'name'    => 'PHPSESSID',
            'varname' => 'ludata',
            'force_start' => true,
        ),
        'session_save_handler'  => false,
        'session_cookie_params' => false,
        'cache_perm' => false,
        'login' => array(
            'force'   => false,
            'regenid' => false
        ),
        'logout' => array(
            'destroy' => true
        )
    );

    /**
     * The auth container object.
     *
     * @var    object
     * @access private
     */
    var $_auth = null;

    /**
     * The permission container object.
     *
     * @var    object
     * @access private
     */
    var $_perm = null;

    /**
     * Nested array with the auth containers that shall be queried for user information.
     * Format:
     * <code>
     * array('name' => array("option1" => "value", ....))
     * </code>
     * Typical options are:
     * <ul>
     * - server: The adress of the server being queried (ie. "localhost").
     * - handle: The user name used to login for the server.
     * - password: The password used to login for the server.
     * - database: Name of the database containing user information (this is
     *   usually used only by RDBMS).
     * - baseDN: Obviously, this is what you need when using an LDAP server.
     * - connection: Present only if an existing connection shall be used. This
     *   contains a reference to an already existing connection resource or object.
     * - type: The container type. This option must always be present, otherwise
     *   the LoginManager can't include the correct container class definition.
     * - name: The name of the auth container. You can freely define this name,
     *   it can be used from within the permission container to see from which
     *   auth container a specific user was coming from.
     *</ul>
     *
     * @var    array
     * @access private
     */
    var $authContainers = array();

    /**
     * Array of settings for the permission container to use for retrieving
     * user rights.
     * If set to false, no permission container will be used.
     * If that is the case, all calls to checkRight() will return false.
     * The array element 'type' must be present for the LoginManager to be able
     * to include the correct class definition (example: "DB_Complex").
     *
     * @var    mixed
     * @access private
     */
    var $permContainer = false;

    /**
     * Current status of the LiveUser object.
     *
     * @var    string
     * @access private
     * @see    LIVEUSER_STATUS_* constants
     */
    var $status = LIVEUSER_STATUS_UNKNOWN;

    /**
     * Error stack
     *
     * @var    PEAR_ErrorStack
     * @access private
     */
    var $_stack = null;

    /**
     * PEAR::Log object
     * used for error logging by ErrorStack
     *
     * @access private
     * @var    Log
     */
    var $_log = null;

    /**
     * Error codes to message mapping array
     *
     * @var    array
     * @access private
     */
    var $_errorMessages = array(
        LIVEUSER_ERROR                        => 'Unknown error',
        LIVEUSER_ERROR_NOT_SUPPORTED          => 'Feature not supported by the container: %feature%',
        LIVEUSER_ERROR_CONFIG                 => 'There is an error in the configuration parameters',
        LIVEUSER_ERROR_MISSING_DEPS           => 'Missing package depedencies: %msg%',
        LIVEUSER_ERROR_COOKIE                 => 'There was an error processing the Remember Me cookie',
        LIVEUSER_ERROR_MISSING_FILE           => 'The file %file% is missing',
        LIVEUSER_ERROR_FAILED_INSTANTIATION   => 'Cannot instantiate class %class%',
        LIVEUSER_ERROR_INIT_ERROR             => 'Container %container% was not initialized properly',
        LIVEUSER_ERROR_MISSING_CLASS          => 'Class %class% does not exist in file %file%',
        LIVEUSER_ERROR_WRONG_CREDENTIALS      => 'The handle and/or password you submitted are not known',
        LIVEUSER_ERROR_UNKNOWN_EVENT          => 'The event %event% is not known',
        LIVEUSER_ERROR_NOT_CALLABLE           => 'Callback %callback% is not callable'
    );

    /**
     * Stores the event dispatcher which
     * handles notifications
     *
     * @var    array
     * @access protected
     */
    var $dispatcher = null;

    /**
     * Constructor
     *
     * @return void
     * @access protected
     */
    function LiveUser()
    {
        $this->_stack = &PEAR_ErrorStack::singleton('LiveUser');

        if ($GLOBALS['_LIVEUSER_DEBUG']) {
            if (!is_object($this->_log)) {
                $this->loadPEARLog();
            }
            $winlog = &Log::factory('win', 'LiveUser');
            $this->_log->addChild($winlog);
        }

        $this->_stack->setErrorMessageTemplate($this->_errorMessages);

        $this->dispatcher =& Event_Dispatcher::getInstance();
    }

    /**
     * Returns an instance of the login manager class.
     *
     * This array contains private options defined by
     * the following associative keys:
     *
     * <code>
     *
     * array(
     *  'autoInit' => false/true,
     *  'session'  => array(
     *      'name'    => 'liveuser session name',
     *      'varname' => 'liveuser session var name'
     *  ),
     * // The session_save_handler options are optional. If they are specified,
     * // session_set_save_handler() will be called with the parameters
     *  'session_save_handler' => array(
     *      'open'    => 'name of the open function/method',
     *      'close'   => 'name of the close function/method',
     *      'read'    => 'name of the read function/method',
     *      'write'   => 'name of the write function/method',
     *      'destroy' => 'name of the destroy function/method',
     *      'gc'      => 'name of the gc function/method',
     *  ),
     * // The session_cookie_params options are optional. If they are specified,
     * // session_set_cookie_params() will be called with the parameters
     *  'session_cookie_params' => array(
     *      'lifetime' => 'Cookie lifetime in days',
     *      'path'     => 'Cookie path',
     *      'domain'   => 'Cookie domain',
     *      'secure'   => 'Cookie send only over secure connections',
     *  ),
     * 'cache_perm' => if the permission data should be cached inside the session
     *  'login' => array(
     *      'force'    => 'Should the user be forced to login'
     *      'regenid'  => 'Should the session be regenerated on login'
     *  ),
     *  'logout' => array(
     *      'destroy'  => 'Whether to destroy the session on logout' false or true
     *  ),
     * // The cookie options are optional. If they are specified, the Remember Me
     * // feature is activated.
     *  'cookie' => array(
     *      'name'     => 'Name of Remember Me cookie',
     *      'lifetime' => 'Cookie lifetime in days',
     *      'path'     => 'Cookie path',
     *      'domain'   => 'Cookie domain',
     *      'secret'   => 'Secret key used for cookie value encryption',
     *      'savedir'  => '/absolute/path/to/writeable/directory' // No / at the end !
     *      'secure'   => 'Cookie send only over secure connections',
     *  ),
     *  'authContainers' => array(
     *      'name' => array(
     *            'type'            => 'DB',
     *            'loginTimeout'    => 0,
     *            'expireTime'      => 3600,
     *            'idleTime'        => 1800,
     *            'updateLastLogin' => true,
     *            'allowDuplicateHandles' => false,
     *            'allowEmptyPasswords'   => false,
     *            'storage' => array(
     *                'connection'      => 'db connection object, use this or dsn',
     *                'dsn'             => 'database dsn, use this or connection',
     *           ),
     *           'externalValues' => array(
     *                  'values'      => &$_SERVER,
     *                  'keysToCheck' => array('HTTP_USER_AGENT')
     *           ),
     *      ),
     *  ),
     *  'permContainer' => array(
     *      'type'     => 'Complex',
     *      'storage'  => array(
     *          'MDB2' => array(
     *              'dsn'       => $dsn,
     *              'prefix'    => 'liveuser_'
     *              'tables'    => array(),
     *              'fields'    => array(),
     *              'alias'     => array(),
     *              'force_seq' => true,
     *          ),
     *      ),
     *  ),
     *
     * </code>
     *
     * Other options in the configuration file relative to
     * the Auth and Perm containers depend on what the
     * containers expect. Refer to the Containers documentation.
     * The examples for containers provided are just general
     * do not reflect all the options for all containers.
     *
     * @param  mixed   $conf      The config file or the config array to configure.
     * @param  string  $handle    Handle of the user trying to authenticate
     * @param  string  $passwd    Password of the user trying to authenticate
     * @param  boolean $logout    set to true if user wants to logout
     * @param  boolean $remember  set if you want to use the rememberMe feature
     * @param  mixed   $confName  Name of array containing the configuration.
     * @return LiveUser|false     Returns an object of either LiveUser or false on error
     *                            if so use LiveUser::getErrors() to get the errors
     *
     * @access public
     * @see    LiveUser::getErrors
     */
    function &factory($conf, $handle = '', $passwd = '',$logout = false,
        $remember = false, $confName = 'liveuserConfig')
    {
        $obj = &new LiveUser();

        if (!empty($conf) || !is_array($conf)) {
            if ($obj->readConfig($conf, $confName)) {
                if (isset($obj->_options['autoInit']) && $obj->_options['autoInit']) {
                    $obj->init($handle, $passwd, $logout, $remember);
                }
            }
        }

        return $obj;
    }

    /**
     * This uses the singleton pattern, making sure you have one and
     * only instance of the class.
     *
     * <b>In PHP4 you MUST call this method with the
     *  $var = &LiveUser::singleton() syntax.
     * Without the ampersand (&) in front of the method name, you will not get
     * a reference, you will get a copy.</b>
     *
     * @param  array|file $conf  The config file or the config array to configure.
     * @param  string  $handle    Handle of the user trying to authenticate
     * @param  string  $passwd    Password of the user trying to authenticate
     * @param  boolean $logout    set to true if user wants to logout
     * @param  boolean $remember  set if remember me is set
     * @param  string  $confName  Name of array containing the configuration.
     * @return LiveUser|false     Returns an object of either LiveUser or false on failure
     *
     * @access public
     * @see    LiveUser::factory
     * @see    LiveUser::getErrors
     */
    function &singleton($conf = array(), $handle = '', $passwd = '', $logout = false,
        $remember = false, $confName = 'liveuserConfig')
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }

        if (empty($conf)) {
            if (empty($instances)) {
                return false;
            }
            $signature = key($instances);
        } else {
            $signature = serialize(array($handle, $passwd, $confName));
            if (!isset($instances[$signature])) {
                $obj = &LiveUser::factory(
                    $conf, $handle, $passwd, $logout, $remember, $confName
                );
                $instances[$signature] =& $obj;
            }
        }

        return $instances[$signature];
    }

    /**
     * Wrapper method to get the Error Stack
     *
     * @return array  an array of the errors or
     *                false if there are no errors
     *
     * @access public
     */
    function getErrors()
    {
        if (is_object($this->_stack)) {
            return $this->_stack->getErrors();
        }
        return false;
    }

    /**
     * Loads a PEAR class
     *
     * @param  string   classname
     * @return boolean  true success or false on failure
     *
     * @access public
     */
    function loadClass($classname)
    {
        if (!class_exists($classname)) {
            $filename = str_replace('_', '/', $classname).'.php';
            if (!LiveUser::fileExists($filename)) {
                return false;
            }
            include_once($filename);
            if (!class_exists($classname)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates an instance of an auth object.
     *
     * @param  array|file   Name of array containing the configuration.
     * @param  string       Name of the container we'll be using.
     * @param  string       Prefix of the class that will be used.
     * @return object|false Returns an instance of an auth container
     *                      class or false on error
     *
     * @access public
     */
    function &authFactory(&$conf, $containerName, $classprefix = 'LiveUser_')
    {
        $auth = false;
        $classname = $classprefix.'Auth_' . $conf['type'];
        if (LiveUser::loadClass($classname)) {
            $auth = &new $classname();
            if ($auth->init($conf, $containerName) === false) {
                $auth = false;
            }
        }
        return $auth;
    }

    /**
     * Creates an instance of an perm object.
     *
     * @param  array|file    Name of array containing the configuration.
     * @param  string        Prefix of the class that will be used.
     * @return object|false  Returns an instance of a perm container
     *                       class or false on error
     *
     * @access public
     */
    function &permFactory(&$conf, $classprefix = 'LiveUser_')
    {
        $perm = false;
        $classname = $classprefix.'Perm_' . $conf['type'];
        if (LiveUser::loadClass($classname)) {
            $perm = &new $classname();
            if ($perm->init($conf) === false) {
                $perm = false;
            }
        }
        return $perm;
    }

    /**
     * Returns an instance of a storage Container.
     *
     * @param  array         configuration array to pass to the storage container
     * @param  string        Prefix of the class that will be used.
     * @return object|false  will return an instance of a Storage container
     *                       or false upon error
     *
     * @access protected
     */
    function &storageFactory(&$confArray, $classprefix = 'LiveUser_Perm_')
    {
        end($confArray);
        $key = key($confArray);
        $count = count($confArray);
        $storageName = $classprefix.'Storage_' . $key;
        if (!LiveUser::loadClass($storageName) && $count <= 1) {
            $storage = false;
            return $storage;
        // if the storage container does not exist try the next one in the stack
        } elseif ($count > 1) {
            array_pop($confArray);
            $storage =& LiveUser::storageFactory($confArray, $classprefix);
            return $storage;
        }
        $storageConf =& $confArray[$key];
        unset($confArray[$key]);
        $storage = &new $storageName();
        if ($storage->init($storageConf, $confArray) === false) {
            $storage = false;
        }
        return $storage;
    }

    /**
     * Clobbers two arrays together.
     *
     * Function taken from the user notes of array_merge_recursive
     * used in LiveUser::readConfig()
     * may be called statically
     *
     * @param  array        array that should be clobbered
     * @param  array        array that should be clobbered
     * @return array|false  array on success and false on error
     *
     * @access public
     * @author kc@hireability.com
     */
    function arrayMergeClobber($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2)) {
            return false;
        }
        foreach ($a2 as $key => $val) {
            if (is_array($val) &&
                isset($a1[$key]) &&
                is_array($a1[$key]))
            {
                $a1[$key] = LiveUser::arrayMergeClobber($a1[$key], $val);
            } else {
                $a1[$key] = $val;
            }
        }
        return $a1;
    }

    /**
     * Checks if a file exists in the include path.
     *
     * @param  string   filename
     * @return boolean  true success and false on error
     *
     * @access public
     */
    function fileExists($file)
    {
        $dirs = split(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($dirs as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reads the configuration.
     *
     * @param  array|file  Conf array or file path to configuration
     * @param  string      Name of array containing the configuration
     * @return boolean     true on success or false on failure
     *
     * @access public
     */
    function readConfig($conf, $confName)
    {
        if (is_array($conf)) {
            if (isset($conf['authContainers'])) {
                $this->authContainers = $conf['authContainers'];
                unset($conf['authContainers']);
            }
            if (isset($conf['permContainer'])) {
                $this->permContainer = $conf['permContainer'];
                unset($conf['permContainer']);
            }

            $this->_options = $this->arrayMergeClobber($this->_options, $conf);
            if (isset($this->_options['cookie'])) {
                $cookie_default = array(
                    'name'     => 'ludata',
                    'lifetime' => '365',
                    'path'     => '/',
                    'domain'   => '',
                    'secret'   => 'secret',
                );
                $this->_options['cookie'] =
                    $this->arrayMergeClobber(
                        $cookie_default, $this->_options['cookie']
                    );
            }

            return true;
        }

        if (!LiveUser::fileExists($conf)) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception', array(),
                "Configuration file does not exist in LiveUser::readConfig(): $conf");
            return false;
        }
        if (!include_once($conf)) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception', array(),
                "Could not read the configuration file in LiveUser::readConfig(): $conf");
            return false;
        }
        if (isset(${$confName}) && is_array(${$confName})) {
            return $this->readConfig(${$confName}, $confName);
        }
        $this->_stack->push(
            LIVEUSER_ERROR_CONFIG, 'exception',
            array(), 'Configuration array not found in LiveUser::readConfig()'
        );
        return false;
    }

    /**
     * This method lazy loads PEAR::Log.
     *
     * @return void
     *
     * @access protected
     */
    function loadPEARLog()
    {
        require_once 'Log.php';
        $this->_log = &Log::factory('composite');
        $this->_stack->setLogger($this->_log);
    }

    /**
     * Adds an error logger to Errorstack.
     *
     * Be aware that if you need add a log
     * at the beginning of your code if you
     * want it to be effective. A log will only
     * be taken into account after it's added.
     *
     * Sample usage:
     * <code>
     * $lu_object = &LiveUser::singleton($conf);
     * $logger = &Log::factory('mail', 'bug@example.com',
     *      'myapp_debug_mail_log', array('from' => 'application_bug@example.com'));
     * $lu_object->addErrorLog($logger);
     * </code>
     *
     * @param  Log      logger instance
     * @return boolean  true on success or false on failure
     *
     * @access public
     */
    function addErrorLog(&$log)
    {
        if (!is_object($this->_log)) {
            $this->loadPEARLog();
        }
        return $this->_log->addChild($log);
    }

    /**
     * Creates an instance of the PEAR::Crypt_Rc4 class.
     *
     * @param  string  token to use to encrypt data
     * @return object  returns an instance of the Crypt_RC4 class
     *
     * @access public
     */
    function &cryptRC4Factory($secret)
    {
        $rc4 = false;
        if (LiveUser::loadClass('Crypt_Rc4')) {
            $rc4 =& new Crypt_Rc4($secret);
        }
        return $rc4;
    }

    /**
     * Crypts data using mcrypt or userland if not available.
     *
     * @param  boolean  true to crypt, false to decrypt
     * @param  string   data to crypt
     * @param  boolean  true if it should be crypted,
     *                  false if it should be decrypted
     * @return string   crypted data
     *
     * @access private
     */
    function cryptRC4($data, $secret, $crypt = true)
    {
        if (function_exists('mcrypt_module_open')) {
            $td = mcrypt_module_open('tripledes', '', 'ecb', '');
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $secret, $iv);
            if ($crypt) {
                $data = mcrypt_generic($td, $data);
            } else {
                $data = mdecrypt_generic($td, $data);
            }
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        } else {
            $rc4 =& LiveUser::cryptRC4Factory($secret);
            if (!$rc4) {
                $this->_stack->push(
                    LIVEUSER_ERROR_CONFIG, 'exception', array(),
                    'RememberMe feature requires either the mcrypt extension or PEAR::Crypt_RC4'
                );
                return false;
            }
            if ($crypt) {
                $rc4->crypt($data);
            } else {
                $rc4->decrypt($data);
            }
        }

        return $data;
    }

    /**
     * Sets an option.
     *
     * @param  string   option name
     * @param  mixed    value for the option
     * @return boolean  true on success or false on failure
     *
     * @access public
     * @see    LiveUser::_options
     */
    function setOption($option, $value)
    {
        if (isset($this->_options[$option])) {
            $this->_options[$option] = $value;
            return true;
        }
        $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception',
            array(), "unknown option $option");
        return false;
    }

    /**
     * Returns the value of an option
     *
     * @param  string  option name
     * @return mixed   the option value or false on failure
     *
     * @access public
     */
    function getOption($option)
    {
        if (isset($this->_options[$option])) {
            return $this->_options[$option];
        }
        $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception',
            array(), "unknown option $option");
        return false;
    }

    /**
     * Sets the session handler and name and starts the session
     *
     * @return void
     *
     * @access private
     */
    function _startSession()
    {
        // set session save handler if needed
        if ($this->_options['session_save_handler']) {
            session_set_save_handler(
                $this->_options['session_save_handler']['open'],
                $this->_options['session_save_handler']['close'],
                $this->_options['session_save_handler']['read'],
                $this->_options['session_save_handler']['write'],
                $this->_options['session_save_handler']['destroy'],
                $this->_options['session_save_handler']['gc']
            );
        }
        if ($this->_options['session_cookie_params']) {
            session_set_cookie_params((
                (LIVEUSER_DAY_SECONDS * $this->_options['session_cookie_params']['lifetime'])),
                $this->_options['session_cookie_params']['path'],
                $this->_options['session_cookie_params']['domain'],
                $this->_options['session_cookie_params']['secure']);
        }
        // Set the name of the current session
        session_name($this->_options['session']['name']);
        // If there's no session yet, start it now
        @session_start();
    }

    /**
     * Tries to retrieve auth object from session.
     * If this fails, the method attempts a login based on the
     * parameters or cookie data
     *
     * @param  string   handle of the user trying to authenticate
     * @param  string   password of the user trying to authenticate
     * @param  boolean  set to true if user wants to logout
     * @param  boolean  set if remember me is set
     * @return boolean  true if init process well, false if something
     *                  went wrong.
     *
     * @access public
     */
    function init($handle = '', $passwd = '', $logout = false, $remember = false)
    {
        if ($this->_options['session']['force_start']) {
            $this->_startSession();
        }

        // Try to fetch auth object from session
        $isReturningUser = $this->unfreeze();

        // current timestamp
        $now = time();

        if ($logout) {
            $this->logout(true);
        } elseif ($this->isLoggedIn()) {
            // Check if user authenticated with new credentials
            if ($handle && $this->_auth->handle != $handle) {
                $this->logout(false);
            } elseif ($isReturningUser) {
                // Check if authentication session is expired.
                if ($this->_auth->expireTime > 0 &&
                    ($this->_auth->currentLogin + $this->_auth->expireTime) < $now
                ) {
                    $this->status = LIVEUSER_STATUS_EXPIRED;
                    $this->dispatcher->post($this,'onExpired');
                    $this->logout(false);
                // Check if maximum idle time is reached.
                } elseif ($this->_auth->idleTime > 0 &&
                    isset($_SESSION[$this->_options['session']['varname']]['idle']) &&
                    ($_SESSION[$this->_options['session']['varname']]['idle'] + $this->_auth->idleTime) < $now
                ) {
                    $this->status = LIVEUSER_STATUS_IDLED;
                    $this->dispatcher->post($this,'onIdled');
                    $this->logout(false);
                }
            }
        }

        if (!$this->isLoggedIn()) {
            if (!$this->login($handle, $passwd, $remember) &&
                $this->getErrors()
            ) {
                return false;
            }
        }

        // set idle time and status
        if ($this->isLoggedIn()) {
            $_SESSION[$this->_options['session']['varname']]['idle'] = $now;
            $this->status = LIVEUSER_STATUS_OK;
        // Force user login.
        } elseif ($this->_options['login']['force']) {
            $this->dispatcher->post($this, 'forceLogin');
        }

        return true;
    }

    /**
     * Tries to log the user in by trying all the Auth containers defined
     * in the configuration file until there is a success or failure.
     *
     * @param  string   handle of the user trying to authenticate
     * @param  string   password of the user trying to authenticate
     * @param  boolean  set rememberMe cookie
     * @return boolean  true on success or false on failure
     *
     * @access private
     */
    function login($handle = '', $passwd = '', $remember = false)
    {
        if (empty($handle)) {
            $result = $this->readRememberCookie();
            if (!is_array($result)) {
                if ($this->status == LIVEUSER_STATUS_UNKNOWN) {
                    $this->status = LIVEUSER_STATUS_AUTHFAILED;
                }
                return false;
            }
            $handle = $result['handle'];
            $passwd = $result['passwd'];
        }

        $this->status = LIVEUSER_STATUS_AUTHFAILED;
        $this->_auth = $this->_perm = null;

        //loop into auth containers
        $indexes = array_keys($this->authContainers);
        foreach ($indexes as $index) {
            if (!$passwd && (!isset($this->authContainers[$index]['allowEmptyPasswords'])
                || !$this->authContainers[$index]['allowEmptyPasswords'])
            ) {
                continue;
            }
            $auth = &$this->authFactory($this->authContainers[$index], $index);
            if ($auth === false) {
                $this->status = LIVEUSER_STATUS_AUTHINITERROR;
                return false;
            }
            $auth->login($handle, $passwd);
            if ($auth->loggedIn) {
                // Create permission object
                if (is_array($this->permContainer)) {
                    $perm =& $this->permFactory($this->permContainer);
                    if ($perm === false) {
                        $this->status = LIVEUSER_STATUS_PERMINITERROR;
                        return false;
                    }
                    $this->_perm =& $perm;
                    if (!$this->_perm->mapUser($auth->authUserId, $index)) {
                        $this->dispatcher->post($this, 'onFailedMapping');
                    }
                }
                $this->_auth = $auth;
                $this->_auth->backendArrayIndex = $index;
                $this->freeze();
                $this->setRememberCookie($handle, $passwd, $remember);
                $this->status = LIVEUSER_STATUS_OK;
                break;
            } elseif ($auth->isActive === false) {
                $this->status = LIVEUSER_STATUS_ISINACTIVE;
                break;
            }
        }

        if (!$this->isLoggedIn()) {
            $this->dispatcher->post($this, 'onFailedLogin');
            return false;
        }

        if (!$this->_options['session']['force_start']) {
            $this->_startSession();
        }

        // user has just logged in
        $this->dispatcher->post($this, 'onLogin');
        if ($this->_options['login']['regenid']) {
            session_regenerate_id();
        }

        return true;
    }

    /**
     * Gets auth and perm container objects back from session and tries
     * to give them an active database/whatever connection again.
     *
     * @return boolean true on success or false on failure
     *
     * @access private
     */
    function unfreeze()
    {
        if (!$this->_options['session']['force_start']) {
            if (!isset($_REQUEST[$this->_options['session']['name']])) {
                return false;
            }
            $this->_startSession();
        }

        if (isset($_SESSION[$this->_options['session']['varname']]['auth'])
            && is_array($_SESSION[$this->_options['session']['varname']]['auth'])
            && isset($_SESSION[$this->_options['session']['varname']]['auth_name'])
            && strlen($_SESSION[$this->_options['session']['varname']]['auth_name']) > 0)
        {
            $containerName = $_SESSION[$this->_options['session']['varname']]['auth_name'];
            $auth = &$this->authFactory($this->authContainers[$containerName], $containerName);
            if ($auth === false) {
                return false;
            }
            if ($auth->unfreeze($_SESSION[$this->_options['session']['varname']]['auth'])) {
                $auth->backendArrayIndex = $_SESSION[$this->_options['session']['varname']]['auth_name'];
                $this->_auth = &$auth;
                if (isset($_SESSION[$this->_options['session']['varname']]['perm'])
                    && $_SESSION[$this->_options['session']['varname']]['perm']
                ) {
                    $perm = &$this->permFactory($this->permContainer);
                    if ($perm === false) {
                        return $perm;
                    }
                    $this->_perm = &$perm;
                    if ($this->_options['cache_perm']) {
                        $this->_perm->unfreeze($this->_options['session']['varname']);
                    } else {
                        $this->_perm->mapUser($auth->authUserId, $auth->backendArrayIndex);
                    }
                }
                $this->status = LIVEUSER_STATUS_UNFROZEN;
                $this->dispatcher->post($this, 'onUnfreeze');
                return true;
            }
        }

        return false;
    }

    /**
     * Stores all properties in the session.
     *
     * @return  boolean true on sucess or false on failure
     *
     * @access  public
     */
    function freeze()
    {
        if (is_a($this->_auth, 'LiveUser_Auth_Common') && $this->isLoggedIn()) {
            // Bind objects to session
            $_SESSION[$this->_options['session']['varname']] = array();
            $_SESSION[$this->_options['session']['varname']]['auth'] = $this->_auth->freeze();
            $_SESSION[$this->_options['session']['varname']]['auth_name'] = $this->_auth->backendArrayIndex;
            if (is_a($this->_perm, 'LiveUser_Perm_Simple')) {
                $_SESSION[$this->_options['session']['varname']]['perm'] = true;
                if ($this->_options['cache_perm']) {
                     $this->_perm->freeze($this->_options['session']['varname']);
                }
            }
            return true;
        }
        $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception',
            array(), 'No data available to store inside session');
        return false;
    }

    /**
     * Properly disconnect resources in the active container
     *
     * @return  boolean true on success or false on failure
     *
     * @access  public
     */
    function disconnect()
    {
        if (is_a($this->_auth, 'LiveUser_Auth_Common')) {
            $result = $this->_auth->disconnect();
            if ($result === false) {
                return false;
            }
            $this->_auth = null;
        }
        if (is_a($this->_perm, 'LiveUser_Perm_Simple')) {
            $result = $this->_perm->disconnect();
            if ($result === false) {
                return false;
            }
            $this->_perm = null;
        }
        return true;
    }

    /**
     * If cookies are allowed, this method checks if the user wanted
     * a cookie to be set so he doesn't have to enter handle and password
     * for his next login. If true, it will set the cookie.
     *
     * @param  string   handle of the user trying to authenticate
     * @param  string   password of the user trying to authenticate
     * @param  boolean  set if remember me is set
     * @return boolean  true if the cookie can be set, false otherwise
     *
     * @access private
     */
    function setRememberCookie($handle, $passwd, $remember)
    {
        if (!$remember) {
            return true;
        }

        if (!isset($this->_options['cookie'])) {
            return false;
        }

        $store_id = md5($handle . $passwd);

        $dir = $this->_options['cookie']['savedir'];
        $file = $dir . '/' . $store_id . '.lu';

        if (!is_writable($dir)) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception', array(),
                'Cannot create file, please check path and permissions');
            return false;
        }

        $fh = @fopen($file, 'wb');
        if (!$fh) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception', array(),
                'Cannot open file for writting');
            return false;
        }

        $passwd_id = md5($passwd);
        $crypted_data = $this->cryptRC4(
            serialize(array($passwd_id, $passwd)),
            $this->_options['cookie']['secret'],
            true
        );

        $write = fwrite($fh, $crypted_data);
        fclose($fh);
        if (!$write) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception', array(),
                'Cannot save cookie data');
            return false;
        }

        $setcookie = setcookie(
            $this->_options['cookie']['name'],
            $store_id . $passwd_id . $handle,
            (time() + (LIVEUSER_DAY_SECONDS * $this->_options['cookie']['lifetime'])),
            $this->_options['cookie']['path'],
            $this->_options['cookie']['domain'],
            $this->_options['cookie']['secure']
        );

        if (!$setcookie) {
            @unlink($file);
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception', array(),
                'Unable to set cookie');
            return false;
        }

        return true;
    }

    /**
     * Handles the rememberMe cookie login.
     *
     * @return boolean true on success or false on failure
     *
     * @access private
     */
    function readRememberCookie()
    {
        if (!isset($this->_options['cookie'])) {
            return false;
        }

        if (!isset($_COOKIE[$this->_options['cookie']['name']])) {
            return false;
        }

        $cookieData = $_COOKIE[$this->_options['cookie']['name']];
        if (strlen($cookieData) < 65
            // kill all old style remember me cookies
            || (strpos($cookieData, ':') && strpos($cookieData, ':') < 64)
        ) {
            // Delete cookie if it's not valid, keeping it messes up the
            // authentication process
            $this->deleteRememberCookie();
            $this->_stack->push(LIVEUSER_ERROR_COOKIE, 'error',
                'Wrong data in cookie store in LiveUser::readRememberMeCookie()');
            return false;
        }

        $store_id = substr($cookieData, 0, 32);
        $passwd_id = substr($cookieData, 32, 32);
        $handle = substr($cookieData, 64);

        $dir = $this->_options['cookie']['savedir'];

        $fh = @fopen($dir . '/' . $store_id . '.lu', 'rb');
        if (!$fh) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception',
                array(), 'Cannot open file for reading');
            return false;
        }

        $fields = fread($fh, 4096);
        fclose($fh);
        if (!$fields) {
            $this->_stack->push(LIVEUSER_ERROR_CONFIG, 'exception',
                array(), 'Cannot read file');
            return false;
        }

        $serverData = @unserialize(
            $this->cryptRC4($fields, $this->_options['cookie']['secret'], false)
        );

        if (!is_array($serverData) || count($serverData) != 2) {
            $this->_stack->push(LIVEUSER_ERROR_COOKIE, 'exception',
                array(), 'Incorrect array structure');
            return false;
        }

        if ($serverData[0] != $passwd_id) {
            // Delete cookie if it's not valid, keeping it messes up the
            // authentication process
            $this->deleteRememberCookie();
            $this->_stack->push(LIVEUSER_ERROR_COOKIE, 'error',
                'Passwords hashes do not match in cookie in LiveUser::readRememberMeCookie()');
            return false;
        }

        return array('handle' => $handle, 'passwd' => $serverData[1]);
    }

    /**
     * Deletes the rememberMe cookie login.
     *
     * @return boolean true on success or false on failure
     *
     * @access private
     */
    function deleteRememberCookie()
    {
        if (!isset($this->_options['cookie'])) {
            return false;
        }

        if (!isset($_COOKIE[$this->_options['cookie']['name']])) {
            return false;
        }

        $cookieData = $_COOKIE[$this->_options['cookie']['name']];
        if (strlen($cookieData) < 65) {
            $this->_stack->push(LIVEUSER_ERROR_COOKIE, 'error',
                'Wrong data in cookie store in LiveUser::deleteRememberCookie()');
            return false;
        }

        $store_id = substr($cookieData, 0, 32);
        @unlink($this->_options['cookie']['savedir'] . '/'.$store_id.'.lu');

        unset($_COOKIE[$this->_options['cookie']['name']]);
        setcookie($this->_options['cookie']['name'],
            false,
            LIVEUSER_COOKIE_DELETE_TIME,
            $this->_options['cookie']['path'],
            $this->_options['cookie']['domain'],
            $this->_options['cookie']['secure']
        );

        return true;
    }

    /**
     * This destroys the session object.
     *
     * @param  boolean $direct  set to true if the logout was initiated directly
     * @return void
     *
     * @access private
     */
    function logout($direct = true)
    {
        $this->status = LIVEUSER_STATUS_LOGGEDOUT;

        if ($direct) {
            // trigger event 'onLogout' as replacement for logout callback function
            $this->dispatcher->post($this,'onLogout');
            // If there's a cookie and the session hasn't idled or expired, kill that one too...
            $this->deleteRememberCookie();
        }

        // If the session should be destroyed, do so now...
        if ($this->_options['logout']['destroy']) {
            session_unset();
            session_destroy();
            if ($this->_options['session']['force_start']) {
                $this->_startSession();
            }
        } elseif(isset($_SESSION[$this->_options['session']['varname']])) {
            unset($_SESSION[$this->_options['session']['varname']]);
        }

        // disable this call for now, since logout() does not necessarily mean
        // we dont intend to ever call the container again and only the
        // MDB/MDB2 containers are able to reconnect on demand
        #$this->disconnect();

        if ($direct) {
            // trigger event 'postLogout', can be used to do a redirect
            $this->dispatcher->post($this,'postLogout');
        }

        return true;
    }

    /**
     * Wrapper method for the permission object's own checkRight method.
     *
     * @param  array|int   A right id or an array of rights.
     * @return int|false  level if the user has the right/rights false if not
     *
     * @access public
     */
    function checkRight($rights)
    {
        if (is_null($rights)) {
            return LIVEUSER_MAX_LEVEL;
        }

        if (is_a($this->_perm, 'LiveUser_Perm_Simple')) {
            if (is_array($rights)) {
                // assume user has the right in order to have min() work
                $hasright = LIVEUSER_MAX_LEVEL;
                foreach ($rights as $currentright) {
                    $level = $this->_perm->checkRight($currentright);
                    if (!$level) {
                        return false;
                    }
                    $hasright = min($hasright, $level);
                }
                return $hasright;
            } else {
                return $this->_perm->checkRight($rights);
            }
        }

        return false;
    }

    /**
     * Wrapper method for the permission object's own checkRightLevel method.
     *
     * @param  array|int  A right id or an array of rights.
     * @param  array|int  Id or array of Ids of the owner of the
                          ressource for which the right is requested.
     * @param  array|int  Id or array of Ids of the group of the
     *                    ressource for which the right is requested.
     * @return boolean    true on success or false on failure
     *
     * @access public
     */
    function checkRightLevel($rights, $owner_user_id, $owner_group_id)
    {
        $level = $this->checkRight($rights);
        if (is_a($this->_perm, 'LiveUser_Perm_Complex')) {
            $level = $this->_perm->checkLevel($level, $owner_user_id, $owner_group_id);
        }

        return (bool)$level;
    }

    /**
     * Wrapper method for the permission object's own checkGroup method.
     *
     * @param  array|int  A group id or an array of groups.
     * @return boolean    true on success or false on failure
     *
     * @access public
     */
    function checkGroup($groups)
    {
        if (is_null($groups)) {
            return true;
        }

        if (is_a($this->_perm, 'LiveUser_Perm_Medium')) {
            if (is_array($groups)) {
                foreach ($groups as $group) {
                    if (!$this->_perm->checkGroup($group)) {
                        return false;
                    }
                }
                return true;
            } else {
                return $this->_perm->checkGroup($groups);
            }
        }

        return false;
    }

    /**
     * Checks if a user is logged in.
     *
     * @return boolean true if user is logged in, false if not
     *
     * @access public
     */
    function isLoggedIn()
    {
        if (!is_a($this->_auth, 'LiveUser_Auth_Common')) {
            return false;
        }

        return $this->_auth->loggedIn;
    }

    /**
     * Function that determines if the user exists but hasn't yet been declared
     * "active" by an administrator.
     *
     * Use this to check if this was the reason
     * why a user was not able to login.
     * true ==  user account is NOT active
     * false == user account is active
     *
     * @return boolean true if the user account is *not* active
     *                 false if the user account *is* active
     *
     * @access public
     */
    function isInactive()
    {
        return $this->status == LIVEUSER_STATUS_ISINACTIVE;
    }

    /**
     * Wrapper method to access properties from the auth and
     * permission containers.
     *
     * @param  string  Name of the property to be returned.
     * @param  string  'auth' or 'perm'
     * @return mixed   a value or an array.
     *
     * @access public
     */
    function getProperty($what, $container = 'auth')
    {
        $that = null;
        if ($container == 'auth' && is_a($this->_auth, 'LiveUser_Auth_Common') &&
            !is_null($this->_auth->getProperty($what))
        ) {
            $that = $this->_auth->getProperty($what);
        } elseif (is_a($this->_perm, 'LiveUser_Perm_Simple') &&
            !is_null($this->_perm->getProperty($what))
        ) {
            $that = $this->_perm->getProperty($what);
        }
        return $that;
    }

    /**
     * updates the properties of the containers from the original source
     *
     * @param  boolean $auth if the auth container should be updated
     * @param  boolean $perm if the perm container should be updated
     * @return boolean
     *
     * @access public
     */
    function updateProperty($auth, $perm = null)
    {
        if (!is_a($this->_auth, 'LiveUser_Auth_Common')) {
            $this->_stack->push(LIVEUSER_ERROR, 'error',
                'Cannot update container if no auth container instance is available');
            return false;
        }
        if ($auth && !$this->_auth->readUserData('', '', true)) {
            return false;
        }
        if (is_null($perm)) {
            $perm = is_a($this->_perm, 'LiveUser_Perm_Simple');
        }
        if ($perm) {
            if (!is_a($this->_perm, 'LiveUser_Perm_Simple')) {
                $this->_stack->push(LIVEUSER_ERROR, 'error',
                    'Cannot update container if no perm container instance is available');
                return false;
            }
            if (!$this->_perm->mapUser($this->_auth->authUserId, $this->_auth->backendArrayIndex)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the current status.
     *
     * @return integer
     *
     * @access public
     * @see    LIVEUSER_STATUS_* constants
     */
    function getStatus()
    {
        return $this->status;
    }

    /**
     * Make a string representation of the object.
     *
     * @return  string  returns a string representation of the class
     *
     * @access  public
     */
    function __toString()
    {
        return get_class($this) . ' logged in: ' . ($this->isLoggedIn() ? 'Yes' : 'No');
    }

    /**
     * Return a textual status message for a LiveUser status code.
     *
     * @param   int     status code
     * @return  string  error message
     *
     * @access  public
     */
    function statusMessage($value)
    {
        // make the variable static so that it only has to do the defining on the first call
        static $statusMessages;

        // define the varies error messages
        if (!isset($statusMessages)) {
            $statusMessages = array(
                LIVEUSER_STATUS_OK              => 'Authentication OK',
                LIVEUSER_STATUS_IDLED           => 'Maximum idle time is reached',
                LIVEUSER_STATUS_EXPIRED         => 'User session has expired',
                LIVEUSER_STATUS_ISINACTIVE      => 'User is set to inactive',
                LIVEUSER_STATUS_PERMINITERROR   => 'Cannot instantiate permission container',
                LIVEUSER_STATUS_AUTHINITERROR   => 'Cannot instantiate authentication configuration',
                LIVEUSER_STATUS_AUTHNOTFOUND    => 'Cannot retrieve Auth object from session',
                LIVEUSER_STATUS_UNKNOWN         => 'An undefined error occurred or init() was not called',
                LIVEUSER_STATUS_LOGGEDOUT       => 'User was logged out correctly',
                LIVEUSER_STATUS_AUTHFAILED      => 'Cannot authenticate, handle/password is probably wrong',
                LIVEUSER_STATUS_UNFROZEN        => 'Object fetched from the session, the user was already logged in'
            );
        }

        // return the textual error message corresponding to the code
        return isset($statusMessages[$value])
            ? $statusMessages[$value] : $statusMessages[LIVEUSER_STATUS_UNKNOWN];
    }
}
?>
