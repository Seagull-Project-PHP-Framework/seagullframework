<?php

/**
 * @package Task
 */
class SGL2_Filter_BuildHtmlView extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $this->_processRequest->process($input, $output);

        //  get all html onLoad events and js files
//        $output->onLoad = $output->getOnLoadEvents();
//        $output->onUnload = $output->getOnUnloadEvents();
//        $output->onReadyDom = $output->getOnReadyDomEvents();
//        $output->javascriptSrc = $output->getJavascriptFiles();

        //  build view
        $templateEngine = isset($output->templateEngine)
            ? $output->templateEngine
            : null;
        $view = new SGL2_View_HtmlSimple($output, $templateEngine);
        $output->setBody($view->render());
    }
}

?>