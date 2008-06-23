<?php

/**
 * @package Task
 */
class SGL_Task_BuildView extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  get all html onLoad events and js files
        $output->onLoad = $output->getOnLoadEvents();
        $output->onUnload = $output->getOnUnloadEvents();
        $output->onReadyDom = $output->getOnReadyDomEvents();
        $output->javascriptSrc = $output->getJavascriptFiles();

        //  unset unnecessary objects
        unset($output->currentUrl);
        unset($output->manager->conf);
        unset($output->manager->dbh);

        //  build view
        $templateEngine = isset($output->templateEngine) ? $output->templateEngine : null;
        $view = new SGL_HtmlSimpleView($output, $templateEngine);
        $output->data = $view->render();
    }
}

?>