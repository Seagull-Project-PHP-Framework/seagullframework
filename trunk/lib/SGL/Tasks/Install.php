<?php
require_once dirname(__FILE__) . '/../Task.php';

class SGL_Task_SetupPaths extends SGL_Task
{
    /**
     * Sets up the minimum paths required for framework execution.
     *
     * - SGL_SERVER_NAME must always be known in order to rewrite config file
     * - SGL_PATH is the filesystem root path
     * - pear include path is setup
     * - PEAR.php included for errors, etc
     * 
     * @param array $data
     */
    function run($data = null)
    {
        define('SGL_SERVER_NAME', $this->hostnameToFilename());        
        define('SGL_PATH', dirname(dirname(dirname((dirname(__FILE__))))));
        define('SGL_LIB_PEAR_DIR',              SGL_PATH . '/lib/pear');
        #define('SGL_LIB_PEAR_DIR',              '@PEAR-DIR@');
        
        $includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
        $allowed = @ini_set('include_path',      '.' . $includeSeparator . SGL_LIB_PEAR_DIR);
        if (!$allowed) {
            //  depends on PHP version being >= 4.3.0
            if (function_exists('set_include_path')) {
                set_include_path('.' . $includeSeparator . SGL_LIB_PEAR_DIR);
            } else {
                die('You need at least PHP 4.3.0 if you want to run Seagull
                with safe mode enabled.');
            }
        }
        require_once 'PEAR.php';
    }
    
    /**
     * Determines the name of the INI file, based on the host name.
     *
     * If PHP is being run interactively (CLI) where no $_SERVER vars
     * are available, a default 'localhost' is supplied.
     *
     * @return  string  the name of the host
     */
    function hostnameToFilename()
    {
        //  start with a default
        $hostName = 'localhost';
        if (php_sapi_name() != 'cli') {

            // Determine the host name
            if (!empty($_SERVER['SERVER_NAME'])) {
                $hostName = $_SERVER['SERVER_NAME'];
                
            } elseif (!empty($_SERVER['HTTP_HOST'])) {
                //  do some spoof checking here, like
                //  if (gethostbyname($_SERVER['HTTP_HOST']) != $_SERVER['SERVER_ADDR'])
                $hostName = $_SERVER['HTTP_HOST'];
            } else {
                //  if neither of these variables are set
                //  we're going to have a hard time setting up
                die('Could not determine your server name');
            }
            // Determine if the port number needs to be added onto the end
            if (!empty($_SERVER['SERVER_PORT']) 
                    && $_SERVER['SERVER_PORT'] != 80 
                    && $_SERVER['SERVER_PORT'] != 443) {
                $hostName .= '_' . $_SERVER['SERVER_PORT'];
            }
        }
        return $hostName;
    }
}

class SGL_Task_CreateConfig extends SGL_Task
{
    function run($data)
    {
        require_once SGL_PATH . '/lib/SGL/Config.php';        
        $c = &SGL_Config::singleton();
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
        $c->set('cookie', array('name' => $data['siteCookie']));
        $c->set('site', array('name' => $data['siteName']));
        $c->set('site', array('description' => $data['siteDesc']));
        $c->set('site', array('keywords' => $data['siteKeywords']));
        $c->set('site', array('language' => $data['siteLanguage']));
        
        //  save
        $configFile = SGL_PATH . '/var/' . SGL_SERVER_NAME . '.conf.php';
        $ok = $c->save($configFile);
        if (!$ok) {
            return SGL_Install::errorPush(PEAR::raiseError('Problem saving config'));
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
        return array('default', 'user');
    }
    
    function setup()
    {
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
                
        //  disable fk constraints if mysql (>= 4.1.x)
        if ($this->conf['db']['type'] == 'mysql' || $this->conf['db']['type'] == 'mysql_SGL') {                    
            $dbh = & SGL_DB::singleton();
            $query = 'SET FOREIGN_KEY_CHECKS=0;';
            $res = $dbh->query($query);
        }
        
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
    
    function tearDown()
    {
        //  re-enable fk constraints if mysql (>= 4.1.x)
        if ($this->conf['db']['type'] == 'mysql' || $this->conf['db']['type'] == 'mysql_SGL') {                    
            $dbh = & SGL_DB::singleton();
            $query = 'SET FOREIGN_KEY_CHECKS=1;';
            $res = $dbh->query($query);
        }
    }
}

class SGL_Task_CreateTables extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        require_once SGL_PATH . '/lib/SGL/Sql.php';
        define('SGL_BASE_URL', 'http://localhost/seagull/trunk/www');
        define('SGL_MOD_DIR', SGL_PATH . '/modules');
        
        if (!(array_key_exists('skipDbCreation', $data) && $data['skipDbCreation'] == 1)) {
            
            $this->setup();
            
            echo '<span class="title">Status: </span><span id="status"></span>
            <div id="progress_bar">
                <img src="' . SGL_BASE_URL . '/themes/default/images/progress_bar.gif" border="0" width="150" height="13">
            </div>
            <div id="additionalInfo"></div>';
            flush();

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
            
            $aModuleList = $this->getMinimumModuleList();
            
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
            $this->tearDown();
        }
    }
}

class SGL_Task_LoadDefaultData extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        if (!(array_key_exists('skipDbCreation', $data) && $data['skipDbCreation'] == 1)) {        
            $this->setup();
            
