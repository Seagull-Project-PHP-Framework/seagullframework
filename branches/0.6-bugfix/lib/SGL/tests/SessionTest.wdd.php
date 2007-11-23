<?php

/**
 * Session test.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SessionTest extends UnitTestCase
{
    function SessionTest()
    {
        $this->UnitTestCase('Session Test');
    }

    function setUp()
    {
        include_once SGL_CORE_DIR . '/Task/Install.php';

        $aAdmin = array(
            'adminUserName'  => 'admin',
            'adminFirstName' => 'Admin',
            'adminLastName'  => 'Admin',
            'adminEmail'     => 'admin@admin.com',
            'adminPassword'  => 'admin',
            'createTables'   => 1
        );
        // create 5 demo users
        SGL_Task_CreateAdminUser::run($aAdmin);
        SGL_Task_CreateMemberUser::run(array('createTables' => 1));
        SGL_Task_CreateDemoUsers::run();

        $this->_destroySession();
        new SGL_Session(1);
    }

    function _destroySession()
    {
        @session_destroy();
        $_SESSION = array();
    }

    function tearDown()
    {
        $this->_destroySession();
    }

    function testRunAs()
    {
        $aStack = SGL_Session::get('sessionStack');

        $this->assertIdentical($aStack, '');
        $this->assertEqual(SGL_Session::getUid(), 1);
        $this->assertEqual(SGL_Session::getUsername(), 'admin');

        // run session as lakiboy
        SGL_Session::runAs(3);
        $aStack = SGL_Session::get('sessionStack');

        $this->assertEqual(count($aStack), 1);
        $this->assertEqual($aStack[0], 1);
        $this->assertEqual(SGL_Session::getUid(), 3);
        $this->assertEqual(SGL_Session::getUsername(), 'lakiboy');

        // run session as demian
        SGL_Session::runAs(4);
        $aStack = SGL_Session::get('sessionStack');

        $this->assertEqual(count($aStack), 2);
        $this->assertEqual($aStack[1], 3);
        $this->assertEqual(SGL_Session::getUid(), 4);
        $this->assertEqual(SGL_Session::getUsername(), 'demian');

        // run session as juju
        SGL_Session::runAs(5);
        $aStack = SGL_Session::get('sessionStack');

        $this->assertEqual(count($aStack), 3);
        $this->assertEqual($aStack[2], 4);
        $this->assertEqual(SGL_Session::getUid(), 5);
        $this->assertEqual(SGL_Session::getUsername(), 'juju');

        // run session as juju
        SGL_Session::runAs(2);
        $aStack = SGL_Session::get('sessionStack');

        $this->assertEqual(count($aStack), 4);
        $this->assertEqual($aStack[3], 5);
        $this->assertEqual(SGL_Session::getUid(), 2);
        $this->assertEqual(SGL_Session::getUsername(), 'member');

        // run session as admin
        SGL_Session::runAs(1);
        $aStack = SGL_Session::get('sessionStack');

        // check whole stack
        $this->assertEqual($aStack, array(1, 3, 4, 5, 2));


        // run session as previos user
        SGL_Session::runAs('prev');
        $aStack = SGL_Session::get('sessionStack');

        // check whole stack
        $this->assertEqual($aStack, array(1, 3, 4, 5));

        // check if current user is a member
        $this->assertEqual(SGL_Session::getUid(), 2);
        $this->assertEqual(SGL_Session::getUsername(), 'member');

        // run session as previous user
        SGL_Session::runAs('prev');
        SGL_Session::runAs('prev');

        $aStack = SGL_Session::get('sessionStack');
        // check whole stack
        $this->assertEqual($aStack, array(1, 3));

        // check if current user is juju
        $this->assertEqual(SGL_Session::getUid(), 4);
        $this->assertEqual(SGL_Session::getUsername(), 'demian');

        // run session as previous user
        SGL_Session::runAs('prev');
        SGL_Session::runAs('prev');

        $aStack = SGL_Session::get('sessionStack');
        // check whole stack - stack is empty
        $this->assertEqual($aStack, array());

        // check if we restored the initial state
        $this->assertEqual(SGL_Session::getUid(), 1);
        $this->assertEqual(SGL_Session::getUsername(), 'admin');


        // fill session stack again
        SGL_Session::runAs(2);
        SGL_Session::runAs(3);
        SGL_Session::runAs(5);
        SGL_Session::runAs(4);
        SGL_Session::runAs(1);

        // check for correct stack values
        $aStack = SGL_Session::get('sessionStack');
        $this->assertEqual($aStack, array(1, 2, 3, 5, 4));

        // revert to certain user in stack
        SGL_Session::runAs(3, $direction = 'prev');

        // check stack
        $aStack = SGL_Session::get('sessionStack');
        $this->assertEqual($aStack, array(1, 2));

        // check current user
        $this->assertEqual(SGL_Session::getUid(), 3);
        $this->assertEqual(SGL_Session::getUsername(), 'lakiboy');

        // trying to get from stack no-existing user
        SGL_Session::runAs(5, $direction = 'prev');

        // check stack - it should be empty
        $aStack = SGL_Session::get('sessionStack');
        $this->assertEqual($aStack, array());

        // check current user
        $this->assertEqual(SGL_Session::getUid(), 5);
        $this->assertEqual(SGL_Session::getUsername(), 'juju');
    }
}

/**
 * Create demo users.
 *
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Task_CreateDemoUsers extends SGL_Task
{
    function run()
    {
        $aUsers = array(
            array(
                'username'        => 'lakiboy',
                'first_name'      => 'Dmitri',
                'last_name'       => 'Lakachauskis',
                'email'           => 'lakiboy83@gmail.com',
                'passwd'          => md5('qwerty'),
                'organisation_id' => 1,
                'is_acct_active'  => 1,
                'country'         => 'LV',
                'role_id'         => 2
            ),
            array(
                'username'        => 'demian',
                'first_name'      => 'Demian',
                'last_name'       => 'Turner',
                'email'           => 'demain@phpkitchen.com',
                'passwd'          => md5('qwerty'),
                'organisation_id' => 1,
                'is_acct_active'  => 1,
                'country'         => 'GB',
                'role_id'         => 2
            ),
            array(
                'username'        => 'juju',
                'first_name'      => 'Julien',
                'last_name'       => 'Casanova',
                'email'           => 'juliencasanova@gmail.fr',
                'passwd'          => md5('qwerty'),
                'organisation_id' => 1,
                'is_acct_active'  => 1,
                'country'         => 'FR',
                'role_id'         => 2
            )
        );

        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $da = &UserDAO::singleton();

        foreach ($aUsers as $aUserData) {
            $oUser = DB_DataObject::factory('usr');
            foreach ($aUserData as $f => $v) {
                $oUser->{$f} = $v;
            }
            $oUser->date_created = $oUser->last_updated = SGL_Date::getTime();
            $oUser->created_by   = $oUser->updated_by   = SGL_ADMIN;

            $ok = $da->addUser($oUser);
            unset($oUser);
        }
    }
}

?>