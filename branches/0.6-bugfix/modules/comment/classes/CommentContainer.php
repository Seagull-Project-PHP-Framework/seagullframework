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
// | CommentContainer.php                                                            |
// +---------------------------------------------------------------------------+
// | Author: Matti Tahvonen  <matti@tahvonen.com>                              |
// +---------------------------------------------------------------------------+
// $Id: ManagerTemplate.html,v 1.2 2005/04/17 02:15:02 demian Exp $

require_once SGL_MOD_DIR . '/comment/classes/Comment.php';

/**
 * Container for comments of one page.
 *
 * Check ../blocks/CommentTree.php and CommentMgr.php until doc is updated
 *
 * @package comment
 * @author  Matti Tahvonen  <matti@tahvonen.com>
 */
class CommentContainer {
    /** uri to page to which comment belongs */
    var $seagull_uri;
    /** comments for this uri
     * @access private
     */
    var $aComment;

    var $dbh;
    var $conf;

    /**
     * Constructor for CommentContainer
     * If second attribute is set, "seagull_uri" will be set from it
     * @param $uri
     * @param $comment_id comment_id from thread
     */
    function CommentContainer($uri, $comment_id = null)
    {
        if (empty($uri) && empty($comment_id)) {
            return false;
        }
        $this->dbh = & SGL_DB::singleton();
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        if (!empty($uri)) {
            // allow to be called with uri parts to fc controller
            if (strpos($uri,'http') === 0) {
                // TODO simplify
                $uristart = $this->conf['site']['baseUrl'];
                if ($this->conf['site']['frontScriptName']) {
                    $uristart .= '/'.$this->conf['site']['frontScriptName'];
                }
                $uristart .= '/';
                $uri = str_replace($uristart,'',$uri);
                // strip trailing slash
                $uri = preg_replace('/\/$/','',$uri);
                $this->seagull_uri = $uri;
            } else {
                $this->seagull_uri = $uri;
            }
        }
        if (!empty($comment_id)) {
            $this->seagull_uri = $this->dbh->getOne("SELECT seagull_uri
                FROM {$this->conf['table']['comment']} WHERE comment_id = {$comment_id}");
        }
    }

    /**
     * @return count of comments
     **/
    function getCount()
    {
        if(isset($this->aComment)) {
            return count($this->aComment);
        } else {
            // query from db
            return $this->dbh->getOne("SELECT COUNT(comment_id)
                FROM {$this->conf['table']['comment']}
                WHERE seagull_uri = '{$this->seagull_uri}'");
        }
    }

    /**
     * Function to save comment for this page
     *
     **/
    function saveComment($subject, $commentBody, $parent_id = 'NULL', $name = 'guest')
    {
        $comment = new Comment();
        $comment->comment_id = $this->dbh->nextId($this->conf['table']['comment']);
        $comment->seagull_uri = $this->seagull_uri;
        $comment->parent_id = $parent_id;
        $comment->subject = $subject;
        $comment->comment = $commentBody;
        $comment->usr_id = SGL_Session::getUid();
        if ($comment->usr_id > 0) {
            // saving username avoids need to make join
            // updating username when miss username changes tought...
            $comment->guestname = SGL_Session::getUsername();
        } else {
            // we have a guest, add his/her name
            $comment->guestname = $name;
        }
        $ret = $comment->insert();
        if(PEAR::isError($ret)) {
            SGL::raiseError($ret);
        }
    }

    /**
     * Fetches comments from DB and builds tree
     * "Second state" of constructing object
     */
    function buildTree()
    {
        $comment = new Comment();
        $comment->whereAdd("seagull_uri = '{$this->seagull_uri}'");
        $comment->groupBy('parent_id, comment_id');
        $result = $comment->find();
        if ($result > 0) {
            // Let's iterate through all sections and build tree & fill aComments.
            while ($comment->fetch()) {
                $c = clone($comment);
                $this->aComment[$comment->comment_id] =& $c;
                // building tree
                if($c->parent_id != 0) { // not for root nodes
                    $this->aComment[$comment->parent_id]->aChildren[] =& $c;
                }
                unset($c);
            } // end while loop
        } // end if results

    }
    /**
     * This function is used to create html tree of pages all comments
     * Has to be called after buildTree() initialision method
     * @return snippet of Html code
     */
    function getSubjectsAsHtmlSnippet()
    {
        if (!isset($this->aComment)) {
            $this->buildTree();
        }
        if (count($this->aComment) > 0) {
            $html = '<ul class="comments">';
            foreach ($this->aComment as $c) {
                if ($c->parent_id > 0) break; // children are already printed
                $html .= $c->getSubjectAsHtmlSnippet();
            }
            $html .= '</ul>';
            return $html;
        }
    }

    /**
     * Get one comment and its children
     * @return Comment
     */
    function getComment($id)
    {
        if (!isset($this->aComment)) {
            $this->buildTree();
        }
        return $this->aComment[$id];
    }

    function getComments()
    {
        if (!isset($this->aComment)) {
            $this->buildTree();
        }
        return $this->aComment;
    }

    function getAllComments()
    {
        $this->dbh = & SGL_DB::singleton();
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
        $query = "
            SELECT comment_id, parent_id, seagull_uri, usr_id, guestname, subject, comment, timestamp
            FROM {$this->conf['table']['comment']}
            ";
        $aComments = $this->dbh->getAll($query);
        return $aComments;
    }
}
?>