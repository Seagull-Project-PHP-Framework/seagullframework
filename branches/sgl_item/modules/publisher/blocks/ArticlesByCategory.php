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
// | Article.php                                                               |
// +---------------------------------------------------------------------------+
// | Author: Werner M. Krauss <werner@seagullproject.org>                      |
// +---------------------------------------------------------------------------+

require_once SGL_CORE_DIR . '/Item.php';

/**
 * Show all articles of current category in a block.
 *
 * @package publisher
 */
class Publisher_Block_ArticlesByCategory
{
    var $template     = 'articleList.html';
    var $templatePath = 'publisher';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $blockOutput          = new SGL_Output();
        //  set block params
        $this->template = (array_key_exists('template', $aParams))
            ? $aParams['template']
            : $this->template;

        $catID = (int)isset($output->catID) 
            ? $output->catID 
            : (array_key_exists('defaultCatId', $aParams)
                ? $aParams['defaultCatId'] : 1);
        $dataTypeID = isset($output->dataTypeID) 
            ? $output->dataTypeID 
            : (array_key_exists('defaultDataTypeId', $aParams)
                ? $aParams['defaultDataTypeId'] : 2);

		$item = new SGL_Item();
		$aArticleList = $item->retrievePaginated(
           array(
                'catID'     => $catID,
                'bPublish'  => true,
                'dataTypeID'    => $dataTypeID,
                'disablePager' => true,
            )
        );


        $blockOutput->articleList = $aArticleList['data'];

 
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