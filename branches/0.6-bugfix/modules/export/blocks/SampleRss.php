<?php
/**
 * A block to dislay an RSS feed.
 *
 * @package export
 * @author  Demian Turner <demian@phpkitchen.com>
 */

require_once "XML/RSS.php";
define('SGL_RSS_ITEMS_TO_SHOW', 5);

class Export_Block_SampleRss
{
    var $rssSource = 'http://seagullproject.org/export/rss/';

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        if (ini_get('safe_mode')) {
            return 'Cannot request remote feed in safe_mode ;-(';
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();

        $cache = & SGL_Cache::singleton($force = true);
        if ($data = $cache->get('sglSiteRss', 'blocks')) {
            $html = unserialize($data);
            SGL::logMessage('rss from cache', PEAR_LOG_DEBUG);
        } else {
            $rss =& new XML_RSS($this->rssSource);
            $rss->parse();

            $html = "<ul class='noindent'>\n";
            $x = 0;
            foreach ($rss->getItems() as $item) {
                $html .= "<li><a href=\"" . $item['link'] . "\">" . $item['title'] . "</a></li>\n";
                $x ++;
                if ($x > SGL_RSS_ITEMS_TO_SHOW) {
                    break;
                }
            }
            $html .= "</ul>\n";
            $cache->save(serialize($html), 'sglSiteRss', 'blocks');
            SGL::logMessage('rss from remote', PEAR_LOG_DEBUG);
        }
        return $html;
    }
}
?>