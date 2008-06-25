<?php

/**
 * Wrapper for simple HTML views.
 *
 * @package SGL
 */
class SGL_HtmlSimpleView extends SGL_View
{
    /**
     * HTML renderer decorator
     *
     * @param SGL_Output $data
     * @return string   Rendered output data
     */
    function SGL_HtmlSimpleView(&$data, $templateEngine = null)
    {
        //  prepare renderer class
        if (!$templateEngine) {
            $templateEngine = SGL_Config::get('site.templateEngine');
        }
        $templateEngine = ucfirst($templateEngine);
        $rendererClass  = 'SGL_HtmlRenderer_' . $templateEngine . 'Strategy';
        $rendererFile   = $templateEngine.'Strategy.php';

        if (is_file(SGL_LIB_DIR . '/SGL/HtmlRenderer/' . $rendererFile)) {
            require_once SGL_LIB_DIR . '/SGL/HtmlRenderer/' . $rendererFile;
        } else {
            PEAR::raiseError('Could not find renderer', SGL_ERROR_NOFILE,
                PEAR_ERROR_DIE);
        }
        parent::SGL_View($data, new $rendererClass);
    }
}

?>