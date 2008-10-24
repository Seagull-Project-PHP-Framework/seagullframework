<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | Seagull 2.0                                                               |
// +---------------------------------------------------------------------------+
// $Id$


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
    protected $_processRequest;

    public function __construct(SGL2_ProcessRequest $pr)
    {
        $this->_processRequest = $pr;
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