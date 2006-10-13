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

    /**
     * Retrieve media hash depending on options params
     *
     * @param array $options
     *              Can have following values:
     *               * type_id
     *               * date_created
     * @return unknown
     */
    function getMediaFiles($options = array())
    {
        $constraint = $this->_buildConstraint($options);

        $query = "
            SELECT      media_id,
                        m.name, file_name, file_size, mime_type,
                        m.date_created, description,
                        mt.name AS file_type_name,
                        u.username AS media_added_by
            FROM        {$this->conf['table']['media']} m
            JOIN        {$this->conf['table']['file_type']} mt ON mt.file_type_id = m.file_type_id
            LEFT JOIN   {$this->conf['table']['user']} u ON u.usr_id = m.added_by
            $constraint
            ORDER BY    m.date_created DESC
            ";
        return $this->dbh->getAll($query);
    }

    function getValidIds($options = array())
    {
        $aMedia = $this->getMediaFiles($options);
        $aRet = array();
        foreach ($aMedia as $key => $oMedia) {
            $aRet[] = $oMedia->media_id;
        }
        return $aRet;
    }

    function _buildConstraint($options)
    {
        $constraint = $typeClause = $dateClause = '';
        if (!empty($options)) {
            //  build clause
            if (isset($options['byTypeId']) && !empty($options['byTypeId'])) {
                $typeClause = ($options['byTypeId'] == 'all')
                    ? ''
                    : "m.file_type_id = {$options['byTypeId']}";
            }
            if (isset($options['byDateRange']) && !empty($options['byDateRange'])
                    && $options['byDateRange'] != 'any') {
                require_once 'Date.php';
                $dateBegins = new Date();
                $dateEnds = new Date();

                switch ($options['byDateRange']) {
                    case 'today':
                        $dateBegins->setHour(0);
                        $dateBegins->setMinute(0);
                        $dateBegins->setSecond(0);

                        $dateClause = "m.date_created >= {$this->dbh->quote($dateBegins->getDate())}
                                       AND m.date_created <= {$this->dbh->quote($dateEnds->getDate())}";
                        break;

                    case 'past7Days':
                        if ($dateBegins->day < 7) {
                            // carefull with month change
                            if($dateBegins->month == 1) {
                                $dateBegins->setMonth(12);
                                $dateBegins->year -= 1;
                            } else {
                                $dateBegins->month -= 1;
                            }
                            $dateBegins->setDay($dateBegins->getDaysInMonth() - (7 - $dateBegins->day));
                        } else {
                            $dateBegins->day -= 7;
                        }
                        $dateBegins->setHour(0);
                        $dateBegins->setMinute(0);
                        $dateBegins->setSecond(0);

                        $dateClause = "m.date_created >= {$this->dbh->quote($dateBegins->getDate())}
                                       AND m.date_created <= {$this->dbh->quote($dateEnds->getDate())}";
                        break;

                    case 'thisMonth':
                        $dateBegins->setDay(1);
                        $dateBegins->setHour(0);
                        $dateBegins->setMinute(0);
                        $dateBegins->setSecond(0);

                        $dateClause = "m.date_created >= {$this->dbh->quote($dateBegins->getDate())}
                                       AND m.date_created <= {$this->dbh->quote($dateEnds->getDate())}";
                        break;

                    case 'beforeThisMonth':
                        // carefull with year change
                        if ($dateEnds->month == 1) {
                            $dateEnds->setMonth(12);
                            $dateEnds->year -= 1;
                        } else {
                            $dateEnds->month -= 1;
                        }
                        $dateEnds->setDay($dateEnds->getDaysInMonth());
                        $dateEnds->setHour(23);
                        $dateEnds->setMinute(59);
                        $dateEnds->setSecond(59);

                        $dateClause = "m.date_created <= {$this->dbh->quote($dateEnds->getDate())}";
                        break;

                    default:
                }
            }
            if ($typeClause && $dateClause) {
                $constraint = "WHERE $typeClause AND $dateClause";
            } elseif($typeClause) {
                $constraint = "WHERE $typeClause";
            } elseif($dateClause) {
                $constraint = "WHERE $dateClause";
            }
        }
        return $constraint;
    }
}
?>