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
// | Sql.php                                                                   |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Sql.php,v 1.23 2005/06/14 00:19:22 demian Exp $

/**
 * Provides SQL schema and data parsing/executing methods.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.23 $
 */
class SGL_Sql
{
    /**
     * Simple function that opens a file with sql statements and executes them
     * using DB
      *
     * @author  Gerry Lachac <glachac@tethermedia.com>
     * @access  public
     * @static
     * @param   string  $filename   File with SQL statements to execute
     * @return  void
     */
    function parse($filename, $errorReporting = E_ALL, $executerCallback = null)
    {
        //  Optionally shut off error reporting if logging isn't set up correctly yet
        $originalErrorLevel = error_reporting();
        error_reporting($errorReporting);

        if (! ($fp = fopen($filename, 'r')) ) {
            return false;
        }

        $sql = '';
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $isMysql323 = false;
        if        ($conf['db']['type'] == 'mysql_SGL'
                || $conf['db']['type'] == 'mysql'
                || $conf['db']['type'] == 'mysqli'
                || $conf['db']['type'] == 'mysqli_SGL') {
            $aEnvData = unserialize(file_get_contents(SGL_VAR_DIR . '/env.php'));
            //if (isset($aEnvData['db_info']) && ereg('3.23', $aEnvData['db_info']['version'])) {
            if (isset($aEnvData['db_info']) && preg_match("/3\.23/", $aEnvData['db_info']['version'])) {
                $isMysql323 = true;
            }
        }

        // Iterate through each line in the file.
        $aLines = array();
        while (!feof($fp)) {

            // Read lines, concat together until we see a semi-colon
            $line = fgets($fp, 32768);

            // Check for various comment types
            if (preg_match("/^\s*(--)|^\s*#/", $line)) {
                continue;
            }
            if (preg_match("/insert/i", $line) && preg_match("/\{SGL_NEXT_ID\}/", $line)) {
                $tableName = SGL_Sql::extractTableNameFromInsertStatement($line);
                $nextId = SGL_Sql::getNextId($tableName);
                if (PEAR::isError($nextId)) {
                    return $nextId;
                }
                $line = SGL_Sql::rewriteWithAutoIncrement($line, $nextId);
            }


            // prefix table name in statement
            if (!empty($conf['db']['prefix'])) {
                $statementType = '';
                if (preg_match('/create table/i', $line)) {
                    $statementType = 'createTable';
                } elseif (preg_match('/insert into/i', $line)) {
                    $statementType = 'insert';
                } elseif (preg_match('/select(.*?)from/i', $line)) {
                    $statementType = 'select';
                } elseif (preg_match('/create (unique )?index/i', $line)) {
                    $statementType = 'createIndex';
                } elseif (preg_match('/delete from/i', $line)) {
                    $statementType = 'delete';
                } elseif (preg_match('/alter table/i', $line)) {
                    $statementType = 'alterTable';
                } elseif (preg_match('/references/i', $line)) {
                    $statementType = 'ref';
                } elseif (preg_match('/add constraint/i', $line)) {
                    $statementType = 'addConstraint';
                } elseif (preg_match('/create sequence/i', $line)) {
                    $statementType = 'createSequence';
                }
                if (!empty($statementType)) {
                    $line = SGL_Sql::prefixTableNameInStatement($line, $statementType);
                }
            }
            $sql .= $line;

            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }

            // strip semi-colons for MaxDB, Oracle and mysql 3.23
            if ($conf['db']['type'] == 'oci8_SGL' || $conf['db']['type'] == 'odbc' || $isMysql323) {
                $sql = preg_replace("/;\s*$/", '', $sql);
            }

            // support for custom mysql engine
            if (in_array($conf['db']['type'], array('mysql', 'mysql_SGL', 'mysqli_SGL'))) {
                if (SGL_Config::get('db.mysqlDefaultStorageEngine')
                        && preg_match('/create table/i', $sql)) {
                    $engine = SGL_Config::get('db.mysqlDefaultStorageEngine');
                    // we only change default storage engine for tables,
                    // which doesn't have explicit storage specified in
                    // create table statement
                    if (!preg_match('/(type|engine)(\s*)=(\s*)([a-z]+)/i', $sql)) {
                        if (preg_match('/\)\s*;\s*$/', $sql)) {
                            $sql = preg_replace('/;\s*$/', 'engine = ' . $engine . ';', $sql);
                        }
                    }
                }
            }

            // Execute the statement.
            if (!is_null($executerCallback) && is_callable($executerCallback)) {

                // support for php 5.3
                if (version_compare(phpversion(), "5.3.0", '>=')) {
                    $sql = array($sql);
                }
                $res = call_user_func_array(
                    array($executerCallback[0], $executerCallback[1]), $sql);
                //  handle error
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
            // support for php 5.3
            if (version_compare(phpversion(), "5.3.0", '>=')) {
                $sql = $sql[0];
            }
            $aLines[] = $sql;
            $sql = '';
        }
        fclose($fp);
        //  reset orig error level
        error_reporting($originalErrorLevel);
        return implode("\n", $aLines);
    }

    function execute($sql)
    {
        // Get database handle based on working config.ini
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        $res = $dbh->query($sql);
        return $res;
    }

    function getNextId($tableName)
    {
        // Get database handle based on working config.ini
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        return $dbh->nextId($tableName);
    }

    function extractTableNameFromInsertStatement($str)
    {
        $pattern = '/^(INSERT INTO)(\W+)(\w+)(\W+)(.*)/i';
        preg_match($pattern, $str, $matches);
        $tableName = SGL_Sql::addTablePrefix($matches[3]);
        return $tableName;
    }

