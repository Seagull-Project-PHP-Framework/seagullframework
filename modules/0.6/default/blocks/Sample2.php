<?php
/**
 * Sample block 2.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class Default_Block_Sample2
{
    var $webRoot = SGL_BASE_URL;

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $text = <<< HTML
<p><a href="http://feeds.feedburner.com/seagullproject" title="Subscribe to my feed, Seagull PHP Framework" rel="alternate" type="application/rss+xml">
<img src="http://www.feedburner.com/fb/images/pub/feed-icon16x16.png" alt="" style="border:0"/></a></p>
<p><a href="http://add.my.yahoo.com/rss?url=http://feeds.feedburner.com/seagullproject" title="Seagull PHP Framework"><img src="http://us.i1.yimg.com/us.yimg.com/i/us/my/addtomyyahoo4.gif" alt="" style="border:0"/></a></p>
<p><a href="http://fusion.google.com/add?feedurl=http://feeds.feedburner.com/seagullproject"><img src="http://buttons.googlesyndication.com/fusion/add.gif" width="104" height="17" style="border:0" alt="Add to Google Reader or Homepage"/></a></p>
<p><a href="http://www.newsgator.com/ngs/subscriber/subext.aspx?url=http://feeds.feedburner.com/seagullproject" title="Seagull PHP Framework"><img src="http://www.newsgator.com/images/ngsub1.gif" alt="Subscribe in NewsGator Online" style="border:0"/></a></p>
<p><a href="http://www.netvibes.com/subscribe.php?url=http://feeds.feedburner.com/seagullproject"><img src="http://www.netvibes.com/img/add2netvibes.gif" width="91" height="17" alt="Add to netvibes" style="border:0" /></a></p>
<p><a href="http://www.bloglines.com/sub/http://feeds.feedburner.com/seagullproject" title="Seagull PHP Framework" type="application/rss+xml"><img src="http://www.bloglines.com/images/sub_modern11.gif" alt="Subscribe in Bloglines" style="border:0"/></a></p>
HTML;
        return $text;
    }
}
?>