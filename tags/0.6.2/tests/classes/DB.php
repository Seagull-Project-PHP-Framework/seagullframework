<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2003-2005 m3 Media Services Limited                         |
// | For contact details, see: http://www.m3.net/                              |
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
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

$projectMnemonic = $GLOBALS['_STR']['CONF']['project']['projectMnemonic'];
$GLOBALS[$projectMnemonic]['CONNECTIONS'] = array();
#require_once 'DB.php';

/**
 * Class for handling DB resources.
 *
 * @author     Demian Turner <demian@m3.net>
 */
class STR_DB
{
    /**
     * Returns a singleton DB handle.
     *
     * example usage:
     * $dbh =& STR_DB::singleton();
     * Warning: In order to work correctly, DB handle singleton must be
     * instantiated statically and by reference.
     *
     * @static
     * @param string $dsn The datasource details if supplied: see {@link DB::parseDSN()} for format
     * @return mixed Reference to DB resource or false on failure to connect
     */
    function &singleton($dsn = null)
    {
        if (is_null($dsn)) {
            $dsn = STR_DB::getDsn();
        }

        $projectMnemonic = $GLOBALS['_STR']['CONF']['project']['projectMnemonic'];

        $dsnMd5 = md5($dsn);
        $aConnections = array_keys($GLOBALS[$projectMnemonic]['CONNECTIONS']);
        if (!(count($aConnections)) || !(in_array($dsnMd5, $aConnections))) {
            $GLOBALS[$projectMnemonic]['CONNECTIONS'][$dsnMd5] = DB::connect($dsn);

            //  If DB connect fails and we're installing, return error
            if (DB::isError($GLOBALS[$projectMnemonic]['CONNECTIONS'][$dsnMd5])) {
                die('Cannot connect to DB, check your credentials');
            }
            $GLOBALS[$projectMnemonic]['CONNECTIONS'][$dsnMd5]->setFetchMode(DB_FETCHMODE_OBJECT);
        }
        return $GLOBALS[$projectMnemonic]['CONNECTIONS'][$dsnMd5];
    }

   /**
     * Returns the default DSN specified in the global config.
     *
     * @static
     * @return mixed A string or array containing the data source name.
     */
    function getDsn()
    {
        $conf = $GLOBALS['_STR']['CONF'];
        $dbType = $conf['database']['type'];
        if ($dbType == 'mysql') {
            $dbType = 'mysql_SGL';
        }

        $protocol = isset($conf['database']['protocol']) ? $conf['database']['protocol'] . '+' : '';
        $dsn = $dbType . '://' .
            $conf['database']['user'] . ':' .
            $conf['database']['pass'] . '@' .
            $protocol .
            $conf['database']['host'] . '/' .
            $conf['database']['name'];

        //   override SGL dsn with temporary testing one
        $GLOBALS['_SGL']['CONF']['db'] = $conf['database'];

        return $dsn;
    }
}
?>