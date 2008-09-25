<?php

require_once SGL_MOD_DIR . '/user2/classes/User2DAO.php';

/**
 * Password recovery manager.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class PasswordRecoveryMgr extends SGL_Manager
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::SGL_Manager();

        $this->pageTitle      = 'Recover Password';
        $this->masterTemplate = 'master.html';
        $this->template       = 'passwordRecoveryList.html';

        $this->_aActionsMapping = array(
            'list'  => array('list'),
            'reset' => array('reset'),
        );

        $this->da = User2DAO::singleton();
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
        $input->hash   = $req->get('k');
    }

    public function display(SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $output->addJavascriptFile(array(
            'js/jquery/plugins/jquery.form.js',
            'user2/js/User2.js',
            'user2/js/User2/Password.js'
        ));
        $output->addOnLoadEvent('User2.Password.init()', true);
    }

    public function _cmd_list(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    public function _cmd_reset(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $oHash = $this->da->getPasswordHashByUserIdAndHash(
            $input->userId, $input->hash);

        // key is outdated
        if (empty($oHash)) {
            $output->message = SGL_Output::tr('reset key is outdated');
        } else {
            $dt = new DateTime($oHash->date_created);
            $dt->modify('+ 5 days');

            // check if hash is not outdated
            if ($dt->format('Y-m-d H:i:s') < SGL_Date::getTime($gmt = true)) {
                $output->message = SGL_Output::tr('reset key is outdated');
            }
        }

        $output->oHash    = $oHash;
        $output->template = 'passwordRecoveryReset.html';
    }
}
?>