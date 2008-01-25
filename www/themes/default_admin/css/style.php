<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2008, Demian Turner                                         |
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
// | style.php                                                                 |
// +---------------------------------------------------------------------------+
// | Author:   John Dell <jdell@unr.edu>                                       |
// +---------------------------------------------------------------------------+
// $Id: style.php,v 1.85 2005/06/22 00:40:44 demian Exp $

    // PHP Stylesheet caching headers.
    // Adapted from PEAR HTTP_Header_Cache authored by Wolfram Kriesing <wk@visionp.de>
    // Adapted by John Dell

    ////////////////////////////   DO NOT MODIFY   /////////////////////////////

    require_once '../../csshelpers.php';

    // send default cacheing headers and content type
    header('Pragma: cache');
    header('Cache-Control: public');
    header('Content-Type: text/css');

    $modTimes = array();

    if (is_file($tmp = './vars.php')) {
        $modTimes['vars'] = filemtime($tmp);
    }
    if (is_file($tmp = './core.php')) {
        $modTimes['core'] = filemtime($tmp);
    }
    if (is_file($tmp = './blockStyle.php')) {
        $modTimes['blockStyle'] = filemtime($tmp);
    }
    $frmNavStyleSheet = @$_REQUEST['navStylesheet'];
    if (is_file($navStyleSheet = realpath("./$frmNavStyleSheet.nav.php"))) {
        $modTimes['navigation'] = filemtime($navStyleSheet);
    }
    $frmModuleName = @$_REQUEST['moduleName'];
    $frmIsSymlink  = @$_REQUEST['isSymlink'];

    if ($frmIsSymlink) {
        if (is_file($moduleName = realpath("../../../$frmModuleName/css/$frmModuleName.php"))) {
            $modTimes['module'] = filemtime($moduleName);
        }
    } else {
        if (is_file($moduleName = realpath("./$frmModuleName.php"))) {
            $modTimes['module'] = filemtime($moduleName);
        }
    }

    // Get last modified time of file
    $modTimes['shared'] = getlastmod();

    // exit out of script if cached on client
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $cliModTime = dateToTimestamp($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if (max($modTimes) <= $cliModTime) {
            header('HTTP/1.x 304 Not Modified');
            exit;
        }
    }

    /* send last modified date of file to client so it will have date for server
     * on next request.
     * Technically we could just send the current time (as PEAR does) rather
     * than the actual modify time of the file since either way would get the
     * correct behavior, but since we already have the actual modified time of
     * the file, we'll just use that.
     */
    $srvModDate = timestampToDate(max($modTimes));
    header("Last-Modified: $srvModDate");

    //  get form context (submitted or not)
    $isFormSubmitted = (isset($_REQUEST['isFormSubmitted']) && $_REQUEST['isFormSubmitted'] == "1")
        ? true
        : false;

    //  get base url for css classes that include images
    $path = dirname($_SERVER['PHP_SELF']);
    $aPath = explode('/', $path);
    $aPath = array_filter($aPath);
    array_pop($aPath);
    $baseUrl = join('/', $aPath);
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  == 'on')
        ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $baseUrl;

    require_once './vars.php';
    require_once './core.php';
    require_once './blockStyle.php';
    if (isset($modTimes['navigation'])) {
        require_once realpath("./$frmNavStyleSheet.nav.php");
    }
    if (isset($modTimes['module'])) {
        if ($frmIsSymlink) {
            require_once realpath("../../../$frmModuleName/css/$frmModuleName.php");
        } else {
            require_once realpath("./$frmModuleName.php");
        }
    }

    // copied from PEAR HTTP Header.php (comments stripped)
    // Author: Wolfram Kriesing <wk@visionp.de>
    // Changes: mktime() to gmmktime() to make work in timezones other than GMT
    function dateToTimestamp($date)
    {
        $months = array_flip(array('Jan','Feb','Mar','Apr','May','Jun',
            'Jul','Aug','Sep','Oct','Nov','Dec'));
        preg_match('~[^,]*,\s(\d+)\s(\w+)\s(\d+)\s(\d+):(\d+):(\d+).*~', $date,
            $splitDate);
        $timestamp = @gmmktime($splitDate[4], $splitDate[5], $splitDate[6],
            $months[$splitDate[2]]+1, $splitDate[1], $splitDate[3]);
        return $timestamp;
    }

    // copied from PEAR HTTP.php Date function (comments stripped)
    // Author: Stig Bakken <ssb@fast.no>
    function timestampToDate($time)
    {
        if (ini_get("y2k_compliance") == true) {
            return gmdate("D, d M Y H:i:s \G\M\T", $time);
        } else {
            return gmdate("F, d-D-y H:i:s \G\M\T", $time);
        }
    }
?>