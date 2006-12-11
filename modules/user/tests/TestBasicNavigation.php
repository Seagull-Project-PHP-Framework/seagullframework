<?php
class TestBasicNavigation extends WebTestCase
{

    function testHomePage()
    {
        $this->get('http://localhost/seagull/www/index.php');
        $this->assertTitle($this->conf['site']['name'] . ' :: Home');
        $this->clickLink('login');
#        $this->setHeader('user-agent', 'admin');
#        $this->showHeaders();
        $this->setField('frmUsername', 'admin');
        $this->setField('frmPassword', 'admin');
        $this->clickSubmit('Login');
#        $this->showSource();
    }
}
?>
