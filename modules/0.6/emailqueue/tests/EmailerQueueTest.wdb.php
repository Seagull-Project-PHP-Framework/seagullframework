<?php

require_once 'KDO/Emailer/Queue.php';

/**
 * Test suite.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class KDOEmailerQueueTest extends UnitTestCase
{
    function __construct()
    {
        $this->UnitTestCase('KDO Emailer Queue Test');
    }

    function setUp()
    {
        $this->aOptions = array(
            'container' => 'db', // the only container supported for now
            'limit'     => 2,    // max 2 emails to pop from the queue
            'delay'     => 0,    // send next time the queue will be processed
            'attempts'  => 5     // 5 attempts to send each email
        );
        //$this->queue = new KDO_Emailer_Queue($aOptions);
    }

    function tearDown()
    {
        //unset($this->queue);
    }

    function _cleanTable()
    {
        $query = "TRUNCATE table email_queue";
        $dbh = SGL_DB::singleton();
        $dbh->query($query);
    }

    function _getTableRecordsCount()
    {
        $query = "SELECT count(*) FROM email_queue";
        $dbh = SGL_DB::singleton();
        return $dbh->getOne($query);
    }

    function testPush()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $groupID   = '';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Time to attack...';
        $recipient = 'spider@man.com';
        $ok = $queue->push($headers, $recipient, $body);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Seagull 2.0...';
        $recipient = 'demian@seagullproject.com';
        $subject   = 'Is it real?';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $headers   = 'From: peter.termaten@gmail.com';
        $body      = 'Grouping...';
        $subject   = 'Robin';
        $recipient = 'bat@man.com';
        $groupID   = '123';
        $ok = $queue->push($headers, $recipient, $body, $subject, $groupID);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }

    function testPop()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // try to pop emails again
        $email_1 = $queue->pop($groupID);
        // no errors should be returned
        $this->assertFalse($email_1 instanceof PEAR_Error);
        // result is the email object
        $this->assertTrue(is_object($email_1) && isset($email_1->email_queue_id));
        $this->assertEqual($email_1->email_queue_id, 1);

        $email_2 = $queue->pop($groupID);
        // no errors should be returned
        $this->assertFalse($email_2 instanceof PEAR_Error);
        // result is the email object
        $this->assertTrue(is_object($email_2) && isset($email_2->email_queue_id));
        $this->assertEqual($email_2->email_queue_id, 2);

        $email_3 = $queue->pop($groupID);
        // only two emails were allowed for retrieval
        $this->assertFalse($email_3);
    }

    function testRemove()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);

        $ok = $queue->remove(1);
        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $ok = $queue->remove(2);
        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $ok = $queue->remove(3);
        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }

    function testPopRemove2()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // pop the only email in queue
        $email = $queue->pop($groupID);
        $this->assertEqual($email->email_queue_id, 4);

        // ensure nothing else left
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // remove it
        $ok = $queue->remove($email->email_queue_id);
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }

    function testPop3()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // ensure there no emails in the queue
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);
    }

    function testPushPopWithDelay()
    {
        $aOptions = $this->aOptions;
        $aOptions['delay'] = 60; // 1 minute

        $queue = new KDO_Emailer_Queue($aOptions);
        $groupID = '';

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        // nothing returned, need to wait for 1 minute
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // clean table
        $this->_cleanTable();
    }

    function testProcessQueue()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // add emails to queue
        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Time to attack...';
        $recipient = 'spider@man.com';
        $ok = $queue->push($headers, $recipient, $body);

        $ok = $queue->processQueue($groupID = null, $skipSend = true);
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        // nothing returned
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // no records in db, they were deleted by default
        $this->assertEqual(0, $this->_getTableRecordsCount());
    }

    function testProcessQueue2()
    {
        $aOptions = $this->aOptions;
        $aOptions['removeSent'] = false; // keep sent emails

        $queue = new KDO_Emailer_Queue($aOptions);
        $groupID = '';

        // add emails to queue
        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Time to attack...';
        $recipient = 'spider@man.com';
        $ok = $queue->push($headers, $recipient, $body);

        $ok = $queue->processQueue($groupID = null, $skipSend = true);
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        // nothing returned
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // 2 emails still i db
        $this->assertEqual(2, $this->_getTableRecordsCount());

        // clean table
        $this->_cleanTable();
    }

    /**
     * This test should send emails to file instead of sending real emails.
     */
    function xtestSendEmail()
    {
        // this recipient will receive 2 test emails on success
        // should be removed as per method's description
        $destinationTestEmail = 'lakiboy83@gmail.com';

        $aOptions = $this->aOptions;
        $aOptions['removeSent'] = false; // keep sent emails

        $queue = new KDO_Emailer_Queue($aOptions);

        // add emails to queue
        $subject   = 'Test subject 1';
        $headers   = array(
            'From'    => 'beta@dev-machine.com',
            'Subject' => $subject
        );
        $body      = 'Test body 1';
        $recipient = $destinationTestEmail;
        $ok = $queue->push($headers, $recipient, $body, $subject);

        $subject   = 'Test subject 2';
        $headers   = array(
            'From'    => 'beta@dev-machine.com',
            'Subject' => $subject
        );
        $body      = 'Test body 2';
        $recipient = $destinationTestEmail;
        $ok = $queue->push($headers, $recipient, $body);

        $ok = $queue->processQueue();
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }
}

?>
<?php

require_once 'KDO/Emailer/Queue.php';

