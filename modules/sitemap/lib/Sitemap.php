<?php

require_once 'Sitemap/Exception.php';

/**
 * Collects URLs for sitemap xml file.
 *
 * @package SGL
 * @subpackage Sitemap
 * @author Laszlo Horvath <pentarim@gmail.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Sitemap
{
    /**
     * XML namespace URL.
     *
     * @var string
     */
    public $xmlns = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * Lisr of URLs.
     *
     * @var array
     */
    public $aUrls = array();

    /**
     * List of available languages. If is it not empty
     * then specified languages will be appended to URLs.
     *
     * @var array
     */
    public $aLanguages = array();

    /**
     * Strategies for collecting URLs.
     *
     * @var array
     */
    private $_aStrategies = array();

    /**
     * Constructor.
     *
     * @return SGL_Sitemap
     */
    public function __construct($xmlns = null)
    {
        if (!empty($xmlns)) {
            $this->xmlns = $xmlns;
        }
    }

    /**
     * Adds sitemap strategy object.
     *
     * @param SGL_Sitemap_Strategy $oStrategy
     */
    public function addStrategy(SGL_Sitemap_Strategy $oStrategy)
    {
    	$this->_aStrategies[] = $oStrategy;
    }

    /**
     * Checks if cacheable version exists, if not then generates sitemap.
     *
     * @param bool $bForceCache
     */
    public function generate($bForceCache = false)
    {
        $cacheId = 'sitemap';
        foreach ($this->_aStrategies as $oStrat) {
        	$cacheId .= '_' . md5(get_class($oStrat));
        }
        $cache = SGL_Cache::singleton($bForceCache);
        if ($data = $cache->get($cacheId, 'sitemap')) {
            $this->aUrls = unserialize($data);
            SGL::logMessage('sitemap from cache', PEAR_LOG_DEBUG);
        } else {
            $this->aUrls = $this->_generate();
            $cache->save(serialize($this->aUrls), $cacheId, 'sitemap');
            SGL::logMessage('sitemap URLs generated', PEAR_LOG_DEBUG);
        }
    }

    /**
     * Generates sitemap by invoking generate method of each added strategy.
     *
     * @return array  sitemap URLs
     */
    protected function _generate()
    {
        $aRet = array();
        foreach ($this->_aStrategies as $oStrategy) {
        	$aUrls = $oStrategy->generate();

        	// if we generate multilingual sitemap, then get links
        	// for other languages than fallback
        	if (!empty($this->aLanguages) && empty($oStrategy->skipMultiLingual)) {
        	    $aLangUrls = array();
                foreach ($this->aLanguages as $lang) {
                    $aCloned   = $this->_cloneUrls($aUrls, "lang/$lang/");
                    $aLangUrls = array_merge($aLangUrls, $aCloned);
                }
                $aUrls = array_merge($aUrls, $aLangUrls);
        	}
        	$aRet = array_merge($aRet, $aUrls);
        }
        return $aRet;
    }

    /**
     * Append $param string to each URL's location.
     *
     * @param array  $aUrls  source URLs
     * @param string $param  query string to append
     *
     * @return array modified URLs
     */
    protected function _cloneUrls($aUrls, $param)
    {
        foreach ($aUrls as &$aUrlDetail) {
            $aUrlDetail['loc'] .= $param;
        }
        return $aUrls;
    }
}

/**
 * Base class of URL harvesting strategies.
 *
 * @package SGL
 * @subpackage Sitemap
 * @author Laszlo Horvath <pentarim@gmail.com>
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
abstract class SGL_Sitemap_Strategy
{
    /**
     * Default change frequency,
     *
     * @var string
     */
    public $changefreq = 'weekly';

    /**
     * Default priority.
     *
     * @var string
     */
    public $priority = '0.5';

    /**
     * Default 'last modified' date,
     *
     * @var string
     */
    public $lastmod = null;

    /**
     * Constructor.
     *
     * @param array $aParams  strategy params
     *
     * @return SGL_Sitemap_Strategy
     */
    function __construct($aParams = array())
    {
        foreach ($aParams as $var => $value) {
            $this->{$var} = $value;
        }
    }

	/**
	 * The urls should be harvested here.
	 *
	 * @return array
	 */
    abstract public function generate();

    /**
     * Returns W3C complaint datetime.
     *
     * @param string $date
     * @param string $tz
     *
     * @return string
     */
    protected function _formatDate($date = null, $tz = null)
    {
        $dt = new DateTime($date);
        if (!is_null($tz)) {
            $dt->setTimezone(new DateTimeZone($tz));
        }
        return $dt->format(DateTime::W3C);
    }
}

?>