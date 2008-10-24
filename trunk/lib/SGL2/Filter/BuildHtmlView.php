<?php

/**
 * @package Task
 */
class SGL2_Filter_BuildHtmlView extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $this->_processRequest->process($input, $output);

        //  build view
        $view = new SGL2_View_HtmlSimple($output);
        $output->setBody($view->render());
    }
}

?>