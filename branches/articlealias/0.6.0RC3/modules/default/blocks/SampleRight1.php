<?php
/**
 * Sample right block.
 *
 * @package block
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class Default_Block_SampleRight1
{
    var $webRoot = SGL_BASE_URL;

    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $theme = $_SESSION['aPrefs']['theme'];
        $text = <<< EOF
        <img class="blocksAvatar" src="$this->webRoot/themes/$theme/images/helpdesk.jpg" alt="helpdesk" />
        Dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh
        euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut
        wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper
        suscipit lobortis nisl ut aliquip ex ea commodo consequat.
EOF;
        return $text;
    }
}
?>
