<?php
require_once dirname(__FILE__) . '/../Task.php';

class SGL_Task_SetBaseUrlMinimal extends SGL_Task
{
    function run($data)
    {
        $conf = array(
            'setup' => true,
            'site' =>   array(  'frontScriptName' => 'index.php',
                                'defaultModule' => 'default',
                                'defaultManager' => 'default',
                        ),
            'cookie' => array(  'name' => ''),
            );

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        $url = new SGL_URL($_SERVER['PHP_SELF'], true, new SGL_UrlParserSefStrategy(), $conf);
        define('SGL_BASE_URL', $url->getBase());
    }
}

class SGL_Task_CreateConfig extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton($autoLoad = false);
        $conf = $c->load(SGL_PATH . '/etc/default.conf.dist.ini');
        $c->replace($conf);

        //  admin emails
        $c->set('email', array('admin' => $data['adminEmail']));
        $c->set('email', array('info' => $data['adminEmail']));
        $c->set('email', array('support' => $data['adminEmail']));

        //  db details
        $c->set('db', array('prefix' => $data['prefix']));
        $c->set('db', array('host' => $data['host']));
        $c->set('db', array('name' => $data['name']));
        $c->set('db', array('user' => $data['user']));
        $c->set('db', array('pass' => $data['pass']));
        $c->set('db', array('port' => $data['dbPort']['port']));
        $c->set('db', array('protocol' => $data['dbProtocol']['protocol']));
        $c->set('db', array('type' => $data['dbType']['type']));

        //  version
        $c->set('tuples', array('version' => $data['frameworkVersion']));

        //  paths
        $c->set('path', array('installRoot' => $data['installRoot']));
        $c->set('path', array('webRoot' => $data['webRoot']));

        //  various
        $c->set('site', array('serverTimeOffset' => $data['serverTimeOffset']));
        $c->set('site', array('baseUrl' => SGL_BASE_URL));
        $c->set('site', array('name' => $data['siteName']));
        $c->set('site', array('description' => $data['siteDesc']));
        $c->set('site', array('keywords' => $data['siteKeywords']));
        $c->set('site', array('language' => $data['siteLanguage']));
        $c->set('site', array('blocksEnabled' => false));
        $c->set('cookie', array('name' => $data['siteCookie']));

        //  save
        $configFile = SGL_PATH . '/var/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($configFile);
        if (PEAR::isError($ok)) {
            SGL_Install::errorPush(PEAR::raiseError($ok));
        }
    }
}

class SGL_Task_DisableForeignKeyChecks extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        //  disable fk constraints if mysql (>= 4.1.x)
        if ($this->conf['db']['type'] == 'mysql' || $this->conf['db']['type'] == 'mysql_SGL') {
            $dbh = & SGL_DB::singleton();
            $query = 'SET FOREIGN_KEY_CHECKS=0;';
            $res = $dbh->query($query);
        }
    }
}

class SGL_UpdateHtmlTask extends SGL_Task
{
    function updateHtml($id, $displayHtml)
    {
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

    function getMinimumModuleList()
    {
        return array('default', 'navigation', 'user');
    }

    function setup()
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        //  setup db type vars
        switch ($this->conf['db']['type']) {
        case 'pgsql':
            $this->dbType = 'pgsql';
            $this->filename1 = '/schema.pg.sql';
            $this->filename2 = '/data.default.pg.sql';
            $this->filename3 = '/constraints.pg.sql';
            break;

        case 'mysql':
            $this->dbType = 'mysql';
            $this->filename1 = '/schema.my.sql';
            $this->filename2 = '/data.default.my.sql';
            $this->filename3 = '/constraints.my.sql';
            break;

        case 'mysql_SGL':
            $this->dbType = 'mysql_SGL';
            $this->filename1 = '/schema.my.sql';
            $this->filename2 = '/data.default.my.sql';
            $this->filename3 = '/constraints.my.sql';
            break;

        case 'oci8_SGL':
            $this->dbType = 'oci8';
            $this->filename1 = '/schema.oci.sql';
            $this->filename2 = '/data.default.oci.sql';
            $this->filename3 = '/constraints.oci.sql';
            break;

        case 'maxdb_SGL':
            $this->dbType = 'maxdb_SGL';
            $this->filename1 = '/schema.mx.sql';
            $this->filename2 = '/data.default.mx.sql';
            $this->filename3 = '/constraints.mx.sql';
            break;
        }

        //  these hold what to display in results grid, depending on outcome
        $this->success = '<img src=\\"' . SGL_BASE_URL . '/themes/default/images/enabled.gif\\" border=\\"0\\" width=\\"22\\" height=\\"22\\">' ;
        $this->failure = '<span class=\\"error\\">ERROR</span>';
        $this->noFile  = '<strong>N/A</strong>';
    }
}

