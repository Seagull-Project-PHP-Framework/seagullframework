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
        $this->assertTitle($this->conf['site']['name'] . ' :: Home');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/faq/');
        $this->assertTitle($this->conf['site']['name'] . ' :: FAQs');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmitByName('submitted');

        $this->get($this->conf['site']['baseUrl'] . '/index.php/faq/');
        $this->assertTitle($this->conf['site']['name'] . ' :: FAQs');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
