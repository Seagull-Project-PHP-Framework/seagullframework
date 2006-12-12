<?php

/**
 * Compares the specified version of browser with current one.
 *
 * Examples:
 *   browser('MSIE7', 'ge') - all 7.x family and younger,
 *   browser('Gecko') - gecko family,
 *   browser('MSIE') - MSIE family,
 *   browser('MSIE6', '<') - MSIE 5.x and older
 *   browser('MSIE6.0', 'eq') - exactly MSIE 6.0
 *   browser('MSIE5.5', 'ge') && browser('MSIE6', '<') - MSIE 5.5
 *
 * @param  string  $currentVersion  version to compare e.g. 'MSIE5.5'
 * @param  string  $operator        comparison operator
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