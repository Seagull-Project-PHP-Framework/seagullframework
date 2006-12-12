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
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $this->processRequest->process($input, $output);

        $output->theme      = $this->conf['site']['defaultTheme'];
        $output->webRoot    = SGL_BASE_URL;
        $output->imagesDir  = SGL_BASE_URL . '/themes/' . $output->theme . '/images';
        $output->conf       = $this->conf;
    }
}
?>