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
// | TestEnv.php                                                               |
// +---------------------------------------------------------------------------+
// | Authors:   Andrew Hill <andrew@m3.net>                                    |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            James Floyd <james@m3.net>                                     |
// +---------------------------------------------------------------------------+

require_once SGL_CORE_DIR . '/Sql.php';

class SGL_Task_SetupSimpleTestORM extends SGL_Task
{
    function run($conf = array())
    {
        $conf['debug']['dataObject'] = 0;
        $oTask = new SGL_Task_InitialiseDbDataObject();
        $ok = $oTask->run($conf);
        require_once 'DB/DataObject/Generator.php';

#FIXME:  add logic so entities aren't regenned on every request
        $generator = new DB_DataObject_Generator();
        $generator->start();
        $dsn = SGL_DB::getDsn(SGL_DSN_ARRAY);

        //  copy over links file
        $target = SGL_ENT_DIR . '/' . $dsn['database'] . '.links.ini';
        if (!file_exists($target)) {
            @copy(SGL_PATH . '/etc/links.ini.dist', $target);
        }
    }
}

/**
 * A class for setting up and tearing down the testing environment.
 *
 * @author     Andrew Hill <andrew@m3.net>
 */
class STR_TestEnv
{
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
                SGL_Sql::parse(STR_PATH .'/'. $schemaFile, E_ALL, array('SGL_Sql', 'execute'));
            }
        }
        //  ensure db_do environment setup correctly for simpletest
        $dbdo = new SGL_Task_SetupSimpleTestORM();
        $dbdo->run();
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
                SGL_Sql::parse(STR_PATH .'/'. $dataFile, E_ALL, array('SGL_Sql', 'execute'));
            }
        }
    }

    /**
     * A method for tearing down (dropping) the test database.
     */
    function teardownDB()
    {
        $conf = $GLOBALS['_STR']['CONF'];

        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');

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
        $newConf = @parse_ini_file(STR_TMP_DIR . '/test.conf.ini.php', true);
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
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
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

        } elseif ($envType == DB_WITH_TABLES) {
            STR_TestEnv::buildSchema();

        } elseif ($envType == DB_WITH_DATA || $envType == DB_WITH_DATA_AND_WEB) {
            STR_TestEnv::buildSchema();
            STR_TestEnv::loadData();

            //  if we're testing a sgl install, update sequences after loading data
            if (isset($GLOBALS['_SGL'])) {
                require_once SGL_CORE_DIR . '/Task/Install.php';
                SGL_Task_SyncSequences::run();
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
     * A method for starting a transaction when testing database code.
     */
    function startTransaction()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        $dbh->startTransaction();
    }

    /**
     * A method for ending a transaction when testing database code.
     */
    function rollbackTransaction()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        $dbh->rollback();
    }
}

?>