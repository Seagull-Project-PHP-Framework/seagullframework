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

class SGL_Task_CreateTables extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_LoadDefaultData extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_CreateAdminUser extends SGL_Task
{
    function run($data)
    {

    }   
}



class SGL_Task_VerifyDbSetup extends SGL_Task
{
    function run($data)
    {

    }   
}

class SGL_Task_CreateConstraints extends SGL_Task
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