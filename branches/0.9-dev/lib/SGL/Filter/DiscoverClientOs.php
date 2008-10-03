<?php

/**
 * Set client OS constant based on user agent.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Filter_DiscoverClientOs extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $ua = '';
        }
        if (!empty($ua) and !defined('SGL_CLIENT_OS')) {
            if (strstr($ua, 'Win')) {
                define('SGL_CLIENT_OS', 'Win');
            } elseif (strstr($ua, 'Mac')) {
                define('SGL_CLIENT_OS', 'Mac');
            } elseif (strstr($ua, 'Linux')) {
                define('SGL_CLIENT_OS', 'Linux');
            } elseif (strstr($ua, 'Unix')) {
                define('SGL_CLIENT_OS', 'Unix');
            } elseif (strstr($ua, 'OS/2')) {
                define('SGL_CLIENT_OS', 'OS/2');
            } else {
                define('SGL_CLIENT_OS', 'Other');
            }
        } else {
            if (!defined('SGL_CLIENT_OS')) {
                define('SGL_CLIENT_OS', 'None');
            }
        }
        $this->processRequest->process($input, $output);
    }
}

?>