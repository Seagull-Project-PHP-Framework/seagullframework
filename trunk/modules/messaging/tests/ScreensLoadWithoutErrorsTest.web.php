<?php

class ScreensLoadWithoutErrorsTest extends WebTestCase
{
    function ScreensLoadWithoutErrorsTest()
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
        $this->clickSubmit('Login');

        //  messaging
        $this->clickLink('Messages');
        $this->assertTitle('Seagull Framework :: Messages');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/messaging/contact/');
        $this->assertTitle('Seagull Framework :: Contact Manager');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>