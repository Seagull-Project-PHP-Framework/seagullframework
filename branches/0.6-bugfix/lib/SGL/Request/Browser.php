<?php

class SGL_Request_Browser extends SGL_Request
{
    function init()
    {
        //  get config singleton
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        //  resolve value for $_SERVER['PHP_SELF'] based in host
        SGL_URL::resolveServerVars($conf);

        //  get current url object
        $cache = & SGL_Cache::singleton();
        $cacheId = md5($_SERVER['PHP_SELF']);

        if ($data = $cache->get($cacheId, 'uri')) {
            $url = unserialize($data);
            SGL::logMessage('URI from cache', PEAR_LOG_DEBUG);
        } else {
            require_once SGL_CORE_DIR . '/UrlParser/SimpleStrategy.php';
            require_once SGL_CORE_DIR . '/UrlParser/AliasStrategy.php';
            require_once SGL_CORE_DIR . '/UrlParser/ClassicStrategy.php';

            $aStrats = array(
                new SGL_UrlParser_ClassicStrategy(),
                new SGL_UrlParser_AliasStrategy(),
                new SGL_UrlParser_SefStrategy(),
                );
            $url = new SGL_URL($_SERVER['PHP_SELF'], true, $aStrats);

            $err = $url->init();
            if (PEAR::isError($err)) {
                return $err;
            }
            $data = serialize($url);
            $cache->save($data, $cacheId, 'uri');
            SGL::logMessage('URI parsed ####' . $_SERVER['PHP_SELF'] . '####', PEAR_LOG_DEBUG);
        }
        $aQueryData = $url->getQueryData();

        if (PEAR::isError($aQueryData)) {
            return $aQueryData;
        }
        //  assign to registry
        $input = &SGL_Registry::singleton();
        $input->setCurrentUrl($url);

        //  merge REQUEST AND FILES superglobal arrays
        $this->aProps = array_merge($_GET, $_FILES, $aQueryData, $_POST);
        return true;
    }
}
?>
