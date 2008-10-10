<?php

/**
 * Wrapper for simple HTML views.
 *
 * @package SGL
 */
class SGL2_View_HtmlSimple extends SGL2_View
{
    /**
     * HTML renderer decorator
     *
     * @param SGL2_Response $data
     * @param string $templateEngine
     */
    public function __construct($response, $templateEngine = null)
    {
        //  prepare renderer class
        if (is_null($templateEngine)) {
            $templateEngine = SGL2_Config::get('site.templateEngine');
        }
        $templateEngine =  ucfirst($templateEngine);
        $rendererClass  = 'SGL2_HtmlRenderer_' . $templateEngine . 'Strategy';
        $rendererFile   = $templateEngine.'Strategy.php';
        $path =  'SGL/HtmlRenderer/' . $rendererFile;
        parent::__construct($response, new $rendererClass);
    }

    public function postProcess(SGL2_View $view)
    {
        // do nothing
    }
}
?>