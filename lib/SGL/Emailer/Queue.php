<?php

require_once dirname(__FILE__) . '/Queue/Container.php';

/**
 * Emailer queue.
 *
 * @package SGL
 * @subpackage Emailer
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Emailer_Queue
{
    /**
     * Default send options.
     *
     * @var array
     */
    private $_aOptions = array(
        'delay'      => 60,   // in seconds
        'attempts'   => 25,   // try to send each email 25 times
        'limit'      => 50,   // how many emails to send per request
        'container'  => 'db', // the only one supported for now
        'removeSent' => 1     // by default we remove sent emails
    );

    /**
     * Container.
     *
     * @var SGL_Emailer_Queue_Container
     */
    private $_container;

    /**
     * Constructor.
     *
     * @param array $aOptions
     *
     * @return SGL_Emailer_Queue
     */
    public function SGL_Emailer_Queue($aOptions = array())
    {
        $this->_aOptions  = array_merge($this->_aOptions, $aOptions);
        $this->_container = SGL_Emailer_Queue_Container::factory($this->_aOptions['container']);
    }

    /**
     * Puts email to queue.
     *
     * @param array  $headers
     * @param mixed  $recipient
     * @param string $body
     * @param string $subject
     *
     * @return boolean
     */
    public function push($headers, $recipient, $body, $subject = '')
    {
        $dateToSend = date("Y-m-d G:i:s", time() + $this->_aOptions['delay']);
        return $this->_container->push(
            serialize($headers),
            serialize($recipient),
            $body, $subject, $dateToSend);
    }

    /**
     * Pops email from queue.
     *
     * @return object.
     */
    public function pop()
    {
        $ok = $this->_preload();
        if (PEAR::isError($ok)) {
            return $ok;
        }
        return $this->_container->fetch();
    }

    /**
     * Removes email from queue.
     *
     * @param integer $emailId
     *
     * @return boolean
     */
    public function remove($emailId)
    {
        return $this->_container->remove($emailId);
    }

    /**
     * Processes email queue. Sends retrieved emails.
     *
     * @return boolean
     */
    public function processQueue($skipSend = false)
    {
        while ($email = $this->pop()) {
            if (PEAR::isError($email)) {
                return $email;
            }
            $id = $this->_container->identifyEmail($email);
            if (!$skipSend) { // need this flag for test purpose
                // try to send the email
                $ok = $this->send($email);
                if (PEAR::isError($ok) || $ok === false) {
                    // email was not send
                    $this->_container->increaseAttemptCount($id);
                    continue;
                }
            }
            // email sent successfully at this point, now do smth with it
            $ok = $this->_aOptions['removeSent']
                ? $this->_container->remove($id)
                : $this->_container->markAsSent($id);
            if (PEAR::isError($ok)) {
                return $ok;
            }
        }
        return true;
    }

    /**
     * Sends emails.
     *
     * @param object $email
     *
     * @return boolean
     */
    public function send($email)
    {
        static $emailer;
        if (!isset($emailer)) {
            require_once 'SGL/Emailer.php';
            // hack to set $this->conf
            $this->conf = SGL_Config::singleton()->getAll();
            $emailer = SGL_Emailer::factory();
            unset($this->conf); // remove hack
        }
        return $emailer->send(
            unserialize($email->mail_recipient),
            unserialize($email->mail_headers),
            $email->mail_body
        );
    }

    /**
     * Preloads queue.
     *
     * @return boolean
     */
    private function _preload()
    {
        if ($this->_container->isPreloaded()) {
            $ret = true;
        } else {
            $ret = $this->_container->preload($this->_aOptions['limit'],
                $this->_aOptions['attempts']);
        }
        return $ret;
    }
}

?>