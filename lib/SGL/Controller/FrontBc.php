<?php

class SGL_Controller_FrontBc extends SGL_Controller_Abstract
{
    public function run()
    {
        if (!defined('SGL_INITIALISED')) {
            $this->init();
        }
        $request  = SGL_Registry::get('request');
        $response = SGL_Registry::get('response');

        $router = $this->getRouter();
        $router->route($request);

        $aFilters = array(
            //  pre-process (order: top down)
           //'SGL_Filter_LoadManager',
           'SGL_Filter_LoadController',
           'SGL_Filter_CreateSession',
           'SGL_Filter_SetupLangSupport2',
           'SGL_Filter_SetupLocale',
           'SGL_Filter_AuthenticateRequest',

            //  post-process (order: bottom up)
            'SGL_Filter_BuildHeaders',
            'SGL_Filter_BuildHtmlView',
//                'SGL_Filter_SetupGui',
            'SGL_Filter_BuildOutputData',
        );
        //$target = 'SGL_MainProcessBc';
        $target = 'SGL_Controller_Main';
        $chain = new SGL_FilterChain($aFilters);
        $chain->setTarget($target);
        $chain->doFilter($request, $response);
        echo $response;
    }
}
?>