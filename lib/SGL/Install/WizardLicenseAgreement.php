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
// | WizardCreateAdminUser.php                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

function hasAgreed()
{
    if ($_SESSION['_installationWizard_container']['values']['page1']['agree']['agree'] == 'yes') {
        return true;
    } else {
        return false;
    }
}

/**
 * @package Install
 */
class WizardLicenseAgreement extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        $licenseTxt = file_get_contents(SGL_PATH . '/COPYING.txt');
        $this->setDefaults(array(
            'license' => $licenseTxt,
            ));
        $this->setDefaults(SGL_Install_Common::overrideDefaultInstallSettings());

        $this->addElement('header',     null, 'Seagull License Agreement: page 1 of 6');
        $this->addElement('textarea',   'license', null, array('rows' => 15, 'cols' => 80));

        $radio[] = &$this->createElement('radio', 'agree',     '', "I accept the terms of the license agreement", 'yes');
        $radio[] = &$this->createElement('radio', 'agree',     '', "I do not accept the terms of the license agreement", 'no');
        $this->addGroup($radio, 'agree', null, '<br />');
        $this->addGroupRule('agree', 'You must agree with the License terms in order to install and use this product', 'required');

        $this->registerRule('hasAgreed','function','hasAgreed');
        $this->addRule('agree', 'You must agree with the License terms in order to install and use this product', 'hasAgreed');

        $this->addElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->setDefaultAction('next');
    }
}
?>