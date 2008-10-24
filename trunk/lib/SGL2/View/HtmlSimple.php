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

        // setup page data
        $ctlr = SGL2_Registry::get('controller');
        if (!isset($response->layout)) {
            $response->layout = $ctlr->getLayout();
        }
        if (!isset($response->template)) {
            $response->template = $ctlr->getTemplate();
        }
        if (!isset($response->pageTitle)) {
            $response->pageTitle = $ctlr->getPageTitle();
        }

        //  get all html onLoad events and js files
//        $response->onLoad = $response->getOnLoadEvents();
//        $response->onUnload = $response->getOnUnloadEvents();
//        $response->onReadyDom = $response->getOnReadyDomEvents();
//        $response->javascriptSrc = $response->getJavascriptFiles();

        parent::__construct($response, new $rendererClass);
    }

    public function postProcess(SGL2_View $view)
    {
        // do nothing
    }
}
?>