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
        $this->assertTitle($this->conf['site']['name'] . ' :: Home');
        $this->assertNoUnwantedPattern("/errorContent/");

//        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/articleview/frmArticleID/1/staticId/6/');
//        $this->assertTitle($this->conf['site']['name'] . ' :: Content Reshuffle');
//        $this->assertWantedPattern("/No article found for that ID/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/articleview/action/summary/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Article Browser');
        $this->assertNoUnwantedPattern("/errorContent/");
    }

    function testAdminScreens()
    {
        $this->addHeader('User-agent: foo-bar');
        $this->get($this->conf['site']['baseUrl'] . '/index.php/user/login/');
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmitByName('submitted');

        //  publisher
        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/article/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Article Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/article/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Article Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/document/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Document Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/category/frmCatID/1/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Category Manager');
        $this->assertNoUnwantedPattern("/errorContent/");

        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/category/action/reorder/');
        $this->assertTitle($this->conf['site']['name'] . ' :: Category Manager');
        $this->assertNoUnwantedPattern("/errorContent/");
#        $this->showHeaders();
    }
}
?>
