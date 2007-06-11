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
        
        //  random msg
        $this->get($this->conf['site']['baseUrl'] . '/index.php/randommsg/rndmsg/');
        $this->assertTitle('Seagull Framework :: RndMsg Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
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
        $this->assertTitle('Seagull Framework :: RndMsg Manager :: Browse');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/randommsg/rndmsg/action/add/');
        $this->assertTitle('Seagull Framework :: RndMsg Manager :: Add');
        $this->assertNoUnwantedPattern("/errorContent/");
    }
}
?>