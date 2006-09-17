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
HTML;
        return $text;
    }
}
?>