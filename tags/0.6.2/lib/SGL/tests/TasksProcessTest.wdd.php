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
}

class ProcFoo
{
    function process($in, $out)
    {
        return true;
    }
}

?>