    /**
     * Given a CREATE TABLE string, will extract the table name.
     *
     * @param string $str
     * @return string
     * @todo consider using SQL_Parser, 19kb lib
     */
    function extractTableNameFromCreateStatement($str)
    {
        //  main pattern, 5th group, matches any alphanum char plus _ and -
        $pattern = '/(CREATE TABLE)(\W+)(IF NOT EXISTS)?(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
        preg_match($pattern, $str, $matches);
        $tableName = SGL_Sql::addTablePrefix($matches[5]);
        return $tableName;
    }

    function rewriteWithAutoIncrement($str, $nextId)
    {
        $res = str_replace('{SGL_NEXT_ID}', $nextId, $str);
        return $res;
    }

    function extractTableNamesFromSchema($data)
    {
        if (is_file($data)) {
            $aLines = file($data);
        } elseif (is_string($data)) {
            $aLines = explode("\n", $data);
        } else {
            return SGL::raiseError('unexpected input', SGL_ERROR_INVALIDARGS);
        }
        $aTablesNames = array();
        foreach ($aLines as $line) {
            if (preg_match("/create table/i", $line)) {
                $aTablesNames[] = SGL_Sql::extractTableNameFromCreateStatement($line);
            }
        }
        return $aTablesNames;
    }

    function getDbShortnameFromType($dbType)
    {
        switch ($dbType) {
        case 'pgsql':
            $shortName = 'pg';
            break;

        case 'mysql_SGL':
        case 'mysqli_SGL':
        case 'mysql':
        case 'mysqli':
            $shortName = 'my';
            break;

        case 'oci8_SGL':
            $shortName = 'oci';
            break;
        }
        return $shortName;
    }

    /**
     * Prefix given table name.
     *
     * @param  string $tableName  table name
     * @return string
     */
    function addTablePrefix($tableName)
    {
        $c = &SGL_Config::singleton();
        $prefix = $c->get(array('db' => 'prefix'));
        return $prefix . $tableName;
    }

    /**
     * Prefix table name in SQL statement.
     *
     * @param  string $str   initial string (statement)
     * @param  string $type  query type
     * @return string
     */
    function prefixTableNameInStatement($str, $type)
    {
        switch ($type) {
        case 'select':
            $pattern     = '/(SELECT)(.*?)(FROM)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}${3}${4}' .
                SGL_Sql::addTablePrefix('$5') . '${6}';
            break;

        case 'insert':
            $pattern     = '/(INSERT INTO)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}' .
                SGL_Sql::addTablePrefix('$3') . '${4}';
            break;

        case 'delete':
            $pattern     = '/(DELETE FROM)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}' . SGL_Sql::addTablePrefix('$3') . '${4}';
            break;

        case 'createTable':
            $pattern     = '/(CREATE TABLE)(\W+)(IF NOT EXISTS)?(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}${3}${4}' .
                SGL_Sql::addTablePrefix('$5') . '${6}';
            break;

        case 'createIndex':
            $pattern     = '/(CREATE)(.*?)(INDEX)(\W+)?([A-Za-z0-9_-]+)(\W+)?' .
                '(.*?)(ON)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}${3}${4}' .
                SGL_Sql::addTablePrefix('$5') . '${6}${7}${8}${9}' .
                SGL_Sql::addTablePrefix('$10') . '${11}';
            break;

        case 'alterTable':
            // prefix sub-statements on the same line
            if (preg_match('/add constraint/i', $str)) {
                $str = SGL_Sql::prefixTableNameInStatement($str, 'addConstraint');
            }
            if (preg_match('/references/i', $str)) {
                $str = SGL_Sql::prefixTableNameInStatement($str, 'ref');
            }
            $pattern     = '/(ALTER)(.*?)(TABLE)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}${3}${4}' .
                SGL_Sql::addTablePrefix('$5') . '${6}';
            break;

        case 'addConstraint':
            $pattern     = '/(ADD CONSTRAINT)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}' . SGL_Sql::addTablePrefix('$3') . '${4}';
            break;

        case 'ref':
            $pattern     = '/(REFERENCES)(\W+)?([A-Za-z0-9_-]+)(\W+)?/i';
            $replacement = '${1}${2}' . SGL_Sql::addTablePrefix('$3') . '${4}';
            break;

        case 'createSequence':
            $pattern     = '/(CREATE)(.*?)(SEQUENCE)(\W+)?([A-Za-z0-9_-]+)/i';
            $replacement = '${1}${2}${3}${4}' . SGL_Sql::addTablePrefix('$5');
            break;

        default:
            return SGL::raiseError('Unknown replacement format',
                SGL_ERROR_INVALIDARGS);
        }
        $str = preg_replace($pattern, $replacement, $str);
        return $str;
    }

    function buildDbCreateStatement($driver, $dbname)
    {
        if ($driver != 'pgsql') {
            $result = 'CREATE DATABASE ' . $dbname;
            if (SGL_Config::get('db.charset')) {
                $result .= ' CHARACTER SET ' . SGL_Config::get('db.charset');
            }
            if (SGL_Config::get('db.collation')) {
                $result .= ' COLLATE ' . SGL_Config::get('db.collation');
            }
        } else {
            $result = 'CREATE SCHEMA public';
        }
        return $result;
    }

    function buildDbDropStatement($driver, $dbname)
    {
        if ($driver != 'pgsql') {
            $result = 'DROP DATABASE IF EXISTS ' . $dbname;
        } else {
            $result = 'DROP SCHEMA public CASCADE';
        }
        return $result;
    }

}
?>
