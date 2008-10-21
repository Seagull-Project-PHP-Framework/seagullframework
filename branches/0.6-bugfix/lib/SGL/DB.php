<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | DB.php                                                                    |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: DB.php,v 1.14 2005/06/20 10:56:31 demian Exp $

define('SGL_DSN_ARRAY',     0);
define('SGL_DSN_STRING',    1);

/**
 * Class for handling DB resources.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.14 $
 */
class SGL_DB
{
    /**
     * Returns a singleton reference to the DB resource.
     *
     * example usage:
     * $dbh = & SGL_DB::singleton();
     * warning: in order to work correctly, DB handle
     * singleton must be instantiated statically and
     * by reference
     *
     * @access  public
     * @static
     * @param   string  $dsn    the datasource details if supplied: see {@link DB::parseDSN()} for format
     * @return  mixed           reference to DB resource or PEAR error on failure to connect
     */
    function &singleton($dsn = null)
    {
        $msg = 'Cannot connect to DB, check your credentials';
        $dsn = (is_null($dsn)) ? SGL_DB::getDsn(SGL_DSN_STRING) : $dsn;
        if (empty($dsn['phptype'])) {
            return PEAR::raiseError($msg, SGL_ERROR_DBFAILURE);
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        static $aInstances;
        if (!isset($aInstances)) {
            $aInstances = array();
        }
        $signature = md5($dsn);
        if (!isset($aInstances[$signature])) {
            $conn = DB::connect($dsn);
            if (PEAR::isError($conn)) {
                //  remove sensitive details, ie, db password
                if ($conn->getCode() == DB_ERROR_CONNECT_FAILED) {
                    $oLastStackError = SGL_Error::pop();
                    $oLastStackError->userinfo = ''; //crude but give me an API!
                    SGL_Error::push($oLastStackError);
                    $conn = $oLastStackError;
                }
                if (is_file(SGL_VAR_DIR . '/INSTALL_COMPLETE.php') && defined('SGL_INSTALLED')) {
                    $msg .= 'If you remove the file seagull/var/INSTALL_COMPLETE.php you will be'.
                    ' able to run the setup again.';
                }
                return $conn;
            }
            if (!empty($conf['db']['postConnect'])) {
                $conn->query($conf['db']['postConnect']);
            }
            $conn->setFetchMode(DB_FETCHMODE_OBJECT);
            $aInstances[$signature] = $conn;
        }
        return $aInstances[$signature];
    }

    /**
     * Returns the default dsn specified in the global config.
     *
     * @access  public
     * @static
     * @param int $type  a constant that specifies the return type, ie, array or string
     * @return mixed     a string or array contained the data source name
     */
    function getDsn($type = SGL_DSN_ARRAY, $excludeDbName = false)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        if (!count($conf)) {
            return false;
        }

        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if ($dbh && count($dbh->dsn)) {
            $locatorDsn = $dbh->dsn;
            $conf['db']['user'] = $locatorDsn['username'];
            $conf['db']['pass'] = $locatorDsn['password'];
            $conf['db']['protocol'] = $locatorDsn['protocol'];
            $conf['db']['socket'] = $locatorDsn['socket'];
            $conf['db']['host'] = $locatorDsn['hostspec'];
            $conf['db']['port'] = $locatorDsn['port'];
            $conf['db']['name'] = $locatorDsn['database'];
        }

        //  override default mysql driver to allow for all sequence IDs to
        //  be kept in a single table
        if ($type == SGL_DSN_ARRAY) {
            $dsn = SGL_DB::_getDsnAsArray($conf, $excludeDbName);
        } else {
            $dsn = SGL_DB::_getDsnAsString($conf, $excludeDbName);
        }
        return $dsn;
    }

    function _getDsnAsArray($conf, $excludeDbName = false)
    {
        $dsn = array(
            'phptype'  => $conf['db']['type'],
            'username' => $conf['db']['user'],
            'password' => $conf['db']['pass'],
            'protocol' => $conf['db']['protocol'],
            'socket'   => $conf['db']['socket'],
            'hostspec' => $conf['db']['host'],
            'port'     => $conf['db']['port']
        );
        if (!$excludeDbName) {
            $dsn['database'] = $conf['db']['name'];
        }
        return $dsn;
    }

