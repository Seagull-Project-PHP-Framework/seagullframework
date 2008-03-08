<?php

/**
 * Gathers URLs from feeds.
 *
 * @package SGL
 * @subpackage sitemap
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Sitemap_FeedReader extends SGL_Sitemap_Strategy
{
    /**
     * Path to ini file with links spec.
     *
     * @var string
     */
    public $path = null;

    /**
     * Force cache.
     *
     * @var boolean
     */
    public $forceCache = false;

    /**
     * Constuctor.
     *
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
        parent::__construct($aParams);
        // default file location
        if (empty($this->path)) {
            $this->path = SGL_MOD_DIR . '/sitemap/feeds.ini';
        } else {
            $this->path = SGL_APP_ROOT . $this->path;
        }
    }

    /**
     * Return array of URL details.
     *
     * @return array
     */
    public function generate()
    {
        if (!is_readable($this->path)) {
            $msg = 'file \'%s\' is not readable';
            throw new SGL_Sitemap_Exception(sprintf($msg, $this->path));
        }
        $aLinks = parse_ini_file($this->path, true);

        // generate ID
        $key = '';
        foreach ($aLinks as $aData) {
            $key .= '_' . md5(implode('_', $aData));
        }
        $cacheId = 'sitemap_feedreader' . $key;
        $cache = SGL_Cache::singleton((boolean) $this->forceCache);
        $aRet  = array();
        if ($data = $cache->get($cacheId, 'sitemap')) {
            $aRet = unserialize($data);
        } else {
            require_once 'XML/RSS.php';
            foreach ($aLinks as $aLinkData) {
                if (empty($aLinkData['loc'])) {
                    continue;
                }
                $rss = new XML_RSS($aLinkData['loc']);
                $ok = $rss->parse();
                // skip feed on error
                if (PEAR::isError($ok)) {
                    $msg = sprintf('feed parsing error: %s', $ok->getMessage());
                    SGL::logMessage($msg, PEAR_LOG_ERR);
                    continue;
                }
                $aItems = $rss->getItems();
                foreach ($aItems as $aItem) {
                    $aUrl = array('loc' => $aItem['link']);
                    if (isset($aLinkData['changefreq'])) {
                        $aUrl['changefreq'] = $aLinkData['changefreq'];
                    } elseif (!empty($this->changefreq)) {
                        $aUrl['changefreq'] = $this->changefreq;
                    }
                    if (isset($aLinkData['priority'])) {
                        $aUrl['priority'] = $aLinkData['priority'];
                    } elseif (!empty($this->priority)) {
                        $aUrl['priority'] = $this->priority;
                    }
                    if (isset($aLinkData['lastmod'])) {
                        $aUrl['lastmod'] = $this->_formatDate($aLinkData['lastmod']);
                    } elseif (!empty($this->lastmod)) {
                        $aUrl['lastmod'] = $this->_formatDate($this->lastmod);
                    }
                    $aRet[] = $aUrl;
                }
                unset($rss);
            }
            $ok = $cache->save(serialize($aRet), $cacheId, 'sitemap');
        }
        return $aRet;
    }
}
?>