<?php

class User_Block_OnlineUsers
{
    var $template     = 'OnlineUsers.html';
    var $templatePath = 'user';

    function init(&$output, $block_id)
    {
        return $this->getBlockContent($output);
    }

    function getBlockContent(&$output)
    {
        $blockOutput = new SGL_Output();

        // prepare content
        $blockOutput->guests  = SGL_Session::getGuestSessionCount();
        $blockOutput->members = SGL_Session::getMemberSessionCount();
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

        $view = new SGL_HtmlSimpleView($output);
        return $view->render();
    }
}
?>