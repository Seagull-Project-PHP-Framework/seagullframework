<?php

class OnlineUsers
{
    var $template     = 'OnlineUsers.html';
    var $templatePath = 'block/blocks';

    function init(&$output, $block_id)
    {
        return $this->getBlockContent($output);
    }

    function getBlockContent(&$output)
    { 
        $blockOutput = new SGL_Output();

        // prepare content
        $blockOutput->guests  = SGL_HTTP_Session::getGuestSessionCount();
        $blockOutput->members = SGL_HTTP_Session::getMemberSessionCount();
        $blockOutput->total   = $blockOutput->members + $blockOutput->guests;

        // set theme name
        $blockOutput->theme   = $output->theme;

        return $this->process($blockOutput);
    }

    function process(&$output)
    {        
        // use moduleName for template path setting
        $output->moduleName     = $this->templatePath;
        $output->masterTemplate = $this->template;

        // get template engine type
        $c              = &SGL_Config::singleton();
        $conf           = $c->getAll();
        $templateEngine = ucfirst($conf['site']['templateEngine']);
        $rendererClass  = 'SGL_Html' . $templateEngine . 'RendererStrategy';

        // render content
        $view = new SGL_View($output, new $rendererClass());
        return $view->render();    
    }
}
?>