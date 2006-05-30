<?php
class UserScreensLoadWithoutErrorsTest extends WebTestCase
{
    function UserScreensLoadWithoutErrorsTest()
    {
        $this->WebTestCase('Load without errors Test');
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmitByName('submitted');

        //  my account
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/');
        $this->assertTitle('Seagull Framework :: Summary');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/viewProfile/');
        $this->assertTitle('Seagull Framework :: View Profile');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/edit/');
        $this->assertTitle('Seagull Framework :: Summary');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/edit/');
        $this->assertTitle('Seagull Framework :: Summary');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/userpreference/');
        $this->assertTitle('Seagull Framework :: Edit Preferences');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/userpassword/action/edit/');
        $this->assertTitle('Seagull Framework :: Change Password');
        $this->assertNoUnwantedPattern("/errorContent/");

        //  profile
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/profile/action/view/frmUserID/1/');
        $this->assertTitle('Seagull Framework :: User Profile');
        $this->assertNoUnwantedPattern("/errorContent/");

        //  user
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/');
        $this->assertTitle('Seagull Framework :: Manage users');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/add/');
        $this->assertTitle('Seagull Framework :: Manage users');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/usersearch/action/add/');
        $this->assertTitle('Seagull Framework :: User Manager :: Search');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/requestPasswordReset/frmUserID/1/');
        $this->assertTitle('Seagull Framework :: Manage users');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/editPerms/frmUserID/2/');
        $this->assertTitle('Seagull Framework :: Manage users');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/org/action/list/');
        $this->assertTitle('Seagull Framework :: Organisation Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/orgtype/action/list/');
        $this->assertTitle('Seagull Framework :: OrgType Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/role/action/list/');
        $this->assertTitle('Seagull Framework :: Manage roles');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/role/action/editPerms/frmRoleID/2/');
        $this->assertTitle('Seagull Framework :: Manage roles');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/list/');
        $this->assertTitle('Seagull Framework :: Manage permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/add/frmPermId/moduleId/');
        $this->assertTitle('Seagull Framework :: Manage permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/scanNew/frmPermId/moduleId/');
        $this->assertTitle('Seagull Framework :: Manage permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/scanOrphaned/frmPermId/moduleId/');
        $this->assertTitle('Seagull Framework :: Manage permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/preference/action/list/');
        $this->assertTitle('Seagull Framework :: Manage preferences');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/preference/action/add/');
        $this->assertTitle('Seagull Framework :: Manage preferences');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>