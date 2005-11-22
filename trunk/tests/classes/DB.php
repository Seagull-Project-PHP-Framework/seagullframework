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
// | Seagull 0.4                                                               |
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

    function rebuildSequences($tables = null)
    {
        $db =& SGL_DB::singleton();
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        switch ($db->phptype) {

        case 'mysql':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $db->getListOf('tables');

            //  "%_seq" is the default, but in case they screwed around with PEAR::DB...
            $suffix = $db->getOption('seqname_format');
            $suffixRaw = str_replace('%s', '', $suffix);
            $suffixRawStart = (0 - strlen($suffixRaw));

            foreach ($aTables as $table) {
                $primary_field = '';
                //  we only build sequences for tables that are not sequences themselves
                if ($table == $conf['table']['sequence'] || substr($table, $suffixRawStart) == $suffixRaw) {
                    continue;
                }

                $info = $db->tableInfo($table);
                foreach ($info as $field) {
                    if (eregi('primary_key', $field['flags'])) {
                        $primary_field = $field['name'];
                        break;
                    }
                }
                if ($primary_field <> '') {
                    $maxId = $db->getOne('SELECT MAX(' . $primary_field . ') FROM ' . $table . ' WHERE 1');
                    if (!is_null($maxId)) {
                    	$data[] = array($table, $maxId);
                    }
                }
            }

            foreach ($data as $k) {
                $tableName = $k[0];
                $seqName = sprintf($suffix, $tableName);
                $maxVal   = $k[1];
                $currVal = $db->nextId($tableName, true);
                $sql = 'UPDATE ' . $seqName . ' SET id=' . $maxVal . ' WHERE id=' . $currVal;
                $result = $db->query($sql);
            }
            break;

        case 'mysql_SGL':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $db->getListOf('tables');
            foreach ($aTables as $table) {
                $primary_field = '';
                if ($table <> $conf['table']['sequence']) {
                    $info = $db->tableInfo($table);
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field <> '') {
                        $data[] = array($table, $db->getOne('SELECT MAX(' .
                            $primary_field . ') FROM ' . $table . ' WHERE 1'));
                    } else {
                        $data[] = array($table, 0);
                    }
                }
            }
            $sth = $db->prepare("REPLACE INTO {$conf['table']['sequence']} (name, id) VALUES(?,?)");
            $db->executeMultiple($sth, $data);
            break;

        case 'pgsql':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $db->getListOf('tables');
            foreach ($aTables as $table) {
                $primary_field = '';
                if ($table <> $conf['table']['sequence']) {
                    $info = $db->tableInfo($table);
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field <> '') {
                        $data[] = array($table, $db->getOne('SELECT MAX(' .
                            $primary_field . ') FROM ' . $table . ' WHERE true'));
                    }
                }
            }
            //  "%_seq" is the default, but in case they screwed around with PEAR::DB...
            $suffix = $db->getOption('seqname_format');

            //  we'll just create the sequences manually...why not?
            foreach ($data as $k) {
                $tableName = $k[0];
                $seqName = sprintf($suffix, $tableName);
                $maxVal   = $k[1] + 1;
                $sql = 'CREATE SEQUENCE ' . $seqName . ' START ' . $maxVal;
                $result = $db->query($sql);
                if (PEAR::isError($result) && $result->code == DB_ERROR_ALREADY_EXISTS) {
                    $sql = 'ALTER SEQUENCE ' . $seqName . ' RESTART WITH ' . $maxVal;
                    $result = $db->query($sql);
                }
            }
            break;

        case 'oci8':
            $db->autoCommit(false);

            $data = '';
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $db->getListOf('sequences');
            foreach ($aTables as $sequence) {
                $primary_field = '';
                // get tablename
                if (preg_match("/^(.*)_seq$/",$sequence,$table)) {
                    $info = $db->tableInfo($table[1]);
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field <> '') {
                        $maxId = $db->getOne('SELECT MAX(' .
                            $primary_field . ') + 1 FROM ' . $table[1]);
                    } else {
                        $maxId = 1;
                    }

                    // check for NULL
                    if (!$maxId) {
                        $maxId = 1;
                    }

                    // drop and recreate sequence
                    $success = false;
                    if (!DB::isError($db->dropSequence($table[1]))) {
                        $success = $db->query('CREATE SEQUENCE ' .
                            $db->getSequenceName($table[1]) . ' START WITH ' . $maxId);
                    }

                    if (!$success) {
                        $db->rollback();
                        $db->autoCommit(true);
                        return SGL_Install::errorPush(PEAR::raiseError('Rebuild failed '));
                    }
                }
            }
            $success = $db->commit();
            $db->autoCommit(true);
            if (!$success) {
                return SGL_Install::errorPush(PEAR::raiseError('Rebuild failed '));
            }
            break;

        default:
            return SGL_Install::errorPush(
                PEAR::raiseError('This feature currently is impmlemented only for MySQL, Oracle and PostgreSQL.'));
        }
    }
}
?>