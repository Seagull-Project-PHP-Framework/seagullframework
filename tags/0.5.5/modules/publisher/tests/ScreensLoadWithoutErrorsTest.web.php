<?php

class PublisherScreensLoadWithoutErrorsTest extends WebTestCase
{
    function PublisherScreensLoadWithoutErrorsTest()
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

//        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/articleview/frmArticleID/1/staticId/6/');
//        $this->assertTitle('Seagull Framework :: Content Reshuffle');
//        $this->assertWantedPattern("/No article found for that ID/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/articleview/');
        $this->assertTitle('Seagull Framework :: Article Browser');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmit('Login');

        //  publisher
        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/article/');
        $this->assertTitle('Seagull Framework :: Article Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/article/');
        $this->assertTitle('Seagull Framework :: Article Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/document/');
        $this->assertTitle('Seagull Framework :: Document Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/category/frmCatID/1/');
        $this->assertTitle('Seagull Framework :: Category Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/category/action/reorder/');
        $this->assertTitle('Seagull Framework :: Category Manager');
        $this->assertNoUnwantedPattern("/errorContent/");
#        $this->showHeaders();
    }
}
?>