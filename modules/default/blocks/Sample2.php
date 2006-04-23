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
<p class="alignCenter">
<a href="http://seagullproject.org/export/rss/">
    <img src="$this->webRoot/images/xml.gif" alt="Seagull RSS" title="RSS 1.0" align="absmiddle"/>
</a>
</p>
HTML;
        return $text;
    }
}
?>