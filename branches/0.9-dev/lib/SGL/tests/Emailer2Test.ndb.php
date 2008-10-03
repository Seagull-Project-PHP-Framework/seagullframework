<?php

require_once SGL_CORE_DIR . '/Emailer2.php';

/**
 * Test suite.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class Emailer2Test extends UnitTestCase
{
    public function __construct()
    {
        $this->UnitTestCase('Emailer2 Test');
    }

    public function testFactory()
    {
        $c = SGL_Config::singleton();

        // test mail backend
        $c->set('mta.backend', 'mail');
        $oMail = SGL_Emailer2::factory();
        $this->assertIsA($oMail, 'Mail_mail');

        // test sendmail backend
        $c->set('mta.backend', 'sendmail');
        $c->set('mta.sendmailPath', '/path/to/sendmail');
        $c->set('mta.sendmailArgs', '-agr1 -arg2');
        $oMail = SGL_Emailer2::factory();
        $this->assertIsA($oMail, 'Mail_sendmail');

        // test sendmail params
        $this->assertEqual($oMail->sendmail_path, '/path/to/sendmail');
        $this->assertEqual($oMail->sendmail_args, '-agr1 -arg2');

        // test SMTP
        $c->set('mta.backend', 'smtp');
        $c->set('mta.smtpLocalHost', 'myhost');
        $c->set('mta.smtpHost', 'smtphost');
        $c->set('mta.smtpPort', 'smtpport');
        $c->set('mta.smtpAuth', 'myauth');
        $c->set('mta.smtpUsername', 'dima');
        $c->set('mta.smtpPassword', 'lak');
        $oMail = SGL_Emailer2::factory();
        $this->assertIsA($oMail, 'Mail_smtp');

        // test SMTP params
        $this->assertEqual($oMail->localhost, 'myhost');
        $this->assertEqual($oMail->host, 'smtphost');
        $this->assertEqual($oMail->port, 'smtpport');
        $this->assertEqual($oMail->auth, 'myauth');
        $this->assertEqual($oMail->username, 'dima');
        $this->assertEqual($oMail->password, 'lak');

        // test default (fallback)
        $c->set('mta.backend', 'unknown');
        $oMail = SGL_Emailer2::factory();
        $this->assertIsA($oMail, 'Mail_mail');
    }

    public function testQueueSingleton()
    {
        $oQueue1 = SGL_Emailer2::queueSingleton();
        $oQueue2 = SGL_Emailer2::queueSingleton();

        $this->assertIsA($oQueue1, 'SGL_Emailer_Queue');
        $this->assertReference($oQueue1, $oQueue2);
    }

    public function testFormatAddress()
    {
        // utf-8 encoded file
        $namesFile = dirname(__FILE__) . '/emailer2.ini';
        // en, fr and ru names
        $aNames = parse_ini_file($namesFile, true);

        $addressEn = SGL_Emailer2::formatAddress($aNames['en']['email'],
            $aNames['en']['name']);
        $addressFr = SGL_Emailer2::formatAddress($aNames['fr']['email'],
            $aNames['fr']['name']);
        $addressRu = SGL_Emailer2::formatAddress($aNames['ru']['email'],
            $aNames['ru']['name']);

        // test English name
        $this->assertEqual($addressEn,
            "{$aNames['en']['name']} <{$aNames['en']['email']}>");

        // test French name
        $this->assertNotEqual($addressFr,
            "{$aNames['fr']['name']} <{$aNames['fr']['email']}>");
        $this->assertEqual($addressFr,
            'Sebastien Casanova <sebastien.casanova@gmail.fr>');

        // test Russian name
        $this->assertNotEqual($addressRu,
            "{$aNames['ru']['name']} <{$aNames['ru']['email']}>");
        $this->assertEqual($addressRu, $aNames['ru']['email']);
    }

    public function testCleanMailInjection()
    {
        // test headers
        $aHeaders = array(
            "From: lakiboy83@gmail.com",
            "From: lakiboy83@gmail.com\nHeader1: value1",
            "From: lakiboy83@gmail.com\nHeader1: value1\r\nHeader2: value2"
        );
        foreach ($aHeaders as $header) {
            $ret = SGL_Emailer2::cleanMailInjection($header);
            $this->assertEqual('From: lakiboy83@gmail.com', $ret);
        }

        // test array of headers
        $aHeaders = array(
            array(
                'From' => "lakiboy83@gmail.com",
                'To'   => "lakiboy83@gmail.com",
                'Cc'   => "lakiboy83@gmail.com",
            ),
            array(
                'From' => "lakiboy83@gmail.com\nHeader1: value1",
                'To'   => "lakiboy83@gmail.com\nHeader1: value1",
                'Cc'   => "lakiboy83@gmail.com\nHeader1: value1",
            ),
            array(
                'From' => "lakiboy83@gmail.com\nHeader1: value1\r\nHeader2: value2",
                'To'   => "lakiboy83@gmail.com\nHeader1: value1\r\nHeader2: value2",
                'Cc'   => "lakiboy83@gmail.com\nHeader1: value1\r\nHeader2: value2",
            )
        );
        foreach ($aHeaders as $aHdrs) {
            $aRet = SGL_Emailer2::cleanMailInjection($aHdrs);
            $this->assertEqual(
                array(
                    'From' => 'lakiboy83@gmail.com',
                    'To'   => 'lakiboy83@gmail.com',
                    'Cc'   => 'lakiboy83@gmail.com',
                ),
                $aRet
            );
        }
    }
}
?>