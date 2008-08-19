<?php

require_once SGL_CORE_DIR . '/Delegator.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';

class Account2Mgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'Account Management';
        $this->template  = 'account2List.html';

        $this->_aActionsMapping = array(
            'list' => array('list')
        );

        $this->da = new SGL_Delegator();
        $this->da->add(UserDAO::singleton());
        $this->da->add(User2DAO::singleton());
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'list';
    }

    public function display(SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

//        $output->addJavascriptFile(array(
//            'js/jquery/plugins/jquery.form.js',
//            'user2/js/User2/Login.js'
//        ));
    }

    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $userId = SGL_Session::getUid();
        $oMedia = $this->da->getProfileImageByUserId($userId);
        $oUser  = $this->da->getUserById($userId);
        $oUser->date_created = SGL_Output::formatDatePretty($oUser->date_created);

        $output->oUser    = $oUser;
        $output->oMedia   = $oMedia;
        $output->roleName = $this->da->getRoleNameById(SGL_Session::getRoleId());
        $output->userId   = SGL_Session::getUid();
//        $output->remoteIp = $_SERVER['REMOTE_ADDR'];
//        $output->login    = $this->da->getLastLogin();
    }
}
?>