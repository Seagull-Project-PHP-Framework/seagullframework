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
// | WizardCreateDb.php                                                        |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+

function canCreateDb()
{
    $aFormValues = array_merge($_SESSION['_installationWizard_container']['values']['page3'],
        $_SESSION['_installationWizard_container']['values']['page4']);

    $skipDbCreation = (bool)@$aFormValues['skipDbCreation'];

    //  if we're not creating a DB, presumably a DB exists so supply DB name to dsn
    $dbName = ($skipDbCreation) ? "/{$aFormValues['name']}" : '';

    //  postgres however does not support logins with no db name
    if ($dbName == '' && ($aFormValues['dbType']['type'] == 'pgsql')) {
        $dbName = '/template1';
    }

    $protocol = isset($aFormValues['dbProtocol']['protocol']) ? $aFormValues['dbProtocol']['protocol'] . '+' : '';
    $port = (!empty($aFormValues['dbPort']['port'])
                && isset($aFormValues['dbProtocol']['protocol'])
                && ($aFormValues['dbProtocol']['protocol'] == 'tcp'))
        ? ':' . $aFormValues['dbPort']['port']
        : '';
    $dsn = $aFormValues['dbType']['type'] . '://' .
        $aFormValues['user'] . ':' .
        $aFormValues['pass'] . '@' .
        $protocol .
        $aFormValues['host'] . $port . $dbName;

    //  attempt to get db connection
    $dbh = & SGL_DB::singleton($dsn);

    if ($skipDbCreation && PEAR::isError($dbh)) {
        SGL_Install_Common::errorPush($dbh);
        return false;

    } elseif ($skipDbCreation) {

        // if DB exists, detect if tables exist
        $tables = $dbh->getListOf('tables');
        if (!count($tables)) {
            $_SESSION['_installationWizard_container']['values']['page4']['createTables'] = 1;
        } else {
            $_SESSION['_installationWizard_container']['values']['page4']['createTables'] = 0;
        }
        return true;

    } elseif (PEAR::isError($dbh)) {
        return false;
    }

    //  attempt to create database
    $ok = $dbh->query("CREATE DATABASE {$aFormValues['name']}");

    if (PEAR::isError($ok)) {
        SGL_Install_Common::errorPush($ok);
        return false;

    } else {
        //  if new db, set flag to create tables
        $_SESSION['_installationWizard_container']['values']['page4']['createTables'] = 1;
        return true;
    }
}

class WizardCreateDb extends HTML_QuickForm_Page
{
    function buildForm()
    {
        require_once SGL_CORE_DIR .'/Translation.php';

        $this->_formBuilt = true;

        $this->setDefaults(array(
            'name' => 'seagull',
            'prefix' => 'not implemented yet',
            ));

        $this->addElement('header', null, 'Database Setup: page 4 of 5');

        //  skip db creation
        $this->addElement('checkbox', 'skipDbCreation', 'Use existing Db?',
            'Yes (If box is not ticked, a new Db will be created)',
                array('onClick' => 'javascript:toggleExistingData()',
                      'id' => 'skipDbCreation'));

        //  use existing data
        $this->addElement('checkbox', 'useExistingData', 'Use existing Data?',
            'Yes (Select this option to preserve your existing data)',
                array('onClick' => 'javascript:toggleOptionsWhenUsingExistingDb()',
                      'id' => 'useExistingData'));

        //  db name
        $this->addElement('text',  'name',     'Database name: ');
        $this->addRule('name', 'Please specify the name of the database', 'required');

        //  db prefix
        $this->addElement('text', 'prefix', 'Table prefix: ', 'id=prefix');

        //  install all modules?
        $this->addElement('checkbox', 'installAllModules', 'Install all modules?',
            'Yes (If box is not ticked, only 3 core modules will be installed)', 'id=installAllModules');

        //  sample data
        $this->addElement('checkbox', 'insertSampleData', 'Include Sample Data?', 'Yes', 'id=insertSampleData');

        $this->addElement('header', null, 'Translation Setup');

        //  store translation in db
        $this->addElement('checkbox', 'storeTranslationsInDB', 'Store Translations in Database?',
            'Yes (Select this for multi-lingual content)',
                array('id' => 'storeTranslationsInDB', 'onClick' => 'javascript:toggleLangList()'));

        //  load available languages
        $this->addElement('select', 'installLangs', 'If yes, which language(s): ',
            SGL_Util::getLangsDescriptionMap(), array('multiple' => 'multiple', 'id' => 'installLangs'));

        //  store translation in db
        $this->addElement('checkbox', 'addMissingTranslationsToDB', 'Add missing Translations to Database?',
            'Yes', "id = addMissingTranslationsToDB");

        //  test db creation
        $this->registerRule('canCreateDb','function','canCreateDb');
        $this->addRule('name', 'there was an error creating the database', 'canCreateDb');

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
    }
}
?>
