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
// | Article.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Andrey Podshivalov <planetaz@gmail.com>                           |
// +---------------------------------------------------------------------------+

require_once SGL_CORE_DIR . '/Item.php';

/**
 * Show static Html article in a block.
 *
 * @package block
 */
class Publisher_Block_Article
{
    var $template     = 'articleViewStaticHtmlArticle.html';
    var $templatePath = 'publisher';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $blockOutput            = new SGL_Output();
        $blockOutput->theme     = $output->theme;
        $blockOutput->isAdmin   = $output->isAdmin();
        $blockOutput->imagesDir = $output->imagesDir;
        $blockOutput->redir     = isset($output->redir) ? $output->redir : '';

        // default parameters
        $allowUnpublished = false;

        //  set block params
        if (array_key_exists('articleId', $aParams)) {
            $articleId = (int)$aParams['articleId'];
        } else {
            return false;
        }
        if (array_key_exists('template', $aParams)) {
            $this->template = $aParams['template'];
        }
        if (array_key_exists('allowUnpublished', $aParams)) {
            $allowUnpublished = $aParams['allowUnpublished'];
        }

        // get current URL
        $input = &SGL_Registry::singleton();
        $url = $input->getCurrentUrl();
        $currentUrl = $url->toString();

        //  get article
        $blockOutput->leadArticle = SGL_Item::getItemDetail($articleId, !$allowUnpublished);
        $blockOutput->articleID   = $articleId;
        $blockOutput->theme       = $_SESSION['aPrefs']['theme'];
        $blockOutput->redir       = urlencode(urlencode($currentUrl));

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