<?php
//  setup seagull environment
require_once dirname(__FILE__)  . '/../lib/SGL/FrontController.php';
require_once dirname(__FILE__)  . '/../lib/SGL/Sql.php';
require_once dirname(__FILE__)  . '/../tests/classes/DB.php';

define('SGL_INSTALLED', true);

class TestRunnerInit extends SGL_FrontController
{
    public static function run()
    {
        define('SGL_TEST_MODE', true);

        if (!defined('SGL_INITIALISED')) {
            SGL_FrontController::init();
        }
        //  assign request to registry
        $input = SGL_Registry::singleton();
        $req = SGL_Request::singleton();

        if (PEAR::isError($req)) {
            //  stop with error page
            SGL::displayStaticPage($req->getMessage());
        }
        $input->setRequest($req);
        $output = new SGL_Output();

        $process =  new SGL_Task_Init(
                    new SGL_Task_DiscoverClientOs(
                    new SGL_Task_SetupTestDb(
                    new SGL_Task_SetupTestDbResource(
                    new SGL_Task_MinimalSession(
                    new SGL_Task_SetupLangSupport(
                    new SGL_Void()
                   ))))));

        $process->process($input, $output);
    }
}

class SGL_Task_SetupTestDb extends SGL_DecorateProcess
{
    function process($input, $output)
    {
        $conf = $GLOBALS['_STR']['CONF'];
        if ($conf['db']['type'] == 'pgsql'){
            $excludeDbName = false;
        } else {
            $excludeDbName = true;
        }
        $dsn = SGL_DB::_getDsnAsString($conf, $excludeDbName);
        $dbh = SGL_DB::singleton($dsn);
        if (PEAR::isError($dbh)) {
            die($dbh->getMessage());
        }

        $query1 = SGL_Sql::buildDbDropStatement($conf['db']['type'], $conf['db']['name']);
        $query2 = SGL_Sql::buildDbCreateStatement($conf['db']['type'], $conf['db']['name']);
        $result = $dbh->query($query1);
        $result = $dbh->query($query2);
        $this->processRequest->process($input, $output);
    }
}

class SGL_Task_SetupTestDbResource extends SGL_DecorateProcess
{
    function process($input, $output)
    {
        $locator = SGL_ServiceLocator::singleton();
        //  in case
        $locator->remove('DB');
        $dbh = STR_DB::singleton();
        $locator->register('DB', $dbh);

        $this->processRequest->process($input, $output);
    }
}

class SGL_Task_MinimalSession extends SGL_DecorateProcess
{
    function process($input, $output)
    {
        session_start();
        $_SESSION['uid'] = 1;
        $_SESSION['rid'] = 1;
        $_SESSION['aPrefs'] = array();

        $this->processRequest->process($input, $output);
    }
}

//  value from php.ini, before sgl modifies it
$oldIncludePath = ini_get('include_path');

TestRunnerInit::run();

//  add global path, so SimpleTest lib can be included
$includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
ini_set('include_path', ini_get('include_path') . $includeSeparator . $oldIncludePath);
?>