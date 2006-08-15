<?php
class MediaDAO extends SGL_Manager
{
    /**
     * @return CmsDAO
     */
    function MediaDAO()
    {
        parent::SGL_Manager();
    }

    function &singleton()
    {
        static $instance;

        // If the instance is not there, create one
        if (!isset($instance)) {
            $instance = new MediaDAO();
        }
        return $instance;
    }

    function getMediaByFileType($id = null)
    {
        $constraint = (is_null($id)) ? '' : ' WHERE m.file_type_id = ' . $id;
        $query = "
            SELECT      media_id,
                        m.name, file_size, mime_type,
                        m.date_created, description,
                        mt.name AS file_type_name,
                        u.username AS media_added_by
            FROM        {$this->conf['table']['media']} m
            JOIN        {$this->conf['table']['file_type']} mt ON mt.file_type_id = m.file_type_id
            LEFT JOIN   {$this->conf['table']['user']} u ON u.usr_id = m.added_by
            $constraint
            ORDER BY    m.date_created DESC";
        return $this->dbh->getAll($query);
    }
}
?>