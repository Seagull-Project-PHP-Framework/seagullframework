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
// | Install.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
require_once dirname(__FILE__) . '/../Task.php';
require_once dirname(__FILE__) . '/../Install/Common.php';

/**
 * @package Task
 */
class SGL_Task_SetBaseUrlMinimal extends SGL_Task
{
    function run($data = array())
    {
        // no data if we're running installer
        if (!defined('SGL_INSTALLED')) {
            $conf = array(
                'site' =>   array(
                    'setup' => true,
                    'frontScriptName' => 'index.php',
                    'defaultModule' => 'default',
                    'defaultManager' => 'default',
                    ),
                'cookie' => array('name' => ''),
                );
            //  create default config values for install
            $c = &SGL_Config::singleton($autoLoad = false);
            $c->merge($conf);
        }
        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars();

        $url = new SGL_URL($_SERVER['PHP_SELF'], true, new SGL_UrlParser_SefStrategy());
        $err = $url->init();
        define('SGL_BASE_URL', $url->getBase());
    }
}

/**
 * @package Task
 */
class SGL_Task_SetTimeout extends SGL_Task
{
    function run($data)
    {
        if (array_key_exists('storeTranslationsInDB', $data)
            && $data['storeTranslationsInDB'] == 1)
        {
            $ok = set_time_limit(60*(count($data['installLangs'])));
        } else {
            $ok = set_time_limit(120);
        }
        return $ok;
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateConfig extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton($autoLoad = false);
        $oldConf = $c->getAll(); // save old config on re-install
        $conf = $c->load(SGL_ETC_DIR . '/default.conf.dist.ini');
        $c->replace($conf);
        $c->merge($oldConf); // overwrite with old values

        //  admin emails
        $c->set('email', array('admin' => $data['adminEmail']));
        $c->set('email', array('info' => $data['adminEmail']));
        $c->set('email', array('support' => $data['adminEmail']));

        // correct db prefix
        if (!empty($data['prefix']) && substr($data['prefix'], -1) != '_') {
            // enforce underscore in prefix
            $data['prefix'] .= '_';
        }

        // mysql storage
        $mysqlStorageEngine = !empty($data['dbMysqlDefaultStorageEngine'])
            ? $data['dbMysqlDefaultStorageEngine']
            : false;

        //  db details
        $c->set('db', array('prefix' => $data['prefix']));
        $c->set('db', array('host' => $data['host']));
        $c->set('db', array('name' => $data['name']));
        $c->set('db', array('user' => $data['databaseUser']));
        $c->set('db', array('pass' => $data['databaseUserPass']));
        $c->set('db', array('port' => $data['dbPort']['port']));
        $c->set('db', array('protocol' => $data['dbProtocol']['protocol']));
        $c->set('db', array('socket' => $data['socket']));
        $c->set('db', array('type' => $data['dbType']['type']));
        $c->set('db', array('postConnect' => $data['postConnect']));
        $c->set('db', array('mysqlDefaultStorageEngine' => $mysqlStorageEngine));
        $c->set('db', array('sepTableForEachSequence' => !$data['dbSequencesInOneTable']['dbSequences']));

        //  version
        $c->set('tuples', array('version' => $data['frameworkVersion']));

        //  demo mode
        if (is_file(SGL_VAR_DIR . '/DEMO_MODE')) {
            $c->set('tuples', array('demoMode' => true));
        }
        //  paths
        $c->set('path', array('installRoot' => $data['installRoot']));
        $c->set('path', array('webRoot' => $data['webRoot']));

        //  reset moduleOverride on re-install
        $c->set('path', array('moduleDirOverride' => ''));

        //  various
        $c->set('site', array('serverTimeOffset' => $data['serverTimeOffset']));
        $c->set('site', array('baseUrl' => SGL_BASE_URL));
        $c->set('site', array('name' => $data['siteName']));
        $c->set('site', array('description' => $data['siteDesc']));
        $c->set('site', array('keywords' => $data['siteKeywords']));
        $c->set('site', array('blocksEnabled' => true));
        $c->set('cookie', array('name' => $data['siteCookie']));

        //  store translations in db
        (array_key_exists('storeTranslationsInDB', $data)
                && $data['storeTranslationsInDB'] == 1)
            ? $c->set('translation', array('container' => 'db'))
            : $c->set('translation', array('container' => 'file'));

        //  add missing translations to db
        (array_key_exists('addMissingTranslationsToDB', $data)
                && $data['addMissingTranslationsToDB'] == 1)
            ? $c->set('translation', array('addMissingTrans' => true))
            : $c->set('translation', array('addMissingTrans' => false));

        //  translation fallback language
        $fallbackLang = str_replace('-', '_', $data['siteLanguage']);
        $c->set('translation', array('fallbackLang' => $fallbackLang));

        //  auto-correct frontScriptName for CGI users
        if (preg_match("/cgi|apache2filter/i", php_sapi_name())) {
            $c->set('site', array('frontScriptName' => 'index.php?'));
        }
        //  parse custom config overrides
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $kk => $vv) {
                    if ($c->exists(array($k => $kk))) {
                        $c->set($k, array($kk => $vv));
                    }
                }
            }
        }
        //  remove setup key before writing out file
        $ok = $c->remove(array('site', 'setup'));

        //  save
        $configFile = SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($configFile);

        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        //  store site language for post-install task
        $_SESSION['install_language'] = $data['siteLanguage'];

        //  and tz
        $_SESSION['install_timezone'] = $data['serverTimeOffset'];

        //  store old prefix for tables drop
        if (isset($oldConf['db']['prefix'])
                && $oldConf['db']['prefix'] != $data['prefix']) {
            $_SESSION['install_dbPrefix'] = $oldConf['db']['prefix'];
        }
        return $ok;
    }
}

/**
 * @package Task
 */
class SGL_UpdateHtmlTask extends SGL_Task
{
    function updateHtml($id, $displayHtml)
    {
        if (SGL::runningFromCli() || defined('SGL_ADMIN_REBUILD')) {
            return false;
        }

        if ($id == 'status') {
            $msg = $displayHtml;
            $displayHtml = '<span class=\\"pageTitle\\">' . $msg . '</span>';
        }
        echo "<script>
              document.getElementById('$id').innerHTML=\"$displayHtml\";
              </script>";

        //  echo 5K+ worth of spaces, since some browsers will buffer internally until they get 4K
        echo str_repeat(' ', 5120);
        flush();
    }

    function setup()
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        //  setup db type vars
        $this->dbType = $this->conf['db']['type'];

        switch ($this->conf['db']['type']) {
        case 'pgsql':
            $this->filename1 = '/schema.pg.sql';
            $this->filename2 = '/data.default.pg.sql';
            $this->filename3 = '/data.sample.pg.sql';
            $this->filename4 = '/data.block.add.pg.sql';
            $this->filename5 = '/data.custom.pg.sql';
            $this->filename6 = '/data.test.pg.sql';
            $this->filename7 = '/constraints.pg.sql';
            break;

        case 'mysql':
        case 'mysqli':
        case 'mysql_SGL':
        case 'mysqli_SGL':
            $this->filename1 = '/schema.my.sql';
            $this->filename2 = '/data.default.my.sql';
            $this->filename3 = '/data.sample.my.sql';
            $this->filename4 = '/data.block.add.my.sql';
            $this->filename5 = '/data.custom.my.sql';
            $this->filename6 = '/data.test.my.sql';
            $this->filename7 = '/constraints.my.sql';
            break;

        case 'oci8_SGL':
            $this->dbType = 'oci8'; // exception to dbType naming
            $this->filename1 = '/schema.oci.sql';
            $this->filename2 = '/data.default.oci.sql';
            $this->filename3 = '/data.sample.oci.sql';
            $this->filename4 = '/data.block.add.oci.sql';
            $this->filename5 = '/data.custom.oci.sql';
            $this->filename6 = '/data.test.oci.sql';
            $this->filename7 = '/constraints.oci.sql';
            break;
        }

