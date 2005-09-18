<?php
/**
 * A block to dislay an RSS feed.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 */

require_once "XML/RSS.php";

class SampleRss
{
    var $rssSource = 'http://rss.gmane.org/messages/excerpts/gmane.comp.php.seagull.general';
    function init()
    {   
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $conf = & $GLOBALS['_SGL']['CONF']; 
        
        $cache = & SGL::cacheSingleton();
        if ($data = $cache->get('mailingListRss', 'blocks')) {
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
                if ($x > 9) {
                    break;   
                }
            }
            $html .= "</ul>\n";
            $cache->save(serialize($html), 'mailingListRss', 'blocks');
            SGL::logMessage('rss from remote', PEAR_LOG_DEBUG);
        }
        return $html;
    }
}
?>