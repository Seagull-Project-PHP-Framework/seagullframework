<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | ConfigMgr.php                                                             |
// +---------------------------------------------------------------------------+
// | Author:   Eric Persson <eric@persson.tm    >                                  |
// +---------------------------------------------------------------------------+
// $Id$

require_once 'Config.php';
require_once 'Validate.php';

/**
 * To manage administering modules config files.
 *
 * @package default
 * @author  Eric Persson <eric@persson.tm>
 * @version $Revision:$
 */
class ModuleConfigMgr extends SGL_Manager
{

    function ModuleConfigMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();
        $this->pageTitle = 'Module Config Manager';
        $this->masterTemplate = 'master.html';
        $this->template = 'moduleConfigEdit.html';

        $this->_aActionsMapping =  array(
            'edit'      => array('edit'),
            'update'    => array('update'),
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->aStyleFiles      = SGL_Util::getStyleFiles();
        $this->validated        = true;
        $input->pageTitle       = $this->pageTitle;
        $input->masterTemplate  = 'masterMinimal.html';
        $input->module          = $req->get('module');
        $input->action          = ($req->get('action')) ? $req->get('action') : 'edit';
        $input->config          = $req->get('config');

        $this->moduleConfigFile = realpath(SGL_MOD_DIR . '/' . $input->module . '/conf.ini');
        if (!$this->_existConfig()){
            $aErrors[] = 'The config file ' . $this->moduleConfigFile . ' does not exist.';
        }
        if (!$this->_writableConfig()) {
            $aErrors[] = 'The config file ' . $this->moduleConfigFile . ' is not writable.';
        }

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $input->template = 'configEdit.html';
            $this->validated = false;
        }
    }

    function _cmd_edit(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->config = $this->_loadConfig();
        $output->template = 'moduleConfigEdit.html';
    }

    function _cmd_update(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = SGL_Config::singleton();
        $c->replace($input->config);

        //  write configuration to file
        $ok = $c->save($this->moduleConfigFile);
        if (PEAR::isError($ok)) {
            SGL::raiseMsg('config info successfully updated');
        } else {
            SGL::raiseError('There was a problem saving your configuration, make sure the conf.ini is writable',
                SGL_ERROR_FILEUNWRITABLE);
        }
    }

    function _existConfig(){
            if (file_exists($this->moduleConfigFile) && is_readable($this->moduleConfigFile)) {
                return true;
            } else {
                return false;
            }
    }

    function _writableConfig()
    {
            if (is_writable($this->moduleConfigFile)) {
                return true;
            } else {
                return false;
            }
    }

    function _loadConfig()
    {
        $moduleConfig = SGL_Config::singleton();
        return $moduleConfig->load($this->moduleConfigFile);
    }
}
?>