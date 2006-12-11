<?php

class DefaultScreensLoadWithoutErrorsTest extends WebTestCase
{
    function DefaultScreensLoadWithoutErrorsTest()
    {
        $this->WebTestCase('Load without errors Test');
        $c = &SGL_Config::singleton();
        $this->conf = $c->getAll();
    }

    function testPublicScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl']);
        $this->assertTitle($this->conf['site']['name'] . ' :: Home');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/register/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Register');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/default/bug/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Bug Report');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmitByName('submitted');
#        $this->showSource();

        //  modules
        $this->assertTitle($this->conf['site']['name'] . ' :: Module Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->clickLink('Manage');
        $this->assertTitle($this->conf['site']['name'] . ' :: Module Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->clickLink('Configuration');
        $this->assertTitle($this->conf['site']['name'] . ' :: Config Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        // bug mgr
        $this->get($this->conf['site']['baseUrl'] . '/index.php/default/bug/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Bug Report');
        $this->assertNoUnwantedPattern("/errorContent/");

        //  mtce
        $this->get($this->conf['site']['baseUrl'] . '/index.php/default/maintenance/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Maintenance');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
