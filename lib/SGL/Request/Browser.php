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
            $aStratsToLoad = array();
            if (isset($conf['site']['inputUrlHandlers'])) {
                $aStratsToLoad = explode(',', $conf['site']['inputUrlHandlers']);
                $aStratsToLoad = array_filter($aStratsToLoad);
            }

            if (!count($aStratsToLoad)) {
                $aStratsToLoad = $this->getDefaultUrlParsingStrategies();
            }
            foreach ($aStratsToLoad as $strat) {
                $strat = trim($strat);
                $className = 'SGL_UrlParser_'.$strat.'Strategy';
                if (!class_exists($className)) {
                    require_once SGL_CORE_DIR . '/UrlParser/'.$strat.'Strategy.php';
                }
                $aStrats[] = new $className();
            }
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

        $this->type = SGL_REQUEST_BROWSER;
        return true;
    }

    /**
     * Returns array of default strategies.
     *
     * These parsing strategies are used for default Seagull URL handling, to parse
     * standard urls (classic), URL aliases (routes) and Sef (internal) URLs.
     *
     * @return array
     */
    function getDefaultUrlParsingStrategies()
    {
        return array('Classic', 'Alias', 'Sef');
    }
}
?>
