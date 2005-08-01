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
        $text = <<< NEWS
        <br /><br /><br />
        your block here
        <br /><br /><br />
NEWS;
        return $text;
    }
}
?>