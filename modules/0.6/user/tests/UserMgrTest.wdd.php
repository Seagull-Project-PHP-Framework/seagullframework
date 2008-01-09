<?php
require_once dirname(__FILE__). '/../classes/UserMgr.php';

/**
 * Test suite.
 *
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Id: TestUserMgr.php,v 1.5 2005/05/17 23:54:53 demian Exp $
 */
class TestUserMgr extends UnitTestCase {

    function TestUserMgr()
    {
        $this->da = & UserDAO::singleton();
        $this->UnitTestCase('UserMgr Test');
        Mock::generatePartial('UserMgr', 'PartialUserMgr', array('_getUserPermsByRole'));
    }

    function setup()
    {
        $locator = &SGL_ServiceLocator::singleton();
        $this->dbh = $locator->get('DB');
        if (!$this->dbh) {
            $this->dbh = & SGL_DB::singleton();
            $locator->register('DB', $this->dbh);
        }
        SGL_DB::setConnection();
    }

    function tearDown()
    {
        $table  = $this->da->conf['table']['user'];
        $query  = "SELECT usr_id FROM $table";
        $aUsers = $this->da->dbh->getCol($query);

        foreach ($aUsers as $userId) {
            $this->_deleteUser($userId);
        }

        // rebuild seqs
        include_once SGL_CORE_DIR . '/Task/Install.php';
        SGL_Task_SyncSequences::run();
    }

    function _deleteUser($userId)
    {
        $this->da->deletePrefsByUserId($userId);
        $this->da->deleteUserLoginCookiesByUserId($userId);

        $oUser = DB_DataObject::factory($this->da->conf['table']['user']);
        $oUser->get($userId);
        $oUser->delete();
    }

    function testInsertingAUserIncrementsTotalCount()
    {
        $conf      = $this->da->conf;
        $countUsr  = 'SELECT COUNT(*) FROM ' . $conf['table']['user'];
        $countPerm = 'SELECT COUNT(*) FROM ' . $conf['table']['user_permission'];

        //  users inherit their role from the default roles of orgs they belong to
        //  go through a range of orgs that cover all roles in the system
        //      2 - traffic manager
        //      3 - traffic operator
        //      4 - account manager
        $aOrgIds = array(2);
        foreach ($aOrgIds as $orgId) {
            $oInput = new stdClass();
            $oOutput = new stdClass();

            // get initial count of usr records
            $initialCountUser = $this->dbh->getOne($countUsr);

            // get initial count of user_permission records
            $initialCountPerms = $this->dbh->getOne($countPerm);

            //  build user object
            $oUser = new stdClass();
            $oUser->organisation_id = $orgId;
            $oUser->role_id = $orgId;
            $oUser->username = 'testUser';
            $oUser->passwd = 'test123';
            $oUser->first_name = 'test';
            $oUser->last_name = 'user';
            $oUser->email = 'test@example.com';
            $oUser->is_acct_active = 1;

            //  assign to input object
            $oInput->user = $oUser;

            $userMgr = new UserMgr();
            $userMgr->_cmd_insert($oInput, $oOutput);

            // get final count of user records
            unset($oInput, $oOutput, $userMgr);

            $finalCountUser = $this->dbh->getOne($countUsr);

            //  test user record inserted
            $this->assertEqual($initialCountUser + 1, $finalCountUser);

            //  test perms inserted
            //  get final count of user_permission records added
            $finalCountPerms = $this->dbh->getOne($countPerm);
            $permsAdded = $finalCountPerms - $initialCountPerms;

            //  determine user's role inherited from parent
            $roleId = 2; // hard coded in loop above

            //  get count of perms required for given role
            $requireNumPerms = count($this->da->getPermsByRoleId($roleId));
            $this->assertEqual($requireNumPerms, $permsAdded);

            //  unset all vars for next round
            unset($doUser, $doUsrPerms, $oOrg, $oUser);
        }
    }


    function _testThatNewUserIsSavedToDb()
    {
            $oUser = new stdClass();
            $oUser->role_id = 29;
            $oInput->user = $oUser;

            $userMgr = new PartialUserMgr($this);
            $userMgr->expectOnce('_getUserPermsByRole', array(29));
            $userMgr->UserMgr();
            $this->assertIdentical($userMgr->_insert($oInput, $oOutput), true);

    }