/**
 * Test suite.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class KDOEmailerQueueTest extends UnitTestCase
{
    function __construct()
    {
        $this->UnitTestCase('KDO Emailer Queue Test');
    }

    function setUp()
    {
        $this->aOptions = array(
            'container' => 'db', // the only container supported for now
            'limit'     => 2,    // max 2 emails to pop from the queue
            'delay'     => 0,    // send next time the queue will be processed
            'attempts'  => 5     // 5 attempts to send each email
        );
        //$this->queue = new KDO_Emailer_Queue($aOptions);
    }

    function tearDown()
    {
        //unset($this->queue);
    }

    function _cleanTable()
    {
        $query = "TRUNCATE table email_queue";
        $dbh = SGL_DB::singleton();
        $dbh->query($query);
    }

    function _getTableRecordsCount()
    {
        $query = "SELECT count(*) FROM email_queue";
        $dbh = SGL_DB::singleton();
        return $dbh->getOne($query);
    }

    function testPush()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $groupID   = '';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Time to attack...';
        $recipient = 'spider@man.com';
        $ok = $queue->push($headers, $recipient, $body);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Seagull 2.0...';
        $recipient = 'demian@seagullproject.com';
        $subject   = 'Is it real?';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $headers   = 'From: peter.termaten@gmail.com';
        $body      = 'Grouping...';
        $subject   = 'Robin';
        $recipient = 'bat@man.com';
        $groupID   = '123';
        $ok = $queue->push($headers, $recipient, $body, $subject, $groupID);

        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }

    function testPop()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // try to pop emails again
        $email_1 = $queue->pop($groupID);
        // no errors should be returned
        $this->assertFalse($email_1 instanceof PEAR_Error);
        // result is the email object
        $this->assertTrue(is_object($email_1) && isset($email_1->email_queue_id));
        $this->assertEqual($email_1->email_queue_id, 1);

        $email_2 = $queue->pop($groupID);
        // no errors should be returned
        $this->assertFalse($email_2 instanceof PEAR_Error);
        // result is the email object
        $this->assertTrue(is_object($email_2) && isset($email_2->email_queue_id));
        $this->assertEqual($email_2->email_queue_id, 2);

        $email_3 = $queue->pop($groupID);
        // only two emails were allowed for retrieval
        $this->assertFalse($email_3);
    }

    function testRemove()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);

        $ok = $queue->remove(1);
        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $ok = $queue->remove(2);
        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        $ok = $queue->remove(3);
        // no errors should be returned
        // and result is DB_OK
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }

    function testPopRemove2()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // pop the only email in queue
        $email = $queue->pop($groupID);
        $this->assertEqual($email->email_queue_id, 4);

        // ensure nothing else left
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // remove it
        $ok = $queue->remove($email->email_queue_id);
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }

    function testPop3()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // ensure there no emails in the queue
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);
    }

    function testPushPopWithDelay()
    {
        $aOptions = $this->aOptions;
        $aOptions['delay'] = 60; // 1 minute

        $queue = new KDO_Emailer_Queue($aOptions);
        $groupID = '';

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        // nothing returned, need to wait for 1 minute
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // clean table
        $this->_cleanTable();
    }

    function testProcessQueue()
    {
        $queue = new KDO_Emailer_Queue($this->aOptions);
        $groupID = '';

        // add emails to queue
        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Time to attack...';
        $recipient = 'spider@man.com';
        $ok = $queue->push($headers, $recipient, $body);

        $ok = $queue->processQueue($groupID = null, $skipSend = true);
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        // nothing returned
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // no records in db, they were deleted by default
        $this->assertEqual(0, $this->_getTableRecordsCount());
    }

    function testProcessQueue2()
    {
        $aOptions = $this->aOptions;
        $aOptions['removeSent'] = false; // keep sent emails

        $queue = new KDO_Emailer_Queue($aOptions);
        $groupID = '';

        // add emails to queue
        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Blow it up...';
        $subject   = 'Top secret';
        $recipient = 'james@bond.com';
        $ok = $queue->push($headers, $recipient, $body, $subject);

        $headers   = 'From: lakiboy83@gmail.com';
        $body      = 'Time to attack...';
        $recipient = 'spider@man.com';
        $ok = $queue->push($headers, $recipient, $body);

        $ok = $queue->processQueue($groupID = null, $skipSend = true);
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);

        // nothing returned
        $ok = $queue->pop($groupID);
        $this->assertFalse($ok);

        // 2 emails still i db
        $this->assertEqual(2, $this->_getTableRecordsCount());

        // clean table
        $this->_cleanTable();
    }

    /**
     * This test should send emails to file instead of sending real emails.
     */
    function xtestSendEmail()
    {
        // this recipient will receive 2 test emails on success
        // should be removed as per method's description
        $destinationTestEmail = 'lakiboy83@gmail.com';

        $aOptions = $this->aOptions;
        $aOptions['removeSent'] = false; // keep sent emails

        $queue = new KDO_Emailer_Queue($aOptions);

        // add emails to queue
        $subject   = 'Test subject 1';
        $headers   = array(
            'From'    => 'beta@dev-machine.com',
            'Subject' => $subject
        );
        $body      = 'Test body 1';
        $recipient = $destinationTestEmail;
        $ok = $queue->push($headers, $recipient, $body, $subject);

        $subject   = 'Test subject 2';
        $headers   = array(
            'From'    => 'beta@dev-machine.com',
            'Subject' => $subject
        );
        $body      = 'Test body 2';
        $recipient = $destinationTestEmail;
        $ok = $queue->push($headers, $recipient, $body);

        $ok = $queue->processQueue();
        $this->assertFalse($ok instanceof PEAR_Error);
        $this->assertTrue($ok);
    }
}

?>
