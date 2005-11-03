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
    $dbName = ($skipDbCreation) ? "/{$aFormValues['name']}" : '';

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
    
    // if DB exists, detect if tables exist
    
    if ($skipDbCreation && PEAR::isError($dbh)) {
        SGL_Install::errorPush($dbh);
        return false;
    } elseif ($skipDbCreation) {
        return true;   
    }

    //  attempt to create database
    $ok = $dbh->query("CREATE DATABASE {$aFormValues['name']}");
    
    //  if new db, set flag to create tables

    if (PEAR::isError($ok)) {
        SGL_Install::errorPush($ok);
        return false;
    } else {
        return true;
    }    
}

class WizardCreateDb extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        
        $this->setDefaults(array(
            'name' => 'seagull',
            'prefix' => 'not implemented yet',
            ));

        $this->addElement('header', null, 'Database Setup: page 4 of 5');

        //  skip db creation FIXME: improve
        $this->addElement('checkbox', 'skipDbCreation', 'Use existing Db?', 'Yes (If box is not ticked, a new Db will be created)');        
        
        //  db name
        $this->addElement('text',  'name',     'Database name: ');
        $this->addRule('name', 'Please specify the name of the database', 'required');
        
        //  db prefix
        $this->addElement('text',  'prefix',     'Table prefix: ');
        
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