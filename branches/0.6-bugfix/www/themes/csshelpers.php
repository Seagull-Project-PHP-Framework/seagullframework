<?php

// css vars
$baseUrl         = resolveBaseUrl(resolveTheme());
$isFormSubmitted = resolveFormStatus();

/**
 * Get current theme name.
 *
 * @return string
 */
function resolveTheme()
{
    return isset($_GET['aParams']['theme'])
        ? $_GET['aParams']['theme']
        : 'default';
}

/**
 * Get current status of form submission.
 *
 * @return boolean
 */
function resolveFormStatus()
{
    return !empty($_GET['aParams']['isFormSubmitted']);
}

/**
 * Get current base url.
 *
 * @param string $theme
 *
 * @return string
 */
function resolveBaseUrl($theme)
{
    // get base path
    $path       = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
    $aPath      = explode('/', $path);
    $aPath      = array_filter($aPath);
    $webRootUrl = implode('/', $aPath);
    $baseUrl    = $webRootUrl . '/themes/' . $theme;
    if ($baseUrl[0] != '/') {
        $baseUrl = '/' . $baseUrl;
    }

    $proto = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  == 'on'
        ? 'https'
        : 'http';
    return "$proto://{$_SERVER['HTTP_HOST']}{$baseUrl}";
}

/**
 * Get language direction. Return or output 'left' or 'right'.
 *
 * Usage example:
 *   1. getLangDirection() -> prints current language direction
 *   2. getLangDirection(true) -> prints opposit language direction
 *   3. getLangDirection(false, true) -> returns current language direction
 *   4. getLangDirection(true, true) -> returns opposit language direction
 *
 * @param unknown_type $reverse
 * @param unknown_type $return
 * @return unknown
 */
function getLangDirection($reverse = false, $return = false)
{
    $ret = isset($_GET['aParams']['langDir'])
            && $_GET['aParams']['langDir'] == 'rtl'
        ? 'right'
        : 'left';
    if ($reverse) {
        $ret = $ret == 'left'
            ? 'right'
            : 'left';
    }
    if ($return) {
        return $ret;
    } else {
        echo $ret;
    }
}

/**
 * Compares the specified version of browser with current one.
 *
 * Examples:
 *   isBrowserFamily('MSIE7', 'ge') - all 7.x family and younger,
 *   isBrowserFamily('Gecko') - gecko family,
 *   isBrowserFamily('MSIE') - MSIE family,
 *   isBrowserFamily('MSIE6', '<') - MSIE 5.x and older
 *   isBrowserFamily('MSIE6.0', 'eq') - exactly MSIE 6.0
 *   isBrowserFamily('MSIE5.5', 'ge') && browser('MSIE6', '<') - MSIE 5.5
 *
 * @param string $currentVersion  version to compare e.g. 'MSIE5.5'
 * @param string $operator        comparison operator
 *
 * @return boolean
 */
function isBrowserFamily($currentVersion, $operator = null, $reload = false)
{
    static $browserFamily;
    if ($reload) {
        $browserFamily = null;
    }
    if (!isset($browserFamily)) {
        $ua = isset($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT'] : '';
        // get browser family and version
        $browserFamily = 'None';
        if (!empty($ua)) {
            if (strstr($ua, 'Opera')) {
                $browserFamily = 'Opera';
            } elseif (strstr($ua, 'MSIE')) {
                $browserFamily = 'MSIE';
                preg_match("/$browserFamily (.+?);/", $ua, $aMatches);
                // append browser version for MSIE
                $browserFamily .= $aMatches[1];
            } else {
                $browserFamily = 'Gecko';
            }
        }
    }

    // family check, first letters: 'M', 'G', 'O' or 'N'
    if ($currentVersion[0] != $browserFamily[0]) {
        return false;
    }

    // family comparison without a version
    // for families other than MSIE we force this check, 'cos browser
    // versioning is not implemented for them yet
    if (false === strpos($browserFamily, 'MSIE')) {
        if (strpos($currentVersion, $browserFamily) !== false) {
            return true;
        } else {
            return false;
        }
    } elseif (is_null($operator)) {
        if (strpos($browserFamily, $currentVersion) !== false) {
            return true;
        } else {
            return false;
        }
    }
    return version_compare($browserFamily, $currentVersion, $operator);
}

?>