<?php

/**
 * Sets generic headers for page generation.
 *
 * Alternatively, headers can be suppressed if specified in module's config.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL2_Filter_BuildHeaders extends SGL2_DecorateProcess
{
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        $this->_processRequest->process($input, $output);

        //  set compression as specified in init, can only be done here :-)
        ini_set('zlib.output_compression', (int)SGL2_Config::get('site.compression'));

        //  build P3P headers
        if (SGL2_Config::get('p3p.policies')) {
            $p3pHeader = '';
            if (SGL2_Config::get('p3p.policyLocation')) {
                $p3pHeader .= " policyref=\"" . SGL2_Config::get('p3p.policyLocation')."\"";
            }
            if (SGL2_Config::get('p3p.compactPolicy')) {
                $p3pHeader .= " CP=\"" . SGL2_Config::get('p3p.compactPolicy')."\"";
            }
            if ($p3pHeader != '') {
                $output->addHeader("P3P: $p3pHeader");
            }
        }
        //  prepare headers during setup, can be overridden later
        if (!headers_sent()) {
            header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
            header('Content-Type: text/html; charset=utf-8');
            header('X-Powered-By: Seagull http://seagullproject.org');
            foreach ($output->getHeaders() as $header) {
                header($header);
            }
        }

    }
}

?>