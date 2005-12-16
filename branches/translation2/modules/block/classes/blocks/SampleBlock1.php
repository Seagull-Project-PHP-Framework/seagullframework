<?php
/**
 * Sample block 1.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class SampleBlock1
{
    function SampleBlock1()
    {
    }

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $text = <<< HTML
<p style="text-align: center;">
<a href="https://sourceforge.net/projects/seagull/"><img src="http://sourceforge.net/sflogo.php?group_id=92482&amp;type=1" width="88" height="31" alt="SourceForge.net Logo" /></a>
</p>
HTML;
        return $text;
    }
}
?>