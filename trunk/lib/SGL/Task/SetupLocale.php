<?php

/**
 * Sets the current locale.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Task_SetupLocale extends SGL_DecorateProcess
{
    function process(&$input, &$output)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        $locale = isset($_SESSION['aPrefs']['locale'])
            ? $_SESSION['aPrefs']['locale']
            : 'en_GB';
        $timezone = isset($_SESSION['aPrefs']['timezone'])
            ? $_SESSION['aPrefs']['timezone']
            : 'UTC';
        $language = substr($locale, 0,2);

        if (!SGL_Config::get('site.extendedLocale')) {
            //  The default locale category is LC_ALL, but this will cause probs for
            //  european users who get their decimal points (.) changed to commas (,)
            //  and php numeric calculations will break.  The solution for these users
            //  is to select the LC_TIME category.  For a global effect change this in
            //  Config.
            if (setlocale(SGL_String::pseudoConstantToInt(SGL_Config::get('site.localeCategory')), $locale) == false) {
                setlocale(LC_TIME, $locale);
            }
            @putenv('TZ=' . $timezone);

            if (strtoupper(substr(PHP_OS, 0,3)) === 'WIN') {
                @putenv('LANG='     . $language);
                @putenv('LANGUAGE=' . $language);
            } else {
                @putenv('LANG='     . $locale);
                @putenv('LANGUAGE=' . $locale);
            }

        } else {
            require_once dirname(__FILE__) . '/../Locale.php';
            $setlocale = & SGL_Locale::singleton($locale);
        }

        $this->processRequest->process($input, $output);
    }
}

?>