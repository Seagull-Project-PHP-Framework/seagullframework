<?php

class SGL2_Controller_Front extends SGL2_Controller_Abstract
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

            //  post-process (order: bottom up)
        );
        $target = 'SGL2_MainProcess';
        $chain = new SGL2_FilterChain($aFilters);
        $chain->setTarget($target);
        $chain->doFilter($request, $response);
        echo $response;
    }
}
?>