<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004, Gerry Lachac                                          |
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
// | SetupWizard.php                                                           |
// +---------------------------------------------------------------------------+
// | Authors:                                                                  |
// |            Gerry Lachac <glachac@tethermedia.com>                         |
// |            Demian Turner <demian@phpkitchen.com>                          |
// |            Andy Crain <apcrain@fuse.net>                                  |
// +---------------------------------------------------------------------------+
// $Id: SetupWizard.php,v 1.29 2005/06/23 18:21:25 demian Exp $

require_once SGL_LIB_DIR . '/SGL.php';
require_once SGL_CORE_DIR . '/Sql.php';
require_once 'Config.php';

/**
 * A wizard to install and configure the application.
 *
 * @package SGL
 * @author  Gerry Lachac <glachac@tethermedia.com>
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Andy Crain <apcrain@fuse.net>
 * @version $Revision: 1.29 $
 * @since   PHP 4.1
 */
class SGL_SetupWizard
{
    var $conf;

    function SGL_SetupWizard($conf)
    {
        $this->conf = $conf;
    }

    /**
     * Process the quick form.  IF skip was chosen, set up conf.ini to skip over bootstrap
     * otherwise try and initialize the database, using the Seagull Base:DB  methods
     *
     * @author  Gerry Lachac <glachac@tethermedia.com>   
     * @access  public
     * @static
     * @param   string  $data   data from form
     * @return  void
     */
    function processSettings($data)
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        // Update conf with new values from form
        if ($data['type'] == 0) {
            $conf['db']['type'] = 'mysql_SGL';
        } elseif ($data['type'] == 1) {
            $conf['db']['type'] = 'mysql';
        } elseif ($data['type'] == 2) {
            $conf['db']['type'] = 'pgsql';
        } elseif ($data['type'] == 3) {
            $conf['db']['type'] = 'oci8_SGL';
        } else {
            $conf['db']['type'] = 'odbc';
        }
        if (!isset($data['port']) || $data['port']==0) {
            $conf['db']['port'] = '3306';
        } elseif ($data['port']==1)  {
            $conf['db']['port'] = '5432';
        } elseif ($data['port']==2)  {
            $conf['db']['port'] = '1521';
        }
        if ($data['protocol']==0) {
            $conf['db']['protocol'] = 'unix';
        } else {
            $conf['db']['protocol'] = 'tcp';
        }

        $conf['db']['host'] = $data['host'];
        $conf['db']['user'] = $data['user'];
        $conf['db']['pass'] = $data['pass'];
        $conf['db']['name'] = $data['name'];

        // Write and re-read conf to make sure we are accessing DB with what seagull would
        // normally read on startup
        $file = SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.php';
        $ok = $c->save($file);

        //  re-read conf for verification, reset $conf
        $conf = $c->load($file);
        
        echo '<span class="title">Status: </span><span id="status"></span>
        <div id="progress_bar">
            <img src="' . SGL_BASE_URL . '/themes/default/images/progress_bar.gif" border="0" width="150" height="13">
        </div>
        <div id="additionalInfo"></div>';
        flush();

        $statusText = 'DB connect succeeded';

