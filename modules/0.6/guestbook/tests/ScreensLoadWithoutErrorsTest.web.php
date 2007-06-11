<?php

class GuestBookScreensLoadWithoutErrorsTest extends WebTestCase
{
    function GuestBookScreensLoadWithoutErrorsTest()
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

        //  guestbook
        $this->get($this->conf['site']['baseUrl'] . '/index.php/guestbook/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Welcome to our Guestbook');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmit('Login');

        //  guestbook
        $this->get($this->conf['site']['baseUrl'] . '/index.php/guestbook/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Welcome to our Guestbook');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
