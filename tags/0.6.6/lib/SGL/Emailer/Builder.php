<?php

require_once 'Mail/mime.php';

/**
 * Email builder.
 *
 * @package SGL
 * @subpackage Emailer
 * @author Julien Casanova
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Emailer_Builder
{
    /**
     * Available modes.
     */
    const MODE_TEXT_ONLY     = 1;
    const MODE_HTML_ONLY     = 2;
    const MODE_HTML_AND_TEXT = 3;

    /**
     * Headers suitable for PEAR::Mail.
     *
     * @var array
     */
    public $aHeaders = array();

    /**
     * Mail body suitable for PEAR::Mail.
     *
     * @var array
     */
    public $body = '';

    /**
     * Creates proper headers and body to send with PEAR::Mail.
     *
     * @todo replace PHP's Exception with SGL_Exception
     * @todo remove outgoing email hack
     *
     * @param array $aDeliveryOpts
     * @param array $aTplOpts
     *
     * @throws Exception
     */
    public function __construct($aDeliveryOpts, $aTplOpts)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aObligDeliveryOpts = array(
            'toEmail', 'toRealName', 'fromEmail', 'fromRealName', 'subject'
        );
        // check delivery options
        foreach ($aObligDeliveryOpts as $optName) {
        	if (!isset($aDeliveryOpts[$optName])) {
        	    $msg = __CLASS__ . ': missing delivery options';
        	    throw new Exception($msg);
        	}
        }
        // check tpl options
        if ((empty($aTplOpts['htmlTemplate']) || empty($aTplOpts['moduleName']))
             && (empty($aTplOpts['textTemplate']) && empty($aTplOpts['rawHtml']) && empty($aTplOpts['rawText']))) {
            $msg = __CLASS__ . ': template options is not specified';
            throw new Exception($msg);
        }

        $aDefaultTplOpts = array(
            'mode'     => self::MODE_HTML_AND_TEXT,
            'siteUrl'  => SGL_BASE_URL,
            'siteName' => SGL_Config::get('site.name'),
            'crlf'     => SGL_String::getCrlf()
        );
        $aTplOpts = array_merge($aDefaultTplOpts, $aTplOpts);
        // check attachments
        $aAttachments = array();
        if (isset($aDeliveryOpts['attachment'])) {
            $aAttachments = is_array($aDeliveryOpts['attachment'])
                ? $aDeliveryOpts['attachment']
                : array($aDeliveryOpts['attachment']);
            unset($aDeliveryOpts['attachment']);
        }

        // sending emails through anotherdomain.com hack
        /*
        if (   strstr($this->options['toEmail'], '@hotmail.')
            || strstr($this->options['toEmail'], '@msn.'))
        {
            $this->options['fromEmail'] = 'outgoing@anotherdomain.com';
        }
        */

        $aHeaders  = self::prepareHeaders($aDeliveryOpts);
        $bodyTxt   = !empty($aTplOpts['textTemplate'])
            ? self::prepareTxtBody($aTplOpts) : '';
        $bodyHtml  = !empty($aTplOpts['htmlTemplate'])
            ? self::prepareHtmlBody($aTplOpts) : '';
        // throw Exception if error occurs
        if (PEAR::isError($bodyHtml)) {
            throw new Exception(__CLASS__ . ': ' . $bodyHtml->getMessage());
        }

        //use 'raw' bodies
        if (empty($bodyTxt)) {
            $bodyTxt  = !empty($aTplOpts['rawText'])
                     ? $aTplOpts['rawText'] : '';
        }
        if (empty($bodyHtml)) {
            $bodyHtml = !empty($aTplOpts['rawHtml'])
                     ? $aTplOpts['rawHtml'] : '';
        }

        // get plain text version of email
        if (empty($bodyTxt) && $aTplOpts['mode'] == self::MODE_HTML_AND_TEXT) {
            $ok = @include_once 'Horde/Text/Filter.php';
            if ($ok) {
                $oFilter = new Text_Filter();
                $bodyTxt = $oFilter->filter(
                    $bodyHtml,
                    array('html2text'),
                    array(array('charset' => SGL::getCurrentCharset()))
                );
            }
        }

        $aOpts     = array(
            'crlf' => $aTplOpts['crlf'],
            'mode' => $aTplOpts['mode']
        );
        $aMimeData = self::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml, $aAttachments);
        // throw Exception if error occurs
        if (PEAR::isError($aMimeData)) {
            throw new Exception(__CLASS__ . ': ' . $aMimeData->getMessage());
        }

        $this->aHeaders = $aMimeData['headers'];
        $this->body     = $aMimeData['body'];
    }

    /**
     * Returns proper mail headers from SGL options.
     *
     * @todo it would be wise to standardize SGL options,
     *        to make possible array iteration
     *
     * @param array $aOpts
     *
     * @return array
     */
    public static function prepareHeaders($aOpts)
    {
        $aHeaders = array();
        if (!empty($aOpts['toEmail'])) {
            $aHeaders['To'] = !empty($aOpts['toRealName'])
                ? SGL_Emailer2::formatAddress($aOpts['toEmail'], $aOpts['toRealName'])
                : $aOpts['toEmail'];
        }
        if (!empty($aOpts['fromEmail'])) {
            $aHeaders['Return-Path'] = $aOpts['fromEmail'];
            $aHeaders['From']        = !empty($aOpts['fromRealName'])
                ? SGL_Emailer2::formatAddress($aOpts['fromEmail'], $aOpts['fromRealName'])
                : $aOpts['fromEmail'];
        }
        if (!empty($aOpts['Return-Path'])) {
            $aHeaders['Return-Path'] = $aOpts['Return-Path'];
        }
        if (!empty($aOpts['subject'])) {
            $aHeaders['Subject'] = $aOpts['subject'];
        }
        if (!empty($aOpts['cc'])) {
            $aHeaders['Cc'] = $aOpts['cc'];
        }
        if (!empty($aOpts['bcc'])) {
            $aHeaders['Bcc'] = $aOpts['bcc'];
        }
        if (!empty($aOpts['replyTo'])) {
            $aHeaders['Reply-To'] = $aOpts['replyTo'];
        }
        return $aHeaders;
    }

    /**
     * Loads text template.
     *
     * @todo replace PHP's Exception with SGL_Exception
     *
     * @param array $aOpts
     *
     * @return string
     *
     * @throws Exception
     */
    public static function prepareTxtBody($aOpts)
    {
        $templatePath = $aOpts['textTemplate'];
        if (!file_exists($templatePath)) {
            // try to resolve template from project's root dir
            $templatePath = SGL_PATH . '/' . $templatePath;
            if (!file_exists($templatePath)) {
                // try to resolve template from default module's template dir
                $defaultModule = SGL_Config::get('site.defaultModule');
                $templateName  = array_pop(explode(DIRECTORY_SEPARATOR, $templatePath));
                $templatePath  = SGL_MOD_DIR . "/$defaultModule/templates/$templateName";
                if (!file_exists($templatePath)) {
                    $msg = __CLASS__ . ': can\'t find text template ' . $templatePath;
                    throw new Exception($msg);
                }
            }
        }
        if (!is_readable($templatePath)) {
            $msg = __CLASS__ . ': text template is not readable ' . $templatePath;
            throw new Exception($msg);
        }
        unset($aOpts['textTemplate']);

        // load template
        $aParams = $aOpts;
        include $templatePath;

        // return body
        return isset($bodyTxt) ? strip_tags($bodyTxt) : '';
    }

    /**
     * Loads HTML template.
     *
     * @param array $aOpts
     * @param boolean $skipOutputVars
     *
     * @return string
     */
    public static function prepareHtmlBody($aOpts, $skipOutputVars = false)
    {
        $outputClass = SGL_Config::get('site.customOutputClassName')
            ? SGL_Config::get('site.customOutputClassName')
            : 'SGL_Output';
        $oOutput = new $outputClass();
        if (!$skipOutputVars) {
            SGL_Task_BuildOutputData::addOutputData($oOutput);
        }

        // setup template/module for HtmlSimpleView
        $oOutput->masterTemplate = $aOpts['htmlTemplate'];
        $oOutput->moduleName     = $aOpts['moduleName'];

        unset($aOpts['moduleName']);
        unset($aOpts['htmlTemplate']);
        foreach ($aOpts as $k => $v) {
        	$oOutput->$k = $v;
        }

        $view = new SGL_HtmlSimpleView($oOutput);
        return PEAR::isError($view) ? $view : $view->render();
    }

    /**
     * Returns headers and body suitable for PEAR::Mail.
     *
     * @param array $aOpts
     * @param array $aHeaders
     * @param string $bodyTxt
     * @param string $bodyHtml
     * @param array $aAttachments
     *
     * @return array
     */
    public static function getMimeData($aOpts, $aHeaders,
        $bodyTxt = '', $bodyHtml = '', $aAttachments = array())
    {
        $oMime = new Mail_mime($aOpts['crlf']);
        $aMods = array(self::MODE_TEXT_ONLY, self::MODE_HTML_AND_TEXT);
        if (!empty($bodyTxt) && in_array($aOpts['mode'], $aMods)) {
            $oMime->setTXTBody($bodyTxt);
        }
        $aMods = array(self::MODE_HTML_ONLY, self::MODE_HTML_AND_TEXT);
        if (!empty($bodyHtml) && in_array($aOpts['mode'], $aMods)) {
            $oMime->setHTMLBody($bodyHtml);
        }
        // add attachments
        foreach ($aAttachments as $key => $filePath) {
            // in case hash is supplied, keys are filenames,
            // but values are mime-types
            if (is_string($key)) {
                $mime     = $filePath;
                $filePath = $key;
            }
            if (is_readable($filePath)) {
                if (!isset($mime)) {
                    if (extension_loaded('fileinfo')) {
                        $fp   = finfo_open(FILEINFO_MIME);
                        $mime = finfo_file($fp, $filePath);
                        finfo_close($fp);
                    } elseif (function_exists('mime_content_type')) {
                        $mime = mime_content_type($filePath);
                    } else {
                        continue;
                    }
                }
                $oMime->addAttachment($filePath, $mime);
            }
            unset($mime);
        }
        // get data
        $retBody = $oMime->get(array(
            'head_encoding' => 'base64',
            'text_encoding' => '8bit',
            'html_encoding' => '8bit',
            'html_charset'  => SGL::getCurrentCharset(),
            'text_charset'  => SGL::getCurrentCharset(),
            'head_charset'  => SGL::getCurrentCharset(),
        ));
        $aRetHeaders = $oMime->headers($aHeaders);
        // reinsert Subject to avoid PEAR::Mail failure
        if (isset($aHeaders['Subject'])) {
            $aRetHeaders['Subject'] = $aHeaders['Subject'];
        }
        $aRetHeaders = SGL_Emailer2::cleanMailInjection($aRetHeaders);

        // return results
        return array(
            'headers' => $aRetHeaders,
            'body'    => $retBody
        );
    }
}

?>