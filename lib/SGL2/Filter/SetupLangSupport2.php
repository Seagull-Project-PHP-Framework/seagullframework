<?php

/**
 * Resolve current language and put in current user preferences.
 * Load relevant language translation file.
 *
 * @package Task
 * @author Julien Casanova <julien@soluo.fr>
 */

class SGL_Filter_SetupLangSupport2 extends SGL_DecorateProcess
{
    /**
     * Initialises multi-language support.
     *
     * langCodeCharset still set in prefs for BC, ie
     *  $_SESSION[aPrefs][language] => es-utf-8
     *
     * @param SGL_Request $input
     * @param SGL_Response $output
     */
    public function process(SGL_Request $input, SGL_Response $output)
    {
        //  sets default language for framework, checks for lang param used to set
        //  user lang
        $trans = SGL_Translation3::singleton('array');
        try {
            $trans->loadDefaultDictionaries();
        } catch (Exception $e) {
            throw new Exception($e);
        }
        // save language in settings
        $_SESSION['aPrefs']['language'] = $trans->langCodeCharset;

        // continue chain execution
        $this->processRequest->process($input, $output);
    }
}
?>