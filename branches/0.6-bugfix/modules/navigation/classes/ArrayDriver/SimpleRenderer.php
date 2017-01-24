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
     * Rendering params.
     *
     * @access private
     *
     * @var array
     */
    var $_aParams = array();

    /**
     * Constructor.
     *
     * @access public
     *
     * @param array $aParams
     *
     * @return ArrayDriver_SimpleRenderer
     */
    function ArrayDriver_SimpleRenderer($aParams)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->_aParams = $aParams;
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
            $menu->forceCurrentIndex($this->_aParams['currentIndex']);
            $menu->setUrlPrefix('');

            // render
            $menu->render($renderer, $this->_aParams['menuType']);
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
        if (is_a($renderer, 'HTML_Menu_DirectTreeRenderer')) {
            $renderer->setItemTemplate('', '</li>');
            $renderer->setLevelTemplate('<ul>', '</ul>');
            $renderer->setEntryTemplate(array(
                HTML_MENU_ENTRY_INACTIVE    => '<li><a href="{url}">{title}</a>',
                HTML_MENU_ENTRY_ACTIVE      => '<li class="current"><a href="{url}">{title}</a>',
                HTML_MENU_ENTRY_ACTIVEPATH  => '<li class="current"><a href="{url}">{title}</a>'
            ));
        } elseif (is_a($renderer, 'HTML_Menu_DirectRenderer')) {
            $renderer->setEntryTemplate(array(
                HTML_MENU_ENTRY_ACTIVE      => '<a href="{url}">{title}</a> &gt; ',
                HTML_MENU_ENTRY_BREADCRUMB  => '<a href="{url}">{title}</a> &gt; ',
                /*
                HTML_MENU_ENTRY_INACTIVE    => '<td>{indent}<a href="{url}">{title}</a></td>',
                HTML_MENU_ENTRY_ACTIVEPATH  => '<td>{indent}<b><a href="{url}">{title}</a></b></td>',
                HTML_MENU_ENTRY_PREVIOUS    => '<td><a href="{url}">&lt;&lt; {title}</a></td>',
                HTML_MENU_ENTRY_NEXT        => '<td><a href="{url}">{title} &gt;&gt;</a></td>',
                HTML_MENU_ENTRY_UPPER       => '<td><a href="{url}">^ {title} ^</a></td>',
                */
            ));
            $renderer->setRowTemplate('', '');
            $renderer->setMenuTemplate('', '');
        }
    }
}
?>