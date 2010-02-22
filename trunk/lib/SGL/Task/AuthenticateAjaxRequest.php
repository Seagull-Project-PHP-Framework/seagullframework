<?php

/**
 * Ajax request authentication.
 *
 * @package Task
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Task_AuthenticateAjaxRequest extends SGL_Task_AuthenticateRequest
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $session = $input->get('session');
        $timeout = !$session->updateIdle();

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
        $req = $input->getRequest();
        $actionName = $req->getActionName();
        $providerContainer = ucfirst($req->getModuleName()) . 'AjaxProvider';

        //  or if page requires authentication and we're not debugging
        if (SGL_Config::get("$providerContainer.requiresAuth")
                && SGL_Config::get('debug.authorisationEnabled')) {
            $aMethods = explode(',', SGL_Config::get("$providerContainer.requiresAuth"));
            $aMethods = array_map('trim', $aMethods);
            if (in_array($actionName, $aMethods)) {
                //  check that session is valid
                if (!$session->isValid()) {
                    SGL::raiseError('authentication required', SGL_ERROR_INVALIDSESSION);
                //  or timed out
                } elseif ($timeout) {
                    $session->destroy();
                    SGL::raiseError('session timeout', SGL_ERROR_INVALIDSESSION);
                }
            }
        }
        $this->processRequest->process($input, $output);
    }
}

?>