<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Seagull 0.4                                                               |
// +---------------------------------------------------------------------------+
// | Emailer.php                                                               |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005 Demian Turner                                          |
// |                                                                           |
// | Author: Demian Turner <demian@phpkitchen.com>                             |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This library is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU Library General Public               |
// | License as published by the Free Software Foundation; either              |
// | version 2 of the License, or (at your option) any later version.          |
// |                                                                           |
// | This library is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         |
// | Library General Public License for more details.                          |
// |                                                                           |
// | You should have received a copy of the GNU Library General Public         |
// | License along with this library; if not, write to the Free                |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
// |                                                                           |
// +---------------------------------------------------------------------------+
// $Id: Emailer.php,v 1.11 2005/06/13 21:44:20 demian Exp $

require_once 'Mail.php';
require_once 'Mail/mime.php';

/**
 * Wrapper class for PEAR::Mail.
 *
 * @package SGL
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.11 $
 * @since   PHP 4.1
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
        'crlf'          => ''
    );

    function SGL_Emailer($options = array())
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $siteName = $conf['site']['name'];
        $this->headerTemplate
            = "<html><head><title>$siteName</title></head></html><body>";
        $this->footerTemplate
            = "<table><tr><td>&nbsp;</td></tr></table></body>";
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
        if (is_readable($includePath)) {
            include $includePath; // populates $body
        } else {
            SGL::raiseError('Email template does not exist: "'.$includePath.'"', SGL_ERROR_NOFILE);
            return false;
        }
        $this->html = $this->headerTemplate . $body . $this->footerTemplate;
        $this->headers['From'] = $this->options['fromEmail'];
        $this->headers['Subject'] = $this->options['subject'];
        return true;
    }

    function send()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $mime = & new Mail_mime($this->options['crlf']);
        $mime->setHTMLBody($this->html);
        $body = $mime->get(array(
            'html_encoding' => '7bit',
            'html_charset' => $GLOBALS['_SGL']['CHARSET'],
            'text_charset' => $GLOBALS['_SGL']['CHARSET'],
            'head_charset' => $GLOBALS['_SGL']['CHARSET'],
        ));
        $hdrs = $mime->headers($this->headers);
        $mail = & SGL_Emailer::factory();
        return $mail->send($this->options['toEmail'], $hdrs, $body);
    }

    // PEAR Mail::factory wrapper
    function factory()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $conf = & $GLOBALS['_SGL']['CONF'];
        $backend = '';
        $aParams = array();

        // setup Mail::factory backend & params using site config
        switch ($conf['mta']['backend']) {
            
        case '':
        case 'mail':
            $backend = 'mail';
            break;
            
        case 'sendmail':
            $backend = 'sendmail';
            $aParams['sendmail_path'] = $conf['mta']['sendmailPath'];
            $aParams['sendmail_args'] = $conf['mta']['sendmailArgs'];
            break;
            
        case 'smtp':
            $backend = 'smtp';
            $aParams['host'] = (isset($conf['mta']['smtpHost']))
                ? $conf['mta']['smtpHost']
                : '127.0.0.1';
            $aParams['port'] = (isset($conf['mta']['smtpPort']))
                ? $conf['mta']['smtpPort']
                : 25;
            if ($conf['mta']['smtpAuth']) {
                $aParams['auth']     = $conf['mta']['smtpAuth'];
                $aParams['username'] = $conf['mta']['smtpUsername'];
                $aParams['password'] = $conf['mta']['smtpPassword'];
            } else {
                $aParams['auth'] = false;
            }
            break;
            
        default:
            SGL::raiseError('Unrecognised PEAR::Mail backend', SGL_ERROR_EMAILFAILURE);
        }
        return Mail::factory($backend, $aParams);
    }
}
?>