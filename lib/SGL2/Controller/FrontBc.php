<?php

/**
 * @todo move to BC plugin
 *
 */
class SGL2_Controller_FrontBc extends SGL2_Controller_Abstract
{
    public function run()
    {
        if (!defined('SGL2_INITIALISED')) {
            $this->init();
        }
        $request  = SGL2_Registry::get('request');
        $response = SGL2_Registry::get('response');

        $router = $this->getRouter();
        $router->route($request);

        $aFilters = array(
            //  pre-process (order: top down)
           'SGL2_Filter_LoadManager',
           'SGL2_Filter_CreateSession',
           'SGL2_Filter_SetupLangSupport',
           'SGL2_Filter_SetupLocale',
           'SGL2_Filter_AuthenticateRequest',

            //  post-process (order: bottom up)
            'SGL2_Filter_BuildHeaders',
            'SGL2_Filter_BuildHtmlView',
//                'SGL2_Filter_SetupGui',
            'SGL2_Filter_BuildOutputData',
        );
        $target = 'SGL2_Controller_MainBc';
        $chain = new SGL2_FilterChain($aFilters);
        $chain->setTarget($target);
        $chain->doFilter($request, $response);
        echo $response;
    }
}
?>