        //  these hold what to display in results grid, depending on outcome
        $this->success = '<img src=\\"' . SGL_BASE_URL . '/themes/default/images/enabled.gif\\" border=\\"0\\" width=\\"22\\" height=\\"22\\">' ;
        $this->failure = '<span class=\\"error\\">ERROR</span>';
        $this->noFile  = '<strong>N/A</strong>';
    }
}

/**
 * @package Task
 */
class SGL_Task_DefineTableAliases extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();

        // get table prefix
        $prefix = $c->get(array('db' => 'prefix'));
        foreach ($data['aModuleList'] as $module) {
            $tableAliasIniPath = SGL_MOD_DIR . '/' . $module  . '/data/tableAliases.ini';
            if (file_exists($tableAliasIniPath)) {
                $aData = parse_ini_file($tableAliasIniPath);
                foreach ($aData as $k => $v) {
                    $c->set('table', array($k => $prefix . $v));
                }
            }
        }
        //  save
        $configFile = (SGL::runningFromCli())
            ? SGL_VAR_DIR . '/' . $data['serverName']. '.conf.php'
            : SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($configFile);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        return $ok;
    }
}

/**
 * @package Task
 */
class SGL_Task_DisableForeignKeyChecks extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        //  disable fk constraints if mysql (>= 4.1.x)
        if        ($this->conf['db']['type'] == 'mysql_SGL'
                || $this->conf['db']['type'] == 'mysql'
                || $this->conf['db']['type'] == 'mysqli'
                || $this->conf['db']['type'] == 'mysqli_SGL') {

            $dbh = & SGL_DB::singleton();
            if (PEAR::isError($dbh)) {
                SGL_Install_Common::errorPush($dbh);
                return $dbh;
            }
            $query = 'SET FOREIGN_KEY_CHECKS=0;';
            $res = $dbh->query($query);
            if (PEAR::isError($res)) {
                SGL_Install_Common::errorPush($res);
            }
            return $res;
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateDatabase extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        if ($this->conf['db']['type'] == 'pgsql') {
            $excludeDbName = false;
        } else {
            $excludeDbName = true;
        }
        $dsn = SGL_DB::getDsn(SGL_DSN_STRING, $excludeDbName);
        $dbh = & SGL_DB::singleton($dsn);
        $query = SGL_Sql::buildDbCreateStatement($this->conf['db']['type'],
            $dbh->quoteIdentifier($this->conf['db']['name']));
        $res = $dbh->query($query);
        if (PEAR::isError($res)) {
            SGL_Install_Common::errorPush($res);
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_DropDatabase extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $dbh = & SGL_DB::singleton();
        require_once SGL_CORE_DIR . '/Sql.php';
        $query = SGL_Sql::buildDbDropStatement($this->conf['db']['type'],
            $dbh->quoteIdentifier($this->conf['db']['name']));
        $res = $dbh->query($query);
        if (PEAR::isError($res)) {
            SGL_Install_Common::errorPush($res);
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_PrepareInstallationProgressTable extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        SGL_Install_Common::printHeader('Building Database');

        if (!(SGL::runningFromCli() || defined('SGL_ADMIN_REBUILD'))) {
            echo '<span class="title">Status: </span><span id="status"></span>
            <div id="progress_bar">
                <img src="' . SGL_BASE_URL . '/themes/default/images/progress_bar.gif" border="0" width="150" height="13">
            </div>
            <div id="additionalInfo"></div>';
            flush();
        }

        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {

            $this->setup();

            $statusText = 'Fetching modules';
            $this->updateHtml('status', $statusText);

            //  Print table shell, with module names; we'll update statuses as we execute sql below
            $out = '<table class="wide">
                        <tr>
                            <th class="alignCenter">Module</th>
                            ';
            if (!array_key_exists('useExistingData', $data) || $data['useExistingData'] == 0) {
            $out .=        '<th class="alignCenter">Drop Table</th>
                           ';
            }
            $out .=        '<th class="alignCenter">Create Table</th>
                            <th class="alignCenter">Load Default Data</th>
                            ';
            if (array_key_exists('insertSampleData', $data) && $data['insertSampleData'] == 1) {
                $out .=    '<th class="alignCenter">Load Sample Data</th>
                           ';
            }
            $out .=        '<th class="alignCenter">Add Constraints</th>
                        </tr>';

            if (!(SGL::runningFromCli() || defined('SGL_ADMIN_REBUILD'))) {
                echo $out;
            }

            foreach ($data['aModuleList'] as $module) {
                $out = '<tr>
                            <td class="title">' . ucfirst($module) . '</td>
                            ';
                if (!array_key_exists('useExistingData', $data) || $data['useExistingData'] == 0) {
                $out .=    '<td id="' . $module . '_drop" class="alignCenter"></td>
                           ';
                }
                $out .=    '<td id="' . $module . '_schema" class="alignCenter"></td>
                            <td id="' . $module . '_data" class="alignCenter"></td>
                            ';
                if (array_key_exists('insertSampleData', $data) && $data['insertSampleData'] == 1) {
                    $out .='<td id="' . $module . '_dataSample" class="alignCenter"></td>
                           ';
                }
                $out .= '<td id="' . $module . '_constraints" class="alignCenter"></td>
                     </tr>';

                if (!(SGL::runningFromCli() || defined('SGL_ADMIN_REBUILD'))) {
                    echo $out;
                }
            }

            if (!(SGL::runningFromCli() || defined('SGL_ADMIN_REBUILD'))) {
                echo '</table>';
                flush();
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_DropTables extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        require_once SGL_CORE_DIR . '/Sql.php';

        if (array_key_exists('createTables', $data) && $data['createTables'] == 1
                && (!array_key_exists('useExistingData', $data) || $data['useExistingData'] == 0)) {
            $this->setup();

            $statusText = 'dropping existing tables';
            $this->updateHtml('status', $statusText);

            $c   = &SGL_Config::singleton();
            $dbh = & SGL_DB::singleton();

            // set old db prefix if any
            if (isset($_SESSION['install_dbPrefix'])) {
                $currentPrefix = $c->get(array('db' => 'prefix'));
                $c->set('db', array('prefix' => $_SESSION['install_dbPrefix']));
            }

            //  drop 'sequence' table unless we're installing a module
            if ($this->conf['db']['type'] == 'mysql_SGL' && !array_key_exists('moduleInstall', $data)) {
                $aSeqTableName = SGL_Sql::extractTableNamesFromSchema(SGL_ETC_DIR . '/sequence.my.sql');
                foreach ($aSeqTableName as $seqTableName) {
                    $query = 'DROP TABLE '. $dbh->quoteIdentifier($seqTableName);
                    $seqResult = $dbh->query($query);
                    if (PEAR::isError($seqResult, DB_ERROR_NOSUCHTABLE)) {
                        SGL_Error::pop();
                    }
                }
            }
            //  Load each module's schema, if there is a sql file in /data
            foreach ($data['aModuleList'] as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's schema
                if (file_exists($modulePath . $this->filename1)) {
                    $aTableNames = SGL_Sql::extractTableNamesFromSchema($modulePath . $this->filename1);
                    $tableExists = true;
                    $dropSucceeded = true;
                    foreach ($aTableNames as $tableName) {
                        $query = 'DROP TABLE ' . $dbh->quoteIdentifier($tableName);
                        $result = $dbh->query($query);
                        if (PEAR::isError($result)) {
                            if (PEAR::isError($result, DB_ERROR_NOSUCHTABLE)) {
                                SGL_Error::pop();
                                $tableExists = false;
                            } else {
                                $dropSucceeded = false;
                            }
                        }
                    }
                    if (!$dropSucceeded) {
                        $displayHtml = $this->failure;
                    } elseif (!$tableExists) {
                        $displayHtml = $this->noFile;
                    } else {
                        $displayHtml = $this->success;
                    }

                    //  remove tablename in Config
                    if (isset($data['moduleInstall'])) {
                        foreach ($aTableNames as $tableName) {
                            $c->remove(array('table', $tableName));
                        }
                        //  save
                        $fileName = SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
                        $ok = $c->save($fileName);

                        if (PEAR::isError($ok)) {
                            SGL_Install_Common::errorPush($ok);
                        }
                    }
                    $this->updateHtml($module . '_drop', $displayHtml);
                } else {
                    $this->updateHtml($module . '_drop', $this->noFile);
                }
            }
            // remove translation tables and lang table
            if (!array_key_exists('moduleInstall', $data)) {
                $conf = $c->getAll();
                if ($conf['translation']['container'] == 'db') {
                    $statusText = 'dropping translation tables';
                    $this->updateHtml('status', $statusText);
                    $trans = &SGL_Translation::singleton('admin');
                    $aLangs = $trans->getLangs('ids');
                    if (!PEAR::isError($aLangs)) {
                        // removeme
                        if (empty($aLangs)) {
                            // basically $aLangs should be a PEAR_Error instance
                            // in that case, but calling method doesn't
                            // return it
                            SGL_Error::pop();
                        }
                        // dropping language tables
                        foreach ($aLangs as $langId) {
                            // force to drop translation table
                            $ok = $trans->removeLang($langId, $force = true);
                            if (PEAR::isError($ok, DB_ERROR_NOSUCHTABLE)) {
                                SGL_Error::pop();
                            }
                        }
                    } elseif (PEAR::isError($aLangs, DB_ERROR_NOSUCHTABLE)) {
                        SGL_Error::pop();
                    }
                    // drop language table
                    $langTable = &$trans->storage->options['langs_avail_table'];
                    $query = 'DROP TABLE ' . $dbh->quoteIdentifier($langTable);
                    $ok = $dbh->query($query);
                    if (PEAR::isError($ok, DB_ERROR_NOSUCHTABLE)) {
                        SGL_Error::pop();
                    }

                    // removeme: it looks like a hack
                    if (isset($currentPrefix)) {
                        $pattern   = "/^{$conf['db']['prefix']}/";
                        $langTable = preg_replace($pattern, '', $langTable);
                        $langTable = $currentPrefix . $langTable;
                    }
                }
            }

            // restore db prefix
            if (isset($currentPrefix)) {
                $c->set('db', array('prefix' => $currentPrefix));
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateTables extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        require_once SGL_CORE_DIR . '/Sql.php';
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            $this->setup();

            $statusText = 'creating and loading tables';
            $this->updateHtml('status', $statusText);

            //  load 'sequence' table
            if ($this->conf['db']['type'] == 'mysql_SGL' || $this->conf['db']['type'] == 'mysqli_SGL') {

                $result = SGL_Sql::parse(SGL_ETC_DIR . '/sequence.my.sql', 0, array('SGL_Sql', 'execute'));
            }
            //  Load each module's schema, if there is a sql file in /data
            foreach ($data['aModuleList'] as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's schema
                if (file_exists($modulePath . $this->filename1)) {
                    SGL::logMessage("creating schemas for '$module' module", PEAR_LOG_DEBUG);
                    $result = SGL_Sql::parse($modulePath . $this->filename1, 0, array('SGL_Sql', 'execute'));
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_schema', $displayHtml);
                } else {
                    $this->updateHtml($module . '_schema', $this->noFile);
                }
            }

            //  catch 'table already exists' error
            if (isset($result) && PEAR::isError($result, DB_ERROR_ALREADY_EXISTS)) {
                if (SGL::runningFromCli() || defined('SGL_ADMIN_REBUILD')) {
                    die('Tables already exist, DB error');
                } else {
                    $this->updateHtml('status', 'Tables already exist');
                    $body = 'It appears that the schema already exists.  Click <a href=\\"index.php\\">here</a> to return to the configuration screen and choose \\"Only set DB connection details\\".';
                    $this->updateHtml('additionalInfo', $body);
                    $this->updateHtml('progress_bar', '');
                    exit;
                }
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_LoadDefaultData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $result = false;
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            $this->setup();

            $statusText = 'loading default data';
            $this->updateHtml('status', $statusText);

            //  Go back and load each module's default data, if there is a sql file in /data
            foreach ($data['aModuleList'] as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';
                //  Load the module's data
                if (file_exists($modulePath . $this->filename2)) {
                    SGL::logMessage("loading default data for '$module'", PEAR_LOG_DEBUG);
                    $result = SGL_Sql::parse($modulePath . $this->filename2, 0, array('SGL_Sql', 'execute'));
                    if (PEAR::isError($result)) {
                        return $result;
                    }
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_data', $displayHtml);
                } else {
                    $this->updateHtml($module . '_data', $this->noFile);
                }
            }
        }
        return $result;
    }
}

/**
 * @package Task
 */
class SGL_Task_LoadSampleData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $result = false;
        if (array_key_exists('insertSampleData', $data) && $data['insertSampleData'] == 1) {
            $this->setup();

            $statusText = 'loading sample data';
            $this->updateHtml('status', $statusText);

            //  Go back and load each module's default data, if there is a sql file in /data
            foreach ($data['aModuleList'] as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's data
                if (file_exists($modulePath . $this->filename3)) {
                    SGL::logMessage("loading sample data for '$module'", PEAR_LOG_DEBUG);
                    $result = SGL_Sql::parse($modulePath . $this->filename3, 0, array('SGL_Sql', 'execute'));
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_dataSample', $displayHtml);
                } else {
                    $this->updateHtml($module . '_dataSample', $this->noFile);
                }
            }
        }
        return $result;
    }
}

/**
 * @package Task
 */
class SGL_Task_LoadCustomData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $this->setup();
        $statusText = 'loading custom data';
        $this->updateHtml('status', $statusText);
        $result = false;

        //  Go back and load each module's custom data, if there is a custom sql file in /data
        foreach ($data['aModuleList'] as $module) {
            $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';
            //  Load the module's custom data if exists
            if (file_exists($modulePath . $this->filename5)) {
                $result = SGL_Sql::parse($modulePath . $this->filename5, 0, array('SGL_Sql', 'execute'));
            }
        }
        return $result;
    }
}

/**
 * @package Task
 */
class SGL_Task_RemoveDefaultData extends SGL_Task
{
    function run($data)
    {
        require_once SGL_MOD_DIR . '/default/classes/DefaultDAO.php';
        $da = & DefaultDAO::singleton();

        //  get perms associated with module
        $aPermNames = $da->getPermNamesByModuleId($data['moduleId']);

        //  delete role_permissions
        foreach ($aPermNames as $permName) {
            $permId = $da->getPermissionIdByPermName($permName);
            $ok = $da->deleteRolePermissionByPermId($permId);
        }
        //  then delete perms
        $ok = $da->deletePermsByModuleId($data['moduleId']);
    }
}

/**
 * @package Task
 */
class SGL_Task_LoadBlockData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $result = false;
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1
            && (!array_key_exists('useExistingData', $data) || $data['useExistingData'] == 0)) {
            $this->setup();

            $statusText = 'loading block data';
            $this->updateHtml('status', $statusText);

            //  Go back and load each module's default data, if there is a sql file in /data
            foreach ($data['aModuleList'] as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's data
                if (file_exists($modulePath . $this->filename4)) {
                    $result = SGL_Sql::parse($modulePath . $this->filename4, 0, array('SGL_Sql', 'execute'));
                }
            }
        }
        return $result;
    }
}

