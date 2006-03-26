<?php
require_once dirname(__FILE__) . '/../../Task.php';
require_once dirname(__FILE__) . '/../../Install/Common.php';

class SGL_Task_SetBaseUrlMinimal extends SGL_Task
{
    function run($data = array())
    {
        $conf = array(
            'setup' => true,
            'site' =>   array(
                'frontScriptName' => 'index.php',
                'defaultModule' => 'default',
                'defaultManager' => 'default',
                ),
            'cookie' => array(  'name' => ''),
            );

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        $url = new SGL_URL($_SERVER['PHP_SELF'], true,
            new SGL_UrlParser_SefStrategy(), $conf);
        $err = $url->init();
        define('SGL_BASE_URL', $url->getBase());
    }
}

class SGL_Task_CreateConfig extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton($autoLoad = false);
        $conf = $c->load(SGL_ETC_DIR . '/default.conf.dist.ini');
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
        $c->set('db', array('socket' => $data['socket']));
        $c->set('db', array('type' => $data['dbType']['type']));

        //  version
        $c->set('tuples', array('version' => $data['frameworkVersion']));

        //  demo mode
        if (is_file(SGL_VAR_DIR . '/DEMO_MODE')) {
            $c->set('tuples', array('demoMode' => true));
        }
        //  paths
        $c->set('path', array('installRoot' => $data['installRoot']));
        $c->set('path', array('webRoot' => $data['webRoot']));

        //  various
        $c->set('site', array('serverTimeOffset' => $data['serverTimeOffset']));
        $c->set('site', array('baseUrl' => SGL_BASE_URL));
        $c->set('site', array('name' => $data['siteName']));
        $c->set('site', array('description' => $data['siteDesc']));
        $c->set('site', array('keywords' => $data['siteKeywords']));
        $c->set('site', array('blocksEnabled' => true));
        $c->set('cookie', array('name' => $data['siteCookie']));

        //  store translations in db
        $storeTransInDbClause = (array_key_exists('storeTranslationsInDB', $data)
                && $data['storeTranslationsInDB'] == 1)
            ? $c->set('translation', array('container' => 'db'))
            : $c->set('translation', array('container' => 'file'));

        //  add missing translations to db
        $missingTransClause =  (array_key_exists('addMissingTranslationsToDB', $data)
                && $data['addMissingTranslationsToDB'] == 1)
            ? $c->set('translation', array('addMissingTrans' => true))
            : $c->set('translation', array('addMissingTrans' => false));

        //  translation fallback language
        $fallbackLang = str_replace('-', '_', $data['siteLanguage']);
        $c->set('translation', array('fallbackLang' => $fallbackLang));

        //  auto-correct frontScriptName for CGI users
        if (preg_match("/cgi/i", php_sapi_name())) {
            $c->set('site', array('frontScriptName' => 'index.php?'));
        }
        //  save
        $configFile = SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($configFile);

        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
        }
        //  store site language for post-install task
        $_SESSION['install_language'] = $data['siteLanguage'];

        //  and tz
        $_SESSION['install_timezone'] = $data['serverTimeOffset'];
    }
}

