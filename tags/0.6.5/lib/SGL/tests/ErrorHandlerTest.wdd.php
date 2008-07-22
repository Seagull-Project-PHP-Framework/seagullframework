<?php

/**
 * Tests for various types of errors that Seagull wraps.
 *
 * @todo implement as web tests to demonstrate GUI error wrapping
 *
 */
class ErrorHandlerTest extends UnitTestCase
{
    function ErrorHandlerTest($name='Test of Error handling')
    {
        $this->UnitTestCase($name);
    }

    function setUp()
    {
        #$c = &SGL_Config::singleton();
        #$this->conf = $c->getAll();
        #$this->addHeader('User-agent: foo-bar');
    }
    function tearDown() { }

    function testWarningError()
    {
//        $process = new TestMainProcess();
//        $input = &SGL_Registry::singleton();
//        $input->set('manager', new SGL_VoidMgr());
//
//        $process->process($input);
//
//        $this->get($this->conf['site']['baseUrl']);
//        $this->showSource();

        $fp = 'not_a_file_handle';
        $row = fgets($fp, 1024);
        $str = preg_quote("fgets(): supplied argument is not a valid stream resource");
        $this->assertErrorPattern("/$str/");
    }

    function testNoticeError()
    {
        $var[ttest] = 'hello';
        $str = preg_quote("Use of undefined constant ttest - assumed 'ttest'");
        $this->assertErrorPattern("/$str/");
    }

    function testUserForcedError()
    {
        trigger_error('trigger msg test', E_USER_NOTICE);
        $str = preg_quote("trigger msg test");
        $this->assertErrorPattern("/$str/");
    }

    function testSglRaiseError()
    {
        SGL::raiseError('test PEAR error msg', SGL_ERROR_INVALIDARGS);
        $this->assertTrue(count($GLOBALS['_SGL']['ERRORS']));
    }

    //  this indeed stops program execution
    function xtestSglFatalError()
    {
        SGL::raiseError('test fatal error msg', SGL_ERROR_INVALIDARGS, PEAR_ERROR_DIE);
    }

    //  this indeed stops program execution
    function xtestPearFatalError()
    {
        PEAR::raiseError('test PEAR fatal error', SGL_ERROR_INVALID_CALL, PEAR_ERROR_DIE);
    }

    function testPearDbError()
    {
        $dbh = & SGL_DB::singleton();
        $query = "SELECT  u.non_existent_field FROM users u";
        $result = $dbh->query($query);
        $this->assertIsA($result, 'db_error');
    }

}

class SGL_VoidMgr extends SGL_Manager
{
    function SGL_VoidMgr()
    {
        $this->_aActionsMapping =  array(
            'add'       => array('add'),
            'list'      => array('list'),
        );
    }

    function validate($req, &$input) {}

    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        //  do nothing
    }
}

class TestMainProcess
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $req = $input->getRequest();

        $mgr = $input->get('manager');
        $mgr->validate($req, $input);

        $output = & new SGL_Output();
        $input->aggregate($output);

        //  process data if valid
        if ($mgr->isValid()) {
            $mgr->process($input, $output);
        }

        $mgr->display($output);

        //  build view
//        $view = new SGL_HtmlView($output, new SGL_HtmlFlexyRendererStrategy());
//        echo $view->render();
    }
}

?>