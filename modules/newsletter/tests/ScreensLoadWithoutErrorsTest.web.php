<?php

class NewsletterScreensLoadWithoutErrorsTest extends WebTestCase
{
    function NewsletterScreensLoadWithoutErrorsTest()
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

        //  newsletter
        $this->get($this->conf['site']['baseUrl'] . '/index.php/newsletter/list/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Newsletter List Mgr');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/newsletter/list/action/listSubscribers/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Newsletter List Mgr');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/newsletter/list/action/listLists/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Newsletter List Mgr');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