/**
 * @package Task
 */
class SGL_Task_RemoveBlockData extends SGL_UpdateHtmlTask
{
    function run($data)
    {

        $this->setup();

        //  Go back and load each module's default data, if there is a sql file in /data
        foreach ($data['aModuleList'] as $module) {
            $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

            //  remove the module's block data
            //  switch 'add' to 'remove'
            $filename = str_replace('add', 'remove', $this->filename4);
            if (is_file($modulePath . $filename)) {
                $result = SGL_Sql::parse($modulePath . $filename, 0, array('SGL_Sql', 'execute'));
            }

        }
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateConstraints extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            $this->setup();

            $statusText = 'loading constraints';
            $this->updateHtml('status', $statusText);

            //  Go back and load module foreign keys/constraints, if any
            foreach ($data['aModuleList'] as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';
                if (file_exists($modulePath . $this->filename7)) {
                    $result = SGL_Sql::parse($modulePath . $this->filename7, 0, array('SGL_Sql', 'execute'));
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_constraints', $displayHtml);
                } else {
                    $this->updateHtml($module . '_constraints', $this->noFile);
                }
            }
        }
    }
}

define('SGL_NODE_USER',  2); // nested set parent_id
define('SGL_NODE_ADMIN', 4); // nested set parent_id
define('SGL_NODE_GROUP', 1);

