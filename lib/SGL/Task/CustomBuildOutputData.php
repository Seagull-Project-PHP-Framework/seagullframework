<?php
/**
 * Minimal output setup.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_CustomBuildOutputData extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);
        $output->theme      = SGL_Config::get('site.defaultTheme');
        $output->webRoot    = SGL_BASE_URL;
        $output->imagesDir  = SGL_BASE_URL . '/themes/' . $output->theme . '/images';
        $c = &SGL_Config::singleton();
        $output->conf       = $c->getAll();
    }
}
?>