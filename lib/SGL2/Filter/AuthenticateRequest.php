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
class SGL2_Filter_AuthenticateRequest extends SGL2_DecorateProcess
{
    /**
     * Returns 'remember me' cookie data.
     *
     * @return mixed
     */
    protected function _getRememberMeCookieData()
    {
        //  no 'remember me' cookie found
        if (!isset($_COOKIE['SGL2_REMEMBER_ME'])) {
            return false;
        }
        $cookie = $_COOKIE['SGL2_REMEMBER_ME'];
        list($username, $cookieValue) = @unserialize($cookie);
        //  wrong cookie value was saved
        if (!$username || !$cookieValue) {
            return false;
        }
        //  get UID by cookie value
        require_once SGL2_MOD_DIR . '/user/classes/UserDAO.php';
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
        SGL2_Registry::set('session', new SGL2_Session($uid, $rememberMe = true));
    }

    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        // check for timeout
        $session = SGL2_Registry::get('session');
        $timeout = !$session->updateIdle();

        //  store request in session
        $aRequestHistory = SGL2_Session::get('aRequestHistory');
        if (empty($aRequestHistory)) {
            $aRequestHistory = array();
        }
        array_unshift($aRequestHistory, $input->getClean());
        $aTruncated = array_slice($aRequestHistory, 0, 2);
        SGL2_Session::set('aRequestHistory', $aTruncated);

        $ctlr = SGL2_Registry::get('controller');
        $ctlrName = get_class($ctlr);

        //  test for anonymous session and rememberMe cookie
        if (($session->isAnonymous() || $timeout)
                && SGL2_Config::get('cookie.rememberMeEnabled')
                && !SGL2_Config::get('site.maintenanceMode')) {
            $aCookieData = $this->_getRememberMeCookieData();
            if (!empty($aCookieData['uid'])) {
                $this->_doLogin($aCookieData['uid']);

                //  session data updated
#FIXME - what's going on here, 2nd invocation
                $session = SGL2_Registry::get('session');
                $timeout = !$session->updateIdle();
            }
        }
        //  if page requires authentication and we're not debugging
        if (   SGL2_Config::get("$ctlrName.requiresAuth")
            && SGL2_Config::get('debug.authorisationEnabled')
            && $input->getType() != SGL2_Request::CLI)
        {
            //  check that session is valid or timed out
            if (!$session->isValid() || $timeout) {

                //  prepare referer info for redirect after login
                $url = SGL2_Registry::get('url');
                $redir = $url->toString();
                $loginPage = array(
                    'moduleName'    => 'user',
                    'managerName'   => 'login',
                    'redir'         => base64_encode($redir)
                    );

                if (!$session->isValid()) {
//SGL::raiseMsg('authentication required');
                    SGL2_Response::redirect($loginPage);
                } else {
                    $session->destroy();
//SGL::raiseMsg('session timeout');
                    SGL2_Response::redirect($loginPage);
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}
?>