    function _getDsnAsString($conf, $excludeDbName = false)
    {
        $socket = (isset($conf['db']['protocol'])
                && $conf['db']['protocol'] == 'unix'
                && !empty($conf['db']['socket']))
            ? '(' . $conf['db']['socket'] . ')'
            : '';
        $protocol = isset($conf['db']['protocol'])
            ? $conf['db']['protocol'] . $socket
            : '';
        $host = !empty($conf['db']['host']) ? $conf['db']['host'] : '';
        $host = empty($conf['db']['socket']) ? '+' . $host : '';
        $port = (!empty($conf['db']['port'])
                && isset($conf['db']['protocol'])
                && ($conf['db']['protocol'] == 'tcp'))
            ? ':' . $conf['db']['port']
            : '';
        $type = !empty($conf['db']['type']) ? $conf['db']['type'] : '';
        $user = !empty($conf['db']['user']) ? $conf['db']['user'] : '';
        $passwd = !empty($conf['db']['pass']) ? $conf['db']['pass'] : '';
        $dsn = $type . '://' .
            $user . ':' .
            $passwd . '@' .
            $protocol .
            $host . $port;
        if (!$excludeDbName && isset($conf['db']['name'])) {
            $dsn .= '/' . $conf['db']['name'];
        }
        return $dsn;
    }

    /**
     * Sets the DB_DataObject DB resource to be the same as the sgl DB singleton. You can
     * use this for sharing connections between PEAR::DataObjects and SGL_DB.
     * This enables you to use DataObjects and SGL_DB in the same transaction.
     *
     * example usage:
     * $oUser = DB_DataObject::factory($this->conf['table']['user']);
     * SGL_DB::setConnection();
     * $dbh->autocommit();
     * ... do some transactional DO and SGL_DB stuff
     * $dbh->commit();
     *
     * @access  public
     * @param   string $dsn Supplied database resource name
     * @static
     */
    function setConnection($dsn = null)
    {
        $locator = &SGL_ServiceLocator::singleton();
        $singleton = $locator->get('DB');
        if (!$singleton) {
            $singleton = & SGL_DB::singleton();
            $locator->register('DB', $singleton);
        }
        $dsn = (is_null($dsn)) ? SGL_DB::getDsn(SGL_DSN_STRING) : $dsn;
        $dsnMd5 = md5($dsn);

        unset($GLOBALS['_DB_DATAOBJECT']['CONNECTIONS'][$dsnMd5]);
        $GLOBALS['_DB_DATAOBJECT']['CONNECTIONS'][$dsnMd5] = &$singleton;
    }

    /**
     * Helper method - Rewrite the query into a "SELECT COUNT(*)" query.
     *
     * @param string $sql query
     * @return string rewritten query OR false if the query can't be rewritten
     * @access private
     */
    function rewriteCountQuery($sql)
    {
        if (preg_match('/^\s*SELECT\s+\bDISTINCT\b/is', $sql) || preg_match('/\s+GROUP\s+BY\s+/is', $sql)) {
            return false;
        }
        $queryCount = preg_replace('/(?:.*)\bFROM\b\s+/Uims', 'SELECT COUNT(*) FROM ', $sql, 1);
        list($queryCount, ) = preg_split('/\s+ORDER\s+BY\s+/is', $queryCount);
        list($queryCount, ) = preg_split('/\bLIMIT\b/is', $queryCount);
        return trim($queryCount);
    }