    function xtestUpdate()
    {
        //  in summary, insert a new user, get last inserted id,
        //  update that user, compare results

        $oInput = new stdClass();
        $oOutput = new stdClass();

        // get initial count of usr records
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $initialCountUser = $doUser->count();

        //  build user object
        $oUser = new stdClass();
        $oUser->organisation_id = 1;
        $oUser->role_id = 2;
        $oUser->username = 'fooTestUser';
        $oUser->passwd = 'test123';
        $oUser->first_name = 'fooTest';
        $oUser->last_name = 'fooUser';
        $oUser->email = 'foo@example.com';

        //  assign to input object
        $oInput->user = $oUser;

        $userMgr = new UserMgr();
        $userMgr->_insert($oInput, $oOutput);

        // get final count of user records
        unset($oInput, $oOutput, $oUser, $doUser, $userMgr);
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $finalCountUser = $doUser->count();

        //  test user record inserted correctly
        $this->assertTrue($initialCountUser + 1 == $finalCountUser);

        //  get id of last inserted user
        $query = '
            SELECT MAX(usr_id) AS last_inserted_id
            FROM  usr';
        $doUser->query($query);
        $doUser->fetch();

        $lastId = $doUser->last_inserted_id;
        unset($oInput, $oOutput, $oUser, $doUser, $userMgr);

        //  update this user's details
        $oInput = new stdClass();
        $oOutput = new stdClass();

        // get initial count of usr records
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $initialCountUser = $doUser->count();

        //  build user object
        $oUser = new stdClass();
        $oUser->usr_id = $lastId;
        $oUser->organisation_id = 1;
        $oUser->username = 'barTestUser';
        $oUser->passwd = 'test123';
        $oUser->first_name = 'barTest';
        $oUser->last_name = 'barUser';
        $oUser->email = 'bar@example.com';

        //  assign to input object
        $oInput->user = $oUser;

        $userMgr = new UserMgr();
        $userMgr->_update($oInput, $oOutput);

        // get final count of user records
        unset($doUser);
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $finalCountUser = $doUser->count();

        //  test user record updated correctly, ie, same num records
        $this->assertTrue($initialCountUser == $finalCountUser);

        unset($oInput, $oOutput, $oUser, $userMgr);

        //  compare actual results with expected results
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $doUser->get($lastId);

        $this->assertTrue($doUser->username == 'barTestUser');
        $this->assertTrue($doUser->passwd == 'test123');
        $this->assertTrue($doUser->first_name == 'barTest');
        $this->assertTrue($doUser->last_name == 'barUser');
        $this->assertTrue($doUser->email == 'bar@example.com');
    }

    function xtestDelete()
    {
        $oInput = new stdClass();
        $oOutput = new stdClass();

        //  get initial count of usr records
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $initialCountUser = $doUser->count();

        //  grab 5 records, stop when 2 adequate records collected
        $doUser->limit(5);
        $doUser->find();

        while ($doUser->fetch()) {
            if ($doUser->usr_id == 1) {
                continue;
            }
            $oInput->aDelete[] = $doUser->usr_id;
            if (count($oInput->aDelete) == 2) {
                break;
            }
        }

        $userMgr = new UserMgr();
        $userMgr->_delete($oInput, $oOutput);

        // get final count of user records
        unset($doUser);
        $doUser = DB_DataObject::factory($conf['table']['user']);
        $finalCountUser = $doUser->count();
        $this->assertTrue(($initialCountUser - $finalCountUser) == 2);
    }

    function testIsUniqueUsername()
    {
        //  generate unique email, test method, insert it, test method

        //  generate unique email
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $username = $oPassword->create();

        //  test for uniqueness
        $userMgr = new UserMgr();
        $this->assertTrue($this->da->isUniqueUsername($username));
        unset($userMgr);

        //  insert it in DB
        $oInput = new stdClass();
        $oOutput = new stdClass();

        //  build user object
        $oUser = new stdClass();
        $oUser->organisation_id = 1;
        $oUser->role_id = 2;
        $oUser->username = $username;
        $oUser->passwd = 'test123';
        $oUser->first_name = 'fooTest';
        $oUser->last_name = 'fooUser';
        $oUser->email = 'foo@example.com';

        //  assign to input object
        $oInput->user = $oUser;

        $userMgr = new UserMgr();
        $userMgr->_cmd_insert($oInput, $oOutput);

        // test for uniqueness again
        $this->assertFalse($this->da->isUniqueUsername($username));
    }

    function testIsUniqueEmail()
    {
        //  generate unique email, test method, insert it, test method

        //  generate unique email
        require_once 'Text/Password.php';
        $oPassword = & new Text_Password();
        $token = $oPassword->create();
        $email = $token . '@example.com';

        //  test for uniqueness
        $userMgr = new UserMgr();
        $this->assertTrue($this->da->isUniqueEmail($email));
        unset($userMgr);

        //  insert it in DB
        $oInput = new stdClass();
        $oOutput = new stdClass();

        //  build user object
        $oUser = new stdClass();
        $oUser->organisation_id = 1;
        $oUser->role_id = 2;
        $oUser->username = 'fooTestUser';
        $oUser->passwd = 'footest123';
        $oUser->first_name = 'fooTest';
        $oUser->last_name = 'fooUser';
        $oUser->email = $email;

        //  assign to input object
        $oInput->user = $oUser;

        $userMgr = new UserMgr();
        $userMgr->_cmd_insert($oInput, $oOutput);

        // test for uniqueness again
        $this->assertFalse($this->da->isUniqueEmail($email));
    }
}
?>