            $statusText .= ', loading data';
            $this->updateHtml('status', $statusText);
            
            //  Go back and load each module's default data, if there is a sql file in /data
            $aModuleList = $this->getMinimumModuleList();            
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
            $this->tearDown();
        }
    }   
}

class SGL_Task_CreateConstraints extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        if (!(array_key_exists('skipDbCreation', $data) && $data['skipDbCreation'] == 1)) {        
            $this->setup();
            
            $statusText .= ', loading constraints';
            $this->updateHtml('status', $statusText);
            
            //  Go back and load module foreign keys/constraints, if any
            $aModuleList = $this->getMinimumModuleList();            
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
            $this->tearDown();
        }
    }   
}

//  some more tests would be helpful
class SGL_Task_VerifyDbSetup extends SGL_UpdateHtmlTask
{
    function run($data)
    {
        //  verify db
        $dbh = & SGL_DB::singleton();
        $query = "SELECT COUNT(*) FROM {$this->conf['table']['permission']}";
        $res = $dbh->getAll($query);
        if (PEAR::isError($res, DB_ERROR_NOSUCHTABLE)) {
            return SGL_Install::errorPush(SGL::raiseError('No tables exist in DB - was schema created?'));
        }
        
        if (!(count($res))) {
            return SGL_Install::errorPush(SGL::raiseError('Perms inserts failed', SGL_ERROR_DBFAILURE));
        }      
        
        if (!(array_key_exists('skipDbCreation', $data) && $data['skipDbCreation'] == 1)) {
            
            //  note: must all be on one line for DOM text replacement
            $message = 'Database initialisation complete!';
            $this->updateHtml('status', $message);
            $body = '<p><a href=\\"' . SGL_BASE_URL . '/\\">LAUNCH SEAGULL</a> </p>NOTE: <strong>N/A</strong> indicates that a schema or data is not needed for this module';
            
        //  else only a DB connect was requested
        } else {
            $statusText = 'DB connect succeeded';
            $statusText .= ', Schema creation skipped';
            $this->updateHtml('status', $statusText);

            $body = '<p><a href=\\"' . SGL_BASE_URL . '/\\">LAUNCH SEAGULL</a> </p>';
        }
        
        //  done, create "launch seagull" link
        $this->updateHtml('additionalInfo', $body);
        $this->updateHtml('progress_bar', '');
    }   
}


class SGL_Task_CreateAdminUser extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_CreateFileSystem extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_CreateDataObjectEntities extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_SyncSequences extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_RemoveLockfile extends SGL_Task
{
    function run($data)
    {

    }   
}
?>