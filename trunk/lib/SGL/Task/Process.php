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
// | Process.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: style.php,v 1.85 2005/06/22 00:40:44 demian Exp $

/**
 * Basic app process tasks: enables profiling, custom error handler, logging
 * and output buffering.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_Init extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        if (SGL_PROFILING_ENABLED && function_exists('apd_set_pprof_trace')) {
            apd_set_pprof_trace();
        }
        //  start output buffering
        if (SGL_Config::get('site.outputBuffering')) {
            ob_start();
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * @package Task
 */
class SGL_Task_SetupORM extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $oTask = new SGL_Task_InitialiseDbDataObject();
        $ok = $oTask->run();

        $this->processRequest->process($input, $output);
    }
}

/**
 * Block blacklisted users by IP.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_DetectBlackListing extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('site.banIpEnabled')) {

            if (SGL_Config::get('site.allowList')) {
                $allowList = explode( ' ', SGL_Config::get('site.allowList'));
                if (!in_array($_SERVER['REMOTE_ADDR'], $allowList)) {
                    $msg = SGL_String::translate('You have been banned');
                    SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
                }
            }

            if (SGL_Config::get('site.denyList')) {
                $denyList = explode( ' ', SGL_Config::get('site.denyList'));
                if (in_array($_SERVER['REMOTE_ADDR'], $denyList)) {
                    $msg = SGL_String::translate('You have been banned');
                    SGL::raiseError($msg, SGL_ERROR_BANNEDUSER, PEAR_ERROR_DIE);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

class SGL_Task_MaintenanceModeIntercept extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        // check for maintenance mode "on"
        if (SGL_Config::get('site.maintenanceMode')) {
            // allow admin to access and to connect if provided a key
            $rid = SGL_Session::getRoleId();
            $adminMode = SGL_Session::get('adminMode');
            if ($rid != SGL_ADMIN && !$adminMode && !SGL::runningFromCLI()) {
                $req = $input->getRequest();
                // show mtnce page for browser requests
                if ($req->getType() == SGL_REQUEST_BROWSER) {
                    SGL::displayMaintenancePage($output);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Detects if session debug is allowed.
 *
 * @package Task
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 *
 * @todo think something better than checking for action to avoid
 *       saving config to file, when value was changed
 */
class SGL_Task_DetectSessionDebug extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $adminMode = SGL_Session::get('adminMode');
        $req       = $input->getRequest();
        //  if not in admin mode, but session debug was allowed
        if (!$adminMode && SGL_Config::get('debug.sessionDebugAllowed')
                && $req->get('action') != 'rebuildSeagull'
                && $req->getManagerName() != 'config') {
            //  flag it as not allowed
            SGL_Config::set('debug.sessionDebugAllowed', false);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Loads global set of application perms from filesystem cache.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupPerms extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $cache = & SGL_Cache::singleton();
        if ($serialized = $cache->get('all_users', 'perms')) {
            $aPerms = unserialize($serialized);
            SGL::logMessage('perms from cache', PEAR_LOG_DEBUG);
        } else {
            require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
            $da = & UserDAO::singleton();
            $aPerms = $da->getPermsByModuleId();
            $serialized = serialize($aPerms);
            $cache->save($serialized, 'all_users', 'perms');
            SGL::logMessage('perms from db', PEAR_LOG_DEBUG);
        }
        if (is_array($aPerms) && count($aPerms)) {
            foreach ($aPerms as $k => $v) {
                define('SGL_PERMS_' . strtoupper($v), $k);
            }
        } else {
            SGL::raiseError('there was a problem initialising perms', SGL_ERROR_NODATA);
        }

        $this->processRequest->process($input, $output);
    }
}

/**
 * Sets up wysiwyg params.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupWysiwyg extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // set the default WYSIWYG editor
        if (isset($output->wysiwyg) && $output->wysiwyg == true && !SGL::runningFromCLI()) {

            // you can preset this var in your code
            if (!isset($output->wysiwygEditor)) {
                $output->wysiwygEditor = SGL_Config::get('site.wysiwygEditor')
                    ? SGL_Config::get('site.wysiwygEditor')
                    : 'fck';
            }
            switch ($output->wysiwygEditor) {

            case 'fck':
                $output->wysiwyg_fck = true;
                $output->addOnLoadEvent('fck_init()');
                break;
            case 'xinha':
                $output->wysiwyg_xinha = true;
                $output->addOnLoadEvent('xinha_init()');
                break;
            case 'htmlarea':
                $output->wysiwyg_htmlarea = true;
                $output->addOnLoadEvent('HTMLArea.init()');
                break;
            case 'tinyfck':
                $output->wysiwyg_tinyfck = true;
                // note: tinymce doesn't need an onLoad event to initialise
                break;
            }
        }
    }
}

/**
 * Builds navigation menus.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupNavigation extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('navigation.enabled')
            && !SGL::runningFromCli())
        {
            //  prepare navigation driver
            $navDriver = SGL_Config::get('navigation.driver');
            $navDrvFile = SGL_MOD_DIR . '/navigation/classes/' . $navDriver . '.php';
            if (is_file($navDrvFile)) {
                require_once $navDrvFile;
            } else {
                return SGL::raiseError("specified navigation driver, $navDrvFile, does not exist",
                    SGL_ERROR_NOFILE);
            }
            if (!class_exists($navDriver)) {
                return SGL::raiseError('problem with navigation driver object',
                    SGL_ERROR_NOCLASS);
            }
            $nav = & new $navDriver($output);

            //  render navigation menu
            $navRenderer = SGL_Config::get('navigation.renderer');
            $aRes        = $nav->render($navRenderer);
            if (!PEAR::isError($aRes)) {
                list($sectionId, $html)  = $aRes;
                $output->sectionId  = $sectionId;
                $output->navigation = $html;
                $output->currentSectionName = $nav->getCurrentSectionName();
            }
        }
    }
}

/**
 * Initialises block loading.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupBlocks extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  load blocks
        if (SGL_Config::get('site.blocksEnabled')
                && !SGL::runningFromCli()) {
            $output->sectionId = empty($output->sectionId)
                ? 0
                : $output->sectionId;
            $blockLoader = & new SGL_BlockLoader($output->sectionId);
            $aBlocks = $blockLoader->render($output);
            foreach ($aBlocks as $key => $value) {
                $blocksName = 'blocks'.$key;
                $output->$blocksName = $value;
            }
        }
    }
}

/**
 * Builds data for debug block.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_BuildDebugBlock extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (SGL_Config::get('debug.infoBlock')) {
            $output->debug_request = $output->request;
            $output->debug_session = $_SESSION;
            $output->debug_module = $output->moduleName;
            $output->debug_manager = isset($output->managerName)
                ? $output->managerName
                : '';
            $output->debug_action = $output->action;
            $output->debug_section = $output->sectionId;
            $output->debug_master_template = isset($output->masterTemplate)
                ? $output->masterTemplate
                : '';
            $output->debug_template = $output->template;
            $output->debug_theme = $output->theme;

        }
    }
}

/**
 * A void object.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Void extends SGL_ProcessRequest
{
    function process(&$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }
}
?>