class SGL_UpdateHtmlTask extends SGL_Task
{
    function updateHtml($id, $displayHtml)
    {
        if (SGL::runningFromCli()) {
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

    function getMinimumModuleList()
    {
        return array('block', 'default', 'navigation', 'user');
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
            $this->filename3 = '/data.sample.pg.sql';
            $this->filename4 = '/constraints.pg.sql';
            break;

        case 'mysql':
            $this->dbType = 'mysql';
            $this->filename1 = '/schema.my.sql';
            $this->filename2 = '/data.default.my.sql';
            $this->filename3 = '/data.sample.my.sql';
            $this->filename4 = '/constraints.my.sql';
            break;

        case 'mysql_SGL':
            $this->dbType = 'mysql_SGL';
            $this->filename1 = '/schema.my.sql';
            $this->filename2 = '/data.default.my.sql';
            $this->filename3 = '/data.sample.my.sql';
            $this->filename4 = '/constraints.my.sql';
            break;

        case 'oci8_SGL':
            $this->dbType = 'oci8';
            $this->filename1 = '/schema.oci.sql';
            $this->filename2 = '/data.default.oci.sql';
            $this->filename3 = '/data.sample.oci.sql';
            $this->filename4 = '/constraints.oci.sql';
            break;

        case 'maxdb_SGL':
            $this->dbType = 'maxdb_SGL';
            $this->filename1 = '/schema.mx.sql';
            $this->filename2 = '/data.default.mx.sql';
            $this->filename3 = '/data.sample.mx.sql';
            $this->filename4 = '/constraints.mx.sql';
            break;

        case 'db2_SGL':
            $this->dbType = 'db2';
            $this->filename1 = '/schema.db2.sql';
            $this->filename2 = '/data.default.db2.sql';
            $this->filename3 = '/data.sample.db2.sql';
            $this->filename4 = '/constraints.db2.sql';
            break;
        }

        //  these hold what to display in results grid, depending on outcome
        $this->success = '<img src=\\"' . SGL_BASE_URL . '/themes/default/images/enabled.gif\\" border=\\"0\\" width=\\"22\\" height=\\"22\\">' ;
        $this->failure = '<span class=\\"error\\">ERROR</span>';
        $this->noFile  = '<strong>N/A</strong>';
    }
}

class SGL_Task_DefineTableAliases extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();

        $aModuleList = SGL_Install_Common::getModuleList();

        foreach ($aModuleList as $module) {
            $tableAliasIniPath = SGL_MOD_DIR . '/' . $module  . '/tableAliases.ini';
            if (file_exists($tableAliasIniPath)) {
                $aData = parse_ini_file($tableAliasIniPath);
                foreach ($aData as $k => $v) {
                    $c->set('table', array($k => $v));
                }
            }
        }

        //  save
        $configFile = SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($configFile);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
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

class SGL_Task_DropDatabase extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $dbh = & SGL_DB::singleton();
        $query = "DROP DATABASE {$this->conf['db']['name']}";
        $res = $dbh->query($query);
    }
}

class SGL_Task_CreateDatabase extends SGL_Task
{
    function run($data)
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $dsn = SGL_DB::getDsn(SGL_DSN_STRING, $excludeDbName = true);
        $dbh = & SGL_DB::singleton($dsn);
        $query = "CREATE DATABASE `{$this->conf['db']['name']}`";
        $res = $dbh->query($query);
    }
}

