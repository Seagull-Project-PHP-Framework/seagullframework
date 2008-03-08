<?php

/**
 * Gathers URLs from ini file.
 *
 * @package SGL
 * @subpackage sitemap
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Sitemap_StaticLinks extends SGL_Sitemap_Strategy
{
    /**
     * Path to ini file with links spec.
     *
     * @var string
     */
    public $path = null;

    /**
     * Constuctor.
     *
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
        parent::__construct($aParams);
        // default location for static links file
        if (empty($this->path)) {
            $this->path = SGL_MOD_DIR . '/sitemap/staticLinks.ini';
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
        $aRet   = array();
        foreach ($aLinks as $aLinkData) {
            if (empty($aLinkData['loc'])) {
                continue;
            }
            $aUrl = array('loc' => $aLinkData['loc']);
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
        return $aRet;
    }
}

?>