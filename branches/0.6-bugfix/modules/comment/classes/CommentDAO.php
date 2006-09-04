<?php
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
     * For retrieving comments associated with entites,
     * ie articles = 'article', faqs = 'faq'
     *
     * @param string $entity
     * @param integer $id
     */
    function getCommentsByEntityId($entity, $id = null)
    {
        $constraint = (is_null($id))
            ? ''
            : 'AND entity_id = $id';
        $query = "
            SELECT *
            FROM {$this->conf['table']['comment']}
            WHERE entity_name = '$entity'
            $constraint
            ";
        $aComments = $this->dbh->getAll($query);
        return $aComments;
    }

    function getAllComments()
    {
        $query = "
            SELECT *
            FROM {$this->conf['table']['comment']}
            ";
        $aComments = $this->dbh->getAll($query);
        return $aComments;
    }
}
?>