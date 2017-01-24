<?php

require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

/**
 * Login manager.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class Login2Mgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle      = 'Login';
        $this->masterTemplate = 'master.html';
        $this->template       = 'login2Login.html';

        $this->_aActionsMapping = array(
            'logout'   => array('logout', 'redirectToHomePage'),
            'register' => array('register'),
            'login'    => array('login')
        );
        $this->da = UserDAO::singleton();
    }

    public function validate(SGL_Request $req, SGL_Registry $input)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->validated       = true;
        $input->pageTitle      = $this->pageTitle;
        $input->template       = $this->template;
        $input->masterTemplate = $this->masterTemplate;
        $input->action         = $req->get('action')
            ? $req->get('action') : 'login';
        $input->redir          = $req->get('redir');
    }

    public function display(SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->addJavascriptFile(array(
            'js/jquery/plugins/jquery.form.js',
            'user2/js/User2.js',
            'user2/js/User2/Login.js'
        ));
        $output->addOnLoadEvent('User2.Login.init()', true);
    }

    public function _cmd_logout(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $uid = SGL_Session::getUid();
        if (SGL_Session::getUid() > SGL_GUEST) {
            if (SGL_Config::get('translation.langInUrl')) {
                $input->lang = SGL::getCurrentLang();
            }

            // cleanup remember me
            if (SGL_Config::get('cookie.rememberMeEnabled')
                && !empty($_COOKIE['SGL_REMEMBER_ME']))
            {
                list(, $cookieValue) = @unserialize($_COOKIE['SGL_REMEMBER_ME']);
                $this->da->deleteUserLoginCookieByUserId($uid, $cookieValue);
            }
            SGL_Session::destroy();

            SGL::raiseMsg('you have been logged out', true, SGL_MESSAGE_INFO);
        }
    }

    public function _cmd_register(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->pageTitle = 'Register';
        $output->template  = 'login2Register.html';
    }

    public function _cmd_login(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    public function _cmd_redirectToHomePage(SGL_Registry $input, SGL_Output $output)
    {
        $aParams['moduleName']  = SGL_Config::get('site.defaultModule');
        $aParams['managerName'] = SGL_Config::get('site.defaultManager');
        if (SGL_Session::get('site.defaultAction')) {
            $aParams['action'] = SGL_Session::get('site.defaultAction');
        }
        if (!empty($input->lang)) {
            $aParams['lang'] = $input->lang;
        }
        SGL_HTTP::redirect($input->getCurrentUrl()->makeLink($aParams));
    }
}
?>