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

require_once 'install_common.php';
require_once 'HTML/QuickForm/Controller.php';

// Load some default action handlers
require_once 'HTML/QuickForm/Action/Submit.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';
require_once 'HTML/QuickForm/Action/Direct.php';

session_start();

class PageFoo extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        $tabs[] =& $this->createElement('submit',   $this->getButtonName('foo'), 'Foo', array('class' => 'flat', 'disabled' => 'disabled'));
        $tabs[] =& $this->createElement('submit',   $this->getButtonName('bar'), 'Bar', array('class' => 'flat'));
        $tabs[] =& $this->createElement('submit',   $this->getButtonName('baz'), 'Baz', array('class' => 'flat'));
        $this->addGroup($tabs, 'tabs', null, '&nbsp;', false);
        
        $this->addElement('header',     null, 'Foo page');

        $radio[] = &$this->createElement('radio', null, null, 'Yes', 'Y');
        $radio[] = &$this->createElement('radio', null, null, 'No', 'N');
        $radio[] = &$this->createElement('radio', null, null, 'Maybe', 'M');
        $this->addGroup($radio, 'iradYesNoMaybe', 'Do you want this feature?', '<br />');

        $this->addElement('text',       'tstText', 'Why do you want it?', array('size'=>20, 'maxlength'=>50));

        $this->addElement('submit',     $this->getButtonName('submit'), 'Big Red Button', array('class' => 'bigred'));

        $this->addRule('iradYesNoMaybe', 'Check a radiobutton', 'required');

        $this->setDefaultAction('submit');
    }
}

class PageBar extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        $tabs[] =& $this->createElement('submit',   $this->getButtonName('foo'), 'Foo', array('class' => 'flat'));
        $tabs[] =& $this->createElement('submit',   $this->getButtonName('bar'), 'Bar', array('class' => 'flat', 'disabled' => 'disabled'));
        $tabs[] =& $this->createElement('submit',   $this->getButtonName('baz'), 'Baz', array('class' => 'flat'));
        $this->addGroup($tabs, 'tabs', null, '&nbsp;', false);
        
        $this->addElement('header',     null, 'Bar page');

        $this->addElement('date',       'favDate', 'Favourite date:', array('format' => 'd-M-Y', 'minYear' => 1950, 'maxYear' => date('Y')));
        $checkbox[] = &$this->createElement('checkbox', 'A', null, 'A');
        $checkbox[] = &$this->createElement('checkbox', 'B', null, 'B');
        $checkbox[] = &$this->createElement('checkbox', 'C', null, 'C');
        $checkbox[] = &$this->createElement('checkbox', 'D', null, 'D');
        $checkbox[] = &$this->createElement('checkbox', 'X', null, 'X');
        $checkbox[] = &$this->createElement('checkbox', 'Y', null, 'Y');
        $checkbox[] = &$this->createElement('checkbox', 'Z', null, 'Z');
        $this->addGroup($checkbox, 'favLetter', 'Favourite letters:', array('&nbsp;', '<br />'));

        $this->addElement('submit',     $this->getButtonName('submit'), 'Big Red Button', array('class' => 'bigred'));

        $this->setDefaultAction('submit');
    }
}

class PageBaz extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        $tabs[] =& $this->createElement('submit',   $this->getButtonName('foo'), 'Foo', array('class' => 'flat'));
        $tabs[] =& $this->createElement('submit',   $this->getButtonName('bar'), 'Bar', array('class' => 'flat'));
        $tabs[] =& $this->createElement('submit',   $this->getButtonName('baz'), 'Baz', array('class' => 'flat', 'disabled' => 'disabled'));
        $this->addGroup($tabs, 'tabs', null, '&nbsp;', false);
        
        $this->addElement('header',     null, 'Baz page');

        $this->addElement('textarea',   'textPoetry', 'Recite a poem:', array('rows' => 5, 'cols' => 40));
        $this->addElement('textarea',   'textOpinion', 'Did you like this demo?', array('rows' => 5, 'cols' => 40));

        $this->addElement('submit',     $this->getButtonName('submit'), 'Big Red Button', array('class' => 'bigred'));

        $this->addRule('textPoetry', 'Pretty please!', 'required');

        $this->setDefaultAction('submit');
    }
}

// We subclass the default 'display' handler to customize the output
class ActionDisplay extends HTML_QuickForm_Action_Display
{
    function _renderForm(&$page) 
    {
        $renderer =& $page->defaultRenderer();
        
        // Do some cheesy customizations
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

$tabbed =& new HTML_QuickForm_Controller('Tabbed', false);

$tabbed->addPage(new PageFoo('foo'));
$tabbed->addPage(new PageBar('bar'));
$tabbed->addPage(new PageBaz('baz'));

// These actions manage going directly to the pages with the same name
$tabbed->addAction('foo', new HTML_QuickForm_Action_Direct());
$tabbed->addAction('bar', new HTML_QuickForm_Action_Direct());
$tabbed->addAction('baz', new HTML_QuickForm_Action_Direct());

// We actually add these handlers here for the sake of example
// They can be automatically loaded and added by the controller
$tabbed->addAction('jump', new HTML_QuickForm_Action_Jump());
$tabbed->addAction('submit', new HTML_QuickForm_Action_Submit());

// The customized actions
$tabbed->addAction('display', new ActionDisplay());
$tabbed->addAction('process', new ActionProcess());

$tabbed->setDefaults(array(
    'iradYesNoMaybe' => 'M',
    'favLetter'      => array('A' => true, 'Z' => true),
    'favDate'        => array('d' => 1, 'M' => 1, 'Y' => 2001),
    'textOpinion'    => 'Yes, it rocks!'
));

$tabbed->run();
?>