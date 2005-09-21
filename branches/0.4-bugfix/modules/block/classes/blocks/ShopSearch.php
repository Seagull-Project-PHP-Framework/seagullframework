<?php
/**
 * Block: Displays search box for Shop module
 *
 * @package block
 * @author  Rares Benea <rbenea@bluestardesign.ro>
 * @version $Revision: 1.2 $
 * @since   PHP 4.1
 */
class ShopSearch
{
	var $webRoot = SGL_BASE_URL;

    function init()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        return $this->getBlockContent();
    }

    function getBlockContent()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $catID = 0;
        if (isset($GLOBALS['_SGL']['REQUEST']['frmCatID'])) {
            $catID = (int) $GLOBALS['_SGL']['REQUEST']['frmCatID'];
        }
        
        // Make select
        require_once SGL_MOD_DIR . '/navigation/classes/MenuBuilder.php';
        $options = array();
        $menu = & new MenuBuilder('SelectBox', $options);
        $menu->setStartId(4);
        $htmlOptions = $menu->toHtml();
        $catSelect = SGL_Output::generateSelect($htmlOptions, $catID);
        $url = SGL_output :: makeUrl ('','shop','shop');
        
        $text = <<< HTML
			<form action="$url" method="get">
			<input type="text" name="keywords" size="18" value="" STYLE="width: 131px;"/>
            <select name="frmCatID" STYLE="width: 130px">
            $catSelect
            </select>
			<input type="submit" name="submit" value="search" style="width:50px;" />
			</form>
HTML;
        return $text;
    }
}
?>
