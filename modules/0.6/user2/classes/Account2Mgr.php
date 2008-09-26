<?php

require_once SGL_CORE_DIR . '/Delegator.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';
require_once SGL_MOD_DIR . '/media2/classes/Media2DAO.php';

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
        $this->da->add(User2DAO::singleton());
        $this->da->add(UserDAO::singleton());
        $this->da->add(Media2DAO::singleton());
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

        $output->addJavascriptFile(array(
            'js/jquery/plugins/jquery.form.js',
            'media2/js/jquery/ajaxfileupload.js',
            'user2/js/User2.js',
            'user2/js/User2/Account.js'
        ));
        $output->addOnLoadEvent('User2.Account.init()', true);

        // export media type constants
        $aMediaTypes = $this->da->getMediaTypes();
        foreach ($aMediaTypes as $oMediaType) {
            $name = 'MEDIATYPE_' . strtoupper($oMediaType->name);
        	$output->exportJsVar($name, $oMediaType->media_type_id);
        }
    }

    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $userId = SGL_Session::getUid();
        $oMedia = $this->da->getProfileImageByUserId($userId);
        $oUser  = $this->da->getUserById($userId);
        $oUser->date_created = SGL_Output::formatDatePretty($oUser->date_created);

        $aDefaultCountry    = array('' => SGL_Output::tr('select'));
        $output->oAddress   = $this->da->getAddressByUserId($userId);
        $output->oUser      = $oUser;
        $output->oMedia     = $oMedia;
        $output->roleName   = $this->da->getRoleNameById(SGL_Session::getRoleId());
        $output->userId     = SGL_Session::getUid();
        $output->aCountries = $aDefaultCountry + SGL::loadRegionList('countries');
        $output->aStates    = SGL::loadRegionList('states');
//        $output->remoteIp = $_SERVER['REMOTE_ADDR'];
//        $output->login    = $this->da->getLastLogin();
    }
}
?>