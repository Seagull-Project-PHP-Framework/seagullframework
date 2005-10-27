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

//  setup pear include path
session_start();
require_once dirname(__FILE__) . '/../lib/SGL/Install.php';

$installRoot = SGL_Install::getInstallRoot();

if (!file_exists($installRoot . '/var/env.php')) {
    require_once $installRoot . '/lib/SGL/Install/WizardDetectEnv.php';
    die();
}


$includeSeparator = (substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':';
$ok = @ini_set('include_path',      '.' . $includeSeparator . $installRoot . '/lib/pear');

// Load QuickFormController libs
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Next.php';
require_once 'HTML/QuickForm/Action/Back.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';

require_once 'DB.php';

//  Load SGL libs
require_once dirname(__FILE__) . '/../lib/SGL/DB.php';
require_once dirname(__FILE__) . '/../lib/SGL/Config.php';

//  Load wizard screens
require_once dirname(__FILE__) . '/../lib/SGL/Install/WizardTestDbConnection.php';
require_once dirname(__FILE__) . '/../lib/SGL/Install/WizardCreateDb.php';
require_once dirname(__FILE__) . '/../lib/SGL/Install/WizardCreateTables.php';


class ActionProcess extends HTML_QuickForm_Action
{
    function perform(&$page, $actionName)
    {
        echo "Submit successful!<br>\n<pre>\n";
        var_dump($page->controller->exportValues());
        echo "\n</pre>\n";
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

$wizard =& new HTML_QuickForm_Controller('installationWizard');
$wizard->addPage(new WizardTestDbConnection('page1'));
$wizard->addPage(new WizardCreateDb('page2'));
$wizard->addPage(new WizardCreateTables('page3'));

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