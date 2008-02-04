<?php

/**
 * PEAR::DB container for SGL_Emailer_Queue.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Emailer_Queue_Container_Db extends SGL_Emailer_Queue_Container
{
    /**
     * PEAR_DB ref.
     *
     * @var DB_Common
     */
    private $_dbh;

    /**
     * Constructor.
     *
     * @return SGL_Emailer_Queue_Container_Db
     */
    public function SGL_Emailer_Queue_Container_Db()
    {
        $this->_dbh = SGL_DB::singleton();
    }

    /**
     * Put new email to database.
     *
     * @param string $headers
     * @param string $recipient
     * @param string $body
     * @param string $subject
     * @param string $dateToSend
     * @param string $groupID
     *
     * @return DB_OK
     */
    public function push($headers, $recipient, $body, $subject, $dateToSend, $groupID)
    {
        $query = sprintf('INSERT INTO email_queue
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
            $this->_dbh->nextId('email_queue'),
            'NOW()',
            $this->_dbh->quoteSmart($dateToSend),
            'NULL',
            $this->_dbh->quoteSmart($headers),
            $this->_dbh->quoteSmart($recipient),
            $this->_dbh->quoteSmart($body),
            $this->_dbh->quoteSmart($subject),
            0,
            SGL_Session::getUid(),
            $this->dbh->quoteSmart($groupID)
        );
        return $this->_dbh->query($query);
    }

    /**
     * Removes email from database.
     *
     * @param integer $emailId
     *
     * @return DB_OK
     */
    public function remove($emailId)
    {
        $query = "
            DELETE FROM email_queue
            WHERE  email_queue_id = " . intval($emailId);
        return $this->_dbh->query($query);
    }

    /**
     * Marks email as sent.
     *
     * @param integer $emailId
     *
     * @return DB_OK
     */
    public function markAsSent($emailId)
    {
        $query = "
            UPDATE email_queue SET date_sent = NOW(), attempts = attempts + 1
            WHERE  email_queue_id = " . intval($emailId);
        return $this->_dbh->query($query);
    }

    /**
     * Increses attempt count.
     *
     * @param unknown_type $emailId
     *
     * @return DB_OK
     */
    public function increaseAttemptCount($emailId)
    {
        $query = "
            UPDATE email_queue SET attempts = attempts + 1
            WHERE  email_queue_id = " . intval($emailId);
        return $this->_dbh->query($query);
    }

    /**
     * Preloads emails from database.
     *
     * @param integer $limit
     * @param integer $attempts
     * @param integer $groupID
     *
     * @return void
     */
    public function preload($limit = null, $attempts = null, $groupID = null)
    {
        $constraint = '';
        if (!empty($attempts)) {
            $constraint = ' AND attempts < ' . intval($attempts);
        }
        if (!empty($groupID)) {
            $constraint .= ' AND group_id = ' . intval($groupID);
        }
        $query = "
            SELECT email_queue_id, date_created, date_to_send, date_sent,
                   mail_headers, mail_recipient, mail_body, mail_subject,
                   date_sent, usr_id, group_id
            FROM   email_queue
            WHERE  date_to_send <= NOW() AND date_sent IS NULL
                   $constraint
        ";
        if (!empty($limit)) {
            $query = $this->_dbh->modifyLimitQuery($query, 0, $limit);
        }
        $ok = $this->_dbh->query($query);
        if (PEAR::isError($ok)) {
            return $ok;
        }
        $this->_preloadResult = $ok;
    }

    /**
     * Retuns email ID.
     *
     * @param object $email
     *
     * @return integer
     */
    public function identifyEmail($email)
    {
        if (is_object($email) && isset($email->email_queue_id)) {
            $ret = $email->email_queue_id;
        } else {
            $ret = false;
        }
        return $ret;
    }
}

?>