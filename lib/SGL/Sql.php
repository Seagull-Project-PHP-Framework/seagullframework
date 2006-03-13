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
    function parseAndExecute($filename, $errorReporting = E_ALL)
    {
        //  Optionally shut off error reporting if logging isn't set up correctly yet
        error_reporting($errorReporting);

        if (! ($fp = fopen($filename, 'r')) ) {
            return false;
        }
        // Get database handle based on working config.ini
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        $sql = '';
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

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
            #$line = trim($line);
            $cmt  = substr($line, 0, 2);
            if ($cmt == '--' || preg_match("/^#/", $cmt)) {
                continue;
            }
#END:FIXME
            if (preg_match("/insert/i", $line) && preg_match("/\{SGL_NEXT_ID\}/", $line)) {
                $tableName = SGL_Sql::extractTableName($line);
                $nextId = $dbh->nextId($tableName);
                $line = SGL_Sql::rewriteWithAutoIncrement($line, $nextId);
            }

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

    function extractTableName($str)
    {
        $pattern = '/^(INSERT INTO)(\W+)(\w+)(\W+)(.*)/i';
        preg_match($pattern, $str, $matches);
        $tableName = $matches[3];
        return $tableName;
    }

    function rewriteWithAutoIncrement($str, $nextId)
    {
        $res = str_replace('{SGL_NEXT_ID}', $nextId, $str);
        return $res;
    }
}
?>