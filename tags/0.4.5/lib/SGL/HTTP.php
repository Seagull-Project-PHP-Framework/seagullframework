<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | HTTP.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: HTTP.php,v 1.36 2005/06/06 22:05:35 demian Exp $

require_once SGL_CORE_DIR . '/Util.php';
require_once SGL_CORE_DIR . '/Manager.php';
require_once SGL_CORE_DIR . '/Url.php';
require_once SGL_LIB_DIR  . '/SGL.php';

/**
 * Class for HTTP functionality including redirects.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 * @since   PHP 4.1
 */
class SGL_HTTP
{
    /**
     * Wrapper for PHP header() redirects.
     *
     * Simplified version of Wolfram's HTTP_Header class
     *
     * @access  public
     * @static
     * @param   mixed   $url    target URL
     * @return  void
     * @author  Wolfram Kriesing <wk@visionp.de>
     */
    function redirect($url = null)
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  get a reference to the request object
        $req = & SGL_HTTP_Request::singleton();

        //  if arg is not an array of params, pass straight to header function
        if (is_array($url)) {
            $moduleName  =  (array_key_exists('moduleName', $url)) ? $url['moduleName'] : $req->get('moduleName');
            $managerName =  (array_key_exists('managerName', $url)) ? $url['managerName'] : $req->get('managerName');

            //  parse out rest of querystring
            $aParams = array();
            foreach ($url as $k => $v) {
                if ($k == 'moduleName' || $k == 'managerName') {
                    continue;
                }
                if (is_string($k)) {
                    $aParams[] = urlencode($k).'/'.urlencode($v);
                }
            }
            $qs = (count($aParams)) ? implode('/', $aParams): '';
            $url = $conf['site']['frontScriptName'] . '/' . $moduleName;
            if (!empty($managerName)) {
                $url .=  '/' . $managerName;
            }
            $url .= '/' . $qs;

            //  check for absolute uri as specified in RFC 2616
            SGL_Url::toAbsolute($url);

            //  add a trailing slash if one is not present
            if (substr($url, -1) != '/') {
                $url .= '/';
            }

            //  determine is session propagated in cookies or URL
            SGL_Url::addSessionInfo($url);
        }

        //  must be absolute URL, ie, string
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Request wraps all $_GET $_POST $_FILES arrays into a Request object.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 * @since   PHP 4.1
 */
class SGL_HTTP_Request
{
    /**
     * Parses raw request into SGL format.
     *
     * @access  public
     * @return  void
     */
    function SGL_HTTP_Request()
    {
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  merge REQUEST AND FILES superglobal arrays
        $GLOBALS['_SGL']['REQUEST'] = array_merge($_REQUEST, $_FILES);
        
        //  remove slashes if necessary
        SGL_String::dispelMagicQuotes($GLOBALS['_SGL']['REQUEST']);

        //  get all URL parts after domain and TLD as an array
        $aUriParts = SGL_Url::getSignificantSegments($_SERVER['PHP_SELF']);
        
        //  parse URL segments into SGL request structure
        $aSglRequest = SGL_Url::makeSearchEngineFriendly($aUriParts);
        
        //  merge results with cleaned $_REQUEST values and $_POST
        $GLOBALS['_SGL']['REQUEST'] = array_merge($aSglRequest, $GLOBALS['_SGL']['REQUEST'], $_POST);
    }

    /**
     * Returns a singleton Request instance.
     *
     * example usage: 
     * $req = & SGL_HTTP_Request::singleton();
     * warning: in order to work correctly, the request
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @return  mixed           reference to Request object
     */
    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new SGL_HTTP_Request();
        }
        return $instance;
    }

    /**
     * Retrieves values from Request object.
     *
     * @access  public
     * @param   mixed   $paramName  Request param name
     * @param   boolean $allowTags  If html/php tags are allowed or not
     * @return  mixed               Request param value or null if not exists
     */
    function get($paramName, $allowTags = false)
    {
        $req = & $GLOBALS['_SGL']['REQUEST'];
        if (isset($req[$paramName])) {

            //  if html not allowed, run an enhanced strip_tags()
            if (!$allowTags) {
                SGL_String::clean($req[$paramName]);

            //  if html is allowed, at least remove javascript
            } else {
                SGL_String::removeJs($req[$paramName]);
            }
            return $req[$paramName];
        } else {
            return null;
        }
    }

    /**
     * Set a value for Request object.
     *
     * @access  public
     * @param   mixed   $name   Request param name
     * @param   mixed   $value  Request param value
     * @return  void
     */
    function set($name, $value)
    {
       $GLOBALS['_SGL']['REQUEST'][$name] = $value;
    }

    function debug()
    {
        $GLOBALS['_SGL']['site']['blocksEnabled'] = 0;
        print '<pre>';
        print_r($GLOBALS['_SGL']['REQUEST']);
    }
}

