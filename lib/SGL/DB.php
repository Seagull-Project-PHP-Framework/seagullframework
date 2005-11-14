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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | DB.php                                                                    |
// +---------------------------------------------------------------------------+
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: DB.php,v 1.14 2005/06/20 10:56:31 demian Exp $

define('SGL_DSN_ARRAY',                 0);
define('SGL_DSN_STRING',                1);

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
     * Returns the default dsn specified in the global config.
     *
     * @access  public
     * @static
     * @param int $type  a constant that specifies the return type, ie, array or string
     * @return mixed     a string or array contained the data source name
     */
    function getDsn($type = SGL_DSN_ARRAY)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  override default mysql driver to allow for all sequence IDs to
        //  be kept in a single table
        $dbType = $conf['db']['type'];
        if ($type == SGL_DSN_ARRAY) {
            $dsn = array(
                'phptype'  => $dbType,
                'username' => $conf['db']['user'],
                'password' => $conf['db']['pass'],
                'protocol' => $conf['db']['protocol'],
                'hostspec' => $conf['db']['host'],
                'database' => $conf['db']['name'],
                'port'     => $conf['db']['port']
            );
        } else {
        	$protocol = isset($conf['db']['protocol']) ? $conf['db']['protocol'] . '+' : '';
            $port = (!empty($conf['db']['port'])
                        && isset($conf['db']['protocol'])
                        && ($conf['db']['protocol'] == 'tcp'))
                ? ':' . $conf['db']['port']
                : '';
            $dsn = $dbType . '://' .
                $conf['db']['user'] . ':' .
                $conf['db']['pass'] . '@' .
                $protocol .
                $conf['db']['host'] . $port . '/' .
                $conf['db']['name'];
        }
        return $dsn;
    }

    /**
     * Sets the DB global DB handle for optionally specified dsn. You can
     * use this for sharing connections between PEAR::DataObjects and SGL_DB.
     * This enables you to use DataObjects and SGL_DB in the same transaction.
     *
     * example usage:
     * $oUser = DB_DataObject::factory('Usr');
     * $dbh = & $oUser->getDatabaseConnection();
     * SGL_DB::setConnection($dbh);
     * $dbh->autocommit();
     * ... do some transactional DO and SGL_DB stuff
     * $dbh->commit();
     *
     * @access  public
     * @static
     * @param   object  $dbh    PEAR::DB instance
     * @param   string  $dsn    the datasource details if supplied: see {@link DB::parseDSN()} for format
     */
    function setConnection (&$dbh, $dsn = null)
    {
        $dsn = ($dsn === null) ? SGL_DB::getDsn(SGL_DSN_STRING) : $dsn;
        $dsnMd5 = md5($dsn);

        //  if we're using SimpleTestRunner, reassign STR db resource
        if (isset($GLOBALS['_STR'])) {
            $dbh = $GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5];
            $dbh->setFetchMode(DB_FETCHMODE_OBJECT);
        } else {
            $GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5] = $dbh;
            $GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5]->setFetchMode(DB_FETCHMODE_OBJECT);
        }
    }

    /**
     * Returns a singleton DB handle.
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
     * @return  mixed           reference to DB resource or false on failure to connect
     */
    function &singleton($dsn = null)
    {
        $dsn = ($dsn === null) ? SGL_DB::getDsn(SGL_DSN_STRING) : $dsn;
        $dsnMd5 = md5($dsn);
        if (!is_array(@$GLOBALS['_SGL']['CONNECTIONS'])) {
            $GLOBALS['_SGL']['CONNECTIONS'] = array();
        }
        $aConnections = array_keys($GLOBALS['_SGL']['CONNECTIONS']);

        if (!(count($aConnections)) || !(in_array($dsnMd5, $aConnections))) {
            $GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5] = DB::connect($dsn);

            //  if db connect fails and seagull is already configured, die
            if (DB::isError($GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5])) {
                return PEAR::raiseError('Cannot connect to DB, check your credentials, exiting ...',
                    SGL_ERROR_DBFAILURE, PEAR_ERROR_DIE);
            }
            $GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5]->setFetchMode(DB_FETCHMODE_OBJECT);
        }
        return $GLOBALS['_SGL']['CONNECTIONS'][$dsnMd5];
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

        // To get Seagull URL Style working for Pager
        $req =& SGL_Request::singleton();
		$pager_options['currentPage'] = $req->get('pageID');

		require_once 'Pager/Pager.php';
		$pager_options['append'] = false;
        $pager_options['fileName'] = '/pageID/%d/';
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
            ? $db->limitQuery($query, 0, $totalItems, $dbparams)
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
?>
