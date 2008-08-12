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
<script type="text/javascript">
if (typeof async_load == 'undefined') {
   /**
    * Used for async load of sourcefourge bloody button,
    */
    function async_load()
    {
        var node;
        try {
            // variable _asyncDom is set from JavaScript in the iframe
            // node = top._asyncDom.cloneNode(true); // kills Safari 1.2.4
            node = top._asyncDom;
            // try to remove the first script element, the one that
            // executed all document.writes().
            node.removeChild(node.getElementsByTagName('script')[0]);
        } catch (e) {}
        try {
            // insert DOM fragment at a DIV with id "async_demo" on current page
            document.getElementById('async_demo').appendChild(node);
        } catch (e) {
            try {
                // fallback for some non DOM compliant browsers
                document.getElementById('async_demo').innerHTML = node.innerHTML;
             } catch (e2) {};
        }
    }
}
</script>
HTML;
        return $text;
    }
}
?>
