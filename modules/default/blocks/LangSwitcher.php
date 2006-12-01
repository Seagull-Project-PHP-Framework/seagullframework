<?php
/**
 * This block allows you to switch language.
 *
 * @package block
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 */
class Default_Block_LangSwitcher
{
    function init()
    {
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        $aLangs  = SGL_Util::getLangsDescriptionMap();
        $options = SGL_Output::generateSelect($aLangs, $_SESSION['aPrefs']['language']);

        $html = <<< HTML
        <form id="langSwitcher" action="" method="post">
            <select name="lang" onChange="document.getElementById('langSwitcher').submit()">
                $options
            </select>
        </form>
HTML;
        return $html;
    }
}
?>