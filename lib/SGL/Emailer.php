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
// | Emailer.php                                                               |
// +---------------------------------------------------------------------------+
// | Author:   Demian Turner <demian@phpkitchen.com>                           |
// +---------------------------------------------------------------------------+
// $Id: Permissions.php,v 1.5 2005/02/03 11:29:01 demian Exp $

require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
 * Wrapper class for PEAR::Mail.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.11 $
 */

class SGL_Emailer
{
    var $headerTemplate = '';
    var $footerTemplate = '';
    var $html           = '';
    var $headers        = array();
    var $options        = array(
        'toEmail'       => '',
        'toRealName'    => '',
        'fromEmail'     => '',
        'fromRealName'  => '',
        'replyTo'       => '',
        'subject'       => '',
        'body'          => '',
        'template'      => '',
        'type'          => '',
        'username'      => '',
        'password'      => '',
        'siteUrl'       => SGL_BASE_URL,
        'siteName'      => '',
        'crlf'          => '',
        'filepath'      => '',
        'mimetype'      => '',
        'Cc'            => '',
        'Bcc'           => '',
        'sendDelay'     => '',
        'groupID'       => '',
   );

    function SGL_Emailer($options = array())
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();

        $siteName = $this->conf['site']['name'];
        $this->headerTemplate
            = "<html><head><title>$siteName</title></head><body>";
        $this->footerTemplate
            = "<table><tr><td>&nbsp;</td></tr></table></body></html>";
        foreach ($options as $k => $v) {
            $this->options[$k] = $v;
        }
        $this->options['siteName'] = $siteName;
        $this->options['crlf'] = SGL_String::getCrlf();
    }

    function prepare()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $includePath = $this->options['template'];
        $template = array_pop(explode('/', $includePath));
        if (!is_readable($includePath)) {

            // try fallback with default template dir
            $req = &SGL_Request::singleton();
            $moduleName = $req->get('moduleName');
            $includePath = SGL_MOD_DIR . '/' . $moduleName . '/templates/'. $template;
        }
        $ok = include $includePath;

        if (!$ok) {
            SGL::raiseError('Email template does not exist: "'.$includePath.'"', SGL_ERROR_NOFILE);
            return false;
        }
        $this->html = $this->headerTemplate . $body . $this->footerTemplate;
        if (!empty($this->options['fromRealName'])) {
            $this->headers['From'] = $this->options['fromRealName'] . ' <' . $this->options['fromEmail'] . '>';
        } else {
            $this->headers['From'] = $this->options['fromEmail'];
        }
        $this->headers['Subject'] = $this->options['subject'];
        $this->headers['Return-Path'] = $this->options['fromEmail'];
        $this->headers['To'] = $this->options['toEmail'];
        $this->headers['Reply-To'] = $this->options['replyTo'];
        $this->headers['Date'] = date('r');
        return true;
    }

    function send()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $mime = & new Mail_mime($this->options['crlf']);
        $mime->setHTMLBody($this->html);
        if (!empty($this->options['filepath'])) {
            if (!is_array($this->options['filepath'])) {
                //single attachment
                $mime->addAttachment($this->options['filepath'],$this->options['mimetype']);
            } else {
                //multiple attachments
                $count_f = count($this->options['filepath']);
                $count_m = count($this->options['mimetype']);
                //$mimetype ='';
                for ($i = 0; $i < $count_f; $i++) {
                    //multiple mime-types??
                    if ($count_m == $count_f) {
                        $mimetype =  $this->options['mimetype'][$i];
                    } else {
                        if (is_string($this->options['mimetype'])) {
                            $mimetype = $this->options['mimetype'];
                        } else {
                            $mimetype = $this->options['mimetype'][0];
                        }
                    }
                    $mime->addAttachment($this->options['filepath'][$i], $mimetype);
                }
            }
        }
        // Add Cc-address
        if (!empty($this->options['Cc'])) {
            $mime->addCc($this->options['Cc']);
        }
        // Add Bcc-address
        if (!empty($this->options['Bcc'])) {
            $mime->addBcc($this->options['Bcc']);
        }
        $body = $mime->get(array(
            'head_encoding' => 'base64',
            'html_encoding' => '7bit',
            'html_charset' => $GLOBALS['_SGL']['CHARSET'],
            'text_charset' => $GLOBALS['_SGL']['CHARSET'],
            'head_charset' => $GLOBALS['_SGL']['CHARSET'],
        ));
        $headers = $mime->headers($this->headers);
        $headers = $this->cleanMailInjection($headers);

        // if queue is enabled put email to queue
        if (SGL_Config::get('emailQueue.enabled')) {
            static $queue;
            if (!isset($queue)) { // init queue
                require_once SGL_CORE_DIR .  '/Emailer/Queue.php';
                $conf = SGL_Config::get('emailQueue')
                    ? SGL_Config::get('emailQueue')
                    : array();
                if ($this->options['sendDelay']) {
                    $conf['delay'] = $this->options['sendDelay'];
                }
                $queue = new SGL_Emailer_Queue($conf);
            }
            // put email to queue
            $ok = $queue->push($headers, $this->options['toEmail'], $body, $headers['Subject'],
                      $this->options['groupID'] );
        // else send email straight away
        } else {
            $mail = &SGL_Emailer::factory();
            $ok = $mail->send($this->options['toEmail'], $headers, $body);
        }
        return $ok;
    }

    // PEAR Mail::factory wrapper
    function &factory()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $backend = '';
        $aParams = array();

        // setup Mail::factory backend & params using site config
        switch ($this->conf['mta']['backend']) {

        case '':
        case 'mail':
            $backend = 'mail';
            break;

        case 'sendmail':
            $backend = 'sendmail';
            $aParams['sendmail_path'] = $this->conf['mta']['sendmailPath'];
            $aParams['sendmail_args'] = $this->conf['mta']['sendmailArgs'];
            break;

        case 'smtp':
            $backend = 'smtp';
            if (isset($this->conf['mta']['smtpLocalHost'])) {
                $aParams['localhost'] = $this->conf['mta']['smtpLocalHost'];
            }

            $aParams['host'] = (isset($this->conf['mta']['smtpHost']))
                ? $this->conf['mta']['smtpHost']
                : '127.0.0.1';
            $aParams['port'] = (isset($this->conf['mta']['smtpPort']))
                ? $this->conf['mta']['smtpPort']
                : 25;
            if ($this->conf['mta']['smtpAuth']) {
                $aParams['auth']     = $this->conf['mta']['smtpAuth'];
                $aParams['username'] = $this->conf['mta']['smtpUsername'];
                $aParams['password'] = $this->conf['mta']['smtpPassword'];
            } else {
                $aParams['auth'] = false;
            }
            break;

        default:
            SGL::raiseError('Unrecognised PEAR::Mail backend', SGL_ERROR_EMAILFAILURE);
        }
        return Mail::factory($backend, $aParams);
    }
   /**
    * Takes a string or an associative array of mail headers with each
    * key representing a header's name and a value representing
    * a header's value. The function removes every additional
    * header from each value to prevent mail injection attacks.
    *
    * @author Andreas Ahlenstorf, Werner M. Krauss <werner.krauss@hallstatt.net>
    *
    * @param mixed $headers
    * @return string or array
    */
    function cleanMailInjection($headers)
    {
         $regex = "#((<CR>|<LF>|0x0A/%0A|0x0D/%0D|\\n|\\r)\S).*#i";
        // strip all possible "additional" headers from the values
        if (is_array($headers)) {
            foreach ($headers as $key => $value) {
               $headers[$key] = preg_replace($regex, null, $value);
            }
        } else {
            $headers = preg_replace($regex, null, $headers);
        }
        return $headers;
    }

}
?>
