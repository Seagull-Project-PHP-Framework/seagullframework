<?php
/**
 * Sets the current locale.
 *
 * @package Task
 * @author  Demian Turner <demian@phpkitchen.com>
 */
class SGL_Filter_SetupLocale extends SGL_DecorateProcess
{
    public function process(SGL_Request $input, SGL_Response $output)
    {
        $locale = $_SESSION['aPrefs']['locale'];
        $timezone = $_SESSION['aPrefs']['timezone'];
        $language = substr($locale, 0,2);

        //  The default locale category is LC_ALL, but this will cause probs for
        //  european users who get their decimal points (.) changed to commas (,)
        //  and php numeric calculations will break.  The solution for these users
        //  is to select the LC_TIME category.  For a global effect change this in
        //  Config.
        if (setlocale(SGL_String::pseudoConstantToInt(SGL_Config::get('site.localeCategory')),
                $locale) == false) {
            setlocale(LC_TIME, $locale);
        }
        putenv('TZ=' . $timezone);

        if (strtoupper(substr(PHP_OS, 0,3)) === 'WIN') {
            putenv('LANG='     . $language);
            putenv('LANGUAGE=' . $language);
        } else {
            putenv('LANG='     . $locale);
            putenv('LANGUAGE=' . $locale);
        }

        $this->processRequest->process($input, $output);
    }
}

?>