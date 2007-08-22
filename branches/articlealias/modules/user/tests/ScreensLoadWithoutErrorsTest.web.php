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
        $this->assertTitle($this->conf['site']['name'] . ' :: My Account');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/viewProfile/');
        $this->assertTitle($this->conf['site']['name'] . ' :: My Profile');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/edit/');
        $this->assertTitle($this->conf['site']['name'] . ' :: My Profile :: edit');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/edit/');
        $this->assertTitle($this->conf['site']['name'] . ' :: My Profile :: edit');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/userpreference/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Preferences');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/userpassword/action/edit/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Change Password');
        $this->assertNoUnwantedPattern("/errorContent/");

        //  profile
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/profile/action/view/frmUserID/1/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Profile');
        $this->assertNoUnwantedPattern("/errorContent/");

        //  user
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/add/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/usersearch/action/add/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Manager :: Search');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/requestPasswordReset/frmUserID/1/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Manager :: Reset password');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/editPerms/frmUserID/2/');
        $this->assertTitle($this->conf['site']['name'] . ' :: User Manager :: Edit permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/org/action/list/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Organisation Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/orgtype/action/list/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Organisation Type Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/role/action/list/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Role Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/role/action/editPerms/frmRoleID/2/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Role Manager :: Permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/list/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Permission Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/add/frmPermId/moduleId/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Permission Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/scanNew/frmPermId/moduleId/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Permission Manager :: Detect & Add');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/scanOrphaned/frmPermId/moduleId/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Permission Manager :: Detect Orphaned');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/preference/action/list/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Preference Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/preference/action/add/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Preference Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
