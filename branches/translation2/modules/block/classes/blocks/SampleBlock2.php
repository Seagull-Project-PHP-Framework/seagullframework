<?php
/**
 * Sample block 2.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 * @version $Revision: 1.1 $
 * @since   PHP 4.1
 */
class SampleBlock2
{
    function SampleBlock2()
    {
    }

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $text = <<< NEWS
        <br /><br /><br />
        your block here
        <br /><br /><br />
NEWS;
        return $text;
    }
}
?>