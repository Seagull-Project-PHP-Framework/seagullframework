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
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | AddCommentLink.php                                                        |
// +---------------------------------------------------------------------------+
// | Author: Matti Tahvonen <matti@nettirasia.com>                             |
// +---------------------------------------------------------------------------+

/**
 * Add comment block. Makes link to comment modules default form
 *
 * @package block
 */
require_once SGL_MOD_DIR . '/comment/classes/CommentContainer.php';

class Comment_Block_CommentTree
{
    var $template     = 'commentList.html';
    var $templatePath = 'comment';

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        return $this->getBlockContent($output, $aParams);
    }

    function getBlockContent(&$output, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $thisurl = $output->aProps['request']->getUri();
        $cc = new CommentContainer($thisurl);
        $cc->buildTree();
        $cCount = $cc->getCount();
        $html = '';
        if ($cCount > 0) {
            $html = $cc->getSubjectsAsHtmlSnippet();
        }
        
        // add "add a comment" button
        // first try to find a default subject for new post if found (can be changed by user)
        if (!empty($output->pageTitle)) {$subject = $output->pageTitle;}
        elseif (!empty($output->leadArticle['title'])) {$subject = $output->leadArticle['title'];}
        $html .= '
        <p>'.SGL_String::translate('Total of').' '. $cCount.' '.SGL_String::translate('comments for this page','vprintf') .'</a></p>
        <form method="post" action="'.SGL_Url::makeLink('add','comment','comment').'">
        <input type="hidden" name="seagull_uri" value="'.$thisurl.'">
        <input type="hidden" name="comment[subject]" value="'.$subject.'">
        <input type="submit" value="'.SGL_String::translate('Add a comment').'" />
        </form>
        ';
        
        return $html;
    }
}
?>