    /**
     * @param object $db            PEAR::DB instance
     * @param string $query         db query
     * @param array  $pager_options PEAR::Pager options
     * @param boolean $disabled     Disable pagination (get all results)
     * @param int    $fetchMode     fetchmode to use
     * @param mixed  $dbparams      array, string or numeric data passed to DB execute
     * @return array with links and paged data
     */
    function getPagedData(&$db, $query, $pager_options = array(), $disabled = false,
        $fetchMode = DB_FETCHMODE_ASSOC, $dbparams = array())
    {
        if (!array_key_exists('totalItems', $pager_options) || is_null($pager_options['totalItems'])) {
            //  be smart and try to guess the total number of records
            if ($countQuery = SGL_DB::rewriteCountQuery($query)) {
                $totalItems = $db->getOne($countQuery, $dbparams);
                if (PEAR::isError($totalItems)) {
                    return $totalItems;
                }
            } else {
                $res =& $db->query($query, $dbparams);
                if (PEAR::isError($res)) {
                    return $res;
                }
                $totalItems = (int)$res->numRows();
                $res->free();
            }
            $pager_options['totalItems'] = $totalItems;
        }

        require_once 'Pager/Pager.php';
        // To get Seagull URL Style working for Pager
        $req =& SGL_Request::singleton();
        $pager_options['currentPage'] = (array_key_exists('currentPage', $pager_options))
            ? $pager_options['currentPage']
            : $req->get('pageID');
        $pager_options['append'] = isset($pager_options['append'])
            ? $pager_options['append']
            : false;
        $pager_options['fileName'] = isset($pager_options['fileName'])
            ? $pager_options['fileName']
            : '/pageID/%d/';

        // translate PEAR::Pager
        $pager_options['altPrev'] = SGL_String::translate('altPrev');
        $pager_options['altNext'] = SGL_String::translate('altNext');
        $pager_options['altPage'] = SGL_String::translate('altPage');
        $pager_options['prevImg'] = SGL_String::translate('prevImg');
        $pager_options['nextImg'] = SGL_String::translate('nextImg');
        $pager = Pager::factory($pager_options);

        $page = array();
        $page['totalItems'] = $pager_options['totalItems'];
        $page['links'] = str_replace("/pageID/".$pager->getCurrentPageID()."/", "/", $pager->links);
        $page['page_numbers'] = array(
            'current' => $pager->getCurrentPageID(),
            'total'   => $pager->numPages()
        );
        list($page['from'], $page['to']) = $pager->getOffsetByPageId();

        $res = ($disabled)
            ? $db->limitQuery($query, 0, $page['totalItems'], $dbparams)
            : $db->limitQuery($query, $page['from']-1, $pager_options['perPage'], $dbparams);

        if (PEAR::isError($res)) {
            return $res;
        }
        $page['data'] = array();
        while ($res->fetchInto($row, $fetchMode)) {
           $page['data'][] = $row;
        }
        if ($disabled) {
            $page['links'] = '';
            $page['page_numbers'] = array(
                'current' => 1,
                'total'   => 1
            );
        }
        return $page;
    }
}

/**
 * ServiceLocator.
 *
 * @package    SGL
 * @author     Luis Correa d'Almeida <luis@awarez.net>
 * @author     Andrew Hill <andrew@awarez.net>
 */

/**
  * A class that allows services to be globally registered, so that they
  * can be accessed by any class that needs them. Also allows Mock Objects
  * to be easily used as replacements for classes during testing.
  *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
  */
class SGL_ServiceLocator
{
    var $aServices = array();

    /**
     * A method to return a singleton handle to the service locator class.
     */
    function &singleton()
    {
        static $instance;
        if (!$instance) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    /**
     * A method to register a service with the service locator class.
     *
     * @param string $serviceName The name of the service being registered.
     * @param mixed $oService The object (service) being registered.
     * @return boolean Always returns true.
     */
    function register($serviceName, &$oService)
    {
        $this->aServices[$serviceName] = &$oService;
        return true;
    }

    /**
     * A method to remove a registered service from the service locator class.
     *
     * @param string $serviceName The name of the service being de-registered.
     */
    function remove($serviceName)
    {
        unset($this->aServices[$serviceName]);
    }

    /**
     * A method to return a registered service.
     *
     * @param string $serviceName The name of the service required.
     * @return mixed Either the service object requested, or false if the
     *               requested service was not registered.
     */
    function &get($serviceName)
    {
        if (isset($this->aServices[$serviceName])) {
            $ret = $this->aServices[$serviceName];
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * A method to return a registered service.
     *
     * @param string $serviceName The name of the service required.
     * @return mixed Either the service object requested, or false if the
     *               requested service was not registered.
     * @static
     */
    function &staticGet($serviceName)
    {
        $oServiceLocator = &SGL_ServiceLocator::singleton();
        return $oServiceLocator->get($serviceName);
    }
}
?>
