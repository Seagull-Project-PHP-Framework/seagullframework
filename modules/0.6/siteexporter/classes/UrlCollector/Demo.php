<?php

/**
 * Collects demo URLs.
 *
 * @package seagull
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_UrlCollector_Demo
{
    public function generate()
    {
        $aUrls[] = 'user/login';
        $aUrls[] = 'user/password';
        $aUrls[] = 'user/register';

        return $aUrls;
    }
}

?>
