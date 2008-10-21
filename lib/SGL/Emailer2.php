<?php

require_once dirname(__FILE__) . '/Emailer/Builder.php';
require_once dirname(__FILE__) . '/String.php';
require_once 'Mail.php';

/**
 * Emailer class version 2.
 *
 * @package SGL
 * @author Demian Turner <demian@phpkitchen.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Emailer2
{
    /**
     * Creates email and sends it via one of the backends or pushes
     * created email to queue for later use.
     *
     * @param array $aDeliveryOpts
     *               obligatory keys
     *                * toEmail      - email of recipient
     *                * toRealName   - name of recipient
     *                * fromEmail    - email of sender
     *                * fromRealName - name of sender
     *                * subject      - email subject
     *               possible keys
     *                * replyTo      - email of reply to address
     *                * cc           - email to send copy to
     *                * bcc          - email to send hidden copy to
     *                * attachment   - fullpath to file or files (array if many)
     *
     * @param array $aTplOpts
     *               obligatory keys:
     *                * mode (by default: SGL_Emailer_Builder::MODE_HTML_AND_TEXT)
     *                * htmlTemplate
     *                * moduleName (for htmlTemplate only)
     *                * textTemplate
     *                * rawText
     *                * rawHtml
     *               default keys (can be overwritten):
     *                * siteUrl
     *                * siteName
     *                * crlf
     *
     * @param array $aQueueOpts
     *               possible keys:
     *                * sendDelay
     *                * groupId
     *                * userId
     *                * batchId
     *
     * @return boolean
     */
    public static function send($aDeliveryOpts, $aTplOpts, $aQueueOpts = array())
    {
        try {
            $oEmail = new SGL_Emailer_Builder($aDeliveryOpts, $aTplOpts);
        } catch (Exception $e) {
            // we want PEAR_Error because code below
            // doesn't support Exceptions
            $oEmail = SGL::raiseError($e->getMessage());
        }
        if (!PEAR::isError($oEmail)) {
            // push email to queue
            if (SGL_Config::get('emailQueue.enabled')) {
                $oQueue = self::queueSingleton($aQueueOpts);
                $ok     = $oQueue->push(
                    $oEmail->aHeaders,
                    $oEmail->aHeaders['To'],
                    $oEmail->body,
                    $oEmail->aHeaders['Subject'],
                    isset($aQueueOpts['groupId'])
                        ? $aQueueOpts['groupId']
                        : null,
                    isset($aQueueOpts['batchId'])
                        ? $aQueueOpts['batchId']
                        : null,
                    isset($aQueueOpts['userId'])
                        ? $aQueueOpts['userId']
                        : null
                );
            // send email
            } else {
                $oEmailer = self::factory();
                $ok       = $oEmailer->send(
                    $oEmail->aHeaders['To'],
                    $oEmail->aHeaders,
                    $oEmail->body
                );
            }
        } else {
            $ok = $oEmail;
        }
        return $ok;
    }

    /**
     * Returns SGL_Emailer_Queue instance.
     *
     * @param array $aOpts
     *
     * @return SGL_Emailer_Queue
     */
    public static function &queueSingleton($aOpts = array())
    {
        static $oInstance;
        if (!isset($oInstance)) {
            require_once dirname(__FILE__) . '/Emailer/Queue.php';
            $aConfOpts = SGL_Config::singleton()->get('emailQueue')
                ? SGL_Config::singleton()->get('emailQueue')
                : array();
            $aOpts     = array_merge($aConfOpts, $aOpts);
            $oInstance = new SGL_Emailer_Queue($aOpts);
        }
        return $oInstance;
    }

    /**
     * PEAR::Mail::factory() wrapper.
     *
     * @return object
     */
    public static function &factory()
    {
        $backend = SGL_Config::get('mta.backend');
        $aParams = array();

        switch ($backend) {

        // setup sendmail
        case 'sendmail':
            $aParams['sendmail_path'] = SGL_Config::get('mta.sendmailPath');
            $aParams['sendmail_args'] = SGL_Config::get('mta.sendmailArgs');
            break;

        // setup smtp
        case 'smtp':
            if (SGL_Config::get('mta.smtpLocalHost')) {
                $aParams['localhost'] = SGL_Config::get('mta.smtpLocalHost');
            }
            $aParams['host'] = SGL_Config::get('mta.smtpHost')
                ? SGL_Config::get('mta.smtpHost')
                : '127.0.0.1';
            $aParams['port'] = SGL_Config::get('mta.smtpPort')
                ? SGL_Config::get('mta.smtpPort')
                : 25;
            if (SGL_Config::get('mta.smtpAuth')) {
                $aParams['auth']     = SGL_Config::get('mta.smtpAuth');
                $aParams['username'] = SGL_Config::get('mta.smtpUsername');
                $aParams['password'] = SGL_Config::get('mta.smtpPassword');
            }

        // use PHP's mail
        case 'mail':
            break;

        // use mail backend by default
        default:
            SGL::logMessage('Unrecognised PEAR::Mail backend', PEAR_LOG_ERR);
            $backend = 'mail';
        }

        return Mail::factory($backend, $aParams);
    }

    /**
     * @todo replace isCyrillic() check to isMultiByte()
     *
     * @param string $email
     * @param string $fullName
     *
     * @return string
     */
    public static function formatAddress($email, $fullName)
    {
        return SGL_String::isCyrillic($fullName)
            ? $email
            : SGL_String::replaceAccents($fullName) . " <$email>";
    }

    /**
     * Takes a string or an associative array of mail headers with each
     * key representing a header's name and a value representing
     * a header's value. The function removes every additional
     * header from each value to prevent mail injection attacks.
     *
     * @param mixed $aHeaders
     *
     * @return mixed
     *
     * @author Andreas Ahlenstorf
     * @author Werner M. Krauss <werner.krauss@hallstatt.net>
     */
    public static function cleanMailInjection($aHeaders)
    {
        $regex = "#((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*#i";
        if (is_array($aHeaders)) {
            foreach ($aHeaders as $k => $v) {
                $aHeaders[$k] = preg_replace($regex, null, $v);
            }
        } else {
            $aHeaders = preg_replace($regex, null, $aHeaders);
        }
        return $aHeaders;
    }
}

?>