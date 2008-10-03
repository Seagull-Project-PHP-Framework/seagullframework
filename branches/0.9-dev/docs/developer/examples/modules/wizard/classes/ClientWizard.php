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
// | Seagull 0.7                                                               |
// +---------------------------------------------------------------------------+
// | ClientWizard.php                                                          |
// +---------------------------------------------------------------------------+
// | Author: Malaney J. Hill <malaney@gmail.com>                               |
// +---------------------------------------------------------------------------+
// $Id: UserMgr.php,v 1.59 2004/12/12 13:05:06 demian Exp $
// Page 1

/**
 * Page 1 of multi-page wizard
 * 
 * 
 * @author  Malaney J. Hill<malaney@gmail.com>
 * @package SGL_WizardController
 * @version $Revision: 1.5 $
 */
class PageClientDetails extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        // Add some elements to the form
        $this->addElement('header', null, SGL_String::translate('Client Details'));
        $this->addElement('text', 'first_name', SGL_String::translate('First name'), array('size' => 50, 'maxlength' => 255));
        $this->addElement('text', 'last_name', SGL_String::translate('Last name'), array('size' => 50, 'maxlength' => 255));
       
        $this->addElement('submit', $this->getButtonName('next'), SGL_String::translate('Next >>'));

        $this->addRule('first_name', SGL_String::translate('Please enter your first name'), 'required');
        $this->addRule('last_name', SGL_String::translate('Please enter your last name'), 'required', null, 'client');

        $this->setDefaultAction('next');
    }
}

/**
 * Page 2 of multi-page wizard
 * 
 * 
 * @author  Malaney J. Hill<malaney@gmail.com>
 * @package SGL_WizardController
 * @version $Revision: 1.5 $
 */
class PageServiceDetails extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        // Add some elements to the form
        $this->addElement('header', null, SGL_String::translate('Service Details'));
        $this->addElement('html','<tr><th colspan=2 align=center>'.SGL_String::translate('Note:  This page uses server-side validation').'</td></tr>');
        $this->addElement('select', 'service_type', SGL_String::translate('Service'), array('massage' => SGL_String::translate('Massage'),'pedicure' => SGL_String::translate('Pedicure'), 'manicure' => SGL_String::translate('Manicure') ));
        $this->addElement('text', 'num_hours', SGL_String::translate('Num hours'), array('size' => 50, 'maxlength' => 255));
        $this->addElement('hidden', 'min_hours_value', 10);

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), SGL_String::translate('<< Back'));
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), SGL_String::translate('Next >>'));
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->addRule('service_type', SGL_String::translate('Please select a service type'), 'required');
        $this->addRule('num_hours', SGL_String::translate('Please enter the number of hours'), 'required');
        $this->addRule(array('num_hours','min_hours_value'), SGL_String::translate('Num hours must be greater than').' 10', 'compare','gte');

        $this->setDefaultAction('next');
    }
}

/**
 * Page 3 of multi-page wizard
 * 
 * 
 * @author  Malaney J. Hill<malaney@gmail.com>
 * @package SGL_WizardController
 * @version $Revision: 1.5 $
 */
class PageSurveyDetails extends HTML_QuickForm_Page
{
    function buildForm()
    { 
        $this->_formBuilt = true;

        // Add some elements to the form
        $this->addElement('header', null, SGL_String::translate('Survey'));
        $this->addElement('select', 'rate_service', SGL_String::translate('Rate your service: (10 being highest)'), array_combine(range(1,10), range(1,10)));

        $radio[] = &$this->createElement('radio', null, null, SGL_String::translate('Yes'), 'Yes');
        $radio[] = &$this->createElement('radio', null, null, SGL_String::translate('No'), 'No');
        $this->addGroup($radio, 'recommend_friend', SGL_String::translate('Would you recommend a friend?'));

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), SGL_String::translate('<< Back'));
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), SGL_String::translate('Finish >>'));
        $this->addGroup($prevnext, null, '', '&nbsp;', false);

        $this->setDefaultAction('next');
    }
}
?>
