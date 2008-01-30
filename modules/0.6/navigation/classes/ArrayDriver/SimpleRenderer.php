<?php

/**
 * SimpleRenderer for ArrayDriver.
 *
 * @package seagull
 * @subpackage navigation
 * @author Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class ArrayDriver_SimpleRenderer
{
    /**
     * Index of current node.
     *
     * @var integer
     */
    var $currentIndex;

    /**
     * Rendering type.
     *
     * @var string
     */
    var $type;

    /**
     * Constructor.
     *
     * @access public
     *
     * @return ArrayDriver_SimpleRenderer
     */
    function ArrayDriver_SimpleRenderer()
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);
    }

    /**
     * Render sections.
     *
     * @access public
     *
     * @param array $aSections
     * @param string $renderer
     *
     * @return string
     */
    function toHtml($aSections, $renderer = 'DirectTreeRenderer')
    {
        $fileNameDriver   = SGL_LIB_PEAR_DIR . '/HTML/Menu.php';
        $fileNameRenderer = SGL_LIB_PEAR_DIR . '/HTML/Menu/' . $renderer . '.php';
        if (!file_exists($fileNameDriver) || !file_exists($fileNameRenderer)) {
            $msg = sprintf('%s: PEAR::HTML_Menu package/renderer not found', __CLASS__);
            $ret = SGL::raiseError($msg);
        } else {
            require_once $fileNameDriver;
            require_once $fileNameRenderer;

            // init renderer
            $rendererClassName = 'HTML_Menu_' . $renderer;
            $renderer = & new $rendererClassName();
            $this->_prepare($renderer);

            // init driver
            $menu = & new HTML_Menu($aSections);
            $menu->forceCurrentIndex($this->currentIndex);
            $menu->setUrlPrefix('');

            // render
            $menu->render($renderer, $this->type);
            $ret = $renderer->toHtml();
        }
        return $ret;
    }

    /**
     * Prepare renderer's entry templates.
     *
     * @access private
     *
     * @param HTML_Menu_Renderer $renderer
     */
    function _prepare(&$renderer)
    {
        $renderer->setItemTemplate('', '</li>');
        $renderer->setLevelTemplate('<ul>', '</ul>');
        $renderer->setEntryTemplate(array(
            HTML_MENU_ENTRY_INACTIVE    => '<li><a href="{url}">{title}</a>',
            HTML_MENU_ENTRY_ACTIVE      => '<li class="current"><a href="{url}">{title}</a>',
            HTML_MENU_ENTRY_ACTIVEPATH  => '<li class="current"><a href="{url}">{title}</a>'
        ));
    }
}

?>