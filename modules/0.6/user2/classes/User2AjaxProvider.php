<?php

require_once SGL_CORE_DIR . '/Observer.php';
require_once SGL_CORE_DIR . '/AjaxProvider2.php';
require_once dirname(__FILE__) . '/User2DAO.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';

/**
 * Ajax provider.
 *
 * @package user2
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class User2AjaxProvider extends SGL_AjaxProvider2
{
    public function __construct()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        parent::__construct();

        $this->da->add(UserDAO::singleton());
        $this->da->add(User2DAO::singleton());
    }

    public function process(SGL_Registry $input, SGL_Output $output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // turn off autocommit
        $this->dbh->autoCommit(false);

        $ok = parent::process($input, $output);
        DB::isError($ok)
            ? $this->dbh->rollback()
            : $this->dbh->commit();

        // turn autocommit on
        $this->dbh->autoCommit(true);

        return $ok;
    }

    /**
     * Ensure the current user can perform requested action.
     *
     * @param integer $requestedUserId
     *
     * @return boolean
     */
    protected function _isOwner($requestedUserId)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        /*
        $ok = SGL_Session::getRoleId() == SGL_ADMIN
            || SGL_Session::getRoleId() == SGL_MEMBER;
        if ($ok) {
            $ok = $this->_isOwnerResource($requestedUserId);
        }
        return $ok;
        */
        return true;
    }

    /*
    protected function _isOwnerResource($requestedUserId)
    {
        return false;
    }
    */

    public function login(SGL_Registry $input, SGL_Output $output)
    {
        $input->user = (object) $this->req->get('user');

        $sessStart = SGL_Session::get('startTime');

        // login routine
        $oLogin = new User2Observable($input, $output);
        $oLogin->attachMany(SGL_Config::get('Login2Mgr.loginObservers'));
        $oLogin->notify();
        $ok = SGL_Error::count() ? SGL_Error::pop() : true;

        $output->isLogged = false;
        if (!PEAR::isError($ok)) {
            $ok = $sessStart != SGL_Session::get('startTime');
            if ($ok) {
                $roleName  = $this->da->getRoleNameById(SGL_Session::getRoleId());
                $loginGoto = sprintf('login%sGoto', ucfirst($roleName));
                $loginGoto = SGL_Config::get("Login2Mgr.$loginGoto")
                    ? SGL_Config::get("Login2Mgr.$loginGoto")
                    : SGL_Config::get('site.defaultModule') . '^'
                        . SGL_Config::get('site.defaultManager');
                list($moduleName, $managerName, ) = explode('^', $loginGoto);

                $msg              = 'welcome returned user';
                $persist          = true;
                $output->isLogged = true;
                $output->redir    = $input->getCurrentUrl()->makeLink(array(
                    'moduleName'  => $moduleName,
                    'managerName' => $managerName
                ));
            } else {
                $msg = array(
                    'message' => 'username/password not recognised',
                    'type'    => SGL_MESSAGE_ERROR
                );
                $persist = false;
            }
            $this->_raiseMsg($msg, $trans = true, $persist);
        }
    }

    public function register(SGL_Registry $input, SGL_Output $output)
    {
        $input->user = (object) $this->req->get('user');

        $ok = $this->_validateUser($input->user);
        if (!is_string($ok)) {
            // register routine
            $oRegister = new User2Observable($input, $output);
            $oRegister->attachMany(SGL_Config::get('Login2Mgr.registerObservers'));
            $oRegister->notify();

            $ok = SGL_Error::count()
                ? SGL_Error::pop()
                : true;

            // default error message
            $msg = 'registration failed';
        // get message
        } else {
            $msg = $ok;
            $ok  = false;
        }

        $output->isRegistered = false;
        if (!PEAR::isError($ok) || (is_bool($ok) && !$ok)) {
            if ($ok) {
                $msg                  = 'welcome new user';
                $persist              = true;
                $output->isRegistered = true;
                $output->redir        = $input->getCurrentUrl()->makeLink(array(
                    'moduleName'  => SGL_Session::get('site.defaultModule'),
                    'managerName' => SGL_Session::get('site.defaultManager')
                ));
            } else {
                $msg = array(
                    'message' => $msg,
                    'type'    => SGL_MESSAGE_ERROR
                );
                $persist = false;
            }
            $this->_raiseMsg($msg, $trans = true, $persist);
        }
    }

    public function recoverPassword(SGL_Registry $input, SGL_Output $output)
    {
        $input->user = (object) $this->req->get('user');

        $ok = $this->_validateEmail($input->user);
        // validate email
        if (is_string($ok)) {
            $msg = array(
                'message' => $ok,
                'type'    => SGL_MESSAGE_ERROR
            );
            $ok = false;
        // check user
        } elseif (!($userId = $this->da->getUserIdByUsername(
            $input->user->username, $input->user->email)))
        {
            $msg = array(
                'message' => 'user not found',
                'type'    => SGL_MESSAGE_ERROR
            );
            $ok = false;
        // send email
        } else {
            $input->userId = $userId;

            $msg = 'password reset email sent';

            // email gen routine
            $oPasswd = new User2Observable($input, $output);
            $oPasswd->attachMany(SGL_Config::get('PasswordRecoveryMgr.createObservers'));
            $oPasswd->notify();

            $ok = SGL_Error::count() ? SGL_Error::pop() : true;
        }
        if (!PEAR::isError($ok)) {
            $this->_raiseMsg($msg, $trans = true);
        }
    }

    public function resetPasswordByHash(SGL_Registry $input, SGL_Output $output)
    {
        $input->oUser  = (object) $this->req->get('user');
        $input->userId = $this->req->get('userId');
        $input->hash   = $this->req->get('k');

        $oHash = $this->da->getPasswordHashByUserIdAndHash(
            $input->userId, $input->hash);

        $output->isReset = false;

        // key is outdated
        if (empty($oHash)) {
            $this->_raiseMsg(array('message' => 'reset key is outdated',
                'type' => SGL_MESSAGE_ERROR), $trans = true);
        } else {
            $dt = new DateTime($oHash->date_created);
            $dt->modify('+ 5 days');

            // check if hash is not outdated
            if ($dt->format('Y-m-d H:i:s') < SGL_Date::getTime($gmt = true)) {
                $this->_raiseMsg(array('message' => 'reset key is outdated',
                    'type' => SGL_MESSAGE_ERROR), $trans = true);
            } else {
                if ($input->oUser->password != $input->oUser->password_repeat) {
                    $this->_raiseMsg(array('message' => 'passwords are not the same',
                        'type' => SGL_MESSAGE_ERROR), $trans = true);
                } else {
                    $ok = $this->da->updatePasswordByUserId($input->userId,
                        $input->oUser->password);
                    $ok = $this->da->deletePasswordHashByUserId($input->userId);
                    if (!PEAR::isError($ok)) {
                        $output->isReset = true;
                        $output->html = $this->_renderTemplate($output, array(
                            'masterTemplate' => 'passwordRecoveryReset.html',
                            'message' => SGL_Output::tr('password successfully reset')
                        ));
                    }
                }
            }
        }
    }

    private function _validateEmail($oUser, $type = 'recover')
    {
        require_once 'Validate.php';
        $v = new Validate();

        if (empty($oUser->email) || !$v->email($oUser->email)) {
            $ret = 'email syntax error';
        } else {
            $ret = true;
        }
        return $ret;
    }

    private function _validateUser($oUser, $type = 'insert')
    {
        require_once 'Validate.php';
        $v = new Validate();

        $aVal = array('format' => VALIDATE_NUM . VALIDATE_ALPHA, 'min_length' => 5);
        if (!$v->string($oUser->username, $aVal)) {
            $ret = 'username min length error';
        } elseif (!$this->da->isUniqueUsername($oUser->username)) {
            $ret = 'username is not unique error';
        } elseif (gettype($msg = $this->_validateEmail($oUser)) == 'string') {
            $ret = $msg;
        } elseif (!$this->da->isUniqueEmail($oUser->email)) {
            $ret = 'email is not unique error';
        } else {
            $ret = true;
        }
        return $ret;
    }
}

class User2Observable extends SGL_Observable
{
    public $input;
    public $conf;
    public $path;

    public function __construct(SGL_Registry $input, SGL_Output $output, $path = '')
    {
        $this->input  = $input;
        $this->output = $output;
        $this->conf   = $input->getConfig();
        $this->path   = !empty($path)
            ? $path : dirname(__FILE__) . '/observers';
    }

    public function attachMany($observersString)
    {
        if (!empty($observersString)) {
            $aObservers = explode(',', $observersString);
            foreach ($aObservers as $observer) {
                $observerFile = "{$this->path}/$observer.php";
                if (file_exists($observerFile)) {
                    require_once $observerFile;
                    $this->attach(new $observer());
                }
            }
        }
    }
}
?>