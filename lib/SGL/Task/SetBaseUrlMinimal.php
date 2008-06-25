<?php

/**
 * @package Task
 */
class SGL_Task_SetBaseUrlMinimal extends SGL_Task
{
    function run($data = array())
    {
        // no data if we're running installer
        if (!defined('SGL_INSTALLED')) {
            $conf = array(
                'site' =>   array(
                    'setup' => true,
                    'frontScriptName' => 'index.php',
                    'defaultModule' => 'default',
                    'defaultManager' => 'default',
                    ),
                'cookie' => array('name' => ''),
                );
            //  create default config values for install
            $c = &SGL_Config::singleton($autoLoad = false);
            $c->merge($conf);
        }
        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars();

        $url = new SGL_URL($_SERVER['PHP_SELF'], true, new SGL_UrlParser_SefStrategy());
        $err = $url->init();
        define('SGL_BASE_URL', $url->getBase());
    }
}

?>