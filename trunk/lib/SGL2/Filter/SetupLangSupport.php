<?php

/**
 * Resolve current language and put in current user preferences.
 * Load relevant language translation file.
 *
 * @package Task
 * @author Julien Casanova <julien@soluo.fr>
 */

class SGL2_Filter_SetupLangSupport extends SGL2_DecorateProcess
{
    /**
     * Initialises multi-language support.
     *
     * langCodeCharset still set in prefs for BC, ie
     *  $_SESSION[aPrefs][language] => es-utf-8
     *
     * @param SGL2_Request $input
     * @param SGL2_Response $output
     */
    public function process(SGL2_Request $input, SGL2_Response $output)
    {
        //  sets default language for framework, checks for lang param used to set
        //  user lang
        $trans = SGL2_Translation::singleton('array');
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