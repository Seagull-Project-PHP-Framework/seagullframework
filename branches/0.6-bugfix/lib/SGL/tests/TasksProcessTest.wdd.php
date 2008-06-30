<?php
require_once dirname(__FILE__) . '/../Task/Process.php';

/**
 * Test suite.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.net>
 * @version $Id: UrlTest.ndb.php,v 1.1 2005/06/23 14:56:01 demian Exp $
 */
class TasksProcessTest extends UnitTestCase {

    function TasksProcessTest()
    {
        $this->UnitTestCase('Tasks Process Test');
    }

    function setup()
    {
        //  reset errors and request
        SGL_Error::reset();
        $req = new SGL_Request();
        $req->reset();
    }

    function testProcessMissingModule()
    {
        //  setup input params
        $input = &SGL_Registry::singleton();
        $req   = &SGL_Request::singleton();
        $req->set('moduleName', 'doesnotexist');
        $input->setRequest($req);
        $output = &new SGL_Output();

        //  stop decorator chain
        $foo = new ProcFoo();
        $proc = new SGL_Task_ResolveManager($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);
        $this->assertEqual(SGL_Error::count(), 1);
        $oError = SGL_Error::getLast();
        $this->assertEqual($oError->getCode(), SGL_ERROR_RESOURCENOTFOUND);
    }

    function testProcessMissingManager()
    {
        //  setup input params
        $input = &SGL_Registry::singleton();
        $req   = &SGL_Request::singleton();
        $req->set('moduleName', 'default');
        $req->set('managerName', 'doesnotexist');
        $input->setRequest($req);
        $output = &new SGL_Output();

        //  stop decorator chain
        $foo = new ProcFoo();
        $proc = new SGL_Task_ResolveManager($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);
        $this->assertEqual(SGL_Error::count(), 1);
        $oError = SGL_Error::getLast();
        $this->assertEqual($oError->getCode(), SGL_ERROR_RESOURCENOTFOUND);
    }

    function testProcessMissingModulesConfigFile()
    {
        //  setup input params
        $input = &SGL_Registry::singleton();
        $req   = &SGL_Request::singleton();

        //  insert bogus module record so locating config file will fail
        $locator = &SGL_ServiceLocator::singleton();
        $dbh = $locator->get('DB');
        $conf = $input->getConfig();
        $id = $dbh->nextId($conf['table']['module']);
        $query = "INSERT INTO ".$conf['table']['module']." VALUES ($id, 1, 'bar', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', 'default/maintenance', '48/module_default.png', 'Demian Turner', NULL, 'BSD', 'beta')";
        $ret = $dbh->query($query);
        $req->set('moduleName', 'bar');
        $input->setRequest($req);
        $output = &new SGL_Output();

        //  stop decorator chain
        $foo = new ProcFoo();
        $proc = new SGL_Task_ResolveManager($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);
        $this->assertEqual(SGL_Error::count(), 1);
        $oError = SGL_Error::getLast();
        $this->assertEqual($oError->getCode(), SGL_ERROR_RESOURCENOTFOUND);
    }

    function testProcessSetupLangSupport()
    {
        $req   = &SGL_Request::singleton();
        $input = &SGL_Registry::singleton();

        $input->setRequest($req);
        $output = new SGL_Output();

        // stop decorator chain
        $foo = new ProcFoo();
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        $_SESSION['aPrefs']['language'] = 'en-utf-8';
        SGL_Config::set('translation.languageAutoDiscover', true);

        // 1. resolve language from request
        $req->set('lang', 'ru-windows-1251');

        // run the task
        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'ru-windows-1251');


        // 2. resolve language from request 2
        $req->set('lang', 'ru-utf-8');

        // run the task
        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'ru-utf-8');

        // clean request
        $req->set('lang', null);
        // clean first launch
        SGL_Session::isFirstAnonRequest($clean = true);
        SGL_Session::isFirstAuthenticatedRequest($clean = true);
        // clean settings
        unset($_SESSION['aPrefs']['language']);


        // 3. test language from browser
        SGL_Config::set('translation.fallbackLang', 'en_utf_8');
        $_SERVER["HTTP_ACCEPT_LANGUAGE"] = 'ru,lv';

        // run the task
        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'ru-utf-8');

        // clean first launch
        SGL_Session::isFirstAnonRequest($clean = true);
        SGL_Session::isFirstAuthenticatedRequest($clean = true);
        // clean settings
        unset($_SESSION['aPrefs']['language']);


        // 4. test language from browser 2
        $_SERVER["HTTP_ACCEPT_LANGUAGE"] = 'fr';

        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'fr-utf-8');

        // clean first launch
        SGL_Session::isFirstAnonRequest($clean = true);
        SGL_Session::isFirstAuthenticatedRequest($clean = true);
        // clean settings
        unset($_SESSION['aPrefs']['language']);


        // 5. test resolving from domain
        $_SERVER['HTTP_HOST'] = 'domain.tr';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';

        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'tr-utf-8');

        // change domain
        $_SERVER['HTTP_HOST'] = 'domain.lv';


        // 6. test settings
        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'tr-utf-8');

        // clean first launch
        SGL_Session::isFirstAnonRequest($clean = true);
        SGL_Session::isFirstAuthenticatedRequest($clean = true);
        // clean settings
        unset($_SESSION['aPrefs']['language']);


        // 7. test default language resolval
        $proc = new SGL_Task_SetupLangSupport($foo);
        $proc->processRequest = $foo;
        $proc->process($input, $output);

        $this->assertEqual($_SESSION['aPrefs']['language'], 'en-utf-8');

        // clean first launch
        SGL_Session::isFirstAnonRequest($clean = true);
        SGL_Session::isFirstAuthenticatedRequest($clean = true);
        // restore env
        unset($_SESSION['aPrefs']['language']);
        if (isset($_SERVER['HTTP_HOST']) && isset($host)) {
            $_SERVER['HTTP_HOST'] = $host;
        }
    }
}

class ProcFoo
{
    function process($in, $out)
    {
        return true;
    }
}

?>