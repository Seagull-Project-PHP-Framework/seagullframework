<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
        if ($conf['db']['type'] == 'mysql_SGL' || $conf['db']['type'] == 'mysql') {
            $aEnvData = unserialize(file_get_contents(SGL_VAR_DIR . '/env.php'));
            if (isset($aEnvData['db_info']) && ereg('3.23', $aEnvData['db_info']['version'])) {
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
                $line = SGL_Sql::rewriteWithAutoIncrement($line, $nextId);
            }
            $sql .= $line;

            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }
            // strip semi-colons for MaxDB, Oracle and mysql 3.23
            if ($conf['db']['type'] == 'oci8_SGL' || $conf['db']['type'] == 'odbc' || $isMysql323) {
                $sql = preg_replace("/;\s*$/", '', $sql);
            }
            // Execute the statement.
            if (!is_null($executerCallback) && is_callable($executerCallback)) {
                $res = call_user_func_array(
                    array($executerCallback[0], $executerCallback[1]), $sql);
                //  handle error
                if (PEAR::isError($res, DB_ERROR_ALREADY_EXISTS)) {
                    return $res;
                } elseif (DB::isError($res)) {
                    // Print out info on bad statements
                    echo '<pre>'.$res->getMessage().'</pre>';
                    echo '<pre>'. $res->getUserInfo() . '</pre>';
                }
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
        $tableName = $matches[3];
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
        $tableName = $matches[5];
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
        case 'mysql':
            $shortName = 'my';
            break;

        case 'oci8_SGL':
            $shortName = 'oci';
            break;
        }
        return $shortName;
    }
}
?>