        if ($this->canConnectToDb()) {
            $this->updateHtml('status', $statusText);

            //  if schema should be created
            $setupType = $_GET['setupType'];
            if (array_key_exists('createSchema', $setupType)) {

                switch ($conf['db']['type']) {
                case 'pgsql':
                    $dbType = 'pgsql';
                    $filename1 = '/schema.pg.sql';
                    $filename2 = '/data.default.pg.sql';
                    $filename3 = '/constraints.pg.sql';
                    break;

                case 'mysql':
                    $dbType = 'mysql';
                    $filename1 = '/schema.my.sql';
                    $filename2 = '/data.default.my.sql';
                    $filename3 = '/constraints.my.sql';
                    break;

                case 'mysql_SGL':
                    $dbType = 'mysql_SGL';
                    $filename1 = '/schema.my.sql';
                    $filename2 = '/data.default.my.sql';
                    $filename3 = '/constraints.my.sql';
                    break;

                case 'oci8_SGL':
                    $dbType = 'oci8';
                    $filename1 = '/schema.oci.sql';
                    $filename2 = '/data.default.oci.sql';
                    $filename3 = '/constraints.oci.sql';
                    break;
                }

                $statusText .= ', fetching modules';
                $this->updateHtml('status', $statusText);

                //  Retrieve list of modules
                $aModuleList = $this->getModuleList();

                //  Print table shell, with module names; we'll update statuses as we execute sql below
                echo '<table class="wide">
                    <tr>
                        <th class="alignCenter">Module</th>
                        <th class="alignCenter">Create Table</th>
                        <th class="alignCenter">Load Data</th>
                        <th class="alignCenter">Add Constraints</th>
                    </tr>
                    <tr>
                        <td class="title">Main</td>
                        <td id="etc_schema" class="alignCenter"></td>
                        <td id="etc_data" class="alignCenter"></td>
                        <td id="etc_constraints" class="alignCenter"></td>
                    </tr>            
                ';
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

                set_time_limit(60);

                //  these hold what to display in results grid, depending on outcome
                $success = '<img src=\\"' . SGL_BASE_URL . '/themes/default/images/enabled.gif\\" border=\\"0\\" width=\\"22\\" height=\\"22\\">' ;
                $failure = '<span class=\\"error\\">ERROR</span>';
                $noFile = '<strong>N/A</strong>';

                $statusText .= ', creating and loading tables';
                $this->updateHtml('status', $statusText);
                
                //  disable fk constraints if mysql (>= 4.1.x)
                if ($conf['db']['type'] == 'mysql' || $conf['db']['type'] == 'mysql_SGL') {                    
                    $dbh = & SGL_DB::singleton();
                    $query = 'SET FOREIGN_KEY_CHECKS=0;';
                    $res = $dbh->query($query);
                }
                
                //  Load SGL schema (/etc)
                $sglPath = SGL_PATH . '/etc';
                $result = SGL_Sql::parseAndExecute($sglPath . $filename1, 0);
                
                //  load 'sequence' table
                if ($conf['db']['type'] == 'mysql_SGL') {
                    $result = SGL_Sql::parseAndExecute($sglPath . '/sequence.my.sql', 0);
                }

                //  catch 'table already exists' error
                if (DB::isError($result, DB_ERROR_ALREADY_EXISTS)) {
                    $this->updateHtml('status', 'Tables already exist');
                    $body = 'It appears that the schema already exists.  Click <a href=\\"index.php\\">here</a> to return to the configuration screen and choose \\"Only set DB connection details\\".';
                    $this->updateHtml('additionalInfo', $body);
                    $this->updateHtml('progress_bar', '');
                    exit;
                }
                $displayHtml = $result ? $success : $failure;
                $this->updateHtml('etc_schema', $displayHtml);

                //  Load SGL data (/etc)
                $result = SGL_Sql::parseAndExecute($sglPath . $filename2, 0);
                $displayHtml = $result ? $success : $failure;
                $this->updateHtml('etc_data', $displayHtml);

                //  Load SGL constraints (/etc)
                $result = SGL_Sql::parseAndExecute($sglPath . $filename3, 0);
                $displayHtml = $result ? $success : $failure;
                $this->updateHtml('etc_constraints', $displayHtml);

                //  Load each module's schema, if there is a sql file in /data
                foreach ($aModuleList as $module) {
                    $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                    //  Load the module's schema
                    if (file_exists($modulePath . $filename1)) {
                        $result = SGL_Sql::parseAndExecute($modulePath . $filename1, 0);
                        $displayHtml = $result ? $success : $failure;
                        $this->updateHtml($module . '_schema', $displayHtml);
                    } else {
                        $this->updateHtml($module . '_schema', $noFile);
                    }
                }

                $statusText .= ', loading data';
                $this->updateHtml('status', $statusText);
                
                //  Go back and load each module's default data, if there is a sql file in /data
                foreach ($aModuleList as $module) {
                    $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';

                    //  Load the module's data
                    if (file_exists($modulePath . $filename2)) {
                        $result = SGL_Sql::parseAndExecute($modulePath . $filename2, 0);
                        $displayHtml = $result ? $success : $failure;
                        $this->updateHtml($module . '_data', $displayHtml);
                    } else {
                        $this->updateHtml($module . '_data', $noFile);
                    }
                }

                $statusText .= ', loading constraints';
                $this->updateHtml('status', $statusText);
                
                //  Go back and load module foreign keys/constraints, if any
                foreach ($aModuleList as $module) {
                    $modulePath = SGL_MOD_DIR . '/' . $module  . '/data';
                    if (file_exists($modulePath . $filename3)) {
                        $result = SGL_Sql::parseAndExecute($modulePath . $filename3, 0);
                        $displayHtml = $result ? $success : $failure;
                        $this->updateHtml($module . '_constraints', $displayHtml);
                    } else {
                        $this->updateHtml($module . '_constraints', $noFile);
                    }
                }
                
                //  re-enable fk constraints if mysql (>= 4.1.x)
                if ($conf['db']['type'] == 'mysql' || $conf['db']['type'] == 'mysql_SGL') {                    
                    $dbh = & SGL_DB::singleton();
                    $query = 'SET FOREIGN_KEY_CHECKS=1;';
                    $res = $dbh->query($query);
                }

                //  note: must all be on one line for DOM text replacement
                $message = 'Database initialisation complete!';
                $this->updateHtml('status', $message);
                $body = '<p><a href=\\"' . SGL_BASE_URL . '/\\">LAUNCH SEAGULL</a> (Please login as username: admin, password: admin, go to My Account and change the default email address and password <strong>ASAP</strong>.)</p>NOTE: <strong>N/A</strong> indicates that a schema or data is not needed for this module';
                
            //  else only a DB connect was requested
            } else {
                $statusText = 'DB connect succeeded';
                $statusText .= ', Schema creation skipped';
                $this->updateHtml('status', $statusText);

                $body = '<p><a href=\\"' . SGL_BASE_URL . '/\\">LAUNCH SEAGULL</a> (Please login as username: admin, password: admin, go to My Account and change the default email address and password <strong>ASAP</strong>.)</p>';
            }
            
            //  sanity check
            $ok = SGL_Sql::verifyDbSetup();
            if (PEAR::isError($ok)) {
                $title = 'DB setup failed';                
                $body = '<p>There was a problem with the DB setup, please use following diagnostic info to correct problem: </p>';
                $body .= '<pre>' . $ok->getMessage() . '</pre>';
                $body .= 'Click <a href=\\"index.php\\">here</a> to return to the configuration screen.</p>';
                $this->updateHtml('status', $title);
                $this->updateHtml('additionalInfo', $body);
                $this->updateHtml('progress_bar', '');
                flush();            
                exit;
            }
            
            //  generate DO files if they don't exist yet
            $this->generateDataObjectEntities();

            //  adjust sequences
            $res = SGL_Sql::rebuildSequences();
            
            //  set framework version
            $c->set('tuples', array('version' => $this->getFrameworkVersion()));

            //  done, create "launch seagull" link
            $this->updateHtml('additionalInfo', $body);
            $this->updateHtml('progress_bar', '');
            
            //  Disable db bootstrap mode
            $c->set('db', array('bootstrap' => 0));
            $ok = $c->save(SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.php');
        }
    }
    
