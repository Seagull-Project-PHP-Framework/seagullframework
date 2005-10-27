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
// | setup.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: setup.php,v 1.5 2005/02/03 11:29:01 demian Exp $

// Start the session, form-page values will be kept there
session_start();
require_once dirname(__FILE__) . '/../lib/SGL/Install.php';

//  setup pear include path
$installRoot = SGL_Install::getInstallRoot();
$includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
$ok = @ini_set('include_path',      '.' . $includeSeparator . $installRoot . '/lib/pear');

require_once 'HTML/QuickForm/Controller.php';

// Load some default action handlers
require_once 'HTML/QuickForm/Action/Next.php';
require_once 'HTML/QuickForm/Action/Back.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';

require_once 'DB.php';
require_once dirname(__FILE__) . '/../lib/SGL/DB.php';
require_once dirname(__FILE__) . '/../lib/SGL/Config.php';
    
function canConnectToDbServer()
{
    $aFormValues = $GLOBALS['_SGL']['dbFormValues'];

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
        $aFormValues['host'] . $port;

    //  attempt to get db connection
    $dbh = & SGL_DB::singleton($dsn);

    if (PEAR::isError($dbh)) {
        SGL_Install::errorPush($dbh);        
        return false;
    } else {
        return true;
    }
}

function canCreateDb()
{
    $aFormValues = array_merge($_SESSION['_installationWizard_container']['values']['page1'], 
        $GLOBALS['_SGL']['dbFormValues']);

    $skipDbCreation = (bool)$aFormValues['skipDbCreation'];
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
    
    if ($skipDbCreation && PEAR::isError($dbh)) {
        SGL_Install::errorPush($dbh);
        return false;
    } elseif ($skipDbCreation) {
        return true;   
    }

    //  attept to create database
    $ok = $dbh->query("CREATE DATABASE {$aFormValues['name']}");

    if (PEAR::isError($ok)) {
        SGL_Install::errorPush($ok);
        return false;
    } else {
        return true;
    }    
}

class PageFirst extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        $this->addElement('header', null, 'Test DB Connection: page 1 of 3');
        
        //  FIXME: use detect.php info to supply sensible defaults
        $this->setDefaults(array(
            'host' => 'localhost',
            'dbProtocol'  => array('protocol' => 'unix'),
            'dbType'  => array('type' => 'mysql_SGL'),
            'dbPort'  => array('port' => 3306),
            ));
        
        //  type
        $radio[] = &$this->createElement('radio', 'type',     'Database type: ',"mysql_SGL (all sequences in one table)", 'mysql_SGL');
        $radio[] = &$this->createElement('radio', 'type',     '', "mysql",  'mysql');
        $radio[] = &$this->createElement('radio', 'type',     '', "postgres", 'pgsql');
        $radio[] = &$this->createElement('radio', 'type',     '', "oci8", 'oci8_SGL');
        $radio[] = &$this->createElement('radio', 'type',     '', "maxdb", 'maxdb_SGL');
        $this->addGroup($radio, 'dbType', 'Database type:', '<br />');
        $this->addGroupRule('dbType', 'Please specify a db type', 'required');
        
        //  host
        $this->addElement('text',  'host',     'Host: ');
        $this->addRule('host', 'Please specify the hostname', 'required');
        
        //  protocol
        unset($radio);
        $radio[] = &$this->createElement('radio', 'protocol', 'Protocol: ',"unix (fine for localhost connections)", 'unix');
        $radio[] = &$this->createElement('radio', 'protocol', '',"tcp", 'tcp');
        $this->addGroup($radio, 'dbProtocol', 'Protocol:', '<br />');
        $this->addGroupRule('dbProtocol', 'Please specify a db protocol', 'required');
        
        //  port
        unset($radio);
        $radio[] = &$this->createElement('radio', 'port',     'TCP port: ',"3306 (Mysql default)", 3306);
        $radio[] = &$this->createElement('radio', 'port',     '',"5432 (Postgres default)", 5432);
        $radio[] = &$this->createElement('radio', 'port',     '',"1521 (Oracle default)", 1521);
        $radio[] = &$this->createElement('radio', 'port',     '',"7210 (MaxDB default)", 7210);
        $this->addGroup($radio, 'dbPort', 'TCP port:', '<br />');
        $this->addGroupRule('dbPort', 'Please specify a db port', 'required');
        
        //  credentials
        $this->addElement('text',  'user',    'Database username: ');
        $this->addElement('password', 'pass', 'Database password: ');
        $this->addRule('user', 'Please specify the db username', 'required');

        //  test db connect
        $this->registerRule('canConnectToDbServer','function','canConnectToDbServer'); 
        $this->addRule('user', 'cannot connect to the db, please check all credentials', 'canConnectToDbServer');
        
        //  submit
        $this->addElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->setDefaultAction('next');
        
        //  make vars available for db connection test
        $GLOBALS['_SGL']['dbFormValues'] = $this->exportValues();
    }
}

