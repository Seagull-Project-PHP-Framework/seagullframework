<?php

class ScreensLoadWithoutErrorsTest extends WebTestCase
{
    function ScreensLoadWithoutErrorsTest()
    {
        $this->WebTestCase('Load without errors Test');
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
    }

    
    function testPublicScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl']);
        $this->assertTitle('Seagull Framework :: Home');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->clickLink('Register Now');
        $this->assertTitle('Seagull Framework :: Register Now');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/default/bug/');
        $this->assertTitle('Seagull Framework :: Bug Report');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
    
    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmit('Login');
#        $this->showSource();

        //  modules
        $this->assertTitle('Seagull Framework :: Modules');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->clickLink('Manage');
        $this->assertTitle('Seagull Framework :: Modules');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->clickLink('Configuration');
        $this->assertTitle('Seagull Framework :: Configuration');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        //  my account
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/');
        $this->assertTitle('Seagull Framework :: My Account');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/viewProfile/');
        $this->assertTitle('Seagull Framework :: My Account');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/edit/');
        $this->assertTitle('Seagull Framework :: My Account');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/account/action/edit/');
        $this->assertTitle('Seagull Framework :: My Account');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/userpreference/');
        $this->assertTitle('Seagull Framework :: User Preferences');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/password/action/edit/');
        $this->assertTitle('Seagull Framework :: Change Password');
        $this->assertNoUnwantedPattern("/errorContent/");
       
        //  profile
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/profile/action/view/frmUserID/1/');
        $this->assertTitle('Seagull Framework :: User Profile');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        //  user
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/');
        $this->assertTitle('Seagull Framework :: User Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/add/');
        $this->assertTitle('Seagull Framework :: User Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/usersearch/action/add/');
        $this->assertTitle('Seagull Framework :: User Manager :: Search');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/requestPasswordReset/frmUserID/1/');
        $this->assertTitle('Seagull Framework :: User Manager :: Reset password');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/action/editPerms/frmUserID/2/');
        $this->assertTitle('Seagull Framework :: User Manager :: Edit permissions');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/org/action/list/');
        $this->assertTitle('Seagull Framework :: Organisation Manager');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/orgtype/action/list/');
        $this->assertTitle('Seagull Framework :: OrgType Manager');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/role/action/list/');
        $this->assertTitle('Seagull Framework :: Role Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/role/action/editPerms/frmRoleID/2/');
        $this->assertTitle('Seagull Framework :: Role Manager :: Permissions');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/list/');
        $this->assertTitle('Seagull Framework :: Permission Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/add/frmPermId/moduleId/');
        $this->assertTitle('Seagull Framework :: Permission Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/scanNew/frmPermId/moduleId/');
        $this->assertTitle('Seagull Framework :: Permission Manager :: Detect & Add');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/permission/action/scanOrphaned/frmPermId/moduleId/');
        $this->assertTitle('Seagull Framework :: Permission Manager :: Detect Orphaned');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/preference/action/list/');
        $this->assertTitle('Seagull Framework :: Preference Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/preference/action/add/');
        $this->assertTitle('Seagull Framework :: Preference Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        // bug mgr
        $this->get($this->conf['site']['baseUrl'] . '/index.php/default/bug/');
        $this->assertTitle('Seagull Framework :: Bug Report');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>