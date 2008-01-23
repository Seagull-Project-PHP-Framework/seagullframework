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
// | Authors:   Demian Turner <demian@phpkitchen.com>                          |
// +---------------------------------------------------------------------------+
// $Id: CommentDAO.php,v 1.14 2005/06/21 23:26:24 demian Exp $

/**
 * Data access methods for the publisher module.
 *
 * @package seagull
 * @subpackage comment
 * @author  Demian Turner <demian@phpkitchen.com>
 * @copyright Demian Turner 2006
 */
class CommentDAO extends SGL_Manager
{
    /**
     * @return CommentDAO
     */
    function CommentDAO()
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

    /**
     * For retrieving comments associated with entities,
     * ie articles = 'articleview', faqs = 'faq'
     *
     * for $status, 0 = awaiting approval, 1 = approved, -1 = all
     *
     * @param string $entity
     * @param integer $id
     * @param integer $status
     */
    function getCommentsByEntityId($entity, $id = null, $status = 1)
    {
        $constraint = (is_null($id))
            ? ''
            : " AND entity_id = $id ";
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
            WHERE   entity_name = '$entity'
            $constraint
            ";
        $aComments = $this->dbh->getAll($query);
        return $aComments;
    }

    function getEntityNames()
    {
        $query = "
            SELECT      distinct(entity_name)
            FROM        {$this->conf['table']['comment']}
            ORDER BY    entity_name ASC
            ";
        $aEntityNames = $this->dbh->getCol($query);
        return $aEntityNames;
    }

    /**
     * For retrieving all comments
     *
     * for $status, 0 = awaiting approval, 1 = approved, -1 = all
     *
     * @param integer $status
     */
    function getAllComments($status = 1)
    {
        if ($status === 1) {
            $constraint = " WHERE status_id = 1 ";
        } elseif ($status === 0) {
            $constraint = " WHERE status_id = 0 ";
        } else {
            $constraint = '';
        }
        $query = "
            SELECT  *
            FROM    {$this->conf['table']['comment']}
            $constraint
            ";
        $aComments = $this->dbh->getAll($query);
        return $aComments;
    }

    function getCommentById($id = null)
    {
        $oComment = DB_DataObject::factory($this->conf['table']['comment']);
        if (!is_null($id)) {
            $oComment->get($id);
        }
        return $oComment;
    }
}
?>
