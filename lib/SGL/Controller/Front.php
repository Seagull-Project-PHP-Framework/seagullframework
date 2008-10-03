<?php

class SGL_Controller_Front extends SGL_Controller_Abstract
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

            //  post-process (order: bottom up)
        );
        $target = 'SGL_MainProcess';
        $chain = new SGL_FilterChain($aFilters);
        $chain->setTarget($target);
        $chain->doFilter($request, $response);
        echo $response;
    }
}
?>