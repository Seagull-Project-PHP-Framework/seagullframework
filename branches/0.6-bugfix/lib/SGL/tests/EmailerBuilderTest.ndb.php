<?php

require_once SGL_CORE_DIR . '/Emailer2.php';
require_once SGL_CORE_DIR . '/Emailer/Builder.php';

/**
 * Test suite.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class EmailerBuilderTest extends UnitTestCase
{
    public function __construct()
    {
        $this->UnitTestCase('EmailerBuilder Test');
    }

    public function testPrepareHeaders()
    {
        // SGL delivery options
        $aOpts = array(
            'toEmail'      => 'lakiboy83@gmail.com',
            'toRealName'   => 'Dmitri Lakachaukis',
            'fromEmail'    => 'demian@phpkitchen.com',
            'fromRealName' => 'Demian Turner',
            'subject'      => 'Testing Emailer',
            'cc'           => 'julien.casanova@gmail.fr',
            'bcc'          => 'andrei.podshivalov@minsk.be',
            'replyTo'      => 'reply@phpkitchen.com'
        );
        // transforms to proper mail headers
        $aHeaders = array(
            'To'          => SGL_Emailer2::formatAddress($aOpts['toEmail'],
                $aOpts['toRealName']),
            'Return-Path' => $aOpts['fromEmail'],
            'From'        => SGL_Emailer2::formatAddress($aOpts['fromEmail'],
                $aOpts['fromRealName']),
            'Subject'     => $aOpts['subject'],
            'Cc'          => $aOpts['cc'],
            'Bcc'         => $aOpts['bcc'],
            'Reply-To'    => $aOpts['replyTo']
        );
        $aRet = SGL_Emailer_Builder::prepareHeaders($aOpts);
        $this->assertEqual($aRet, $aHeaders);
    }

    public function testPrepareTxtBody()
    {
        // test, when no tpl set
        $aOpts   = array('textTemplate' => 'missing template');
        $error   = null;
        $bodyTxt = null;
        try {
            $bodyTxt = SGL_Emailer_Builder::prepareTxtBody($aOpts);
        } catch (Exception $error) {}
        $this->assertIsA($error, 'Exception');
        $this->assertNull($bodyTxt);

        // test when template is found
        $aOpts   = array('textTemplate' => dirname(__FILE__) . '/emailer2.txt.tpl');
        $error   = null;
        $bodyTxt = null;
        try {
            $bodyTxt = SGL_Emailer_Builder::prepareTxtBody($aOpts);
        } catch (Exception $error) {}
        $this->assertNull($error);
        $this->assertTrue(is_string($bodyTxt));

        // test tpl body
        $expected = <<< TXT
Hello !

How's things?

--

TXT;
        $this->assertEqual($expected, $bodyTxt);

        // test tpl body with vars
        $aOpts['recipientName'] = 'Dmitri';
        $aOpts['senderName']    = 'Demian';
        try {
            $bodyTxt = SGL_Emailer_Builder::prepareTxtBody($aOpts);
        } catch (Exception $error) {}
        $expected = <<< TXT
Hello Dmitri!

How's things?

--
Demian
TXT;
        $this->assertEqual($expected, $bodyTxt);
    }

    public function testPrepareHtmlBody()
    {
        SGL_Config::set('site.customOutputClassName', '');

        // Unfortunately we can't test code with wrong template,
        // because Flexy issues die command on error.
//        $aOpts    = array('masterTemplate' => 'missing template');
//        $bodyHtml = SGL_Emailer_Builder::prepareHtmlBody($aOpts);

        // test when template is found
        $aOpts = array(
            'htmlTemplate' => 'test_emailer2.html.tpl',
            'moduleName'   => 'emailqueue'
        );
        $bodyHtml = SGL_Emailer_Builder::prepareHtmlBody($aOpts,
            $skipOutputVars = true);
        $this->assertNotA($bodyHtml, 'PEAR_Error');
        $this->assertTrue(is_string($bodyHtml));

        // test tpl body
        $expected = <<< HTML
<p>
    Hello !
</p>
<p>
    How's things?
</p>
<p>
    --<br />
    </p>
HTML;
        $this->assertEqual($expected, $bodyHtml);

        // test tpl body with vars
        $aOpts['recipientName'] = 'Dmitri';
        $aOpts['senderName']    = 'Demian';
        $bodyHtml = SGL_Emailer_Builder::prepareHtmlBody($aOpts,
            $skipOutputVars = true);

        // test tpl body
        $expected = <<< HTML
<p>
    Hello Dmitri!
</p>
<p>
    How's things?
</p>
<p>
    --<br />
    Demian</p>
HTML;
        $this->assertEqual($expected, $bodyHtml);
    }

    public function testGetMimeData()
    {
        $aOpts = array(
            'crlf' => "\n",
            'mode' => SGL_Emailer_Builder::MODE_HTML_AND_TEXT
        );

        // no headers, no body text
        $aHeaders = array();
        $bodyTxt  = '';
        $bodyHtml = '';
        // test result
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // result is array
        $this->assertTrue(is_array($aRet));
        // where first element is an array of headers
        // and sencond one is body string
        $this->assertEqual(array_keys($aRet), array('headers', 'body'));
        // by default there is always one header returned
        $this->assertEqual(count($aRet['headers']), 1);
        // which is MIME-Version
        $this->assertEqual(array_keys($aRet['headers']), array('MIME-Version'));
        // body is empty
        $this->assertTrue(empty($aRet['body']));

        // add headers
        $aHeaders['To']          = 'Dmitri Lakachauskis';
        $aHeaders['Return-Path'] = 'lakiboy83@gmail.com';
        $aHeaders['From']        = 'lakiboy83@gmail.com';
        $aHeaders['Subject']     = 'Testing Emailer';
        $aHeaders['Cc']          = 'julien.casanova@gmail.fr';
        $aHeaders['Bcc']         = 'andrei.podshivalov@minsk.be';
        $aHeaders['Reply-To']    = 'reply@phpkitchen.com';

        // test result
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // check headers count
        $this->assertEqual(count($aHeaders) + 1, count($aRet['headers']));
        // same headers with default one are returned
        $this->assertEqual(array_keys($aRet['headers']), array_merge(
            array('MIME-Version'),
            array_keys($aHeaders)
        ));
        // because we supplied clean headers result is the same
        $this->assertEqual($aRet['headers'], array_merge(
            array('MIME-Version' => '1.0'),
            $aHeaders
        ));
        // look in Emailer2Test::testCleanMailInjection()
        // for headers cleaning tests

        // add txt body
        $bodyTxt = 'text body';
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // same body is returned
        $this->assertEqual($bodyTxt, $aRet['body']);

        // add HTML body
        $bodyTxt  = '';
        $bodyHtml = '<p>HTML body</p>';
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // same body is returned
        $this->assertEqual($bodyHtml, $aRet['body']);

        // add both txt and HTML bodies
        $bodyTxt  = 'text body';
        $bodyHtml = '<p>HTML body</p>';
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // both bodies exist
        $this->assertTrue(strpos($aRet['body'], $bodyTxt));
        $this->assertTrue(strpos($aRet['body'], $bodyHtml));

        // get boundary
        $ok = preg_match('/boundary="(.*?)"/', $aRet['headers']['Content-Type'],
            $aMatches);
        $boundary = $aMatches[1];
        // multipart headers
        $multipartTxtHeaders  = "Content-Transfer-Encoding: 8bit"
            . $aOpts['crlf']
            . "Content-Type: text/plain; charset=\"utf-8\"";
        $multipartHtmlHeaders = "Content-Transfer-Encoding: 8bit"
            . $aOpts['crlf']
            . "Content-Type: text/html; charset=\"utf-8\"";
        // expected multipart body
        $expected = '--' . $boundary . $aOpts['crlf']
            . $multipartTxtHeaders . $aOpts['crlf'] . $aOpts['crlf']
            . $bodyTxt . $aOpts['crlf']
            . '--' . $boundary . $aOpts['crlf']
            . $multipartHtmlHeaders . $aOpts['crlf'] . $aOpts['crlf']
            . $bodyHtml . $aOpts['crlf']
            . '--' . $boundary . "--" . $aOpts['crlf'];
        // test result
        $this->assertEqual($aRet['body'], $expected);

        // switch to text only mode
        $aOpts['mode'] = SGL_Emailer_Builder::MODE_TEXT_ONLY;
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // text body is returned
        $this->assertEqual($bodyTxt, $aRet['body']);

        // switch to HTML only mode
        $aOpts['mode'] = SGL_Emailer_Builder::MODE_HTML_ONLY;
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml);
        // HTML body is returned
        $this->assertEqual($bodyHtml, $aRet['body']);

        // test attachments
        $aHeaders     = array();
        $bodyTxt      = '';
        $bodyHtml     = '';
        $aAttachments = array(
            __FILE__                                => 'text/plain',
            dirname(__FILE__) . '/emailer2.ini'     => 'text/plain',
            dirname(__FILE__) . '/emailer2.txt.tpl' => 'text/plain'
        );
        $aRet = SGL_Emailer_Builder::getMimeData($aOpts, $aHeaders,
            $bodyTxt, $bodyHtml, $aAttachments);

        $expectedBodyPart = 'Content-Disposition: attachment;'
            . $aOpts['crlf'] . ' filename="' . basename(__FILE__) .'"';
        $this->assertTrue(strpos($aRet['body'], $expectedBodyPart));
    }
}

?>