<?php

/**
 * Sets generic headers for page generation.
 *
 * Alternatively, headers can be suppressed if specified in module's config.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_BuildHeaders extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        $this->processRequest->process($input, $output);

        SGL::logMessage(null, PEAR_LOG_DEBUG);

        //  don't send headers according to config
        $currentMgr = SGL_Inflector::caseFix(get_class($output->manager));
        $c = &SGL_Config::singleton(); $conf = $c->getAll();
        if (!isset($conf[$currentMgr]['setHeaders'])
                || $conf[$currentMgr]['setHeaders'] == true) {

            //  set compression as specified in init, can only be done here :-)
            @ini_set('zlib.output_compression', (int)SGL_Config::get('site.compression'));

            //  build P3P headers
            if (SGL_Config::get('p3p.policies')) {
                $p3pHeader = '';
                if (SGL_Config::get('p3p.policyLocation')) {
                    $p3pHeader .= " policyref=\"" . SGL_Config::get('p3p.policyLocation')."\"";
                }
                if (SGL_Config::get('p3p.compactPolicy')) {
                    $p3pHeader .= " CP=\"" . SGL_Config::get('p3p.compactPolicy')."\"";
                }
                if ($p3pHeader != '') {
                    header("P3P: $p3pHeader");
                }
            }
            //  prepare headers during setup, can be overridden later
            header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
            header('Content-Type: text/html; charset=' . $GLOBALS['_SGL']['CHARSET']);
            header('X-Powered-By: Seagull http://seagullproject.org');
            foreach ($output->getHeaders() as $header) {
                header($header);
            }
        }
    }
}

?>