<?php

require_once SGL_MOD_DIR . '/navigation/classes/ArrayDriver.php';

/**
 * ArrayNavigation strategy. Gathers URLs from Array navigation.
 *
 * @package SGL
 * @subpackage sitemap
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Sitemap_ArrayNavigation extends SGL_Sitemap_Strategy
{
    /**
     * Root node.
     *
     * @var integer
     */
    public $rootNode = SGL_NODE_USER;

    /**
     * Constuctor.
     *
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
        parent::__construct($aParams);
    }

    /**
     * Return array of URL details.
     *
     * @return array
     */
    public function generate()
    {
        // get all possible URLs
        $oDriver = ArrayDriver::singleton($output = new stdClass());
        $oDriver->setParams($this->rootNode);
        $aNav = $oDriver->render('UrlCollection');

        $aUrls = !empty($aNav) ? $aNav[1] : array();
        $aRet  = array();
        foreach ($aUrls as $url) {
            $aUrl = array('loc' => $url);
            if (!empty($this->changefreq)) {
                $aUrl['changefreq'] = $this->changefreq;
            }
            if (!empty($this->priority)) {
                $aUrl['priority'] = $this->priority;
            }
            if (!empty($this->lastmod)) {
                $aUrl['lastmod'] = $this->_formatDate($this->lastmod);
            }
            $aRet[] = $aUrl;
        }
        return $aRet;
    }
}

?>