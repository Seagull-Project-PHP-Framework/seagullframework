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
// | CommentDAO.php                                                            |
// +---------------------------------------------------------------------------+
// | Authors:   Thomas Goetz <thomas@getconfused.net>                          |
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: CommentDAO.php,v 1.14 2005/06/21 23:26:24 demian Exp $

require_once 'DB/DataObject.php';

/**
 * Data access methods for the comment2 module.
 *
 * @package seagull
 * @subpackage comment2
 */
class Comment2DAO extends SGL_Manager
{
    /**
     * @return Comment2DAO
     */
    function __construct()
    {
        parent::SGL_Manager();
    }

    function &singleton()
    {
        static $instance;

        if (!isset($instance)) {
            $class = __CLASS__;
            $instance = new $class();
        }
        return $instance;
    }

    function getCommentById($id = null)
    {
        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        if (!is_null($id)) {
            $oComment->get($id);
        }
        return $oComment;
    }


    function addComment($oData)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  insert record
        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        $oComment->setFrom($oData);
        $oComment->comment_id = $this->dbh->nextId('comment');
        $oComment->comment_fk = $oData['fk'];
        $oComment->date_created = SGL_Date::getTime(true);
        $oComment->created_by = SGL_Session::getUid();
        $oComment->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $oComment->ip = $_SERVER['REMOTE_ADDR'];
        $oComment->status_id = 1;;

        $success = $oComment->insert();

        if ($success) {
           $success = $oComment->comment_id;
        } else {
            SGL::raiseError('There was a problem inserting the record',
                SGL_ERROR_NOAFFECTEDROWS);
        }
        return $success;
    }

    /**
     * For retrieving comments.
     *
     * for $status, 0 = awaiting approval, 1 = approved, -1 = all
     *
     * @param string $entity
     * @param integer $id
     * @param integer $status
     */
    function getCommentsByFk($fk, $status = 1)
    {
        $constraint = '';
        if ($status === 1) {
            $constraint .= " AND status_id = 1 ";
        } elseif ($status === 0) {
            $constraint .= " AND status_id = 0 ";
        } else {
            $constraint .= '';
        }
        $query = "
            SELECT  *
            FROM    {$this->conf['table']['comment']}
            WHERE   comment_fk = $fk
                $constraint
            ORDER BY date_created DESC
            ";
        $aComments = $this->dbh->getAll($query);
        return $aComments;
    }


     /**
     * For retrieving number of comments per item.
     *
     * @param integer $itemId
     */
    function getCommentCount($fk)
    {
        $query = "
            SELECT  COUNT(*) AS comment_count
            FROM    {$this->conf['table']['comment']}
            WHERE comment_fk = $fk
            ";
        $commentCount = $this->dbh->getOne($query);
        return $commentCount;
    }


    function deleteComment($commentId)
    {
        $query = "
            DELETE
            FROM    {$this->conf['table']['comment']}
            WHERE comment_id = $commentId
            ";
        $ok = $this->dbh->query($query);
    }
}
?>
