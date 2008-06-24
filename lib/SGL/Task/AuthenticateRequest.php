<?php

/**
 * Initiates session check.
 *
 *      o global set of perm constants loaded from file cache
 *      o current class's config file is checked to see if authentication is required
 *      o if yes, session is checked for validity and expiration
 *      o if it's valid and not expired, the session is deemed valid.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_AuthenticateRequest extends SGL_DecorateProcess
{
    /**
     * Returns 'remember me' cookie data.
     *
     * @return mixed
     */
    function getRememberMeCookieData()
    {
        //  no 'remember me' cookie found
        if (!isset($_COOKIE['SGL_REMEMBER_ME'])) {
            return false;
        }
        $cookie = $_COOKIE['SGL_REMEMBER_ME'];
        list($username, $cookieValue) = @unserialize($cookie);
        //  wrong cookie value was saved
        if (!$username || !$cookieValue) {
            return false;
        }
        //  get UID by cookie value
        require_once SGL_MOD_DIR . '/user/classes/UserDAO.php';
        $da  = &UserDAO::singleton();
        $uid = $da->getUserIdByCookie($username, $cookieValue);
        if ($uid) {
            $ret = array('uid' => $uid, 'cookieVal' => $cookieValue);
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Authenticate user.
     *
     * @access protected
     *
     * @param integer $uid
     * @param SGL_Registry $input
     *
     * @return void
     */
    function doLogin($uid, &$input)
    {
        // if we do login here, then $uid was recovered by cookie,
        // thus activating 'remember me' functionality
        $input->set('session', new SGL_Session($uid, $rememberMe = true));

        // record login if allowed
        require_once SGL_MOD_DIR . '/user/classes/observers/RecordLogin.php';
        if (RecordLogin::loginRecordingAllowed()) {
            $dbh = &SGL_DB::singleton();
            RecordLogin::insert($dbh);
        }
    }

    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // check for timeout
        $session = $input->get('session');
        $timeout = !$session->updateIdle();

        //  store request in session
        $aRequestHistory = SGL_Session::get('aRequestHistory');
        if (empty($aRequestHistory)) {
            $aRequestHistory = array();
        }
        $req = $input->getRequest();
        array_unshift($aRequestHistory, $req->getAll());
        $aTruncated = array_slice($aRequestHistory, 0, 2);
        SGL_Session::set('aRequestHistory', $aTruncated);

        $mgr = $input->get('manager');
        $mgrName = SGL_Inflector::caseFix(get_class($mgr));

        //  test for anonymous session and rememberMe cookie
        if (($session->isAnonymous() || $timeout)
                && SGL_Config::get('cookie.rememberMeEnabled')
                && !SGL_Config::get('site.maintenanceMode')) {
            $aCookieData = $this->getRememberMeCookieData();
            if (!empty($aCookieData['uid'])) {
                $this->doLogin($aCookieData['uid'], $input);

                //  session data updated
                $session = $input->get('session');
                $timeout = !$session->updateIdle();
            }
        }
        //  if page requires authentication and we're not debugging
        if (   SGL_Config::get("$mgrName.requiresAuth")
            && SGL_Config::get('debug.authorisationEnabled')
            && !SGL::runningFromCLI())
        {
            //  check that session is valid or timed out
            if (!$session->isValid() || $timeout) {

                //  prepare referer info for redirect after login
                $url = $input->getCurrentUrl();
                $redir = $url->toString();

                if (SGL_Config::get('site.logoutTarget')) {
                    $loginPage = SGL_Config::getCommandTarget(SGL_Config::get('site.logoutTarget'));
                    $loginPage['redir'] = urlencode($redir);
                } else {
                    $loginPage = array(
                        'moduleName'    => 'user',
                        'managerName'   => 'login',
                        'redir'         => urlencode($redir));
                }

                if (!$session->isValid()) {
                    SGL::raiseMsg('authentication required');
                    SGL_HTTP::redirect($loginPage);
                } else {
                    $session->destroy();
                    SGL::raiseMsg('session timeout');
                    SGL_HTTP::redirect($loginPage);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}
?>