class SGL_Task_CreateTables extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        require_once SGL_CORE_DIR . '/Sql.php';

        SGL_Install_Common::printHeader('Building Database');

        if (!SGL::runningFromCli()) {
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
                            <th class="alignCenter">Create Table</th>
                            <th class="alignCenter">Load Default Data</th>
                            ';
            if (array_key_exists('insertSampleData', $data) && $data['insertSampleData'] == 1) {
                $out .=    '<th class="alignCenter">Load Sample Data</th>
                           ';
            }
            $out .=        '<th class="alignCenter">Add Constraints</th>
                        </tr>';

            if (!SGL::runningFromCli()) {
                echo $out;
            }

            $aModuleList = (isset($data['installAllModules']))
                ? SGL_Install_Common::getModuleList()
                : $this->getMinimumModuleList();

            foreach ($aModuleList as $module) {
                $out = '<tr>
                            <td class="title">' . ucfirst($module) . '</td>
                            <td id="' . $module . '_schema" class="alignCenter"></td>
                            <td id="' . $module . '_data" class="alignCenter"></td>
                            ';
                if (array_key_exists('insertSampleData', $data) && $data['insertSampleData'] == 1) {
                    $out .='<td id="' . $module . '_dataSample" class="alignCenter"></td>
                           ';
                }
                $out .= '<td id="' . $module . '_constraints" class="alignCenter"></td>
                     </tr>';

                if (!SGL::runningFromCli()) {
                    echo $out;
                }
            }

            if (!SGL::runningFromCli()) {
                echo '</table>';
                flush();
            }

            $statusText .= ', creating and loading tables';
            $this->updateHtml('status', $statusText);

            //  load 'sequence' table
            if ($this->conf['db']['type'] == 'mysql_SGL') {
                $result = SGL_Sql::parseAndExecute(SGL_ETC_DIR . '/sequence.my.sql', 0);
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

            $statusText = 'loading default data';
            $this->updateHtml('status', $statusText);

            //  Go back and load each module's default data, if there is a sql file in /data
            $aModuleList = (isset($data['installAllModules']))
                ? SGL_Install_Common::getModuleList()
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

class SGL_Task_LoadSampleData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        if (array_key_exists('insertSampleData', $data) && $data['insertSampleData'] == 1) {
            $this->setup();

            $statusText = 'loading sample data';
            $this->updateHtml('status', $statusText);

            //  Go back and load each module's default data, if there is a sql file in /data
            $aModuleList = (isset($data['installAllModules']))
                ? SGL_Install_Common::getModuleList()
                : $this->getMinimumModuleList();

            foreach ($aModuleList as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                //  Load the module's data
                if (file_exists($modulePath . $this->filename3)) {
                    $result = SGL_Sql::parseAndExecute($modulePath . $this->filename3, 0);
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_dataSample', $displayHtml);
                } else {
                    $this->updateHtml($module . '_dataSample', $this->noFile);
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
                ? SGL_Install_Common::getModuleList()
                : $this->getMinimumModuleList();

            foreach ($aModuleList as $module) {
                $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';
                if (file_exists($modulePath . $this->filename4)) {
                    $result = SGL_Sql::parseAndExecute($modulePath . $this->filename4, 0);
                    $displayHtml = $result ? $this->success : $this->failure;
                    $this->updateHtml($module . '_constraints', $displayHtml);
                } else {
                    $this->updateHtml($module . '_constraints', $this->noFile);
                }
            }
        }
    }
}

define('SGL_NODE_ADMIN', 4); // nested set parent_id
define('SGL_NODE_GROUP', 1);

class SGL_Task_BuildNavigation extends SGL_UpdateHtmlTask
{
    var $groupId = null;
    var $childId = null;

    function run($data)
    {
        require_once SGL_MOD_DIR . '/navigation/classes/DA_Navigation.php';
        $da = & DA_Navigation::singleton();
        $aModuleList = (isset($data['installAllModules']))
            ? SGL_Install_Common::getModuleList()
            : $this->getMinimumModuleList();

        foreach ($aModuleList as $module) {
            $navigationPath = SGL_MOD_DIR . '/' . $module  . '/data/navigation.php';
            if (file_exists($navigationPath)) {
                require_once $navigationPath;
                foreach ($aSections as $aSection) {

                    //  check if section is designated as child to last insert
                    if ($aSection['parent_id'] == SGL_NODE_GROUP) {
                        $aSection['parent_id'] = $this->groupId;
                    } else {
                        $aSection['parent_id'] = SGL_NODE_ADMIN;
                    }
                    $id = $da->addSimpleSection($aSection);
                    if (!PEAR::isError($id)) {
                        if ($aSection['parent_id'] == SGL_NODE_ADMIN) {
                            $this->groupId = $id;
                        } else {
                            $this->childId = $id;
                        }
                    } else {
                        SGL_Install_Common::errorPush(PEAR::raiseError($$idk));
                    }
                }
            }
        }
    }
}

class SGL_Task_LoadTranslations extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        $configFile = SGL_VAR_DIR . '/' . SGL_SERVER_NAME . '.conf.php';
        $c = &SGL_Config::singleton();
        $aLangOptions = SGL_Util::getLangsDescriptionMap();

        if (array_key_exists('storeTranslationsInDB', $data) && $data['storeTranslationsInDB'] == 1) {
            require_once SGL_CORE_DIR .'/Translation.php';
            $trans = & SGL_Translation::singleton('admin');

            $this->setup();

            $statusText .= 'loading languages';
            $this->updateHtml('status', $statusText);

            //  fetch available languages
            $availableLanguages = & $GLOBALS['_SGL']['LANGUAGE'];

            //  add languaged to inifile container
            $this->installedLanguages = $data['installLangs'];

            $c->set('translation', array('installedLanguages' => implode(',',
                str_replace('-', '_', $data['installLangs']))));

            $ok = $c->save($configFile);
            if (PEAR::isError($ok)) {
                SGL_Install_Common::errorPush(PEAR::raiseError($ok));
            }

            //  interate through languages adding to langs table
            foreach ($data['installLangs'] as $aKey => $aLang) {
                $globalLangFile = $availableLanguages[$aLang][1] .'.php';
                $langID = str_replace('-', '_', $aLang);
                $encoding       = substr($aLang, strpos('-', $aLang));
                $langData       = array(
                    'lang_id' => $langID,
                    'table_name' => $this->conf['table']['translation'] .'_'. $langID,
                    'meta' => '',
                    'name' => $aLangOptions[$aLang],
                    'error_text' => 'not available',
                    'encoding' => $encoding
                     );
                $result = $trans->addLang($langData);

                //  iterate through modules
                $aModuleList = (isset($data['installAllModules']))
                    ? SGL_Install_Common::getModuleList()
                    : $this->getMinimumModuleList();

                foreach ($aModuleList as $module) {
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
            $installedLangs = implode(',', str_replace('-', '_', array_keys($aLangOptions)));

            $c->set('translation', array('installedLanguages' => $installedLangs));
            $ok = $c->save($configFile);
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

class SGL_Task_CreateFileSystem extends SGL_Task
{
    function run($data)
    {
        require_once 'System.php';

        //  pass paths as arrays to avoid widows space parsing prob
        //  create cache dir
        $cacheDir = System::mkDir(array(SGL_CACHE_DIR));
        @chmod($cacheDir, 0777);

        if (!($cacheDir)) {
            SGL_Install_Common::errorPush(PEAR::raiseError('Problem creating cache dir'));
        }

        //  create entities dir
        $entDir = System::mkDir(array(SGL_ENT_DIR));
        @chmod($entDir, 0777);
        if (!($entDir)) {
            SGL_Install_Common::errorPush(PEAR::raiseError('Problem creating entity dir'));
        }

        //  create tmp dir, mostly for sessions
        if (!is_writable(SGL_TMP_DIR)) {

            $tmpDir = System::mkDir(array(SGL_TMP_DIR));
            if (!$tmpDir) {
                SGL_Install_Common::errorPush(SGL::raiseError('The tmp directory does not '.
                'appear to be writable, please give the webserver permissions to write to it'));
            }
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
#SGL::logMessage('out = ' . $out);

        if (PEAR::isError($out)) {
            SGL_Install_Common::errorPush(
                PEAR::raiseError('generating DB_DataObject entities failed'));
        }

        //  copy over links file
        $target = SGL_ENT_DIR . '/' . $conf['db']['name'] . '.links.ini';
        if (!file_exists($target)) {
            @copy(SGL_PATH . '/etc/links.ini.dist', $target);
        }
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
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        if (!$dbh) {
            $dbh = & SGL_DB::singleton();
            $locator->register('DB', $dbh);
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $tables = null;

        switch ($dbh->phptype) {

        case 'mysql':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $dbh->getListOf('tables');

            //  "%_seq" is the default, but in case they screwed around with PEAR::DB...
            $suffix = $dbh->getOption('seqname_format');
            $suffixRaw = str_replace('%s', '', $suffix);
            $suffixRawStart = (0 - strlen($suffixRaw));

            foreach ($aTables as $table) {
                $primary_field = '';
                //  we only build sequences for tables that are not sequences themselves
                if ($table == $conf['table']['sequence'] || substr($table, $suffixRawStart) == $suffixRaw) {
                    continue;
                }

                $info = $dbh->tableInfo($table);
                foreach ($info as $field) {
                    if (eregi('primary_key', $field['flags'])) {
                        $primary_field = $field['name'];
                        break;
                    }
                }
                if ($primary_field <> '') {
                    $maxId = $dbh->getOne('SELECT MAX(' . $primary_field . ') FROM ' . $table . ' WHERE 1');
                    if (!is_null($maxId)) {
                    	$data[] = array($table, $maxId);
                    }
                }
            }

            foreach ($data as $k) {
                $tableName = $k[0];
                $seqName = sprintf($suffix, $tableName);
                $maxVal   = $k[1];
                $currVal = $dbh->nextId($tableName, true);
                $sql = 'UPDATE ' . $seqName . ' SET id=' . $maxVal . ' WHERE id=' . $currVal;
                $result = $dbh->query($sql);
            }
            break;

        case 'mysql_SGL':
            $data = array();
            $aTables = (count( (array) $tables) > 0) ? (array) $tables :  $dbh->getListOf('tables');
            foreach ($aTables as $table) {
                $primary_field = '';
                if ($table <> $conf['table']['sequence']) {
                    $info = $dbh->tableInfo($table);
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field <> '') {
                        $data[] = array($table, $dbh->getOne('SELECT MAX(' .
                            $primary_field . ') FROM ' . $table . ' WHERE 1'));
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
                if ($table <> $conf['table']['sequence']) {
                    $info = $dbh->tableInfo($table);
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field <> '') {
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
                    $info = $dbh->tableInfo($table[1]);
                    foreach ($info as $field) {
                        if (eregi('primary_key', $field['flags'])) {
                            $primary_field = $field['name'];
                            break;
                        }
                    }
                    if ($primary_field <> '') {
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
                        SGL_Install_Common::errorPush(PEAR::raiseError('Rebuild failed '));
                    }
                }
            }
            $success = $dbh->commit();
            $dbh->autoCommit(true);
            if (!$success) {
                SGL_Install_Common::errorPush(PEAR::raiseError('Rebuild failed '));
            }
            break;

        default:
            SGL_Install_Common::errorPush(
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
            require_once SGL_CORE_DIR . '/String.php';
            $da = & DA_User::singleton();
            $oUser = $da->getUserById();

            $oUser->username = $data['adminUserName'];
            $oUser->first_name = $data['adminRealName'];
            $oUser->email = $data['adminEmail'];
            $oUser->passwd = md5($data['adminPassword']);
            $oUser->organisation_id = 1;
            $oUser->is_acct_active = 1;
            $oUser->country = 'GB';
            $oUser->role_id = SGL_ADMIN;
            $oUser->date_created = $oUser->last_updated = SGL_Date::getTime();
            $oUser->created_by = $oUser->updated_by = SGL_ADMIN;
            $success = $da->addUser($oUser);

            if (PEAR::isError($success)) {
                SGL_Install_Common::errorPush(PEAR::raiseError($success));
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
        $ok = file_put_contents(SGL_VAR_DIR . '/INSTALL_COMPLETE.php', $newFile);

        //  update lang in default prefs
        require_once SGL_MOD_DIR . '/user/classes/DA_User.php';
        $da = & DA_User::singleton();
        $lang = isset($_SESSION['install_language'])
            ? $_SESSION['install_language']
            : 'en-iso-8859-15';
        $ok = $da->updateMasterPrefs(array('language' => $lang));
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
        }
        //  update lang in admin prefs
        $aMapping = $da->getPrefsMapping();
        $langPrefId = $aMapping['language'];
        $ok = $da->updatePrefsByUserId(array($langPrefId => $lang), SGL_ADMIN);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
        }
        //  update tz in default prefs
        $tz = isset($_SESSION['install_timezone'])
            ? $_SESSION['install_timezone']
            : 'UTC';
        $ok = $da->updateMasterPrefs(array('timezone' => $tz));
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
        }
        //  update tz in admin prefs
        $tzPrefId = $aMapping['timezone'];
        $ok = $da->updatePrefsByUserId(array($tzPrefId => $tz), SGL_ADMIN);
        if (PEAR::isError($ok)) {
            SGL_Install_Common::errorPush(PEAR::raiseError($ok));
        }
    }
}
?>