/**
 * Handles session management.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.36 $
 * @since   PHP 4.1
 */
class SGL_HTTP_Session
{
    /**
     * Session timeout configurable in preferences.
     *
     * @access  private
     * @var     int
     */
    var $_timeout;

    /**
     * Setup session.
     *
     *  o custimise session name
     *  o configure custom cookie params
     *  o setup session backed, ie, file or DB
     *  o start session
     *  o persist user object in session
     *
     * @access  public
     * @param   int $uid    user id if present
     * @return  void
     */
    function SGL_HTTP_Session($uid = -1)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];

        //  customise session
        $sessName = isset($conf['cookie']['name']) ? $conf['cookie']['name'] : 'SGLSESSID';
        session_name($sessName);

        //  set session timeout to 0 (until the browser is closed) initially,
        //  then use user timeout in isTimedOut() method
        session_set_cookie_params(
            0, 
            $conf['cookie']['path'],
            $conf['cookie']['domain'],
            $conf['cookie']['secure']);

        if ($conf['site']['sessionHandler'] == 'database') {
             $ok = session_set_save_handler(
                array(& $this, 'dbOpen'), 
                array(& $this, 'dbClose'), 
                array(& $this, 'dbRead'), 
                array(& $this, 'dbWrite'), 
                array(& $this, 'dbDestroy'), 
                array(& $this, 'dbGc')
                );
        } else {
            session_save_path(SGL_TMP_DIR);
        }
     
        //  start PHP session handler
