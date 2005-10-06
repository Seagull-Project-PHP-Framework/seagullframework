<?php
/**
 * Example 3 for HTML_QuickForm_Controller: Tabbed form
 * 
 * $Id: tabbed.php,v 1.2 2003/10/02 12:49:38 avb Exp $
 */

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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Controller example 3: tabbed form</title>
<style type="text/css">
input.bigred {font-weight: bold; background: #FF6666;}
input.flat {border-style: solid; border-width: 2px; border-color: #000000;}
</style>
</head>

<body>
<form{attributes}>
<table border="0">
{content}
</table>
</form>
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
