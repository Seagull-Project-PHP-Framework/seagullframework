<?php
/**
 * Basic lang file structure - borrowed from phpMyAdmin, lib/select_lang.lib.php.
 *
 * @package default
 * @author  phpMyAdmin group
 */

    //  language choices
    /**
     * All the supported languages have to be listed in the array below.
     * 1. The key must be the "official" ISO 639 language code and, if required,
     *    the dialect code. It can also contain some informations about the
     *    charset (see the Russian case).
     * 2. The first of the values associated to the key is used in a regular
     *    expression to find some keywords corresponding to the language inside two
     *    environment variables.
     *    These values contains:
     *    - the "official" ISO language code and, if required, the dialect code
     *      also ('bu' for Bulgarian, 'fr([-_][[:alpha:]]{2})?' for all French
     *      dialects, 'zh[-_]tw' for Chinese traditional...);
     *    - the '|' character (it means 'OR');
     *    - the full language name.
     * 3. The second values associated to the key is the name of the file to load
     *    without the 'inc.php' extension.
     * 4. The last values associated to the key is the language code as defined by
     *    the RFC1766.
     *
     * Beware that the sorting order (first values associated to keys by
     * alphabetical reverse order in the array) is important: 'zh-tw' (chinese
     * traditional) must be detected before 'zh' (chinese simplified) for
     * example.
     *
     * When there are more than one charset for a language, we put the -utf-8
     * first.
     *
     * For Russian, we put 1251 first, because MSIE does not accept 866
     * and users would not see anything.
     *
     * Seagull naming:
     *
     * lang elements example data structure:
        [ru-utf-8] => Array
            (
                [0] => ru|russian
                [1] => russian-utf-8
                [2] => ru
            )
        [zhtw-utf-8] => Array
            (
                [0] => zh[-_](tw|hk)|chinese traditional
                [1] => chinese_traditional-utf-8
                [2] => zh-TW
            )
     *
     *
     * Seagull naming of lang elements:
        [ISO 639 language code] => Array            <-- langCodeCharset
            (
                [0] => regex
                [1] => long languageNameCharset     <-- langFileName
                [2] => RFC1766 language code        <-- langCode
            )
     */

    $GLOBALS['_SGL']['LANGUAGE'] = (SGL::isMinimalInstall())
        ? array('en-iso-8859-15'=> array('en([-_][[:alpha:]]{2})?|english',  'english-iso-8859-15', 'en'))
        : array(
                    'cs-iso-8859-2'=> array('cs|czech', 'czech-iso-8859-2', 'cs'),
                    'de-iso-8859-1'=> array('de([-_][[:alpha:]]{2})?|german', 'german-iso-8859-1', 'de'),
                    'de-utf-8'=> array('de([-_][[:alpha:]]{2})?|german', 'german-utf-8', 'de'),
                    'en-iso-8859-15'=> array('en([-_][[:alpha:]]{2})?|english',  'english-iso-8859-15', 'en'),
                    'en-utf-8'=> array('en([-_][[:alpha:]]{2})?|english',  'english-utf-8', 'en'),
                    'es-iso-8859-1'=> array('es([-_][[:alpha:]]{2})?|spanish', 'spanish-iso-8859-1', 'es'),
                    'es-utf-8'=> array('es([-_][[:alpha:]]{2})?|spanish', 'spanish-utf-8', 'es'),
                    'fr-iso-8859-1'=> array('fr([-_][[:alpha:]]{2})?|french', 'french-iso-8859-1', 'fr'),
                    'fr-utf-8'     => array('fr([-_][[:alpha:]]{2})?|french', 'french-utf-8', 'fr'),
                    'it-iso-8859-1'=> array('it|italian', 'italian-iso-8859-1', 'it'),
                    'ja-euc-jp' => array('ja|japanese', 'japanese-euc-jp', 'ja'),
                    'ja-utf-8' => array('ja|japanese', 'japanese-utf-8', 'ja'),
                    'lv-utf-8' => array('lv|latvian', 'latvian-utf-8', 'lv'),
                    'nl-iso-8859-1'=> array('nl([-_][[:alpha:]]{2})?|dutch', 'dutch-iso-8859-1', 'nl'),
                    'no-iso-8859-1'=> array('no|norwegian', 'norwegian-iso-8859-1', 'no'),
                    'ptbr-iso-8859-1' => array('pt[-_]br|brazilian portuguese', 'brazilian_portuguese-iso-8859-1', 'pt-BR'),
                    'pt-iso-8859-1'=> array('pt([-_][[:alpha:]]{2})?|portuguese', 'portuguese-iso-8859-1', 'pt'),
                    'pl-iso-8859-2'=> array('pl|polish', 'polish-iso-8859-2', 'pl'),
                    'ru-windows-1251' => array('ru|russian', 'russian-windows-1251', 'ru'),
                    'ru-utf-8'      => array('ru|russian', 'russian-utf-8', 'ru'),
                    'sv-iso-8859-1'=> array('sv|swedish', 'swedish-iso-8859-1', 'sv'),
                    'tr-iso-8859-9'=> array('tr|turkish', 'turkish-iso-8859-9', 'tr'),
                    'tr-utf-8'     => array('tr|turkish', 'turkish-utf-8', 'tr'),
                    'zhtw-utf-8'  => array('zh[-_](tw|hk)|chinese traditional', 'chinese_traditional-utf-8', 'zh-TW'),
                    'zhtw-big5'   => array('zh[-_](tw|hk)|chinese traditional', 'chinese-big5', 'zh-TW'),
                    'zh-utf-8'     => array('zh|chinese simplified', 'chinese_simplified-utf-8', 'zh'),
                    'zh-gb2312'    => array('zh|chinese simplified', 'chinese-gb2312', 'zh')
    );

    function SGL_cmp(&$a, $b)
    {
        return (strcmp($a[1], $b[1]));
    }
?>
