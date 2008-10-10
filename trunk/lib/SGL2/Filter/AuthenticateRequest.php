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
class SGL_Filter_AuthenticateRequest extends SGL_DecorateProcess
{
    /**
     * Returns 'remember me' cookie data.
     *
     * @return mixed
     */
    protected function _getRememberMeCookieData()
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
        $da  = UserDAO::singleton();
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
     * @param integer $uid
     *
     * @return void
     */
    protected function _doLogin($uid)
    {
        // if we do login here, then $uid was recovered by cookie,
        // thus activating 'remember me' functionality
        SGL_Registry::set('session', new SGL_Session($uid, $rememberMe = true));
    }

    public function process(SGL_Request $input, SGL_Response $output)
    {
        // check for timeout
        $session = SGL_Registry::get('session');
        $timeout = !$session->updateIdle();

        //  store request in session
        $aRequestHistory = SGL_Session::get('aRequestHistory');
        if (empty($aRequestHistory)) {
            $aRequestHistory = array();
        }
        array_unshift($aRequestHistory, $input->getClean());
        $aTruncated = array_slice($aRequestHistory, 0, 2);
        SGL_Session::set('aRequestHistory', $aTruncated);

        $ctlr = SGL_Registry::get('controller');
        $ctlrName = get_class($ctlr);

        //  test for anonymous session and rememberMe cookie
        if (($session->isAnonymous() || $timeout)
                && SGL_Config2::get('cookie.rememberMeEnabled')
                && !SGL_Config2::get('site.maintenanceMode')) {
            $aCookieData = $this->_getRememberMeCookieData();
            if (!empty($aCookieData['uid'])) {
                $this->_doLogin($aCookieData['uid']);

                //  session data updated
#FIXME - what's going on here, 2nd invocation
                $session = SGL_Registry::get('session');
                $timeout = !$session->updateIdle();
            }
        }
        //  if page requires authentication and we're not debugging
        if (   SGL_Config2::get("$ctlrName.requiresAuth")
            && SGL_Config2::get('debug.authorisationEnabled')
            && $input->getType() != SGL_Request::CLI)
        {
            //  check that session is valid or timed out
            if (!$session->isValid() || $timeout) {

                //  prepare referer info for redirect after login
                $url = SGL_Registry::get('url');
                $redir = $url->toString();
                $loginPage = array(
                    'moduleName'    => 'user',
                    'managerName'   => 'login',
                    'redir'         => base64_encode($redir)
                    );

                if (!$session->isValid()) {
//SGL::raiseMsg('authentication required');
                    SGL_Response::redirect($loginPage);
                } else {
                    $session->destroy();
//SGL::raiseMsg('session timeout');
                    SGL_Response::redirect($loginPage);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}
?>