/**
 * @package Task
 */
class SGL_Task_BuildNavigation extends SGL_UpdateHtmlTask
{
    var $groupId = null;
    var $childId = null;

    function run($data)
    {
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1
                && (!array_key_exists('useExistingData', $data) || $data['useExistingData'] == 0)
                && SGL_Config::get('navigation.driver') != 'ArrayDriver') {
            require_once SGL_MOD_DIR . '/navigation/classes/NavigationDAO.php';
            $da = & NavigationDAO::singleton();

            foreach ($data['aModuleList'] as $module) {
                $navigationPath = SGL_MOD_DIR . '/' . $module  . '/data/navigation.php';
                if (file_exists($navigationPath)) {
                    require_once $navigationPath;
                    if (isset($aSections)) {
                        foreach ($aSections as $aSection) {

                            //  check if section is designated as child to last insert
                            if ($aSection['parent_id'] == SGL_NODE_GROUP) {
                                $aSection['parent_id'] = $this->groupId;
                            }
                            $id = $da->addSimpleSection($aSection);
                            if (!PEAR::isError($id)) {
                                if ($aSection['parent_id'] == SGL_NODE_ADMIN
                                        || $aSection['parent_id'] == SGL_NODE_USER) {
                                    $this->groupId = $id;
                                } else {
                                    $this->childId = $id;
                                }
                            } else {
                                SGL_Install_Common::errorPush($id);
                            }
                        }
                    }
                }
            }
        } elseif (SGL_Config::get('navigation.driver') == 'ArrayDriver') {
            require_once SGL_MOD_DIR . '/navigation/classes/ArrayDriver.php';

            $aNodes = ArrayDriver::getNavigationStructure();
            $ok = ArrayDriver::saveNodes($aNodes);
            if (!$ok) {
                SGL::raiseError('ArrayDriver: can\'t save nodes');
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_RemoveNavigation extends SGL_Task
{
    function run($data)
    {
        require_once SGL_MOD_DIR . '/navigation/classes/NavigationDAO.php';
        $da = & NavigationDAO::singleton();

        foreach ($data['aModuleList'] as $module) {
            $navigationPath = SGL_MOD_DIR . '/' . $module  . '/data/navigation.php';
            if (file_exists($navigationPath)) {
                require_once $navigationPath;
                foreach ($aSections as $aSection) {
                    $sectionId = $da->getSectionIdByTitle($aSection['title']);
                    if ($sectionId) {
                        $ok = $da->deleteSectionById($sectionId);
                    }
                }
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_DeregisterModule extends SGL_Task
{
    function run($data)
    {
        $ok = false;
        foreach ($data['aModuleList'] as $module) {
            $rm = DB_DataObject::factory(SGL_Config::get('table.module'));
            $rm->get($data['moduleId']);
            $ok = $rm->delete();
        }
        return $ok;
    }
}


/**
 * @package Task
 */
class SGL_Task_EnableDebugBlock extends SGL_Task
{
    function run($data)
    {
        require_once SGL_MOD_DIR . '/block/classes/BlockDAO.php';
        $da = & BlockDAO::singleton();
        if (!empty($da->conf['debug']['enableDebugBlock'])) {
            $oBlock = new stdClass();
            $oBlock->name = 'Default_Block_Debug';
            $oBlock->title = 'Debug Block';
            $oBlock->is_enabled = 1;
            $oBlock->position = 'Left';
            $oBlock->sections = array(0); // all
            $oBlock->roles = array(SGL_ADMIN);
            $ok = $da->addBlock($oBlock);
        }
    }
}


/**
 * @package Task
 */
class SGL_Task_LoadTranslations extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $aLangOptions = SGL_Util::getLangsDescriptionMap();

        if (array_key_exists('storeTranslationsInDB', $data) && $data['storeTranslationsInDB'] == 1) {
            $trans = & SGL_Translation::singleton('admin');

            $this->setup();

            $statusText = 'loading languages';
            $this->updateHtml('status', $statusText);

            //  fetch available languages
            $availableLanguages = & $GLOBALS['_SGL']['LANGUAGE'];

            //  add languages to config
            $this->installedLanguages = $data['installLangs'];
            $langString = (is_array($data['installLangs']))
                ? implode(',', str_replace('-', '_', $data['installLangs']))
                : '';
            $c->set('translation', array('installedLanguages' => $langString));

            //  iterate through languages adding to langs table
            foreach ($data['installLangs'] as $aLang) {
                $globalLangFile = $availableLanguages[$aLang][1] .'.php';
                $langID = str_replace('-', '_', $aLang);

                // skip language creation during module install
                if (empty($data['skipLangTablesCreation'])) {
                    $prefix = $this->conf['db']['prefix'] .
                        $this->conf['translation']['tablePrefix'] . '_';
                    $encoding = substr($aLang, strpos('-', $aLang));
                    $langData = array(
                        'lang_id' => $langID,
                        'table_name' => $prefix . $langID,
                        'meta' => '',
                        'name' => $aLangOptions[$aLang],
                        'error_text' => 'not available',
                        'encoding' => $encoding
                    );

                    //  switch phptype to mysql when using mysql_SGL otherwise the langs table
                    //  and index's will not be created.
                    if (($oldType = $trans->storage->db->phptype) == 'mysql_SGL') {
                        $trans->storage->db->phptype = 'mysql';
                    }
                    $result = $trans->addLang($langData);
                    $trans->storage->db->phptype = $oldType;
                }

                //  iterate through modules
                foreach ($data['aModuleList'] as $module) {
                    $statusText = 'loading languages - '. $module .' ('. str_replace('_','-', $langID) .')';
                    $this->updateHtml('status', $statusText);

                    $modulePath = SGL_MOD_DIR . '/' . $module  . '/lang';

                    if (file_exists($modulePath .'/'. $globalLangFile)) {
                        //  load current module lang file
                        require $modulePath .'/'. $globalLangFile;

                        //  defaultWords clause
                        $words = ($module == 'default') ? $defaultWords : $words;

                        //  add current translation to db
                        if (count($words)) {
                            foreach ($words as $tk => $tValue) {
                                if (is_array($tValue) && $tk) { // if an array

                                    //  create key|value|| string
                                    $value = '';
                                    foreach ($tValue as $k => $aValue) {
                                        $value .= $k . '|' . $aValue .'||';
                                    }
                                    $string = array($langID => $value);
                                    $result = $trans->add($tk, $module, $string);
                                } elseif ($tk && $tValue) {
                                    $string = array($langID => $tValue);
                                    $result =  $trans->add($tk, $module, $string);
                                }
                            }
                            unset($words);
                        }
                    }
                }
            }
        } else {
            //  set installed languages
            $installedLangs = (is_array($aLangOptions))
                ? str_replace('-', '_', implode(',', array_keys($aLangOptions)))
                : '';

            $c->set('translation', array('installedLanguages' => $installedLangs));
        }
        $fileName = SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($fileName);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        return $ok;
    }
}

/**
 * @package Task
 */
class SGL_Task_EnableForeignKeyChecks extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $res = true;

        //  re-enable fk constraints if mysql (>= 4.1.x)
        if        ($this->conf['db']['type'] == 'mysql_SGL'
                || $this->conf['db']['type'] == 'mysql'
                || $this->conf['db']['type'] == 'mysqli'
                || $this->conf['db']['type'] == 'mysqli_SGL') {


            $dbh = & SGL_DB::singleton();
            $query = 'SET FOREIGN_KEY_CHECKS=1;';
            $res = $dbh->query($query);
        }
        return $res;
    }
}

/**
 * @package Task
 */
class SGL_Task_VerifyDbSetup extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $this->setup();

        //  verify db
        $dbh = & SGL_DB::singleton();
        $query = "SELECT COUNT(*) FROM {$this->conf['table']['permission']}";
        $res = $dbh->getAll($query);
        if (PEAR::isError($res, DB_ERROR_NOSUCHTABLE)) {
            SGL_Install_Common::errorPush(
                PEAR::raiseError('No tables exist in DB - was schema created?'));
        } elseif (!(count($res))) {
            SGL_Install_Common::errorPush(
                PEAR::raiseError('Perms inserts failed', SGL_ERROR_DBFAILURE));
        }

        //  create error message if appropriate
        if (SGL_Install_Common::errorsExist()) {
            $statusText = 'Some problems were encountered';
            $this->updateHtml('status', $statusText);
            $body = 'please diagnose and try again';
        } else {
            if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {

                //  note: must all be on one line for DOM text replacement
                $message = 'Database initialisation complete!';
                $this->updateHtml('status', $message);
                $body = '<p><a href=\\"' . SGL_BASE_URL . '/setup.php?start\\">LAUNCH SEAGULL</a> </p>NOTE: <strong>N/A</strong> indicates that a schema or data is not needed for this module';

            //  else only a DB connect was requested
            } else {
                $statusText = 'DB setup succeeded';
                $statusText .= ', schema creation skipped';
                $this->updateHtml('status', $statusText);
                $body = '<p><a href=\\"' . SGL_BASE_URL . '/setup.php?start\\">LAUNCH SEAGULL</a> </p>';
            }
        }

        //  done, create "launch seagull" link
        $this->updateHtml('additionalInfo', $body);
        $this->updateHtml('progress_bar', '');

        SGL_Install_Common::printFooter();
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateFileSystem extends SGL_Task
{
    function run($data)
    {
        require_once 'System.php';
        $err = false;

        //  pass paths as arrays to avoid widows space parsing prob
        //  create cache dir
        if (!is_dir(SGL_CACHE_DIR)) {
            $cacheDir = System::mkDir(array(SGL_CACHE_DIR));
            if (is_dir($cacheDir)) {
                @chmod($cacheDir, 0777);
            }
            if (!($cacheDir)) {
                SGL_Install_Common::errorPush(PEAR::raiseError('Problem creating cache dir'));
            }
        }

        //  create entities dir
        if (!is_dir(SGL_ENT_DIR)) {
            $entDir = System::mkDir(array(SGL_ENT_DIR));
            if (is_dir($entDir)) {
                @chmod($entDir, 0777);
            }
            if (!($entDir)) {
                SGL_Install_Common::errorPush(PEAR::raiseError('Problem creating entity dir'));
            }
        }

        //  create tmp dir, mostly for sessions
        if (!is_writable(SGL_TMP_DIR)) {

            $tmpDir = System::mkDir(array(SGL_TMP_DIR));
            $htAccessContent = <<< EOF
Order allow,deny
Deny from all
EOF;
            $ok = file_put_contents(SGL_TMP_DIR . '/.htaccess', $htAccessContent);
            if (!$tmpDir) {
                $err = SGL::raiseError('The tmp directory does not '.
                    'appear to be writable, please give the webserver permissions to write to it');
                SGL_Install_Common::errorPush($err);
            }
        }
        return $err;
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateDataObjectEntities extends SGL_Task
{
    function run($data = null)
    {
        $err = false;
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  init DB_DataObject
        $oTask = new SGL_Task_InitialiseDbDataObject();
        $ok = $oTask->run($conf);

        require_once 'DB/DataObject/Generator.php';
        ob_start();
        // remove original dbdo keys file as it is unable to update an existing file
        $keysFile = SGL_ENT_DIR . '/' . $conf['db']['name'] . '.ini';
        if (is_file($keysFile)) {
            $ok = unlink($keysFile);
        }
        // drop old entities on re-install
        if (isset($_SESSION['install_dbPrefix'])) {
            if (is_writable(SGL_ENT_DIR)) {
                if ($dh = opendir(SGL_ENT_DIR)) {
                    $prefix = $_SESSION['install_dbPrefix'];
                    while (($file = readdir($dh)) !== false) {
                        if ($file != '.' && $file != '..'
                                && substr($file, -3) == 'php'
                                && substr($file, 0, strlen($prefix)) == ucfirst($prefix)) {
                            $ok = unlink(SGL_ENT_DIR . '/' . $file);
                        }
                    }
                }
            }
        }
        $generator = new DB_DataObject_Generator();
        $generator->start();
        $out = ob_get_contents();
        ob_end_clean();

        if (PEAR::isError($out)) {
            $err =  PEAR::raiseError('generating DB_DataObject entities failed');
            SGL_Install_Common::errorPush($err);
        }
        return $err;
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateDataObjectLinkFile extends SGL_Task
{
    function run($data = null)
    {
        $err = false;
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        // original dbdo links file
        $linksFile = SGL_ENT_DIR . '/' . $conf['db']['name'] . '.links.ini';

        // read existing data if any
        if (is_readable($linksFile)) {
            $aOrigData = parse_ini_file($linksFile, true);
            // only remove when not installing modules, ie for sgl-rebuild
            if (empty($data['moduleInstall']) && is_writable($linksFile)) {
                unlink($linksFile);
            }
        }
        $linkData = '';
        foreach ($data['aModuleList'] as $module) {
            if ($module == 'block' && in_array('cms', $data['aModuleList'])) {
                $linksPath = SGL_MOD_DIR . '/' . $module  . '/data/dataobjectLinks.cms.ini';
            } else {
                $linksPath = SGL_MOD_DIR . '/' . $module  . '/data/dataobjectLinks.ini';
            }
            if (is_file($linksPath)) {
                $linkData .= file_get_contents($linksPath);
                $linkData .= "\n\n";
            }
        }
        if (!empty($linkData)) {
            //  first check to ensure key doesn't exist if a module is being installed
            if (!empty($data['moduleInstall'])) {
                $aNewData = parse_ini_file($linksPath, true);

                //  compare with existing data if there is any
                if (!empty($aOrigData)) {
                    foreach ($aNewData as $key => $aValues) {
                        $tableName = $conf['db']['prefix'] . $key;
                        if (array_key_exists($tableName, $aOrigData)) {
                            //  key already exists, so return instead of adding it
                            return;
                        }
                    }
                }
            }
            // we don't forget about prefixes
            if (!empty($conf['db']['prefix'])) {
                // prefix containers
                $linkData = preg_replace('/\[(\w+)\]/i',
                    '[' . SGL_Sql::addTablePrefix('$1') . ']',  $linkData);
                // prefix references
                $linkData = preg_replace('/(\w+):/i',
                    SGL_Sql::addTablePrefix('$1') . ':' , $linkData);
            }
            if (is_writable($linksFile) || !file_exists($linksFile)) {
                if (!$handle = fopen($linksFile, 'a+')) {
                    $err = PEAR::raiseError('could not open links file for writing');
                    SGL_Install_Common::errorPush($err);
                }
                if (fwrite($handle, $linkData) === false) {
                    $err = PEAR::raiseError('could not write to file' . $linksFile);
                    SGL_Install_Common::errorPush($err);
                }
            }
        }
        return $err;
    }
}

/**
 * @package Task
 */
class SGL_Task_SymLinkWwwData extends SGL_Task
{
    function run($data = null)
    {
        $ret = true;
        foreach ($data['aModuleList'] as $module) {
            $wwwDir = SGL_MOD_DIR . '/' . $module  . '/www';
            if (file_exists($wwwDir)) {
                if (is_writable(SGL_WEB_ROOT)) {

                    // windows
                    if (strpos(PHP_OS, 'WIN') !== false) {

                        // if linkd binary is present
                        $ret = symlink($wwwDir, SGL_WEB_ROOT . "/$module");

                        //  otherwise just copy
                        if (!$ret) {
                            require_once SGL_CORE_DIR . '/File.php';
                            $ret = SGL_File::copyDir($wwwDir, SGL_WEB_ROOT . "/$module");
                        }
                    } elseif (is_link(SGL_WEB_ROOT . "/$module")) {
                            $ret = PEAR::raiseError('A www directory was detected in ' .
                                ' one of the modules therefore an attempt to create ' .
                                ' a corresponding symlink was made ' .
                                ' but the symlink already exists ' .
                                ' in seagull/www');
                    } else {
                        $ret = symlink($wwwDir, SGL_WEB_ROOT . "/$module");
                    }

                } else {
                    $ret = PEAR::raiseError('A www directory was detected in one of the modules '.
                    ' but the required webserver' .
                    ' write perms on seagull/www do not exist, so the symlink could'.
                    ' not be created');
                }
            }
        }
        return $ret;
    }
}

/**
 * @package Task
 */
class SGL_Task_UnLinkWwwData extends SGL_Task
{
    function run($data = null)
    {
        $ret = true;
        foreach ($data['aModuleList'] as $module) {
            if (empty($module)) {
                continue;
            }
            $wwwDir = SGL_MOD_DIR . '/' . $module  . '/www';
            // if we're windows
            if ((strpos(PHP_OS, 'WIN') !== false) && is_dir(SGL_WEB_ROOT . "/$module")) {
                require_once SGL_CORE_DIR . '/File.php';
                if (readlink(SGL_WEB_ROOT . "/$module")) {
                    SGL_File::rmDir(SGL_WEB_ROOT . "/$module");
                } else {
                    SGL_File::rmDir(SGL_WEB_ROOT . "/$module", '-r');
                }
            } elseif (file_exists($wwwDir)) {
                if (is_writable(SGL_WEB_ROOT)) {
                    if (is_link(SGL_WEB_ROOT . "/$module")) {
                        unlink(SGL_WEB_ROOT . "/$module");
                    }
                } else {
                    $ret = PEAR::raiseError('An attempt to remove an existing ' .
                        ' symlink failed, the webserver no longer has ' .
                        ' required write perms on seagull/www dir');
                }
            }
        }
        return $ret;
    }
}


/**
 * @package Task
 */
class SGL_Task_AddTestDataToConfig extends SGL_UpdateHtmlTask
{
    /**
     * Updates test config file.
     *
     * 1. Reads ini file with php extension (used for security)
     * 2. Updates keys in file
     * 3. Saves file as ini
     * 4. Modifies file adding security
     * 5. Changes extension to php
     * 6. Removes saved ini file
     *
     * @param unknown_type $data
     */
    function run($data = null)
    {
        $this->setup();

        //  get relevant module directory
        $globalConf = SGL_Config::singleton();
        $moduleDir = ($globalConf->get(array('path' => 'moduleDirOverride')))
            ? $globalConf->get(array('path' => 'moduleDirOverride'))
            : 'modules';

        $c = new SGL_Config();
        foreach ($data['aModuleList'] as $module) {
            $dataDir = SGL_MOD_DIR . '/' . $module  . '/data';
            //  get available data files
            $aFiles = array();
            if (is_file($dataDir . $this->filename1)) {
                $aFiles['schema'] = 1;
            }
            if (is_file($dataDir . $this->filename2)) {
                $aFiles['dataDefault'] = 1;
            }
            if (is_file($dataDir . $this->filename6)) {
                $aFiles['dataTest'] = 1;
            }
            //  load current test config
            if (is_file(SGL_VAR_DIR . '/test.conf.ini.php')) {
                $aTestData = parse_ini_file(SGL_VAR_DIR . '/test.conf.ini.php', true);
                //  and add schema/data files
                $update = false;
                if (isset($aFiles['schema'])) {
                    $nextId = $this->getNextKey($aTestData['schemaFiles']);
                    $aTestData['schemaFiles']['file'.$nextId] =  $moduleDir . '/' . $module  . '/data/schema.my.sql';
                    $update = true;
                }
                if (isset($aFiles['dataDefault'])) {
                    $nextId = $this->getNextKey($aTestData['dataFiles']);
                    $aTestData['dataFiles']['file'.$nextId] =  $moduleDir . '/' . $module  . '/data/data.default.my.sql';
                    $update = true;
                }
                if (isset($aFiles['dataTest'])) {
                    $nextId = $this->getNextKey($aTestData['dataFiles']);
                    $aTestData['dataFiles']['file'.$nextId] =  $moduleDir . '/' . $module  . '/data/data.test.my.sql';
                    $update = true;
                }
                if ($update) {
                    $c->replace($aTestData);
                    $ok = $c->save(SGL_VAR_DIR . '/test.conf.ini');
                    SGL_Util::makeIniUnreadable(SGL_VAR_DIR . '/test.conf.ini');
                }
            }
        }
    }

    function getNextKey($aKeys)
    {
        $keys = array_keys($aKeys);
        $out = array();
        foreach ($keys as $k) {
            preg_match("/[0-9].*/", $k, $matches);
            $out[] = $matches[0];
        }
        return (max($out)) +1;
    }
}

/**
 * @package Task
 */
class SGL_Task_RemoveTestDataFromConfig extends SGL_UpdateHtmlTask
{
    function run($data = null)
    {
        if (is_file(SGL_VAR_DIR . '/test.conf.ini.php')) {
            $this->setup();
            $c = new SGL_Config();
            foreach ($data['aModuleList'] as $module) {
                //  load current test config
                $aTestData = parse_ini_file(SGL_VAR_DIR . '/test.conf.ini.php', true);
                //  and add schema/data files
                $update = false;
                foreach ($aTestData['schemaFiles'] as $k => $line) {
                    if (preg_match("/$module/", $line)) {
                        unset($aTestData['schemaFiles'][$k]);
                        $update = true;
                    }
                }
                foreach ($aTestData['dataFiles'] as $k => $line) {
                    if (preg_match("/$module/", $line)) {
                        unset($aTestData['dataFiles'][$k]);
                        $update = true;
                    }
                }
                if ($update) {
                    $c->replace($aTestData);
                    $ok = $c->save(SGL_VAR_DIR . '/test.conf.ini');
                    SGL_Util::makeIniUnreadable(SGL_VAR_DIR . '/test.conf.ini');
                }
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_SyncSequences extends SGL_Task
{
    /**
     * Creates new or updates existing sequences, based on max(primary key).
     * Default is to act on all tables in db, unless specified in $tables.
     *
     * @access  public
     * @static
     * @param   mixed  $tables  string table name or array of string table names
     * @return  true | PEAR Error
     * @todo we need to reinstate this method's ability to receive an array of tables as an argument
     */
    function run($data = null)
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  postgres sequence routine creates errors, get initial count
        $initialErrorCount = SGL_Error::count();
        $tables = null;
        $phptype = $dbh->phptype;

        // if it is SGL MySQL driver
        if (strpos($dbh->phptype, 'mysql') === 0
                && strpos($dbh->phptype, '_SGL')) {
            // and all sequences should NOT be in one table
            if (SGL_Config::get('db.sepTableForEachSequence')) {
                // fake it as regular MySQL driver
                $phptype = str_replace('_SGL', '', $dbh->phptype);
            }
        }

        switch ($phptype) {

        case 'mysql':
        case 'mysqli':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $dbh->getListOf('tables');

            //  "%_seq" is the default, but in case they screwed around with PEAR::DB...
            $suffix = $dbh->getOption('seqname_format');
            $suffixRaw = str_replace('%s', '', $suffix);
            $suffixRawStart = (0 - strlen($suffixRaw));

            // get views
            $aViews = SGL_Task_SyncSequences::_getViews();
            if (PEAR::isError($aViews)) {
                SGL_Error::pop();
                $aViews = array(); // no views by default
            }

            foreach ($aTables as $table) {
                $primary_field = '';
                //  we only build sequences for tables that are not sequences themselves
                if ($table == $conf['table']['sequence'] || substr($table, $suffixRawStart) == $suffixRaw) {
                    continue;
                }

                // skip views
                if (in_array($table, $aViews)) {
                    continue;
                }

                $info = $dbh->tableInfo($dbh->quoteIdentifier($table));
                foreach ($info as $field) {
                    if (eregi('primary_key', $field['flags'])) {
                        $primary_field = $field['name'];
                        break;
                    }
                }
                if ($primary_field != '') {
                    $maxId = $dbh->getOne('SELECT MAX(' . $primary_field . ') FROM ' . $dbh->quoteIdentifier($table) . ' WHERE 1');
                    if (!is_null($maxId) && (is_numeric($maxId))) {
                        $data[] = array($table, $maxId);
                    }
                }
            }

            foreach ($data as $k) {
                $tableName = $k[0];
                $seqName = $dbh->quoteIdentifier(sprintf($suffix, $tableName));
                $maxVal   = $k[1];
                $currVal = $dbh->nextId($tableName, true);
                $sql = 'UPDATE ' . $seqName . ' SET id=' . $maxVal . ' WHERE id=' . $currVal;
                $result = $dbh->query($sql);
            }
            break;

        case 'mysqli_SGL':
        case 'mysql_SGL':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $dbh->getListOf('tables');

            //  make sure sequence table exists
            if (!in_array('sequence',$aTables)) {
                require_once SGL_CORE_DIR . '/Sql.php';
                SGL_Sql::parse(SGL_ETC_DIR . '/sequence.my.sql', 0, array('SGL_Sql', 'execute'));
            }

            // get views
            $aViews = SGL_Task_SyncSequences::_getViews();
            if (PEAR::isError($aViews)) {
                SGL_Error::pop();
                $aViews = array(); // no views by default
            }

            foreach ($aTables as $table) {
                // skip views
                if (in_array($table, $aViews)) {
                    continue;
                }
                $primary_field = '';
                if ($table != $conf['table']['sequence']) {
                    $info = $dbh->tableInfo($dbh->quoteIdentifier($table));
                    foreach ($info as $field) {
                        if (isset($field['flags']) && preg_match('/primary_key/', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field != '') {
                        $maxId = $dbh->getOne('SELECT MAX(' . $primary_field .
                            ') FROM ' . $dbh->quoteIdentifier($table) . ' WHERE 1');
                        if (is_numeric($maxId)) {
                            $data[] = array($table, $maxId);
                        } else {
                            $data[] = array($table, 0);
                        }
                    } else {
                        $data[] = array($table, 0);
                    }
                }
            }
            $sth = $dbh->prepare("REPLACE INTO {$conf['table']['sequence']} (name, id) VALUES(?,?)");
            $dbh->executeMultiple($sth, $data);
            break;

        case 'pgsql':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $dbh->getListOf('tables');
            foreach ($aTables as $table) {
                $primary_field = '';
                if ($table != $conf['table']['sequence']) {
                    $info = $dbh->tableInfo($dbh->quoteIdentifier($table));
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field != '') {
                        $data[] = array($table, $dbh->getOne('SELECT MAX(' .
                            $primary_field . ') FROM ' . $table . ' WHERE true'));
                    }
                }
            }
            //  "%_seq" is the default, but in case they screwed around with PEAR::DB...
            $suffix = $dbh->getOption('seqname_format');

            //  we'll just create the sequences manually...why not?
            foreach ($data as $k) {
                $tableName = $k[0];
                $seqName = sprintf($suffix, $tableName);
                $maxVal   = $k[1] + 1;
                $sql = 'CREATE SEQUENCE ' . $seqName . ' START ' . $maxVal;
                $result = $dbh->query($sql);
                if (PEAR::isError($result) && $result->code == DB_ERROR_ALREADY_EXISTS) {
                    $sql = 'ALTER SEQUENCE ' . $seqName . ' RESTART WITH ' . $maxVal;
                    $result = $dbh->query($sql);
                }
            }
            break;

        case 'oci8':
        case 'db2':
            $dbh->autoCommit(false);

            $data = '';
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $dbh->getListOf('sequences');
            foreach ($aTables as $sequence) {
                $primary_field = '';
                // get tablename
                if (preg_match("/^(.*)_seq$/",$sequence,$table)) {
                    $info = $dbh->tableInfo($dbh->quoteIdentifier($table[1]));
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field != '') {
                        $maxId = $dbh->getOne('SELECT MAX(' .
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
                    if (!DB::isError($dbh->dropSequence($table[1]))) {
                        $success = $dbh->query('CREATE SEQUENCE ' .
                            $dbh->getSequenceName($table[1]) . ' START WITH ' . $maxId);
                    }

                    if (!$success) {
                        $dbh->rollback();
                        $dbh->autoCommit(true);
                        SGL_Install_Common::errorPush(PEAR::raiseError('Sequence rebuild failed'));
                    }
                }
            }
            $success = $dbh->commit();
            $dbh->autoCommit(true);
            if (!$success) {
                SGL_Install_Common::errorPush(PEAR::raiseError('Sequence rebuild failed'));
            }
            break;

        default:
            SGL_Install_Common::errorPush(
                PEAR::raiseError('This feature currently is implemented only for MySQL, Oracle and PostgreSQL.'));
        }
        //  remove irrelevant errors
        $finalErrorCount = SGL_Error::count();
        if ($finalErrorCount > $initialErrorCount) {
            $numErrors = $finalErrorCount - $initialErrorCount;
            for ($x = 0; $x < $numErrors; $x++) {
                SGL_Error::pop();
            }
        }
    }

    function _getViews()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $dbh     = $locator->get('DB');
        $dbName  = SGL_Config::get('db.name');
        $query   = "
            SELECT table_name
            FROM   information_schema.views
            WHERE  table_schema = '" . $dbh->escapeSimple($dbName) . "'
        ";
        return $dbh->getCol($query);
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateAdminUser extends SGL_Task
{
    function run($data)
    {
        $ok = true;
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
            $da = & UserDAO::singleton();
            $oUser = $da->getUserById();
            $oUser->username        = $data['adminUserName'];
            $oUser->first_name      = $data['adminFirstName'];
            $oUser->last_name       = $data['adminLastName'];
            $oUser->email           = $data['adminEmail'];
            $oUser->passwd          = !empty($data['adminPasswordIsHash'])
                ? $data['adminPassword']
                :  md5($data['adminPassword']);
            $oUser->organisation_id = 1;
            $oUser->is_acct_active  = 1;
            $oUser->country         = 'GB';
            $oUser->role_id         = SGL_ADMIN;
            $oUser->date_created    = $oUser->last_updated = SGL_Date::getTime();
            $oUser->created_by      = $oUser->updated_by = SGL_ADMIN;
            $ok = $da->addUser($oUser);

            if (PEAR::isError($ok)) {
                SGL_Install_Common::errorPush($ok);
            }
        }
        return $ok;
    }
}

/**
 * @package Task
 */
class SGL_Task_CreateMemberUser extends SGL_Task
{
    function run($data)
    {
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
            $da = & UserDAO::singleton();
            $oUser = $da->getUserById();

            $oUser->username = 'member';
            $oUser->first_name = 'Example';
            $oUser->last_name = 'Member User';
            $oUser->email = 'example@seagullproject.org';
            $oUser->passwd = md5('password');
            $oUser->organisation_id = 1;
            $oUser->is_acct_active = 1;
            $oUser->country = 'GB';
            $oUser->role_id = 2;
            $oUser->date_created = $oUser->last_updated = SGL_Date::getTime();
            $oUser->created_by = $oUser->updated_by = SGL_ADMIN;
            $success = $da->addUser($oUser);

            if (PEAR::isError($success)) {
                SGL_Install_Common::errorPush($success);
            }
        }
    }
}

/**
 * @package Task
 */
class SGL_Task_InstallerCleanup extends SGL_Task
{
    function run($data)
    {
        $newFile = <<<PHP
<?php
#{$data['installPassword']}
?>
PHP;
        if (is_writable(SGL_VAR_DIR)) {
            $ok = file_put_contents(SGL_VAR_DIR . '/INSTALL_COMPLETE.php', $newFile);
        } else {
            SGL_Install_Common::errorPush(PEAR::raiseError('var dir is not writable'));
        }

        //  update lang in default prefs
        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $da = & UserDAO::singleton();
        $lang = isset($_SESSION['install_language'])
            ? $_SESSION['install_language']
            : $data['aPrefs']['language'];
        $ok = $da->updateMasterPrefs(array('language' => $lang));
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        //  update lang in admin prefs
        $aMapping = $da->getPrefsMapping();
        $langPrefId = $aMapping['language'];
        $ok = $da->updatePrefsByUserId(array($langPrefId => $lang), SGL_ADMIN);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        //  update tz in default prefs
        $tz = isset($_SESSION['install_timezone'])
            ? $_SESSION['install_timezone']
            : $data['aPrefs']['timezone'];
        $ok = $da->updateMasterPrefs(array('timezone' => $tz));
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        //  clean up errors stored in session
        unset($_SESSION['ERRORS']);

        //  update tz in admin prefs
        $tzPrefId = $aMapping['timezone'];
        $ok = $da->updatePrefsByUserId(array($tzPrefId => $tz), SGL_ADMIN);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush($ok);
        }
        return $ok;
    }
}

if (strpos(PHP_OS, 'WIN') !== false) {
    if (!function_exists('symlink')) {
        function symlink($target, $link) {
            // it's Vista
            if (isset($_SERVER['HTTP_USER_AGENT'])
                    && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows NT 6') !== false) {

                // mklink utility doesn't understand ".."
                $ds        = DIRECTORY_SEPARATOR;
                $linkDir   = realpath(dirname($link)) . $ds . basename($link);
                $targetDir = realpath(dirname($target)) . $ds . basename($target);

                exec('mklink /D '  . $linkDir . ' ' . $targetDir, $ret);
            // other Windows version
            } else {
                exec('linkd ' . $link . ' ' . $target, $ret);
            }
            return $ret;
        }
    }
    if (!function_exists('readlink')) {
        function readlink($link) {
            // it's Vista
            if (isset($_SERVER['HTTP_USER_AGENT'])
                    && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows NT 6') !== false) {
                // todo: find linkd native equivalent for reading links
                $ret = true;
            // other Windows version
            } else {
                exec('linkd ' . $link, $ret);
            }
            return $ret;
        }
    }
}

?>
