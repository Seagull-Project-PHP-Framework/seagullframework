<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Demian Turner                                         |
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
// | Comment.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Matti Tahvonen  <matti@tahvonen.com>                              |
// +---------------------------------------------------------------------------+
// $Id: ManagerTemplate.html,v 1.2 2005/04/17 02:15:02 demian Exp $

/**
 * Type your class description here ...
 *
 * @package comment
 * @author  Matti Tahvonen  <matti@tahvonen.com>
 */
require_once SGL_ENT_DIR . '/Comment.php';
 
class Comment extends DataObjects_Comment
{
    /** array of childs, if tree built */
    var $aChildren;

    function getSubjectAsHtmlSnippet() {
        $html = "
        <li class=\"comment\">
            <a href=\"".
            SGL_Url::makeLink('view','comment','comment')
            ."comment_id/{$this->comment_id}\">{$this->subject}</a>
            <span class=\"from\">From: ";
        if ($this->usr_id > 0) {
            $html .= '<a href="'. 
                SGL_Url::makeLink('view','profile','user',null,'frmUserID/'.$this->usr_id)
                .'">'.$this->guestname.'</a>';
        } else {
            $html .= $this->guestname;
        } 
        $html .= " ({$this->timestamp})</span> ";
        $html .= $this->getChildTreeAsHtmlSnippet();
        $html .= "
        </li>
        ";
        return $html;
    }
    
    function getChildTreeAsHtmlSnippet() {
        if (is_array($this->aChildren) && count($this->aChildren > 0)) {
            $html = "<ul class=\"children\">";
            foreach($this->aChildren as $c) {
                $html .= $c->getSubjectAsHtmlSnippet();
            }
            $html .= "</ul>";
        }
        return $html;
    }
}
?>