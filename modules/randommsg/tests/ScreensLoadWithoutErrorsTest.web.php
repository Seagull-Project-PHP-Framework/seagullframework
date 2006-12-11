<?php

class RandomMsgScreensLoadWithoutErrorsTest extends WebTestCase
{
    function RandomMsgScreensLoadWithoutErrorsTest()
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

        //  random msg
        $this->get($this->conf['site']['baseUrl'] . '/index.php/randommsg/rndmsg/');
        $this->assertTitle($this->conf['site']['name'] . ' :: RndMsg Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/randommsg/rndmsg/action/add/');
        $this->assertTitle($this->conf['site']['name'] . ' :: RndMsg Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>
