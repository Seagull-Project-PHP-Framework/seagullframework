<?php

require_once SGL_CORE_DIR . '/Delegator.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';

class Profile2Mgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle = 'User Profile';
        $this->template  = 'profile2List.html';

        $this->_aActionsMapping = array(
            'list' => array('list')
        );

        $this->da = new SGL_Delegator();
        $this->da->add(User2DAO::singleton());
        $this->da->add(UserDAO::singleton());
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

        $input->userId = $req->get('userId');
    }

    public function display(SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $aRegs  = SGL::loadRegionList('countries');
        $oMedia = $this->da->getProfileImageByUserId($input->userId);
        $oUser  = $this->da->getUserById($input->userId);
        $oUser->date_created = SGL_Output::formatDatePretty($oUser->date_created);
        $oUser->countryName  = isset($aRegs[$oUser->country])
            ? $aRegs[$oUser->country] : $oUser->country;

        $output->oUser  = $oUser;
        $output->oMedia = $oMedia;
    }
}
?>