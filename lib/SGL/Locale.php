<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2006, Jacob Singh                                        |
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | o Redistributions of source code must retain the above copyright          |
// |   notice, this list of conditions and the following disclaimer.           |
// | o Redistributions in binary form must reproduce the above copyright       |
// |   notice, this list of conditions and the following disclaimer in the     |
// |   documentation and/or other materials provided with the distribution.    |
// | o The names of the authors may not be used to endorse or promote          |
// |   products derived from this software without specific prior written      |
// |   permission.                                                             |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS       |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT         |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR     |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT      |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,     |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT          |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,     |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE     |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.      |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Seagull 0.6                                                               |
// +---------------------------------------------------------------------------+
// | Locale.php                                                                |
// +---------------------------------------------------------------------------+
// | Author:   Jacob Singh <jacob@calabashmusic.com>                           |
// +---------------------------------------------------------------------------+

require_once 'I18Nv2.php';

/**
 * Wraps PEAR locale package.
 *
 * @package SGL
 * @author  Jacob Singh <jacob@calabashmusic.com>
 * @version $Revision: 1.6 $
 */
class SGL_Locale
{
    /**
     * Negotiates the locale from HTTP if necessary.  Gets it from _SESSION and database otherwise.
     *
     *   usage:
     *
     *   $locale =& SGL_Locale::singleton();
     *   // setting locale here would override defaults.
     *
     *   echo $locale->formatCurrency(2000,I18Nv2_CURRENCY_LOCAL);
     *   echo $locale->formatDate(time());
     *
     * @param string $locale Overrides getting the locale from session/usr
     * @return I18Nv2 Returns a single instance of I18Nv2
     */
    function &singleton($newLocale = false)
    {
        SGL::logMessage(null, PEAR_LOG_DEBUG);

        static $locale;
        if (!isset($locale)) {
            if ($newLocale) {
                $locale = &I18Nv2::createLocale($newLocale);
            } else {
                //  Get the language shortcode from the session
                $langCode = SGL::getCurrentLang();

                $uid = SGL_Session::getUid();
                require_once 'I18Nv2/Negotiator.php';
                if ($uid && isset($langCode)) {
                    $dbh = &SGL_DB::singleton();
                    $c = &SGL_Config::singleton();
                    $conf = $c->getAll();

                    $country = $dbh->getOne("SELECT country FROM {$conf['table']['user']} WHERE usr_id = ".$uid);
                    $country = strtoupper($country);

                    if (!$country) {
                        $neg = &new I18Nv2_Negotiator();
                        $country = $neg->getCountryMatch($langCode);
                    }
                    $localeId = empty($country) ? $langCode : $langCode . "_" . $country;

                } else {
                    $neg = &new I18Nv2_Negotiator();
                    $localeId = $neg->getLocaleMatch();
                }
                $locale = &I18Nv2::createLocale($localeId);
            }
        }
        return $locale;
    }

    function setTimeZone($tzone)
    {
        setlocale(LC_TIME, $tzone);
        @putenv('TZ=' . $tzone);
    }
}

//  if iconv extension not present, at least fail silently
if (!(function_exists('iconv'))) {
    function iconv($in_charset, $out_charset, $string)
    {
        return $string;
    }
}
?>