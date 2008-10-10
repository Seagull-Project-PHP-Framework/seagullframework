<?php

/**
 * Wrapper for simple HTML views.
 *
 * @package SGL
 */
class SGL_View_HtmlSimple extends SGL_View
{
    /**
     * HTML renderer decorator
     *
     * @param SGL_Response $data
     * @param string $templateEngine
     */
    public function __construct($response, $templateEngine = null)
    {
        //  prepare renderer class
        if (is_null($templateEngine)) {
            $templateEngine = SGL_Config2::get('site.templateEngine');
        }
        $templateEngine =  ucfirst($templateEngine);
        $rendererClass  = 'SGL_HtmlRenderer_' . $templateEngine . 'Strategy';
        $rendererFile   = $templateEngine.'Strategy.php';
        $path =  'SGL/HtmlRenderer/' . $rendererFile;
        parent::__construct($response, new $rendererClass);
    }

    public function postProcess(SGL_View $view)
    {
        // do nothing
    }
}
?>