<?php

require_once SGL_CORE_DIR . '/Observer.php';
require_once SGL_CORE_DIR . '/AjaxProvider2.php';
require_once dirname(__FILE__) . '/User2DAO.php';
require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
require_once SGL_MOD_DIR . '/media2/classes/Media2DAO.php';

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

        $this->da->add(User2DAO::singleton());
        $this->da->add(UserDAO::singleton());
        $this->da->add(Media2DAO::singleton());
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
        $input->user  = (object) $this->req->get('user');
        $input->redir = $this->req->get('redir');

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
                $output->redir    = !empty($input->redir)
                    ? base64_decode($input->redir)
                    : $input->getCurrentUrl()->makeLink(array(
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
        $input->user  = (object) $this->req->get('user');
        $input->redir = $this->req->get('redir');

        $ok = $this->_validateUser($input->user);
        if (!is_string($ok)) {
            if (!isset($input->user->first_name)) {
                $input->user->first_name = '';
            }
            if (!isset($input->user->last_name)) {
                $input->user->last_name = '';
            }

            // register routine
            $oRegister = new User2Observable($input, $output);
            $oRegister->rememberMeIsActive = true;
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
                if (!$input->redir) {
                    $msg           = 'welcome new user';
                    $persist       = true;
                    $output->redir = $input->getCurrentUrl()->makeLink(array(
                        'moduleName'  => SGL_Config::get('site.defaultModule'),
                        'managerName' => SGL_Config::get('site.defaultManager')
                    ));
                // skip message and use specified URL
                } else {
                    $msg           = '';
                    $output->redir = $input->redir;
                    // if custom URL wants username back, give it
                    if (strpos($output->redir, 'USERNAME') !== false) {
                        $output->redir = str_replace('USERNAME',
                            $input->user->username, $output->redir);
                    }
                }
                $output->isRegistered = true;
            } else {
                $msg = array(
                    'message' => $msg,
                    'type'    => SGL_MESSAGE_ERROR
                );
                $persist = false;
            }
            if ($msg) {
                $this->_raiseMsg($msg, $trans = true, $persist);
            }
        }
    }

    /**
     * Update user info for specified field.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function updateUserData(SGL_Registry $input, SGL_Output $output)
    {
        $aAllowedFields = array('first_name', 'last_name', 'about', 'gender');

        $field = $this->req->get('fieldName');
        $val   = $this->req->get('value');

        if (!in_array($field, $aAllowedFields)) {
            $output->val = 'hack';
        } else {
            $this->da->updateUserById(SGL_Session::getUid(), array(
                $field => $val
            ));
            if ($field == 'gender') {
                switch ($val) {
                    case 'm':
                        $val = SGL_Output::tr('male');
                        break;
                    case 'f':
                        $val = SGL_Output::tr('female');
                        break;
                    default:
                        $val = SGL_Output::tr('unknown');
                }
            }
            $output->val = $val;
        }
    }

    /**
     * Update user table data with supplied values (array).
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function updateUserMainInfo(SGL_Registry $input, SGL_Output $output)
    {
        $aUser = $this->req->get('user');

        $aData          = array();
        $aAllowedFields = array('first_name', 'last_name');
        foreach ($aAllowedFields as $fieldName) {
            $aData[$fieldName] = isset($aUser[$fieldName])
                ? $aUser[$fieldName] : '';
        }

        $ok = $this->da->updateUserById(SGL_Session::getUid(), $aData);
        if (!PEAR::isError($ok)) {
            $this->_raiseMsg('user information updated', $trans = true);
        }
    }

    public function updateUserPreferences(SGL_Registry $input, SGL_Output $output)
    {
        $aPrefs      = $this->req->get('prefs');
        $moduleName  = $this->req->get('redirModuleName');
        $managerName = $this->req->get('redirManagerName');

        // ensure we update allowed props
        $aData          = array();
        $aAllowedFields = array('language', 'color', 'timezone');
        foreach ($aAllowedFields as $fieldName) {
            if (isset($aPrefs[$fieldName])) {
                $aData[$fieldName] = $aPrefs[$fieldName];
            }
        }
        $output->redir = false;

        $ok = $this->da->updatePreferencesByUserId(SGL_Session::getUid(), $aData);
        if (!PEAR::isError($ok)) {
            // if language was changed -> redir
            if (!empty($aData['language'])
                && $aData['language'] != $_SESSION['aPrefs']['language'])
            {
                // default values
                if (empty($moduleName) || empty($managerName)) {
                    $moduleName  = 'user2';
                    $managerName = 'account2';
                }
                $langCode = $aData['language'];
                // we need only lang code for 'langInUrl' option
                if (SGL_Config::get('translation.langInUrl')) {
                    $langCode = reset(explode('-', $langCode));
                }
                $output->redir = $input->getCurrentUrl()->makeLink(array(
                    'moduleName'  => $moduleName,
                    'managerName' => $managerName,
                    'lang'        => $langCode
                ));
            }

            // update session
            $_SESSION['aPrefs'] = array_merge($_SESSION['aPrefs'], $aData);
            $this->_raiseMsg('user preferences updated', $trans = true,
                $persistMsg = true);
        }
    }

    /**
     * This action creates new 'password recovery' entry and sends user
     * an email with instructions how to reset theirs password.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
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

    public function resetPassword(SGL_Registry $input, SGL_Output $output)
    {
        $input->passwordNew    = $this->req->get('password_new');
        $input->passwordRepeat = $this->req->get('password_repeat');
        $input->passwordOrig   = $this->req->get('password_orig');

        $userId            = SGL_Session::getUid();
        $output->isUpdated = false;
        if (empty($input->passwordNew)
            || empty($input->passwordRepeat)
            || empty($input->passwordOrig))
        {
            $this->_raiseMsg(array('message' => 'fill in required data',
                'type' => SGL_MESSAGE_ERROR), $trans = true);
        } else {
            $oUser = $this->da->getUserById($userId);
            if ($oUser->passwd != md5($input->passwordOrig)) {
                $this->_raiseMsg(array('message' => 'wrong current password',
                    'type' => SGL_MESSAGE_ERROR), $trans = true);
            } elseif (strlen($input->passwordNew) < 5) {
                $this->_raiseMsg(array('message' => 'password is too short',
                    'type' => SGL_MESSAGE_ERROR), $trans = true);
            } elseif ($input->passwordNew != $input->passwordRepeat) {
                $this->_raiseMsg(array('message' => 'passwords are not the same',
                    'type' => SGL_MESSAGE_ERROR), $trans = true);
            } else {
                $ok = $this->da->updatePasswordByUserId($userId,
                    $input->passwordNew);
                if (!PEAR::isError($ok)) {
                    $this->_raiseMsg('password successfully updated', $trans = true);
                    $output->isUpdated = true;
                }
            }
        }
    }

    /**
     * This action does user password renewal based on supplied
     * 'password recovery' hash entry.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
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
                            'message' => SGL_Output::tr('password successfully updated (string)')
                        ));
                    }
                }
            }
        }
    }

    /**
     * Link already uploaded media to current user.
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    public function linkProfileMediaAndView(SGL_Registry $input, SGL_Output $output)
    {
        $mediaId = $this->req->get('mediaId');

        // link media
        $ok = $this->da->linkMediaToFk($mediaId, SGL_Session::getUid());

        $oMedia = $this->da->getMediaById($mediaId);

        // return path for newly created media
        $output->imgPath = SGL_BASE_URL
            . '/media2/img.php?path=var/uploads/thumbs/small_'
            . $oMedia->file_name;
    }

    public function updateAddress(SGL_Registry $input, SGL_Output $output)
    {
        $aAddress = $this->req->get('address');

        $userId   = SGL_Session::getUid();
        $oAddress = $this->da->getAddressByUserId($userId);
        if (!empty($oAddress)) {
            $this->da->updateAddressById($oAddress->address_id, $aAddress);
        } else {
            $this->da->addAddress($userId, $aAddress);
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

        $aVal = array('format' => VALIDATE_NUM . VALIDATE_ALPHA . '\.', 'min_length' => 3);
        if (!$v->string($oUser->username, $aVal)) {
            $ret = 'username min length error';
        } elseif (!$this->da->isUniqueUsername($oUser->username)) {
            $ret = 'username is not unique error';
        } elseif (gettype($msg = $this->_validateEmail($oUser)) == 'string') {
            $ret = $msg;
        } elseif (!$this->da->isUniqueEmail($oUser->email)) {
            $ret = 'email is not unique error';
        } elseif (strlen($oUser->password) < 5) {
            $ret = 'password is too short';
        } elseif ($oUser->password != $oUser->password_repeat) {
            $ret = 'passwords are not the same';
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
    }

    public function attachMany($observersString)
    {
        if (!empty($observersString)) {
            $aObservers = explode(',', $observersString);
            foreach ($aObservers as $observer) {
                list($moduleName, $observer) = explode('_', $observer);
                $moduleName = strtolower($moduleName);
                $path = SGL_MOD_DIR . '/' . $moduleName . '/classes/observers';
                $observerFile = "$path/$observer.php";
                if (file_exists($observerFile)) {
                    require_once $observerFile;
                    $this->attach(new $observer());
                }
            }
        }
    }
}
?>