class SGL_Task_CreateTables extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        require_once SGL_PATH . '/lib/SGL/Sql.php';

        SGL_Install::printHeader('Building Database');
        echo '<span class="title">Status: </span><span id="status"></span>
        <div id="progress_bar">
            <img src="' . SGL_BASE_URL . '/themes/default/images/progress_bar.gif" border="0" width="150" height="13">
        </div>
        <div id="additionalInfo"></div>';
        flush();
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {

            $this->setup();

            $statusText = 'Fetching modules';
            $this->updateHtml('status', $statusText);

            //  Print table shell, with module names; we'll update statuses as we execute sql below
            echo '<table class="wide">
                <tr>
                    <th class="alignCenter">Module</th>
                    <th class="alignCenter">Create Table</th>
                    <th class="alignCenter">Load Data</th>
                    <th class="alignCenter">Add Constraints</th>
                </tr>
                <!--tr>
                    <td class="title">Main</td>
                    <td id="etc_schema" class="alignCenter"></td>
                    <td id="etc_data" class="alignCenter"></td>
                    <td id="etc_constraints" class="alignCenter"></td>
                </tr-->
            ';

            $aModuleList = (isset($data['installAllModules']))
                ? SGL_Install::getModuleList()
                : $this->getMinimumModuleList();

            foreach ($aModuleList as $module) {
                echo '<tr>
                        <td class="title">' . ucfirst($module) . '</td>
                        <td id="' . $module . '_schema" class="alignCenter"></td>
                        <td id="' . $module . '_data" class="alignCenter"></td>
                        <td id="' . $module . '_constraints" class="alignCenter"></td>
                    </tr>';
            }
            echo '</table>';
            flush();

            $statusText .= ', creating and loading tables';
            $this->updateHtml('status', $statusText);

            //  load 'sequence' table
            if ($this->conf['db']['type'] == 'mysql_SGL') {
                $result = SGL_Sql::parseAndExecute(SGL_PATH . '/etc/sequence.my.sql', 0);
            }

            //  Load each module's schema, if there is a sql file in /data
            foreach ($aModuleList as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's schema
                if (file_exists($modulePath . $this->filename1)) {
                    $result = SGL_Sql::parseAndExecute($modulePath . $this->filename1, 0);
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_schema', $displayHtml);
                } else {
                    $this->updateHtml($module . '_schema', $this->noFile);
                }
            }

            //  catch 'table already exists' error
            if (DB::isError($result, DB_ERROR_ALREADY_EXISTS)) {
                $this->updateHtml('status', 'Tables already exist');
                $body = 'It appears that the schema already exists.  Click <a href=\\"index.php\\">here</a> to return to the configuration screen and choose \\"Only set DB connection details\\".';
                $this->updateHtml('additionalInfo', $body);
                $this->updateHtml('progress_bar', '');
                exit;
            }
        }
    }
}

class SGL_Task_LoadDefaultData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            $this->setup();

            $statusText = 'loading data';
            $this->updateHtml('status', $statusText);

            //  Go back and load each module's default data, if there is a sql file in /data
            $aModuleList = (isset($data['installAllModules']))
                ? SGL_Install::getModuleList()
                : $this->getMinimumModuleList();

            foreach ($aModuleList as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's data
                if (file_exists($modulePath . $this->filename2)) {
                    $result = SGL_Sql::parseAndExecute($modulePath . $this->filename2, 0);
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_data', $displayHtml);
                } else {
                    $this->updateHtml($module . '_data', $this->noFile);
                }
            }
        }
    }
}

class SGL_Task_CreateConstraints extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            $this->setup();

            $statusText = 'loading constraints';
            $this->updateHtml('status', $statusText);

            //  Go back and load module foreign keys/constraints, if any
            $aModuleList = (isset($data['installAllModules']))
                ? SGL_Install::getModuleList()
                : $this->getMinimumModuleList();

            foreach ($aModuleList as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';
                if (file_exists($modulePath . $this->filename3)) {
                    $result = SGL_Sql::parseAndExecute($modulePath . $this->filename3, 0);
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_constraints', $displayHtml);
                } else {
                    $this->updateHtml($module . '_constraints', $this->noFile);
                }
            }
        }
    }
}

