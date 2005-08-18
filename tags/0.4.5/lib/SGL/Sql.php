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
// | Sql.php                                                                   |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Sql.php,v 1.23 2005/06/14 00:19:22 demian Exp $

/**
 * Provides tools to manage translations and mtce tasks.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.23 $
 * @since   PHP 4.1
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
    function parseAndExecute($filename, $errorReporting = E_ALL)
    {
        //  Optionally shut off error reporting if logging isn't set up correctly yet
        error_reporting($errorReporting);

        if (! ($fp = fopen($filename, 'r')) ) {
            return false;
        }
        // Get database handle based on working config.ini
        $dbh = & SGL_DB::singleton();
        $sql = '';
        $conf = $GLOBALS['_SGL']['CONF'];

        // Iterate through each line in the file.
        while (!feof($fp)) {

            // Read lines, concat together until we see a semi-colon
            $line = fgets($fp, 32768);

            // Check if '--' comment line.  Fixes Problem with commented Postgres SQL statements
            // This avoids printing bogus errors to screen when we try to execute a comment only
#            if (preg_match("/^\s*(--)|^\s*#/", $line)) {
#                continue;
#            }
#FIXME: the above code fails in certain situations, write tests to improve regex
            $line = trim($line);
            $cmt  = substr($line, 0, 2);
            if ($cmt == '--' || trim($cmt) == '#') {
                continue;
            } 
#END:FIXME            
            $sql .= $line;

            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }

            // replace ; for MaxDB and Oracle
            if (($conf['db']['type'] == 'oci8_SGL') || ($conf['db']['type'] == 'odbc')){
                $sql = preg_replace("/;\s*$/", '', $sql);
            }

            // Execute the statement.
            $res = $dbh->query($sql);
            if (PEAR::isError($res, DB_ERROR_ALREADY_EXISTS)) {
                return $res;
            } elseif (DB::isError($res)) {

                // Print out info on bad statements
                echo '<pre>'.$res->getMessage().'</pre>';
                echo '<pre>'. $res->getUserInfo() . '</pre>';
            }
            $sql = '';
        }
        fclose($fp);
        return true;
    }
    
    /**
     * Returns true if database was setup correctly.
     *
     * Checks for existence of permission table and that records were inserted 
     *
     * @return mixed    True on success, PEAR error on failure
     */
    function verifyDbSetup()
    {
        $conf = $GLOBALS['_SGL']['CONF'];
        $dbh = & SGL_DB::singleton();
        $query = "SELECT COUNT(*) FROM {$conf['table']['permission']}";
        $res = $dbh->getAll($query);
        if (PEAR::isError($res, DB_ERROR_NOSUCHTABLE)) {
            return SGL::raiseError('No tables exist in DB - was schema created?', SGL_ERROR_DBFAILURE);
        }
        
        if (!(count($res))) {
            return SGL::raiseError('Perms inserts failed', SGL_ERROR_DBFAILURE);
        }
        return true;
    }
   
    /**
     * Regenerates dataobject entity files.
     *
     * @return string   Error message if any
     */
    function generateDataObjectEntities()
    {
        require_once 'DB/DataObject/Generator.php';
        ob_start();
        $generator = new DB_DataObject_Generator();
        $generator->start();
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }
    
    /**
     * Creates new or updates existing sequences, based on max(primary key).
     * Default is to act on all tables in db, unless specified in $tables.
     *
     * @access  public
     * @static
     * @param   mixed  $tables  string table name or array of string table names
     * @return  true | PEAR Error
     */    
    function rebuildSequences($tables = null)
    {
        $db =& SGL_DB::singleton();
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
                if ($table == 'sequence' || substr($table, $suffixRawStart) == $suffixRaw) {
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
                if ($table <> 'sequence') {
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
            $sth = $db->prepare('REPLACE INTO sequence (name, id) VALUES(?,?)');
            $db->executeMultiple($sth, $data);
            break;            
           
        case 'pgsql':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $db->getListOf('tables');
            foreach ($aTables as $table) {
                $primary_field = '';
                if ($table <> 'sequence') {
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
                        return SGL::raiseError('Rebuild failed ', SGL_ERROR_DBFAILURE);
                    }
                }
            }
            $success = $db->commit();
            $db->autoCommit(true);
            if (!$success) {
                return SGL::raiseError('Rebuild failed ', SGL_ERROR_DBTRANSACTIONFAILURE);
            }
            break;

        default:
            return SGL::raiseError('This feature currently is impmlemented only for MySQL, Oracle and PostgreSQL.', 
                SGL_ERROR_INVALIDCALL);
        }
        return true;
    }
}
?>
