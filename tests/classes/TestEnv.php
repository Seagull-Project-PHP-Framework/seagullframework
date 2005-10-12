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
// | TestEnv.php                                                               |
// +---------------------------------------------------------------------------+
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

require_once STR_PATH . '/tests/classes/DB.php';

/**
 * A class for setting up and tearing down the testing environment.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */
class STR_TestEnv
{
        
    /**
     * A method for setting up a test database.
     */
    function setupDB()
    {   
        $conf = $GLOBALS['_STR']['CONF'];

        // Create a DSN to create DB (must not include database name from config)
        $dbType = $conf['database']['type'];        
        if ($dbType == 'mysql') {
            $dbType = 'mysql_SGL';
        }
    	$protocol = isset($conf['database']['protocol']) ? $conf['database']['protocol'] . '+' : '';
        $dsn = $dbType . '://' .
            $conf['database']['user'] . ':' .
            $conf['database']['pass'] . '@' .
            $protocol .
            $conf['database']['host'];
        $dbh = &STR_DB::singleton($dsn);
        
        $query = 'DROP DATABASE IF EXISTS ' . $conf['database']['name'];
        $result = $dbh->query($query);
        $query = 'CREATE DATABASE ' . $conf['database']['name'];
        $result = $dbh->query($query);
    }
    
    /**
     * A method for setting up the core tables in the test database.
     */
    function buildSchema()
    {
        $dbType = $GLOBALS['_STR']['CONF']['database']['type'];
        
        // get schema files
        $aSchemaFiles = $GLOBALS['_STR']['CONF']['schemaFiles'];
        
        if (is_array($aSchemaFiles) && count($aSchemaFiles)) {
            foreach ($aSchemaFiles as $schemaFile) {
                STR_TestEnv::parseAndExecute(STR_PATH .'/'. $schemaFile);
            }
        }
    }
    
    /**
     * A method for setting up the default data set for testing.
     */
    function loadData()
    {
        $dbType = $GLOBALS['_STR']['CONF']['database']['type'];
        
        // get schema files
        $aDataFiles = $GLOBALS['_STR']['CONF']['dataFiles'];
        
        if (is_array($aDataFiles) && count($aDataFiles)) {
            foreach ($aDataFiles as $dataFile) {
                STR_TestEnv::parseAndExecute(STR_PATH .'/'. $dataFile);
            }
        }
    }
    
    /**
     * A method for tearing down (dropping) the test database.
     */
    function teardownDB()
    {
        $conf = $GLOBALS['_STR']['CONF'];
        $dbh = &STR_DB::singleton();
        $query = 'DROP DATABASE ' . $conf['database']['name'];
        $result = $dbh->query($query);
    }
    
    /**
     * A method for re-parsing the testing environment configuration
     * file, to restore it in the event it needed to be changed
     * during a test.
     */
    function restoreConfig()
    {     
        // Re-parse the config file
        $newConf = @parse_ini_file(STR_TMP_DIR . '/test.conf.ini', true);
        foreach ($newConf as $configGroup => $configGroupSettings) {
            foreach ($configGroupSettings as $confName => $confValue) {
                $GLOBALS['_STR']['CONF'][$configGroup][$confName] = $confValue;
            }
        }
    }
    
    /**
     * A method for restoring the testing environment database setup.
     * This method can normaly be avoided by using transactions to
     * rollback database changes during testing, but sometimes a
     * DROP TEMPORARY TABLE (for example) is used during testing,
     * causing any transaction to be committed. In this case, this
     * method is needed to re-set the testing database.
     */
    function restore()
    {
        // Disable transactions, so that setting up the test environment works
        $dbh = &STR_DB::singleton();
        $query = 'SET AUTOCOMMIT=1';
        $result = $dbh->query($query);

        // Drop the database connection, so that temporary tables will be
        // removed (hack needed to overcome MySQL keeping temporary tables
        // if a database is dropped and re-created)
        $dbh->disconnect();
        $GLOBALS['_STR']['CONNECTIONS'] = array();

        // Re-set up the test environment
        STR_TestEnv::setup($GLOBALS['_STR']['layerEnv']);
    }
    
    /**
     * A method to set up the environment based on
     * the layer the test/s is/are in.
     *
     * @param string $layer The layer the test/s is/are in.
     */
    function setup($layer)
    {
        $type = $GLOBALS['_STR']['test_type'];
        $envType = $GLOBALS['_STR'][$type . '_layers'][$layer][1];
        
        // Ensure the config file is fresh
        STR_TestEnv::restoreConfig();
        
        // Setup the database, if needed
        if ($envType == DB_NO_TABLES) {
            STR_TestEnv::setupDB();
        } elseif ($envType == DB_WITH_TABLES) {
            STR_TestEnv::setupDB();
            STR_TestEnv::buildSchema();
        } elseif ($envType == DB_WITH_DATA || $envType == DB_WITH_DATA_AND_WEB) {
            STR_TestEnv::setupDB();
            STR_TestEnv::buildSchema();
            STR_TestEnv::loadData();
            
            //  if we're testing a sgl install, update sequences after loading data
            if (isset($GLOBALS['_SGL'])) {
                require_once SGL_CORE_DIR . '/Sql.php';
                SGL_Sql::rebuildSequences();   
            }
        }
        // Store the layer in a global variable, so the environment
        // can be completely re-built during tests using the
        // STR_TestEnv::restore() method
        $GLOBALS['_STR']['layerEnv'] = $layer;
    }
    
    /**
     * A method to tear down the environment based on
     * the layer the test/s is/are in.
     *
     * @param string $layer The layer the test/s is/are in.
     */
    function teardown($layer)
    {
        $type = $GLOBALS['_STR']['test_type'];
        $envType = $GLOBALS['_STR'][$type . '_layers'][$layer][1];
        if ($envType != NO_DB) {
            STR_TestEnv::teardownDB();
        }
    }
    
    /**
     * A method for rebuilding the database sequences.
     */
    function rebuildSequences()
    {
        
    }
    
    /**
     * A method for starting a transaction when testing database code.
     */
    function startTransaction()
    {
        $dbh = &STR_DB::singleton();
        $dbh->startTransaction();
    }
    
    /**
     * A method for ending a transaction when testing database code.
     */
    function rollbackTransaction()
    {
        $dbh = &STR_DB::singleton();
        $dbh->rollback();
    }
    
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
    function parseAndExecute($filename)
    {
        if (! ($fp = fopen($filename, 'r')) ) {
            return false;
        }
        // Get database handle based on working config.ini
        $dbh = & STR_DB::singleton();
        $sql = '';
        $conf = $GLOBALS['_STR']['CONF'];

        // Iterate through each line in the file.
        while (!feof($fp)) {

            // Read lines, concat together until we see a semi-colon
            $line = fgets($fp, 32768);
            $line = trim($line);
            $cmt  = substr($line, 0, 2);
            if ($cmt == '--' || trim($cmt) == '#') {
                continue;
            }
            $sql .= $line;

            if (!preg_match("/;\s*$/", $sql)) {
                continue;
            }

            // replace ; for MaxDB and Oracle
            if (($conf['database']['type'] == 'oci8_SGL') || ($conf['database']['type'] == 'odbc')){
                $sql = preg_replace("/;\s*$/", '', $sql);
            }
            
            // Execute the statement.
            $res = $dbh->query($sql);
            if (DB::isError($res, DB_ERROR_ALREADY_EXISTS)) {
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
    
}

?>