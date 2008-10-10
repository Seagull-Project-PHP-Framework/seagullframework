<?php

/**
 * @package Task
 */
class SGL_Filter_BuildHtmlView extends SGL_DecorateProcess
{
    public function process(SGL_Request $input, SGL_Response $output)
    {
        $this->processRequest->process($input, $output);

        //  get all html onLoad events and js files
//        $output->onLoad = $output->getOnLoadEvents();
//        $output->onUnload = $output->getOnUnloadEvents();
//        $output->onReadyDom = $output->getOnReadyDomEvents();
//        $output->javascriptSrc = $output->getJavascriptFiles();

        //  build view
        $templateEngine = isset($output->templateEngine)
            ? $output->templateEngine
            : null;
        $view = new SGL_View_HtmlSimple($output, $templateEngine);
        $output->setBody($view->render());
    }
}

?>