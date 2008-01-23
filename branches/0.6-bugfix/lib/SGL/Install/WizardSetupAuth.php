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
// | WizardCreateAdminUser.php                                                 |
// +---------------------------------------------------------------------------+
// | Author:   Steven Stremciuc <steve@freeslacker.net>                        |
// +---------------------------------------------------------------------------+

function authFileExists()
{
    if (file_exists(SGL_PATH . '/AUTH.txt')) {
        $file = file_get_contents(SGL_PATH . '/AUTH.txt');
        if (strpos($file, $_SESSION['authString']) !== false) {
            return true;
        } else {
            return array('authFile' => '* Authorisation string not found in AUTH.txt file');
        }
    } else {
        return array('authFile' => '* AUTH.txt file does not exist');
    }
}

/**
 * @package Install
 */
class WizardSetupAuth extends HTML_QuickForm_Page
{
    function buildForm()
    {
        $this->_formBuilt = true;

        if (!isset($_SESSION['authString'])) {
            $_SESSION['authString'] = md5($_SERVER['HTTP_HOST'] . SGL_PATH);
        }

        $this->addElement('header',     null, 'Seagull Setup Authorisation: page 2 of 6');

        $this->addElement('static', 'authFile', 'Authenticate',
            'This step is a simple authentication check to make sure the owner of ' .
            'this site is performing this installation.<br />' .
            '<br />' .
            'All that is required for you to do is to create a text file named <b>AUTH.txt</b> ' .
            'containing the following randomly generated string of characters and place it ' .
            'in the root directory of the Seagull application. This is the same ' .
            'directory that has the INSTALL.txt, README.txt, and VERSION.txt files.<br />' .
            '<br />' .
            '<b>' . $_SESSION["authString"] . '</b><br />' .
            '<br />' .
            'To simplify things for you, you can <a href="setup.php?download=1">click on this link to download the AUTH.txt ' .
            'file</a>. Once you download the file, simply place it in the root ' .
            'directory of this Seagull application.');

        $this->addElement('hidden', 'authString', $_SESSION['authString']);
        $this->addFormRule('authFileExists');

        //  submit
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('back'), '<< Back');
        $prevnext[] =& $this->createElement('submit',   $this->getButtonName('next'), 'Next >>');
        $this->addGroup($prevnext, null, '', '&nbsp;', false);
        $this->setDefaultAction('next');
    }
}
?>