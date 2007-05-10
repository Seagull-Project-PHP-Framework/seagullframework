<?php

class NavigationScreensLoadWithoutErrorsTest extends WebTestCase
{
    function NavigationScreensLoadWithoutErrorsTest()
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

        //  navigation
        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/section/');
        $this->assertTitle('Seagull Framework :: Section Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/section/action/add/');
        $this->assertTitle('Seagull Framework :: Section Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/navstyle/action/list/');
        $this->assertTitle('Seagull Framework :: Navigation Style Manager');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>