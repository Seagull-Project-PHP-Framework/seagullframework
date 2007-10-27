<?php

// base url
$baseUrl = resolveBaseUrl(resolveTheme());

function resolveTheme()
{
    return isset($_GET['aParams']['theme']) ? $_GET['aParams']['theme'] : 'default';
}

function resolveBaseUrl($theme)
{
    // get base path
    $path       = dirname($_SERVER['PHP_SELF']);
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

function getLangDirection()
{
    return isset($_GET['aParams']['langDir'])
            && $_GET['aParams']['langDir'] == 'rtl'
        ? 'rtl'
        : 'ltr';
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