<?php
/**
 * Add extra languages (in config) to global language array.
 *
 * Add this to the filter chain after SGL_Task_CreateSession.
 *
 * @package Task
 * @author  Dmitri Lakachauskis <lakiboy83@gmail.com>
 */
class SGL_Task_SetupExtraLanguages extends SGL_DecorateProcess
{
    /**
     * Task processing routine.
     *
     * @access public
     *
     * @param SGL_Registry $input
     * @param SGL_Output $output
     */
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        // only setup extra languages in admin mode
        if (SGL_Session::get('adminMode')
                // or when not in production
                || !SGL_Config::get('debug.production')) {
            $this->setup();
        }

        $this->processRequest->process($input, $output);
    }

    /**
     * Add extra languages (in config) to global language array.
     * Can only be run once.
     *
     * @todo fix SGL_Config::get('TranslationMgr')
     *
     * @access public
     */
    function setup()
    {
        static $runOnce;
        // we shouldn't run this code more than once per request
        if (empty($runOnce)) {

            // check if translation module's config is loaded
            if (!SGL_Config::get('TranslationMgr.requiresAuth')) {
                $c     = SGL_Config::singleton();
                $conf  = $c->ensureModuleConfigLoaded('translation');
                $setup = $conf['TranslationMgr']['extraLanguages'];
            } else {
                $setup = SGL_Config::get('TranslationMgr.extraLanguages');
            }

            if ($setup) {
                $aLangs = &$GLOBALS['_SGL']['LANGUAGE'];
                $aExtra = explode(',', $setup);
                $aExtra = array_map('trim', $aExtra);
                foreach ($aExtra as $v) {
                    $aLang = array_map('trim', explode(':', $v));
                    if (!array_key_exists($aLang[0], $aLangs)) {
                        // get language code
                        $code = reset(explode('-', $aLang[0]));
                        // add language to global array
                        $aLangs[$aLang[0]] = array(
                            $code . '|' . $aLang[1], // ar|arabic
                            $aLang[2],               // arabic-utf-8
                            $code                    // ar
                        );
                    }
                }
                ksort($aLangs);
            }
        }
        $runOnce = true;
    }
}

?>