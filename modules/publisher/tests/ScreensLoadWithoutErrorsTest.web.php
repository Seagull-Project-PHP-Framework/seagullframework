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
        
        $this->clickLink('Sample');
        $this->assertTitle('Seagull Framework :: Content Reshuffle');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->clickLink('Articles');
        $this->assertTitle('Seagull Framework :: Articles');
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
        $this->assertTitle('Seagull Framework :: PubArticles');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/article/');
        $this->assertTitle('Seagull Framework :: PubArticles');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/publisher/document/');
        $this->assertTitle('Seagull Framework :: PubDocuments');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/category/frmCatID/1/');
        $this->assertTitle('Seagull Framework :: PubCategories');
        $this->assertNoUnwantedPattern("/errorContent/");
        
        $this->get($this->conf['site']['baseUrl'] . '/index.php/navigation/category/action/reorder/');
        $this->assertTitle('Seagull Framework :: PubCategories');
        $this->assertNoUnwantedPattern("/errorContent/");
#        $this->showHeaders();  
    }
}
?>