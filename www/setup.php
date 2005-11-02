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


/*
sgl setup
=========
- ability to upload and unzip/tar a packaged module
- file permission handling ideas from FUDforum installer
- more user-friendly error messages from Gallery2
- if no DB detected, prompt to create, otherwise offer to create tables

PROCESS
========

- create lockfile, system in 'setup' mode
- block public access
    one idea, using key in hidden file
        - put main site in standby mode
        - create randomly named dir
        - perform install in above dir
        - delete dir when finished

php interpreter
===============
- min php version, not over max
- get php sapi type
- check loaded extensions

php ini
=======
- deal with register_globals and set session.use_trans_sid = 0
- allow_url_fopen = Off
- detect and deal with safe_mode
- magic_quotes must be off
- file_uploads ideally enabled

filesystem
==========
- check pear libs exists and are loadable
- determine location in filesystem
- test if seagull/var exists & is writable
- copy config file there
- rewrite with correct values
- create seagull/var tmp dir for session
    
db setup
========
- test db connection
- test db perms
- get prefix, db params
- create tables
- insert default SQL data
- insert sample SQL data
- load constraints

- generateDataObjectEntities
- rebuildSequences

config setup
============
- form
    - system paths
    - general
        - name + desc of site [metas]
        - admin email address
        - lang
        - server time offset
        
- the domain of the cookie that will be used
- getFrameworkVersion
        
module setup
============
- choose modules and permissions must be created and set at install time
- attempt to 
    - uncompress
    - move to correct locatin
    - apply user perms
    - apply prefs
    - add module's db tables to Config
    - load module's schema + data
    - add 'section' or 'screen' navigation links
    - register module in registry

user setup
==========
- create admin, set username, password and email
- option to add user

- For security reasons, you must remove the installation script ...
- remove lockfile, system set to 'production' mode

paths setup
===========
allow enduser to dynamically setup paths, ie, for hosted environments

*/

//  initialise
session_start();
require_once dirname(__FILE__) . '/../lib/SGL/Install.php';
require_once dirname(__FILE__) . '/../lib/SGL/Task.php';
require_once dirname(__FILE__) . '/../lib/SGL/TaskRunner.php';
require_once dirname(__FILE__) . '/../lib/SGL/Tasks/Install.php';

$init = new SGL_TaskRunner();
$init->addTask(new SGL_Task_SetupPaths());
$init->addTask(new SGL_Task_SetupConstants());
$init->main();

// load QuickFormController libs
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Next.php';
require_once 'HTML/QuickForm/Action/Back.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';

require_once 'DB.php';

//  load SGL libs
require_once SGL_PATH . '/lib/SGL/DB.php';
require_once SGL_PATH . '/lib/SGL/Config.php';

//  load wizard screens
require_once SGL_PATH . '/lib/SGL/Install/WizardLicenseAgreement.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardDetectEnv.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardTestDbConnection.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardCreateDb.php';
require_once SGL_PATH . '/lib/SGL/Install/WizardCreateAdminUser.php';

//  load tasks
require_once SGL_PATH . '/lib/SGL/Tasks/DetectEnv.php';

//  subclass the default 'display' handler to customize the output
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
<table border="0" width="800px">
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
        $data = $page->controller->exportValues();
        
        $runner = new SGL_TaskRunner();
        $runner->addData($data);
        $runner->addTask(new SGL_Task_CreateConfig());
        $runner->addTask(new SGL_Task_CreateTables());
        $runner->addTask(new SGL_Task_LoadDefaultData());
        $runner->addTask(new SGL_Task_CreateConstraints());
        $runner->addTask(new SGL_Task_VerifyDbSetup());
        $runner->addTask(new SGL_Task_CreateFileSystem());        
        $runner->addTask(new SGL_Task_CreateDataObjectEntities());
        $runner->addTask(new SGL_Task_SyncSequences());
        $runner->addTask(new SGL_Task_CreateAdminUser());
//        $runner->addTask(new SGL_Task_RemoveLockfile());
        
        set_time_limit(60);
        $ok = $runner->main();
    }
}

//  clear session cookie so theme comes from DB and not session
#setcookie(  $this->conf['cookie']['name'], null, 0, $this->conf['cookie']['path'], 
#            $this->conf['cookie']['domain'], $this->conf['cookie']['secure']);

$wizard =& new HTML_QuickForm_Controller('installationWizard');
$wizard->addPage(new WizardLicenseAgreement('page1'));
$wizard->addPage(new WizardDetectEnv('page2'));
$wizard->addPage(new WizardTestDbConnection('page3'));
$wizard->addPage(new WizardCreateDb('page4'));
$wizard->addPage(new WizardCreateAdminUser('page5'));

// We actually add these handlers here for the sake of example
// They can be automatically loaded and added by the controller
$wizard->addAction('display', new ActionDisplay()/*HTML_QuickForm_Action_Display()*/);
$wizard->addAction('next', new HTML_QuickForm_Action_Next());
$wizard->addAction('back', new HTML_QuickForm_Action_Back());
$wizard->addAction('jump', new HTML_QuickForm_Action_Jump());

// This is the action we should always define ourselves
$wizard->addAction('process', new ActionProcess());

$wizard->run();

if (SGL_Install::errorsExist()) {
    foreach ($_SESSION['ERRORS'] as $oError) {
        $out =  $oError->getMessage() . '<br /> ';   
        $out .= $oError->getUserInfo(); 
        print $out;
    }
}
?>