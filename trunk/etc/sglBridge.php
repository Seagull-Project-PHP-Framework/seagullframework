<?php
//  setup seagull environment
require_once dirname(__FILE__)  . '/../lib/SGL/AppController.php';
require_once dirname(__FILE__)  . '/../tests/classes/DB.php';

class TestRunnerInit extends SGL_AppController
{
    function run()
    {
        if (!defined('SGL_INITIALISED')) {
            SGL_AppController::init();
        }

        //  get config singleton
        $c = &SGL_Config::singleton();
        $c->set('debug', array('customErrorHandler' => false));
        $conf = $c->getAll();

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        //  get current url object
        $urlHandler = $conf['site']['urlHandler'];
        $url = new SGL_URL($_SERVER['PHP_SELF'], true, new $urlHandler());

        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($url);
        $input->setRequest($req = SGL_Request::singleton());

        $process =  new SGL_Process_Init(
                    new SGL_Process_DiscoverClientOs(
                    new SGL_Process_SetupTestDb(
                    new SGL_Process_SetupTestDbResource(
                    new SGL_Process_ResolveManager(
                    new SGL_Process_CreateSession(
                    new SGL_Process_SetupLangSupport(
                    new SGL_Process_SetupPerms(
                    new SGL_Process_AuthenticateRequest(
                    new SGL_Process_BuildHeaders(
                    new SGL_Process_SetupLocale(
                    new SGL_Void()
                   )))))))))));

        $process->process($input);
    }
}

class SGL_Process_SetupTestDb extends SGL_DecorateProcess
{
    function process(&$input)
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
        $dbh = &SGL_DB::singleton($dsn);

        $query = 'DROP DATABASE IF EXISTS ' . $conf['database']['name'];
        $result = $dbh->query($query);
        $query = 'CREATE DATABASE ' . $conf['database']['name'];
        $result = $dbh->query($query);
        $this->processRequest->process($input);
    }
}

class SGL_Process_SetupTestDbResource extends SGL_DecorateProcess
{
    function process(&$input)
    {
        $locator = &SGL_ServiceLocator::singleton();
        //  in case
        $locator->remove('DB');
        $dbh =& STR_DB::singleton();
        $locator->register('DB', $dbh);

        $this->processRequest->process($input);
    }
}

TestRunnerInit::run();

$includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
ini_set('include_path', ini_get('include_path') . $includeSeparator . '/usr/local/lib/php');
?>