//        if (!(defined('SID'))) {
//            $req = & SGL_HTTP_Request::singleton();
//            define('SID', $conf['cookie']['name'] . '=' . $req->get('SGLSESSID'));
//        }
        @session_start();       

        //  if user id is passed in constructor, ie, during login, init user
        if ($uid > 0) {
            include_once SGL_ENT_DIR . '/Usr.php';
            $sessUser = & new DataObjects_Usr();
            $sessUser->get($uid);
            $this->_init($sessUser);

        //  if session doesn't exist, initialise
        } elseif (!SGL_HTTP_Session::exists()){
            $this->_init();
        }
    }

    /**
     * Initialises session, sets some default values.
     *
     * @access  private
     * @param   object  $oUser  user object if present
     * @return  boolean true on successful initialisation
     */
    function _init($oUser = null)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        //  get DA_User object
        require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
        $da = & DA_User::singleton();

        //  set secure session key
        $startTime = mktime();
        $acceptLang = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        //  user object is passed only during login
        if (is_object($oUser)) {

            $aSessVars = array(
                'uid'               => $oUser->usr_id,
                'rid'               => $oUser->role_id,
                'oid'               => $oUser->organisation_id,
                'username'          => $oUser->username,
                'startTime'         => $startTime,
                'lastRefreshed'     => $startTime,
                'key'               => md5($oUser->username . $startTime . $acceptLang . $userAgent),
                'aPrefs'            => $da->getPrefsByUserId($oUser->usr_id),
                'aPerms'            => ($oUser->role_id == SGL_ADMIN) 
                    ? array() 
                    : $da->getPermsByUserId($oUser->usr_id),
            );
        //  otherwise it's a guest session, these values always get
        //  set and exist in the session before a login
        } else {
            //  initialise session with some default values
            $aSessVars = array(
                'uid'               => 0,
                'rid'               => 0,
                'oid'               => 0,
                'username'          => 'guest',
                'startTime'         => $startTime,
                'lastRefreshed'     => $startTime,
                'key'               => md5($startTime . $acceptLang . $userAgent),
                'currentResRange'   => 'all',
                'sortOrder'         => 'ASC',
                'aPrefs'            => $da->getPrefsByUserId(),
                'aPerms'            => $da->getPermsByRoleId(),
            );
        }
        //  set vars in session
        if (isset($_SESSION)) {
            foreach ($aSessVars as $k => $v) {
                $_SESSION[$k] = $v;
            }
        }       

        //  make session more secure if possible      
        if  (function_exists('session_regenerate_id')) {
            $conf = & $GLOBALS['_SGL']['CONF'];  
            $oldSessionId = session_id();
            session_regenerate_id();

            if ($conf['site']['sessionHandler'] == 'file') {
                
                //  manually remove old session file, see http://ilia.ws/archives/47-session_regenerate_id-Improvement.html
                @unlink(SGL_TMP_DIR . '/sess_'.$oldSessionId);
                
            } elseif ($conf['site']['sessionHandler'] == 'database') {
                $value = $this->dbRead($oldSessionId);
                $this->dbDestroy($oldSessionId);
                $this->dbRead(session_id());          // creates new session record
                $this->dbWrite(session_id(), $value); // store old session value in new session record
            } else {
                die('Internal Error: unknown session handler');
            }
        }
        return true;
    }

    /**
     * Determines whether a session currently exists.
     *
     * @access  public
     * @static
     * @return  boolean true if session exists and has 1 or more elements
     */
    function exists()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        return isset($_SESSION) && count($_SESSION);
    }

    /**
     * Determines whether the current session is valid.
     *
     * @access  public
     * @static
     * @return  boolean true if session is valid
     */
    function isValid()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $acceptLang = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $currentKey = md5($_SESSION['username'] . $_SESSION['startTime'] . 
            $acceptLang . $userAgent);

        //  compare actual key with session key, and that UID is not 0 (guest)
        return  ($currentKey == $_SESSION['key']) && $_SESSION['uid'];
    }

    /**
     * Determines whether the current session is timed out.
     *
     * @access  public
     * @return  boolean true if session is timed out
     */
    function isTimedOut()
     {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  check for session timeout
        $currentTime = mktime();
        $lastPageRefreshTime = $_SESSION['lastRefreshed'];
        $timeout = $_SESSION['aPrefs']['sessionTimeout'];
        if ($currentTime - $lastPageRefreshTime > $timeout) {
            return true;
        } else {
            $_SESSION['lastRefreshed'] = mktime();
            return false;
        }
    }

    /**
     * Returns true if specified permission exists in the session.
     *
     * @access  public
     * @param   int $permId the permission id
     * @return  boolean if perm exists or not
     */
    function hasPerms($permId)
    {
        //  if admin role, give perms by default
        if (@$_SESSION['rid'] == SGL_ADMIN) {
            $ret = true;
        } else {
            if (is_array($_SESSION['aPerms'])) {
                $ret = in_array($permId, $_SESSION['aPerms']);
            } else {
                $ret = false;
            }
        }
        return $ret;
    }

    /**
     * Determines current user type.
     *
     *      - guest (not logged in)
     *      - member
     *      - admin
     *
     * @access  public
     * @static
     * @return  int  $currentUserType
     */
    function getUserType()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $currentRoleId = @$_SESSION['rid'];

        if ($currentRoleId == SGL_GUEST) {
            $currentUserType = SGL_GUEST;
        } elseif ($currentRoleId == SGL_ADMIN) {
            $currentUserType = SGL_ADMIN;
        } else {
            $currentUserType = SGL_MEMBER;
        }
        return $currentUserType;
    }

    /**
     * Returns the current user's id.
     *
     * @access  public
     * @return  int the id
     */
    function getUid()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        static $instance;
        if (!isset($instance)) {
            $instance = $_SESSION['uid'];
        }
        return $instance;
    }

    /**
     * Returns the current user's role id.
     *
     * @access  public
     * @return  int the role id
     */
    function getRoleId()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        static $instance;
        if (!isset($instance)) {
            $instance = $_SESSION['rid'];
        }
        return $instance;
    }

    /**
     * Returns the current user's organisation id.
     *
     * @access  public
     * @return  int the organisation id
     */
    function getOrganisationId()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        static $instance;
        if (!isset($instance)) {
            $instance = $_SESSION['oid'];
        }
        return $instance;
    }

    /**
     * Removes specified var from session.
     *
     * @access  public
     * @static
     * @param   string  $sessVarName   name of session var
     * @return  boolean
     */
    function remove($sessVarName)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        if (isset($sessVarName)) {
            unset($_SESSION[$sessVarName]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets specified var from session.
     *
     * @access  public
     * @static
     * @param   string  $sessVarName    name of session var
     * @return  string                  value of session variable
     */
    function get($sessVarName)
    {
        if (isset($sessVarName)) {
            return is_array($_SESSION) 
                ? (array_key_exists($sessVarName, $_SESSION) ? $_SESSION[$sessVarName] : '')
                : ''; 
        }
    }

    /**
     * Sets specified var in session.
     *
     * @access  public
     * @static
     * @param   string  $sessVarName   name of session var
     * @param   mixed   $sessVarValue  value of session var
     * @return  void
     */
    function set($sessVarName, $sessVarValue)
    {
        $_SESSION[$sessVarName] = $sessVarValue;
    }

    /**
     * Dumps session contents.
     *
     * @access  public
     * @static
     * @return  void
     */
    function debug()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $ret = '';
        foreach ($_SESSION as $sessVarName => $sessVarValue) {
            $ret .= "$sessVarName => $sessVarValue<br />\n";
        }
        return $ret;
    }

    /**
     * Destroys current session.
     *
     * @access  public
     * @static
     * @return  void
     * @todo    why does session_destroy fail sometimes?
     */
    function destroy()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $conf = & $GLOBALS['_SGL']['CONF'];

        foreach ($_SESSION as $sessVarName => $sessVarValue) {
            if (isset($_SESSION)) {
                unset($sessVarName);
            }
        }
        @session_destroy();
        $_SESSION = array();

        //  clear session cookie so theme comes from DB and not session
        setcookie(  $conf['cookie']['name'], null, 0, $conf['cookie']['path'], 
                    $conf['cookie']['domain'], $conf['cookie']['secure']);

        $sess = & new SGL_HTTP_Session();
    }

    /**
     * Callback method for DB session start.
     *
     * @return  boolean
     */
    function dbOpen()
    {
        $timeout = isset($_SESSION['aPrefs']['sessionTimeout']) 
            ? $_SESSION['aPrefs']['sessionTimeout'] 
            : 900;
        $this->dbGc($timeout);
        return true;
    }

    /**
     * Callback method for DB session end.
     *
     * @return  boolean
     */
    function dbClose()
    {
        return true;
    }

    /**
     * Callback method for DB session get.
     *
     * @return  string  return session value
     */
    function dbRead($sessId)
    {
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
        
        $query = "SELECT data_value FROM {$conf['table']['user_session']} WHERE session_id = '$sessId'";
        $res = $dbh->query($query);
        if ($res->numRows() == 1) {
            return $dbh->getOne($query);
        } else {
            $query = "INSERT INTO {$conf['table']['user_session']} (session_id, last_updated, data_value)
            VALUES ('$sessId', '" . SGL::getTime(true) . "', '')";
            $dbh->query($query);
            return '';
        }
    }

    /**
     * Callback method for DB session set.
     *
     * @return  boolean
     */
    function dbWrite($sessId, $value)
    {
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
                
        $query = "  UPDATE {$conf['table']['user_session']} SET data_value = " . $dbh->quote($value) . ", 
                        last_updated = '" . SGL::getTime(true) . "' 
                    WHERE session_id = '$sessId'";
        $res = $dbh->query($query);
        return true;
    }

    /**
     * Callback method for DB session destroy.
     *
     * @return  boolean
     */
    function dbDestroy($sessId)
    {
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
                
        $query = "DELETE FROM {$conf['table']['user_session']} WHERE session_id = '$sessId'";
        $res = $dbh->query($query);
        return true;
    }

    /**
     * Callback method for DB session garbage collection.
     *
     * @return  boolean
     */
    function dbGc($expiry)
    {
        $dbh = & SGL_DB::singleton();
        $conf = & $GLOBALS['_SGL']['CONF'];
                
        $query = "DELETE FROM {$conf['table']['user_session']} WHERE UNIX_TIMESTAMP('" . SGL::getTime(true) . 
                "') - UNIX_TIMESTAMP(last_updated ) > $expiry";
        $dbh->query($query);
        return true;
    }
}
?>