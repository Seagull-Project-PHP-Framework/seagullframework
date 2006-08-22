<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | WiztestMgr.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Malaney J. Hill <malaney@gmail.com>                               |
// +---------------------------------------------------------------------------+
// $Id: ManagerTemplate.html,v 1.2 2005/04/17 02:15:02 demian Exp $

/**
 * This class demonstrates Wizard Functionality
 *
 * @package wiztest
 * @author  Malaney J. Hill <malaney@gmail.com>
 */
class WiztestMgr extends SGL_Manager
{
    function WiztestMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle    = 'Wiztest Manager';
        $this->template     = 'wiztestMgrList.html';

        $this->_aActionsMapping =  array(
            'wizard'    => array('wizard')
        );
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $this->validated    = true;
        $input->error       = array();
        $input->pageTitle   = $this->pageTitle;
        $input->masterTemplate = $this->masterTemplate;
        $input->template    = $this->template;
        $input->action      = ($req->get('action')) ? $req->get('action') : 'list';
        $input->aDelete     = $req->get('frmDelete');
        $input->submit      = $req->get('submitted');

        //  if errors have occured
        if (isset($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
            $this->validated = false;
        }
    }

    function _cmd_wizard(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->template = 'wizard.html';
        $output->pageTitle = 'Add Client Wizard';

        require_once SGL_LIB_DIR . '/SGL/WizardController.php';
        require_once 'ClientWizard.php';
    
        // Instantiate the Controller
        $controller =& new SGL_WizardController('clientWizard');
    
        // Set defaults for the form elements
        $controller->setDefaults(array(
        'first_name' => 'Thierry',
        'last_name' => 'Henry'
        ));
    
        // Add pages to Controller
        $controller->addPage(new PageClientDetails('page1'));
        $controller->addPage(new PageServiceDetails('page2'));
        $controller->addPage(new PageSurveyDetails('page3'));
    
        // Add actions to controller
        $controller->addAction('display', new SGL_WizardControllerDisplay());
        $controller->addAction('jump', new SGL_WizardControllerJump());
        $controller->addAction('process', new SGL_WizardControllerProcess());

        // Process the request
        $controller->run();

        // Get the current page name
        list($pageName, $actionName) = $controller->getActionName();
        $page = $controller->getPage($pageName);

        // Set page output vars
        $output->wizardOutput = $page->wizardOutput;
        $output->wizardData = $page->wizardData;
    }
}
?>