class SGL_Task_EnableForeignKeyChecks extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        //  re-enable fk constraints if mysql (>= 4.1.x)
        if ($this->conf['db']['type'] == 'mysql' || $this->conf['db']['type'] == 'mysql_SGL') {
            $dbh = & SGL_DB::singleton();
            $query = 'SET FOREIGN_KEY_CHECKS=1;';
            $res = $dbh->query($query);
        }
    }
}

//  some more tests would be helpful
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
            SGL_Install::errorPush(
                PEAR::raiseError('No tables exist in DB - was schema created?'));
        } elseif (!(count($res))) {
            SGL_Install::errorPush(
                PEAR::raiseError('Perms inserts failed', SGL_ERROR_DBFAILURE));
        }

        //  create error message if appropriate
        if (SGL_Install::errorsExist()) {
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

        SGL_Install::printFooter();
    }
}

class SGL_Task_CreateFileSystem extends SGL_Task
{
    function run($data)
    {
        require_once 'System.php';
        require_once 'DB/DataObject/Generator.php';
        $cacheDir = System::mkDir(array(SGL_CACHE_DIR));
        if (PEAR::isError($cacheDir)) {
            SGL_Install::errorPush(PEAR::raiseError('Problem creating cache dir'));
        }
        $entDir = System::mkDir(array(SGL_ENT_DIR));
        if (PEAR::isError($entDir)) {
            SGL_Install::errorPush(PEAR::raiseError('Problem creating entity dir'));
        }
    }
}

class SGL_Task_CreateDataObjectEntities extends SGL_Task
{
    function run($data = null)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  init DB_DataObject
        $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        $options = array(
            'database'              => SGL_DB::getDsn(SGL_DSN_STRING),
            'schema_location'       => SGL_ENT_DIR,
            'class_location'        => SGL_ENT_DIR,
            'require_prefix'        => SGL_ENT_DIR . '/',
            'class_prefix'          => 'DataObjects_',
            'debug'                 => 0,
            'production'            => 0,
            'ignore_sequence_keys'  => 'ALL',
            'generator_strip_schema'=> 1,
        );

        require_once 'DB/DataObject/Generator.php';
        ob_start();
        $generator = new DB_DataObject_Generator();
        $generator->start();
        $out = ob_get_contents();
        ob_end_clean();

        if (PEAR::isError($out)) {
            SGL_Install::errorPush(
                PEAR::raiseError('generating DB_DataObject entities failed'));
        }

        //  copy over links file
        @copy(SGL_PATH . '/etc/links.ini.dist',
            SGL_ENT_DIR . '/' . $conf['db']['name'] . '.links.ini');
    }
}

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
        $db =& SGL_DB::singleton();
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $tables = null;

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
                        SGL_Install::errorPush(PEAR::raiseError('Rebuild failed '));
                    }
                }
            }
            $success = $db->commit();
            $db->autoCommit(true);
            if (!$success) {
                SGL_Install::errorPush(PEAR::raiseError('Rebuild failed '));
            }
            break;

        default:
            SGL_Install::errorPush(
                PEAR::raiseError('This feature currently is impmlemented only for MySQL, Oracle and PostgreSQL.'));
        }
    }
}

class SGL_Task_CreateAdminUser extends SGL_Task
{
    function run($data)
    {
        if (array_key_exists('createTables', $data) && $data['createTables'] == 1) {
            require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
            require_once SGL_PATH . '/lib/SGL/String.php';
            $da = & DA_User::singleton();
            $oUser = $da->getUserById();

            $oUser->username = $data['adminUserName'];
            $oUser->first_name = $data['adminRealName'];
            $oUser->email = $data['adminEmail'];
            $oUser->passwd = md5($data['adminPassword']);
            $oUser->organisation_id = 1;
            $oUser->is_acct_active = 1;
            $oUser->role_id = SGL_ADMIN;
            $oUser->date_created = $oUser->last_updated = SGL_Date::getTime();
            $oUser->created_by = $oUser->updated_by = SGL_ADMIN;
            $success = $da->addUser($oUser);
            if (PEAR::isError($success)) {
                SGL_Install::errorPush(PEAR::raiseError($success));
            }
        }
    }
}

class SGL_Task_InstallerCleanup extends SGL_Task
{
    function run($data)
    {
        $newFile = <<<PHP
<?php
#{$data['installPassword']}
?>
PHP;
        $ok = file_put_contents(SGL_PATH . '/var/INSTALL_COMPLETE.php', $newFile);
    }
}
?>