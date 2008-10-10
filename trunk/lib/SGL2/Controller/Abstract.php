<?php

/**
 * Abstract request processor.
 *
 * @abstract
 * @package SGL
 *
 */
abstract class SGL2_ProcessRequest
{
    abstract public function process(SGL2_Request $input, SGL2_Response $output);
}

/**
 * Decorator.
 *
 * @abstract
 * @package SGL
 */
abstract class SGL2_DecorateProcess extends SGL2_ProcessRequest
{
    protected $processRequest;

    public function __construct(SGL2_ProcessRequest $pr)
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
class SGL2_MainProcessBc extends SGL2_ProcessRequest
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        foreach ($input->getAll() as $k => $v) {
            $output->set($k, $v);
        }
    }
}

abstract class SGL2_Controller_Abstract
{
    protected $_router;

    public function getRouter()
    {
        if (is_null($this->_router)) {
            $this->setRouter(new SGL2_Router());
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
        SGL2_Registry::set('request',    new SGL2_Request());
        SGL2_Registry::set('response',   new SGL2_Response());
        SGL2_Registry::set('config',     new SGL2_Config());

        $this->setupEnv();

        define('SGL2_INITIALISED', true);
    }

    public function setupEnv()
    {
        $init = new SGL2_TaskRunner();
        $init->addData(SGL2_Config::getAll());
        $init->addTask(new SGL2_Task_SetupConstants());
        $init->main();
    }
}
?>