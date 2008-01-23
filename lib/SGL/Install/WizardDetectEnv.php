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
// | WizardDetectEnv.php                                                       |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: setup.php,v 1.5 2005/02/03 11:29:01 demian Exp $

function environmentOk()
{
    if (SGL_Install_Common::errorsExist()) {
        return false;
    } else {
        // cleanup data for storage
        $oTask = $GLOBALS['_SGL']['runner'];
        $aSummary = array();
        foreach ($oTask->aTasks as $oTask) {
            $aSummary[$oTask->key] = $oTask->aData;
        }
        $serialized = serialize($aSummary);
        @file_put_contents(SGL_VAR_DIR . '/env.php', $serialized);
        return true;
    }
}

/**
 * @package Install
 */
class WizardDetectEnv extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        $this->setDefaults(array(
            'detectEnv' => 1,
            ));
        $this->setDefaults(SGL_Install_Common::overrideDefaultInstallSettings());

        $this->addElement('header',     null, 'Detect Environment: page 3 of 6');

        $runner = new SGL_TaskRunner();
        $runner->addTask(new SGL_Task_GetLoadedModules());
        $runner->addTask(new SGL_Task_GetPhpEnv());
        $runner->addTask(new SGL_Task_GetPhpIniValues());
        $runner->addTask(new SGL_Task_GetFilesystemInfo());
        if (!SGL::isMinimalInstall()) {
            $runner->addTask(new SGL_Task_GetPearInfo());
        }

        $html = $runner->main();

        //  store global copy for error callback
        $GLOBALS['_SGL']['runner'] = $runner;

        $this->addElement('checkbox', 'detectEnv', 'Detect Env?', 'Yes');
        $this->addElement('static', 'colourKey', 'Legend', 'Errors are displayed in '.
            '<span style="color: red; font-weight: bold;">red</span>, recommendations in '.
            '<span style="color: orange; font-weight: bold;">yellow</span> and success in '.
            '<span style="color: green; font-weight: bold;">green</span>');
        $this->registerRule('environmentOk','function','environmentOk');
        $this->addRule('detectEnv', 'please fix the listed errors', 'environmentOk');

        $this->addElement('static',  'env', null, $html);

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
    }
}
?>