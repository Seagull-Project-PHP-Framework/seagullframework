<?php
/**
 * A block to dislay an RSS feed.
 *
 * @package export
 * @author  Demian Turner <demian@phpkitchen.com>
 * @author  Werner M. Krauss <werner@seagullproject.org>
 */

class Export_Block_ShowRss
{

    function init(&$output, $block_id, &$aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        
        return $this->getBlockContent($output, $block_id, $aParams);
    }

    function getBlockContent(&$output, $block_id, &$aParams)
    {
        if (ini_get('safe_mode') || !ini_get('allow_url_fopen')) {
            return 'Cannot request remote feed with safe_mode on or allow_url_fopen off';
        }
        $c = &SGL_Config::singleton();
        $conf = $c->getAll();
        
        //  set block params
        if (array_key_exists('rssSource', $aParams)) {
            $rssSource = $aParams['rssSource'];
        } else {
            return false;
        }
        
        $itemsToShow = (array_key_exists('itemsToShow', $aParams))
            ? $aParams['itemsToShow']
            : 5;


        $cache = & SGL_Cache::singleton($force = true);
        if ($data = $cache->get('sglSiteRss'.$block_id, 'blocks')) {
            $html = unserialize($data);
            SGL::logMessage('rss from cache', PEAR_LOG_DEBUG);
        } else {
            require_once "XML/RSS.php";
            $rss =& new XML_RSS($rssSource);
            $rss->parse();

            $html = "<ul class='noindent'>\n";
            $x = 0;
            foreach ($rss->getItems() as $item) {
                $html .= "<li><a href=\"" . $item['link'] . "\">" . $item['title'] . "</a></li>\n";
                $x ++;
                if ($x >= $itemsToShow) {
                    break;
                }
            }
            $html .= "</ul>\n";
            $cache->save(serialize($html), 'sglSiteRss'.$block_id, 'blocks');
            SGL::logMessage('rss from remote', PEAR_LOG_DEBUG);
        }
        return $html;
    }
}
?>