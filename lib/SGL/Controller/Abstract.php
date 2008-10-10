<?php

/**
 * Abstract request processor.
 *
 * @abstract
 * @package SGL
 *
 */
abstract class SGL_ProcessRequest
{
    abstract public function process(SGL_Request $input, SGL_Response $output);
}

/**
 * Decorator.
 *
 * @abstract
 * @package SGL
 */
abstract class SGL_DecorateProcess extends SGL_ProcessRequest
{
    protected $processRequest;

    public function __construct(SGL_ProcessRequest $pr)
    {
        $this->processRequest = $pr;
    }
}

/**
 * Core data processing routine.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_MainProcessBc extends SGL_ProcessRequest
{
    public function process(SGL_Request $input, SGL_Response $output)
    {
        foreach ($input->getAll() as $k => $v) {
            $output->set($k, $v);
        }
    }
}

abstract class SGL_Controller_Abstract
{
    protected $_router;

    public function getRouter()
    {
        if (is_null($this->_router)) {
            $this->setRouter(new SGL_Router());
        }
        return $this->_router;
    }

    public function setRouter($router)
    {
        $this->_router = $router;
        return true;
    }

    abstract public function run();

    public function init()
    {
        SGL_Registry::set('request',    new SGL_Request());
        SGL_Registry::set('response',   new SGL_Response());
        SGL_Registry::set('config',     new SGL_Config2());

        $this->setupEnv();

        define('SGL_INITIALISED', true);
    }

    public function setupEnv()
    {
        $init = new SGL_TaskRunner();
        $init->addData(SGL_Config2::getAll());
        $init->addTask(new SGL_Task_SetupConstants());
        $init->main();
    }
}
?>