<?php

class ContactUsScreensLoadWithoutErrorsTest extends WebTestCase
{
    function ContactUsScreensLoadWithoutErrorsTest()
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

        $this->get($this->conf['site']['baseUrl'] . '/index.php/contactus/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Contact Us');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmit('Login');

        $this->get($this->conf['site']['baseUrl'] . '/index.php/contactus/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Contact Us');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
