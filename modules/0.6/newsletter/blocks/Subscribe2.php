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
// | Subscribe2.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors: Yevgeniy A. Viktorov <wik@osmonitoring.com>                      |
// +---------------------------------------------------------------------------+

require_once SGL_MOD_DIR . '/newsletter/classes/NewsletterMgr.php';
require_once SGL_MOD_DIR . '/newsletter/classes/Output.php';

/**
 * Newsletter Block2.
 *
 * @package block
 * @author  Yevgeniy A. Viktorov <wik@osmonitoring.com>
 */
class Newsletter_Block_Subscribe2
{
    var $template     = 'blockSubscribeGuest.html';
    var $templatePath = 'newsletter';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->userID = isset($output->loggedOnUserID) ? $output->loggedOnUserID : '';
        $this->username = isset($output->loggedOnUser) ? $output->loggedOnUser : '';
        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $blockOutput = new SGL_Output();
        $blockOutput->webRoot   = $output->webRoot;
        $imageDir = (isset($output->imagesDir))
            ? $output->imagesDir
            : SGL_BASE_URL  . '/themes/' . $theme . '/image' ;
        $blockOutput->imagesDir = $imageDir;
        $blockOutput->theme = $output->theme;

        $c = &SGL_Config::singleton();
        $blockOutput->conf = $c->ensureModuleConfigLoaded('newsletter');

#FIXME data access methods should really go in a NewsletterDAO
        $news = & new NewsletterMgr();
        $blockOutput->aLists = $news->_getList();

        if (array_key_exists('guestTemplate', $aParams)) {
            $this->template = $aParams['guestTemplate'];
        }

        if ($this->username) {
            if (array_key_exists('loggedTemplate', $aParams)) {
                //  set block params
                $this->template = $aParams['loggedTemplate'];
            }
            $blockOutput->loggedOnUserID = $this->userID;
            $blockOutput->aUnsubscribedLists = $news->getUnsubscribedLists($this->userID);
            $blockOutput->aSubscribedLists = $news->getSubscribedLists($this->userID);
            foreach ($blockOutput->aSubscribedLists as $k => $v) {
                foreach ($blockOutput->aLists as $lKey => $lValue) {
                    if ($lValue['name'] == $v->list) {
                        $blockOutput->aSubscribedLists[$k]->listID = $lKey;
                    }
                }
            }
        } else {
            if (array_key_exists('guestTemplate', $aParams)) {
                //  set block params
                $this->template = $aParams['guestTemplate'];
            }
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