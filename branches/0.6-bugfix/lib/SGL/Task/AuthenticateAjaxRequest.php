<?php

/**
 * Ajax request authentication.
 *
 * @package SGL
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Task_AuthenticateAjaxRequest extends SGL_Task_AuthenticateRequest
{
    function process(&$input, &$output)
    {
        $session = $input->get('session');
        $timeout = !$session->updateIdle();

        //  test for anonymous session and rememberMe cookie
        if ($session->isAnonymous()
                && SGL_Config::get('cookie.rememberMeEnabled')) {
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
                    $input->ajaxRequestMessage = array(
                        'type'    => 'error',
                        'message' => SGL_Output::translate('authentication required')
                    );
                //  or timed out
                } elseif ($timeout) {
                    $session->destroy();
                    $input->ajaxRequestMessage = array(
                        'type'    => 'error',
                        'message' => SGL_Output::translate('session timeout')
                    );
                }
            }
        }

        $this->processRequest->process($input, $output);
    }
}

?>