<?php

/**
 * Base class for all containers.
 *
 * @package SGL
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
abstract class SGL_Emailer_Queue_Container
{
    /**
     * Stores current result set pointer.
     *
     * @var DB_Result
     */
    protected $_preloadResult = null;

    /**
     * Returns container.
     *
     * @param string $containerName
     *
     * @return SGL_Emailer_Queue_Container
     */
    public static function factory($containerName)
    {
        $containerName = ucfirst(strtolower($containerName));
        $fileName = dirname(__FILE__) . "/Container/{$containerName}.php";
        if (file_exists($fileName)) {
            include_once $fileName;
        } else {
            return SGL::raiseError('Container not found', SGL_ERROR_NOFILE);
        }
        $className = "SGL_Emailer_Queue_Container_$containerName";
        if (class_exists($className)) {
            return new $className;
        } else {
            return SGL::raiseError('Class not found', SGL_ERROR_NOCLASS);
        }
    }

    /**
     * Puts email to queue
     *
     * @param $headers
     * @param $recipient
     * @param $body
     * @param $subject
     * @param $dateToSend
     * @param $groupId
     * @param $batchId
     * @param $userId
     * @return object
     */
    public function push($headers, $recipient, $body, $subject, $dateToSend,
        $groupId, $batchId, $userId)
    {
        return SGL::raiseError('Not implemented', SGL_ERROR_NOMETHOD);
    }

    /**
     * Removes email from queue.
     *
     * @param integer $emailId
     *
     * @return object
     */
    public function remove($emailId)
    {
        return SGL::raiseError('Not implemented', SGL_ERROR_NOMETHOD);
    }

    /**
     * Preloads emails from queue.
     *
     * @param integer $limit
     * @param integer $attempts
     * @param string $deliveryDate
     * @param array $aParams
     *
     * @return object
     */
    public function preload($limit = null, $attempts = null,
        $deliveryDate = null, $aParams = null)
    {
        return SGL::raiseError('Not implemented', SGL_ERROR_NOMETHOD);
    }

    /**
     * Marks email as sent.
     *
     * @param integer $id
     *
     * @return object
     */
    public function markAsSent($id)
    {
        return SGL::raiseError('Not implemented', SGL_ERROR_NOMETHOD);
    }

    /**
     * Increases attempt count.
     *
     * @param integer $id
     *
     * @return object
     */
    public function increaseAttemptCount($id)
    {
        return SGL::raiseError('Not implemented', SGL_ERROR_NOMETHOD);
    }

    /**
     * Returns email identification string.
     *
     * @param object $email
     *
     * @return object
     */
    public function identifyEmail($email)
    {
        return SGL::raiseError('Not implemented', SGL_ERROR_NOMETHOD);
    }

    /**
     * Checks if queue is preloaded.
     *
     * @return boolean
     */
    public function isPreloaded()
    {
        return !is_null($this->_preloadResult)
                && $this->_preloadResult instanceof DB_result;
    }

    /**
     * Gets next email from preload queue.
     *
     * @return object
     */
    public function fetch()
    {
        if (!$this->isPreloaded()) {
            $ret = SGL::raiseError('Queue is not preloaded',
                SGL_ERROR_INVALIDCALL);
        } else {
            $ret = $this->_preloadResult->fetchRow();
        }
        return $ret;
    }
}

?>