<?php
/**
 * This block allows you to switch language.
 *
 * @package block
 * @author  Andrey Podshivalov <planetaz@gmail.com>
 * @version $Revision: 1.0 $
 * @since   PHP 4.4.2
 */
class LangSwitcher
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