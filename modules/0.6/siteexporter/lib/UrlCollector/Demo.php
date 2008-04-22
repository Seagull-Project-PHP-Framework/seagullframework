<?php

/**
 * Collects demo URLs.
 *
 * @package SGL
 * @subpackage UrlCollector
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_UrlCollector_Demo
{
    /**
     * Params.
     *
     * @var array
     */
    private $_aParams = array();

    function __construct($aParams = array())
    {
        $this->_aParams = array_merge($this->_aParams, $aParams);
    }

    public function generate()
    {
        $aUrls[] = 'user/login';
        $aUrls[] = 'user/password';
        $aUrls[] = 'user/register';

        return $aUrls;
    }
}

?>