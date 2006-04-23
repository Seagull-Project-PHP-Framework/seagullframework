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
// | Page1Wiz.php                                                              |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Page1WizMgr.php,v 1.1 2005/04/04 10:41:09 demian Exp $

require_once SGL_CORE_DIR . '/Wizard.php';
require_once 'Validate.php';

/**
 * To allow users to contact site admins.
 *
 * @package wizardexample
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2004
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class Page1WizMgr extends SGL_Wizard
{
    function Page1WizMgr()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Wizard();
    }

    function validate($req, &$input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::validate($req, $input);

        $this->validated    = true;
        $input->error       = null;
        $input->pageTitle   = 'Page 1';
        $input->template    = 'contact1.html';
        $input->masterTemplate = 'masterBlank.html';
        $input->action      = $req->get('action');
        $input->aDelete     = $req->get('frmDelete');
        $input->contact     = (object)$req->get('contact');

        $aErrors = array();
        if ($this->submit) {
            $v = & new Validate();
            if (empty($input->contact->first_name)) {
                $this->validated = false;
                $aErrors['first_name'] = SGL_Output::translate('You must enter your first name');
            }
            if (empty($input->contact->last_name)) {
                $this->validated = false;
                $aErrors['last_name'] = SGL_Output::translate('You must enter your last name');
            }
            if (empty($input->contact->email)) {
                $this->validated = false;
                $aErrors['email'] = SGL_Output::translate('You must enter your email');
            } else {
                if (!$v->email($input->contact->email)) {
                    $aErrors['email'] = SGL_Output::translate('Your email is not correctly formatted');
                    $this->validated = false;
                }
            }
            if (empty($input->contact->user_comment)) {
                $this->validated = false;
                $aErrors['user_comment'] = SGL_Output::translate('You must fill in your comment');
            }
        }
        //  if errors have occured
        if (is_array($aErrors) && count($aErrors)) {
            SGL::raiseMsg('Please fill in the indicated fields');
            $input->error = $aErrors;
        }

        $this->maintainState($input->contact);
        return $input;
    }

    function process(&$input, &$output)
    {
        //  no processing required here, will be done on final page of wizard
        return $output;
    }

    function display(&$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::display($output);

        //  build contact type dropdown
        $aContactType = array(  'General enquiry' => 'General enquiry',
                                'Get a quote' => 'Get a quote',
                                'Hosting info' => 'Hosting info',
                                'Site feedback' => 'Site feedback'
                              );
        $output->selectOptions = SGL_Output::generateSelect($aContactType);
        return $output;
    }
}
?>
