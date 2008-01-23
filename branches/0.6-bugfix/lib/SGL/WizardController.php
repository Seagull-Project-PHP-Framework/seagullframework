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
// | ClientWizard.php                                                          |
// +---------------------------------------------------------------------------+
// | Author: Malaney J. Hill <malaney@gmail.com>                               |
// +---------------------------------------------------------------------------+
// $Id: ClientWizard.php,v 1.5 2006/07/31 15:57:01 mhill Exp $

require_once 'HTML/QuickForm/Action.php';
require_once 'HTML/QuickForm/Action/Display.php';
require_once 'HTML/QuickForm/Action/Next.php';
require_once 'HTML/QuickForm/Action/Back.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Renderer/Default.php';

/**
 * Inherit from HTML_QuickForm_Controller to build
 * multi-page forms (wizards).
 *
 * @package SGL
 * @author  Malaney J. Hill <malaney@gmail.com>
 * @version $$
 */
class SGL_WizardController extends HTML_QuickForm_Controller
{
   /**
    * Extracts the names of the current page and the current action from
    * HTTP request data.
    *
    * @access public
    * @return array     first element is page name, second is action name
    */
    function getActionName()
    {
        if (is_array($this->_actionName)) {
            return $this->_actionName;
        }
        $names = array_map('preg_quote', array_keys($this->_pages));
        $regex = '/^_qf_(' . implode('|', $names) . ')_(.+?)(_x)?$/';

    $count = 0;
    // This section of the code could most likely be improved ...
    // Basically we are collecting the action vars from $_REQUEST
    // and if it is a "back" or "next" action we process immediately,
    // if it is a "display" action, we delay processing to see if there
    // is still a "back" or "next" action.  In other words, we give priority
    // to "next/back" over "display" actions.
    $possibles = array();
        foreach (array_keys($_REQUEST) as $key) {
            if (preg_match($regex, $key, $matches)) {
            $possibles[$key] =  $matches;
            $count++;
            }
        }
    if (count($possibles))
    {
        foreach ($possibles as $p => $arr)
        {
            if ( (preg_match("/next/", $p)) || (preg_match("/back/", $p)) )
            {
                $matches = $arr;
                break;
            }
            else if (preg_match("/display/", $p))
            {
                $matches = $arr;
            }
        }
                return array($matches[1], $matches[2]);
    }
        if (isset($_REQUEST['_qf_default'])) {
            $matches = explode(':', $_REQUEST['_qf_default'], 2);
            if (isset($this->_pages[$matches[0]])) {
                return $matches;
            }
        }
        reset($this->_pages);
        return array(key($this->_pages), 'display');
    }
}

/**
 * The action handles the HTTP redirect to a specific page.
 *
 * @package SGL
 * @version $Revision: 1.4 $
 */
class SGL_WizardControllerJump extends HTML_QuickForm_Action_Jump
{
   /**
    * Extracts the names of the current page and the current action from
    * HTTP request data, strips off all query string vars.
    *
    * @access public
    * @return array     first element is page name, second is action name
    */
    function perform(&$page, $actionName)
    {
        // check whether the page is valid before trying to go to it
        if ($page->controller->isModal()) {
            // we check whether *all* pages up to current are valid
            // if there is an invalid page we go to it, instead of the
            // requested one
            $pageName = $page->getAttribute('id');
            if (!$page->controller->isValid($pageName)) {
                $pageName = $page->controller->findInvalid();
            }
            $current =& $page->controller->getPage($pageName);

        } else {
            $current =& $page;
        }
        // generate the URL for the page 'display' event and redirect to it
        $action = $current->getAttribute('action');

    // simply remove all query string vars ...
    // NOTE:  this may only work with Seagull SEF Url Handler, may need to
    // be expanded to deal with other URL Handlers
    $action = preg_replace("/\?.*/", "", $action);

        $url    = $action . (false === strpos($action, '?')? '?': '&') .
                  $current->getButtonName('display') . '=true' .
                  ((!defined('SID') || '' == SID || ini_get('session.use_only_cookies'))? '': '&' . SID);
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Class representing an action to perform on HTTP request. The Controller
 * will select the appropriate Action to call on the request and call its
 * perform() method. The subclasses of this class should implement all the
 * necessary business logic.
 *
 * @package SGL
 * @version $Revision: 1.1 $
 */
class SGL_WizardControllerProcess extends HTML_QuickForm_Action
{
   /**
    * Processes the request, assigning controller output to var for
    * rendering.
    *
    * @access public
    * @param  object HTML_QuickForm_Page    the current form-page
    * @param  string    Current action name, as one Action object can serve multiple actions
    */
    function perform(&$page, $actionName)
    {
        // Assign form data for rendering purposes
        $page->wizardData = $page->controller->exportValues();
        // reset controller
        $page->controller->container(true);
    }
}

/**
 * This action handles the output of the form.
 *
 * @author  Alexey Borzov <avb@php.net>
 * @package SGL
 * @version $Revision: 1.5 $
 */
class SGL_WizardControllerDisplay extends HTML_Quickform_Action_Display
{
   /**
    * Actually outputs the form.
    *
    * This assigns the wizard output to a variable for rendering
    *
    * @access public
    * @param  object HTML_QuickForm_Page  the page being processed
    */
    function _renderForm(&$page)
    {
        $renderer = & new HTML_QuickForm_Renderer_Default();
        $page->accept($renderer);
        $page->wizardOutput = $renderer->toHtml();
    }
}
?>