    function getFrameworkVersion()
    {
        $version = file_get_contents(SGL_PATH . '/VERSION.txt');
        return $version;
    }

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

    function canConnectToDb()
    {
        // We don't return from here on error because SGL_DB::singleton() dies.  Shut off error reporting because
        // logging isn't set up correctly yet
        error_reporting(0);
        $dbh = & SGL_DB::singleton();
        error_reporting(E_WARNING);

        if (PEAR::isError($dbh)) {
            $title = 'DB connect failed';
            $body = 'Check that your db is up and running and that the database name is the same as you specified in the form.  Click <a href=\\"index.php\\">here</a> to return to the configuration screen.';
            $body .= '<p>Additionally, the following error was reported: ' . $dbh->getMessage(). '</p>';
            $body .= '<pre>' . $dbh->getDebugInfo() . '</pre>';
            $this->updateHtml('status', $title);
            $this->updateHtml('additionalInfo', $body);
            $this->updateHtml('progress_bar', '');
            flush();            
            exit;
        } else {
            return true;
        }
    }

    /**
     * Using QuickForm, present a really simple form to ask users to enter correct database
     * access information.  
     *
     * Two buttons.  One skips this entirely.  The second tries to access the DB.  If it works, then
     * it initializes the DB.
     *
     * @author  Gerry Lachac <glachac@tethermedia.com>   
     * @access  public
     * @static
     * @return  void
     */
    function run()
    {
        // If conf has no bootstrap var, add one to set our bootstrap state to 1 (exec bootstrap code)
        if (!isset($this->conf['db']['bootstrap'])) {
            $c = &SGL_Config::singleton();
            $c->set('db', array('bootstrap' => 1));
            $ok = $c->save(SGL_PATH . '/var/' . SGL_SERVER_NAME . '.default.conf.php');
        }

        //  clear session cookie so theme comes from DB and not session
        setcookie(  $this->conf['cookie']['name'], null, 0, $this->conf['cookie']['path'], 
                    $this->conf['cookie']['domain'], $this->conf['cookie']['secure']);

        echo '
            <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
            <head>
            <link rel="stylesheet" type="text/css" href="' . SGL_BASE_URL . '/themes/default/css/style.php" />
            </head>
            <body style="margin: 0 auto; padding: 10">
            <h1> Seagull Database Initialisation </h1>';

        $instructions = '
            <p>Fill out the form with the information on how to access your seagull database.</p>
            <p>You must have already created a database named \'seagull\' or whatever you specify below.</p>
            <hr>';
        $warning = <<<EOF
            <p style="color:#FF0000"><strong>Note</strong>: It appears you're using Apache as a webserver
            so you should consider copying the file 'htaccess.dist' from the etc folder to the root of your
            Seagull install, and renaming it .htaccess.  This will prevent users from being able
            to view the contents of ini files where configuration details are kept.  If you are using
            a virtual host setup that exposes only the www directory then disregard this notice.</p>
EOF;
        require_once 'HTML/QuickForm.php';
        
        $deleteConfigFlag = empty($_GET['deleteConfig']) ? false : true;
        
        //  unholy hack required for quickform ...
        if (!isset($_GET['btnSubmit'])) {
            $_GET = array();
        }
        $form = & new HTML_QuickForm('frmInstall', 'get');

        $form->setDefaults(array(
            'port'  => 0,
            'setupType[setConnectionDetails]'  => true,
            ));

        $form->addElement('header','MyHeader', 'Database parameters');
        $form->addElement('text',  'name',     'Database name: ');
        $form->addElement('radio', 'type',     'Database type: ',"mysql_SGL (all sequences in one table)", 0);
        $form->addElement('radio', 'type',     '', "mysql", 1);
        $form->addElement('radio', 'type',     '', "postgres",2);
        $form->addElement('radio', 'type',     '', "oci8",3);
        $form->addElement('text',  'host',     'Host: ');
        $form->addElement('radio', 'protocol', 'Protocol: ',"unix (fine for localhost connections)", 0);
        $form->addElement('radio', 'protocol', '',"tcp", 1);
        $form->addElement('radio', 'port',     'TCP port: ',"3306 (Mysql default)",0);
        $form->addElement('radio', 'port',     '',"5432 (Postgres default)",1);
        $form->addElement('radio', 'port',     '',"1521 (Oracle default)",2);
        $form->addElement('text',  'user',     'Database username: ');
        $form->addElement('password', 'pass', 'Database password: ');

        $form->addElement('header','MyHeader', 'Setup');
        
        $checkbox[] = &HTML_QuickForm::createElement('checkbox', 'createSchema', null, 'create schema');
        $checkbox[] = &HTML_QuickForm::createElement('checkbox', 'loadDefaultData', null, 'load default data');
        $checkbox[] = &HTML_QuickForm::createElement('checkbox', 'setConnectionDetails', null, 'set connection details');
        $form->addGroup($checkbox, 'setupType', 'Actions', '<br />');    
     
        $form->addElement('submit', 'btnSubmit', 'Execute (pls be patient if schema creation selected)');

        $form->setDefaults($this->conf['db']);
        
        //  get around probs with parse_ini_file's inability to handle non-alphanumeric chars
        $form->addRule('pass', 'Please use only alphanumeric chars for DB password', 'alphanumeric', null, 'client');

        if (!$deleteConfigFlag && $form->validate()) {
            $form->process(array(&$this, 'processSettings'));
        } else {
            print $instructions;

            //  if apache webserver, prompt for .htaccess install
            if (preg_match('/apache/i', @$_SERVER['SERVER_SOFTWARE'])
                    && (!file_exists(SGL_PATH . '/.htaccess'))) {
                print $warning;
            }
            $form->display();
        }
        echo "</body></html>";

        //  create 'finished' flag to prevent against hostname spoofs
        file_put_contents(SGL_PATH . '/var/INSTALL_COMPLETE', '');
        
        //  Done
        exit();
    }

    function generateDataObjectEntities()
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

        include_once 'System.php';
        include_once 'DB/DataObject/Generator.php';
        System::mkDir(array(SGL_CACHE_DIR));
        System::mkDir(array(SGL_ENT_DIR));

        //  create dataobject entities
        $res = SGL_Sql::generateDataObjectEntities();

        if (PEAR::isError($res)) {
            print $res->getMessage();
            print $res->getCode();
            return PEAR::raiseError('generating DB_DataObject entities failed', PEAR_ERROR_DIE);
        }

        //  copy over links file
        @copy(SGL_PATH . '/etc/links.ini.dist', 
            SGL_ENT_DIR . '/' . $conf['db']['name'] . '.links.ini');
    }

    function getModuleList() 
    {
        $dir =  SGL_MOD_DIR;
        $fileList = array();
        $stack[] = $dir;
        while ($stack) {
            $currentDir = array_pop($stack);
            if ($dh = opendir($currentDir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file !== '.' && $file !== '..' && $file !== '.svn') {
                        $currentFile = "{$currentDir}/{$file}";
                        if (is_dir($currentFile)) {
                            $fileList[] = "{$file}";
                        }
                    }
               }
           }
       }
       return $fileList;
    }
}
?>