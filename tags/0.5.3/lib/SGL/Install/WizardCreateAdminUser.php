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
// | Seagull 0.5                                                               |
// +---------------------------------------------------------------------------+
// | WizardCreateAdminUser.php                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

class WizardCreateAdminUser extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        $this->addElement('header',     null, 'Create Admin User: page 5 of 5');

        //  set defaults
        $this->setDefaults(array(
            'frameworkVersion' => SGL_Install::getFrameworkVersion(),
            'adminUserName' => 'admin',
            'adminRealName' => 'Alouicious Bird',
            'siteName'  => 'Seagull Framework',
            'siteKeywords'  => 'seagull, php, framework, cms, content management',
            'siteDesc'  => 'Coming soon to a webserver near you.',
            'siteLanguage'  => 'en-iso-8859-15',
            'serverTimeOffset'  => 'UTC',
            'siteCookie'  => 'SGLSESSID',
            'installRoot'  => SGL_PATH,
            'webRoot'  => SGL_PATH . '/web',
            ));

        //  setup admin user
        $this->addElement('hidden',  'frameworkVersion', '');
        $this->addElement('text',  'adminUserName', 'Admin username: ');
        $this->addElement('password',  'adminPassword', 'Admin password: ');
        $this->addElement('text',  'adminRealName', 'Real name: ');
        $this->addElement('text',  'adminEmail', 'Email: ');

        $this->addRule('adminUserName', 'Please specify the admin\'s username', 'required');
        $this->addRule('adminPassword', 'Please specify the admin\'s password', 'required');
        $this->addRule('adminEmail', 'Please specify the admin\'s email', 'required');
        $this->addRule('adminEmail', 'Please specify the admin\'s email', 'email');

        //  paths
        $this->addElement('header',     null, 'Paths:');
        $this->addElement('text',  'installRoot', 'Full path: ', 'size="50"');
        $this->addElement('text',  'webRoot', 'Web root: ', 'size="50"');

        $this->addRule('installRoot', 'You must specify an install root path', 'required');
        $this->addRule('webRoot', 'You must specify a web root path', 'required');
        //  test if dirs exist

        //  general
        $this->addElement('header',     null, 'General:');
        $this->addElement('text',  'siteName',     'Site name: ');
        $this->addElement('text',  'siteKeywords',     'Keywords: ', 'size="50"');
        $this->addElement('textarea',   'siteDesc', 'Description:', array('rows' => 5, 'cols' => 40));

        $this->addRule('siteName', 'Please specify the site\'s name', 'required');

        //  set lang
        require_once SGL_PATH . '/lib/data/ary.languages.php';
        $availableLanguages = $GLOBALS['_SGL']['LANGUAGE'];
        uasort($availableLanguages, 'SGL_cmp');
        foreach ($availableLanguages as $id => $tmplang) {
            $langName = ucfirst(substr(strstr($tmplang[0], '|'), 1));
            $aLangData[$id] =  $langName . ' (' . $id . ')';
        }
        $this->addElement('select', 'siteLanguage', 'Site language:', $aLangData);

        //  set tz offset
        require_once SGL_PATH . '/lib/data/ary.timezones.en.php';
        $this->addElement('select', 'serverTimeOffset', 'Server time offset:', $tz);
        $this->addElement('text',  'siteCookie',     'Cookie name: ');

        //  install passwd
        $this->addElement('password',  'installPassword', 'Install password: ');

        $this->addRule('siteCookie', 'Please specify the cookie\'s name', 'required');
        $this->addRule('installPassword', 'Please specify a password to be used to access the installer', 'required');

        $this->addElement('checkbox', 'installAllModules', 'Install all modules?',
            'Yes (If box is not ticked, only 3 core modules will be installed)');

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Finish >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
    }
}
?>