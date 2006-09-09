<?php
/**
 * Debug block.
 *
 * @package seagull
 * @subpackage block
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.1 $
 */
class Default_Block_Debug
{
    var $webRoot = SGL_BASE_URL;

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $url = SGL_Output::makeUrl('', 'maintenance', 'default');
        $text = <<< HTML
<form name="rebuildSeagull" method="post" action="$url" id="rebuildSeagull" flexy:ignore>
    <p class="center">
        <input type="hidden" name="action" value="rebuildSeagull" />
        <input type="submit" class="sgl-button"name="submit" value="Rebuild Seagull" />
    </p>
</form>
HTML;
        return $text;
    }
}
?>