class PageSecond extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;
        
        $this->setDefaults(array(
            'name' => 'seagull',
            ));

        $this->addElement('header', null, 'Database Setup: page 2 of 3');

        //  skip db creation FIXME: improve
        $this->addElement('checkbox', 'skipDbCreation', 'Use existing Db', 'Yes (If box is not ticked, a new Db will be created)');        
        
        //  db name
        $this->addElement('text',  'name',     'Database name: ');
        $this->addRule('name', 'Please specify the name of the database', 'required');
        
        //  test db creation
        $this->registerRule('canCreateDb','function','canCreateDb'); 
        $this->addRule('name', 'the db does not exist or could not be created', 'canCreateDb');

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
        
        //  make vars available for db creation test
        $GLOBALS['_SGL']['dbFormValues'] = $this->exportValues();
    }
}

class PageThird extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        $this->addElement('header',     null, 'Wizard page 3 of 3');

        $this->addElement('textarea',   'itxaTest', 'Parting words:', array('rows' => 5, 'cols' => 40));

        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Finish');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);

        $this->addRule('itxaTest', 'Say something!', 'required');

        $this->setDefaultAction('next');
    }
}


// We subclass the default 'display' handler to customize the output
class ActionDisplay extends HTML_QuickForm_Action_Display
{
    function perform(&$page, $actionName)
    {
        SGL_Install::errorCheck($page);
        return parent::perform($page, $actionName);   
    }
    
    function _renderForm(&$page) 
    {
        $renderer =& $page->defaultRenderer();
        
        $renderer->setElementTemplate("\n\t<tr>\n\t\t<td align=\"right\" valign=\"top\" colspan=\"2\">{element}</td>\n\t</tr>", 'tabs');
        $renderer->setFormTemplate(<<<_HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>Seagull Framework :: Installation</title>        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
    <meta http-equiv="Content-Language" content="en" />
    <meta name="ROBOTS" content="ALL" />
    <meta name="Copyright" content="Copyright (c) 2005 Seagull Framework, Demian Turner, and the respective authors" />
    <meta name="Rating" content="General" />
    <meta name="Generator" content="Seagull Framework" />

    <link rel="help" href="http://seagull.phpkitchen.com/docs/" title="Seagull Documentation." />
    
    <style type="text/css" media="screen">
        @import url("http://localhost/seagull/trunk/www/themes/default/css/style.php?navStylesheet=SglDefault_TwoLevel&moduleName=faq");
    </style>
    </head>
<body>

<div id="sgl">
<!-- Logo and header -->
<div id="header">
    <a id="logo" href="http://localhost/seagull/trunk/www" title="Home">
        <img src="http://localhost/seagull/trunk/www/themes/default/images/logo.gif" align="absmiddle" alt="Seagull Framework Logo" /> Seagull Framework :: Installation
    </a>
</div>
<p>&nbsp;</p>
<form{attributes}>
<table border="0">
{content}
</table>
</form>
    <div id="footer">
    Powered by <a href="http://seagull.phpkitchen.com" title="Seagull framework homepage">Seagull Framework</a>  
    </div>
</body>
</html>
_HTML
);
        $page->display();
    }
}

class ActionProcess extends HTML_QuickForm_Action
{
    function perform(&$page, $actionName)
    {
        echo "Submit successful!<br>\n<pre>\n";
        var_dump($page->controller->exportValues());
        echo "\n</pre>\n";
    }
}

//class SGL_Installer_Action_Display extends HTML_QuickForm_Action_Display
//{
//    function perform(&$page, $actionName)
//    {
//        SGL_Install::errorCheck();
//        return parent::perform($page, $actionName);   
//    }
//}

$wizard =& new HTML_QuickForm_Controller('installationWizard');
$wizard->addPage(new PageFirst('page1'));
$wizard->addPage(new PageSecond('page2'));
$wizard->addPage(new PageThird('page3'));

// We actually add these handlers here for the sake of example
// They can be automatically loaded and added by the controller
$wizard->addAction('display', new ActionDisplay()/*HTML_QuickForm_Action_Display()*/);
$wizard->addAction('next', new HTML_QuickForm_Action_Next());
$wizard->addAction('back', new HTML_QuickForm_Action_Back());
$wizard->addAction('jump', new HTML_QuickForm_Action_Jump());

// This is the action we should always define ourselves
$wizard->addAction('process', new ActionProcess());

$wizard->run();
?>