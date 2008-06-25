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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Init.php                                                                  |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Init.php,v 1.85 2005/06/22 00:40:44 demian Exp $

/**
 * Basic init tasks: sets up paths, contstants, include_path, etc.
 *
 * @author  Demian Turner <demian@phpkitchen.com>
 */

// if SGL_FrontController::init() called without index.php
if (!isset($GLOBALS['varDir'])) {
    $GLOBALS['varDir']  = realpath(dirname(__FILE__) . '/../../../var');
    $GLOBALS['rootDir'] = realpath(dirname(__FILE__) . '/../../../');
}

/**
 * @package Task
 */
class SGL_Task_InitialiseDbDataObject extends SGL_Task
{
    function run()
    {
        $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
        $options = array(
            'database'              => SGL_DB::getDsn(SGL_DSN_STRING),
            'schema_location'       => SGL_ENT_DIR,
            'class_location'        => SGL_ENT_DIR,
            'require_prefix'        => SGL_ENT_DIR . '/',
            'class_prefix'          => 'DataObjects_',
            'debug'                 => SGL_Config::get('debug.dataObject'),
            'production'            => 0,
            'ignore_sequence_keys'  => 'ALL',
            'generator_strip_schema'=> 1,
            'quote_identifiers'     => 1,
        );
    }
}

/**
 * @package Task
 */
class SGL_Task_EnsurePlaceholderDbPrefixIsNull extends SGL_Task
{
    function run($conf)
    {
        // for 0.6.x versions
        if (!empty($conf['db']['prefix'])
                && $conf['db']['prefix'] == 'not implemented yet') {
            $config = &SGL_Config::singleton();
            $config->set('db', array('prefix' => ''));
            $config->save();
        }
    }
}

//          $userInfo = posix_getpwuid(fileowner($configFile));
//          $fileOwnerName = $userInfo['name'];
//          $allowedFileOwners = array('nobody', 'apache');
//
//          if (!in_array($fileOwnerName, $allowedFileOwners)) {
//                die("<br />Your config file in the seagull/var directory has the wrong " .
//                  "owner (currently set as: $fileOwnerName). " .
//                    "Please set the correct file owner to this directory and it's contents, eg:<br/>" .
//                    "<code>'chmod -R 777 seagull/var'</code>");
//          }


/**
 * @package Task
 */
class SGL_Task_RegisterTrustedIPs extends SGL_Task
{
    function run($data)
    {
        //  only IPs defined here can access debug sessions and delete config files
        $GLOBALS['_SGL']['TRUSTED_IPS'] = array(
            '127.0.0.1',
        );
    }
}

/**
 * @package Task
 */
class SGL_Task_InitialiseModules extends SGL_Task
{
    function run()
    {
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  skip if we're in installer
        if (defined('SGL_INSTALLED')) {
            $locator = &SGL_ServiceLocator::singleton();
            $dbh = $locator->get('DB');
            if (!$dbh) {
                $dbh = & SGL_DB::singleton();
                $locator->register('DB', $dbh);
            }
            //  this task can be called when installing a new module
            if (!empty($conf['aModuleList'])) {
                $oMod = new stdClass();
                $oMod->name = $conf['aModuleList'][0];
                $aRet[] = $oMod;
            } else {
                $query = "
                    SELECT  name
                    FROM    {$conf['table']['module']}
                    ";
                $aRet = $dbh->getAll($query);
            }
            if (is_array($aRet) && count($aRet)) {
                foreach ($aRet as $oModule) {
                    $moduleInitFile = SGL_MOD_DIR . '/' . $oModule->name . '/init.php';
                    if (is_file($moduleInitFile)) {
                        require_once $moduleInitFile;
                    }
                }
            }
        }
    }
}

    // +---------------------------------------+
    // | Abstract classes                      |
    // +---------------------------------------+

/**
 * Abstract renderer strategy
 *
 * @abstract
 * @package SGL
 */
class SGL_OutputRendererStrategy
{
    /**
     * Prepare renderer options.
     *
     */
    function initEngine() {}

    /**
     * Abstract render method.
     *
     * @param SGL_View $view
     */
    function render($view) {}
}

/**
 * Container for output data and renderer strategy.
 *
 * @abstract
 * @package SGL
 */
class SGL_View
{
    /**
     * Output object.
     *
     * @var SGL_Output
     */
    var $data;

    /**
     * Reference to renderer strategy.
     *
     * @var SGL_OutputRendererStrategy
     */
    var $rendererStrategy;

    /**
     * Constructor.
     *
     * @param SGL_Output $data
     * @param SGL_OutputRendererStrategy $rendererStrategy
     * @return SGL_View
     */
    function SGL_View(&$data, $rendererStrategy)
    {
        $this->data = &$data;
        $this->rendererStrategy = $rendererStrategy;
    }

    /**
     * Post processing tasks specific to view type.
     *
     * @abstract
     * @return boolean
     */
    function postProcess() {}


    /**
     * Delegates rendering strategy based on view.
     *
     * @return string   Rendered output data
     */
    function render()
    {
        return $this->rendererStrategy->render($this);
    }
}

/**
 * Wrapper for simple HTML views.
 *
 * @package SGL
 */
class SGL_HtmlSimpleView extends SGL_View
{
    /**
     * HTML renderer decorator
     *
     * @param SGL_Output $data
     * @return string   Rendered output data
     */
    function SGL_HtmlSimpleView(&$data, $templateEngine = null)
    {
        //  prepare renderer class
        if (!$templateEngine) {
            $templateEngine = SGL_Config::get('site.templateEngine');
        }
        $templateEngine = ucfirst($templateEngine);
        $rendererClass  = 'SGL_HtmlRenderer_' . $templateEngine . 'Strategy';
        $rendererFile   = $templateEngine.'Strategy.php';

        if (is_file(SGL_LIB_DIR . '/SGL/HtmlRenderer/' . $rendererFile)) {
            require_once SGL_LIB_DIR . '/SGL/HtmlRenderer/' . $rendererFile;
        } else {
            PEAR::raiseError('Could not find renderer', SGL_ERROR_NOFILE,
                PEAR_ERROR_DIE);
        }
        parent::SGL_View($data, new $rendererClass);
    }
}

?>
