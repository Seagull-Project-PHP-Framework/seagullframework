<?php

class FaqScreensLoadWithoutErrorsTest extends WebTestCase
{
    function FaqScreensLoadWithoutErrorsTest()
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

        $this->get($this->conf['site']['baseUrl'] . '/index.php/faq/');
        $this->assertTitle('Seagull Framework :: FAQs');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmit('Login');

        $this->get($this->conf['site']['baseUrl'] . '/index.php/faq/');
        $this->assertTitle('Seagull Framework :: FAQ Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>