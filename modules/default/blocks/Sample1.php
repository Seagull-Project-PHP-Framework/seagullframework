<?php
/**
 * Sample block 1.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.1 $
 */
class Default_Block_Sample1
{
    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $baseUrl = SGL_BASE_URL;
        $text = <<< HTML
<div class="alignCenter">
<iframe width="1" height="1" marginwidth="0" marginheight="0"
        id="async_frame" style="float:left;" frameborder="0" scrolling="no"
        src="{$baseUrl}/iframe.html" onload="async_load();">.</iframe>
<div id="async_demo">&#xA0;</div>
<img src="{$baseUrl}/images/seagull.png" alt="Seagull PHP Framework" />
<img src="{$baseUrl}/images/sgl-framework.png" alt="Seagull PHP Framework" />
</div>
HTML;
        return $text;
    }
}
?>
