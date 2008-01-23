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
// | LoginBlock2.php                                                           |
// +---------------------------------------------------------------------------+
// | Authors: Andrey Podshivalov <planetaz@gmail.com>                          |
// |          Julien Casanova <julien_casanova@yahoo.fr>                       |
// +---------------------------------------------------------------------------+

/**
 * User / Login Block2.
 *
 * Shows login form if not logged in, user data (username and "session started at")
 * if logged in
 *
 * @package block
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 * @author  Julien Casanova <julien_casanova@yahoo.fr>
 */
class User_Block_Login2
{
    var $template     = 'blockLogin.html';
    var $templatePath = 'user';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->uid = isset($output->loggedOnUserID) ? $output->loggedOnUserID : '';
        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $blockOutput            = new SGL_Output();
        $theme = isset( $_SESSION['aPrefs']['theme'])
            ? $_SESSION['aPrefs']['theme']
            : 'default';
        $blockOutput->theme     = $theme;
        $imageDir = (isset($output->imagesDir))
            ? $output->imagesDir
            : SGL_BASE_URL  . '/themes/' . $theme . '/image' ;
        $blockOutput->imagesDir = $imageDir;

        $c = &SGL_Config::singleton();
        $blockOutput->conf = $c->ensureModuleConfigLoaded('user');

        if ($this->uid == SGL_GUEST) {
            //  display login info
            if (array_key_exists('loginTemplate', $aParams)) {
                //  set block params
                $this->template = $aParams['loginTemplate'];
            }
            if (!empty($output->conf['tuples']['demoMode'])) {
                $blockOutput->username = 'admin';
                $blockOutput->password = 'admin';
            } else {
                $blockOutput->username = '';
                $blockOutput->password = '';
            }
        } else {
            //  user is logged in, display connection info
            if (array_key_exists('loginTemplate', $aParams)) {
                //  set block params
                $this->template = $aParams['logoutTemplate'];
            }
            $blockOutput->loggedOnUserID = $this->uid;
            $blockOutput->loggedOnUser   = isset($output->loggedOnUser) ? $output->loggedOnUser : '';
            $blockOutput->loggedOnSince  = isset($output->loggedOnSince) ? $output->loggedOnSince : '';
        }

        return $this->process($blockOutput);
    }

    function process(&$output)
    {
        // use moduleName for template path setting
        $output->moduleName     = $this->templatePath;
        $output->masterTemplate = $this->template;

        $view = new SGL_HtmlSimpleView($output);
        return $view->render();
    }
}
?>