<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005, Demian Turner                                         |
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
// | Seagull 0.4                                                               |
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

    // send default cacheing headers and content type
    header('Pragma: cache');
    header('Cache-Control: public');
    header('Content-Type: text/css');

    // Get last modified time of file
    $srvModTime = getlastmod();

    // exit out of script if cached on client
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $cliModTime = dateToTimestamp($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($srvModTime <= $cliModTime) {
            header('HTTP/1.x 304 Not Modified');
            exit;
        }
    }

    // send last modified date of file to client so it will have date for server on next request.
    // technically we could just send the current time (as PEAR does) rather than the actual modify
    // time of the file since either way would get the correct behavior, but since we already have
    // the actual modified time of the file, we'll just use that.
    $srvModDate = timestampToDate($srvModTime);
    header("Last-Modified: $srvModDate");

    //  get base url for css classes that include images
    $path = dirname($_SERVER['PHP_SELF']);
    $aPath = explode('/', $path);
    $aWithoutBlanks = array_filter($aPath, 'strlen');
    array_pop($aWithoutBlanks);
    $baseUrl = join('/', $aWithoutBlanks);
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  == 'on') 
        ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $baseUrl;

    /////////////////////////   MODIFY FROM HERE DOWN   ////////////////////////

    // CSS Substitution Variables

    $fontFamily             = '"Bitstream Vera Sans", Trebuchet MS, Verdana, Arial, Helvetica, sans-serif';  

    $primary                = '#99cc00'; // lime green
    $primaryLight           = '#bbe713'; // light green
    $primaryText            = '#e6ffa2'; // pale white for text on lime green
    $primaryTextLight       = '#ffffff'; // white
    $secondaryLight         = '#e5f1ff'; // baby blue
    $secondary              = '#9dcdfe'; // blue
    $secondaryMedium        = '#3399ff'; // medium blue
    $secondaryDark          = '#184a84'; // dark blue
    $tertiary               = '#d9d9d9'; // normal gray
    $tertiaryLight          = '#efefef'; // light gray
    $tertiaryMedium         = '#bcbcbc'; // medium gray
    $tertiaryDark           = '#999999'; // dark gray
    $tertiaryDarker         = '#666666'; // darker gray

    $blocksMarginTop        = '97px';
    $blocksWidthLeft        = '180px';
    $blocksWidthRight       = '180px';
    $blocksBorderBody       = $tertiaryMedium;
    $blocksBorderTitle      = $tertiaryMedium;
    $blocksBackgroundBody   = $tertiaryLight;
    $blocksBackgroundTitle  = $primary;
    $blocksColorBody        = $secondaryDark;
    $blocksColorTitle       = $primaryText;

    $tableRowLight          = $tertiaryLight;
    $tableRowDark           = $tertiary;

    /* Publisher */
    $sectionHeaderBackground = $tertiary;
    $sectionHeaderColor     = $secondaryDark;
    $colHeaderBackground    = $tertiaryLight;
    $colHeaderColor         = $secondaryDark;
    $navigatorBackground    = $tertiaryDarker;
    $navigatorColor         = $tertiaryMedium;
    
    $forApproval            = '#ff0000';
    $approved               = '#ff9933';
    $published              = '#00cc00';
    $archived               = '#909090';
    
    $error                  = '#ffcc00';
    $errorLight             = '#ffff99';
    $errorDark              = '#ff9600';
    $errorText              = $secondaryDark;
    $errorTextLight         = '#ffffcc';
    $errorTextMedium        = '#ff0000';

    /* Button like border colors */
    $buttonBorderColors     = '#ffffff #333333 #333333 #ffffff';
    
    // include local style file
    $localStyle = './style.local.php';
    if (file_exists($localStyle)) {
        include_once $localStyle;
    }    
?>


/******************************************************************************/
/*                                  LAYOUT CSS                                */
/******************************************************************************/

body {
    margin: 0;
    padding: 0;
    font: small <?php echo $fontFamily ?>;
    color: <?php echo $tertiaryDarker ?>;
    background: <?php echo $primaryTextLight ?>;
}

/******************************* LAYOUT : HEADER ******************************/

#sgl-header {
    background-color: <?php echo $primary ?>;
    height: 50px;
}
#sgl-header-left {
    float: left;
    margin: 5px 5px 5px 10px;
    font-size: 2em;
    color: <?php echo $primaryTextLight ?>;
}
#sgl-header-right {
    float: right;
    margin: 10px;
    margin-left: 0;
    font-size: 0.9em;
    color: <?php echo $primaryTextLight ?>;
}
#sgl-header-right a {
    padding: 0 5px;
    text-decoration: none;
    color: <?php echo $primaryTextLight ?>;
}
#sgl-header-right a:hover {
    text-decoration: underline;
}
#sgl-header-right #headerLogAction {
    float: left;
    margin-left: 1em;
    padding: 0.2em;
    border: 1px solid transparent;
    border-color: <?php echo $buttonBorderColors ?>;
    background-color: <?php echo $primaryLight ?>;
}
#sgl-header-right #headerUserInfo {
    float: left;
    padding-top: 0.35em;
}
#sgl-header-right #headerUserInfo .guest {
    font-weight: bold;
}

/***************************** LAYOUT : TABLES ********************************/

table {
    border: none;
    /* This is not a typo, we want first set a fallback for IE, then set the
     * real margin for real browsers ;) */
    margin: 0 5%;
    margin: 0 auto;
}
td, th {
    padding: 2px;
}
th {
    background-color: <?php echo $tertiaryMedium ?>;
    text-align: left;
    color: <?php echo $primaryTextLight ?>;
    font-size: 1.1em;
    letter-spacing: 0.1px;
    line-height: 1.75em;
}
#imRead {
    background-color: <?php echo $tertiaryMedium ?>;
}
#moduleOverview {
    width: 240px;
    height: 125px;
}
.full {
    width: 100%;
}
.wide {
    width: 90%;
}
.narrow {
    width: 60%;
}
.sgl-row-light, .sgl-row-light-bold {
    background-color: <?php echo $tableRowLight ?>;
}
.sgl-row-dark, .sgl-row-dark-bold {
    background-color: <?php echo $tableRowDark ?>;
}
.sgl-row-light-bold, .sgl-row-dark-bold {
    font-weight: bold;
}

/****************************** LAYOUT : MAIN *********************************/

#sgl-main {
    top: <?php echo $blocksMarginTop ?>;
}

/************************ LAYOUT : LEFT & RIGHT BLOCKS ************************/

#sgl-blocks-left, #sgl-blocks-right {
    position: absolute;
    margin-top: <?php echo $blocksMarginTop ?>;
    top: 0;
    z-index:1;
}
#sgl-blocks-left {
    width: <?php echo $blocksWidthLeft ?>;
    left: 0;
}
#sgl-blocks-right {
    width: <?php echo $blocksWidthRight ?>;
    right: 0;
}
.navWidget {
    overflow: auto;
}
.options-block {
    margin: 20px 0;
}
#sgl-blocks-left .blockContainer, #sgl-blocks-right .blockContainer {
    margin: 4px 1px 0 1px;
}
.blockHeader {
    background-color: <?php echo $blocksBackgroundTitle ?>;
    color: <?php echo $blocksColorTitle ?>;
    line-height: 1.5em;
    font-weight: bold;
    text-align: center;
    border: 1px solid <?php echo $blocksBorderTitle ?>;
    margin: 0;
}
.blockContent {
    background-color: <?php echo $blocksBackgroundBody ?>;
    color: <?php echo $blocksColorBody ?>;
    font-size: 0.9em;
    padding: 10px;
    border: 1px solid <?php echo $blocksBorderBody ?>;
    border-top: none;
}

/*************************** LAYOUT : MIDDLE BLOCKS ***************************/

#sgl-blocks-middle, #sgl-blocks-middle-nocols, #sgl-blocks-middle-leftcol, #sgl-blocks-middle-rightcol {
    position: relative;
    margin: 0 <?php echo $blocksWidthRight; ?> 0 <?php echo $blocksWidthLeft ?>;
    width: auto;
    min-width: 20%;
    font-size: 0.9em;
    /*z-index: 2;*/
    padding: 0 20px;
}
#sgl-blocks-middle #options {
    float: right;
    width: 28%;
}
#sgl-blocks-middle-nocols {
    margin: 0;
}
#sgl-blocks-middle-leftcol {
    margin: 0 0 0 <?php echo $blocksWidthLeft ?>;
}
#sgl-blocks-middle-rightcol {
    margin: 0 <?php echo $blocksWidthRight ?> 0 0;
}
/* Holly Hack here so that tooltips don't act screwy:
 * http://www.positioniseverything.net/explorer/threepxtest.html */
/* Hide next from Mac IE plus non-IE \*/
* html #sgl-blocks-middle {
    height: 1%;
}
/* End hide from IE5/mac plus non-IE */

/******************************* LAYOUT : FOOTER ******************************/

#sgl-footer {
    position: relative;
    float: middle;
    clear: both;
    margin-bottom: 5px;
    padding-top: 10px;
    font-size: 0.8em;
    text-align: center;
}

/******************************************************************************/
/*                                 CONTENT CSS                                */
/******************************************************************************/

/***************************** CONTENT : HEADINGS *****************************/

h1 {
    font-size: 2em;
    font-weight: normal;
}
h1.pageTitle {
    font-weight: normal;
    text-align: center;
    color: <?php echo $secondaryDark ?>;
}
h2 {
    font-size: 1.5em;
}
h3 {
    font-size: 1.25em;
}
h4 {
    font-size: 1em;
}
.pageTitle {
    color: <?php echo $secondaryDark ?>;
    font-size: 1.75em;
    font-weight: normal;
}

/***************************** CONTENT : ANCHORS ******************************/

a {
    color: <?php echo $secondaryMedium ?>;
    font-weight: bold;
}
a:visited {
    color: <?php echo $tertiaryDark ?>;
}
a:hover {
    color: <?php echo $secondaryDark ?>;
    text-decoration: none;
}

/******************************* CONTENT : BLOCKS *****************************/

img.blocksAvatar {
    /* move the image up to be flush with bottom of title */
    position: relative;
    top: -5px;
    float: right;
    padding-left: 5px;
    align: left;
}

/*************************** CONTENT : MISCELLANEOUS **************************/

acronym {
    cursor: help;
}
hr {
    border: none;
    border-bottom: 1px solid <?php echo $tertiary ?>;
}
img {
    border: none;
}
.alignCenter {
    text-align: center;
}
.error {
    color: <?php echo $errorTextMedium ?>;
}
.small {
    font-size: 0.8em;
}
.title {
    color: <?php echo $tertiaryDark ?>;
    font-weight: normal;
    font-size: 1.5em;
}
.detail {
    color: <?php echo $tertiaryDark ?>;
    font-weight: normal;
    font-size: 0.8em;
}
.toolBtnSeparate {
    margin-left: 20px;
}

/*************************** MODULE: PUBLISHER ********************************/

.sectionHeader {
    font-size: 1.3em;
    font-weight: normal;
    color: <?php echo $sectionHeaderColor ?>;
    background-color: <?php echo $sectionHeaderBackground ?>;
    padding-left: 10px;
    line-height: 34px;
    letter-spacing: 1px;
    margin: 0;
}
.colHeader {
    color: <?php echo $colHeaderColor ?>;
    background-color: <?php echo $colHeaderBackground ?>;
    font-size: 11px;
    line-height: 20px;
    font-weight: normal;
    padding-left: 10px;
    letter-spacing: 0.5px;
    margin: 2px 0 0 0;
}
.navigator {
    color: <?php echo $navigatorColor ?>;
    background-color: <?php echo $navigatorBackground ?>;
    padding-left: 10px;
    font-weight: bold;
    text-align: right;
    line-height: 18px;
}
    
/* /////////////// Article Manager /////////////// */

.forApproval {
    font-weight: bold;
    color: <?php echo $forApproval ?>;
}
.approved {
    font-weight: bold;
    color: <?php echo $approved ?>;
}
.published {
    font-weight: bold;
    color: <?php echo $published ?>;
}
.archived {
    font-weight: bold;
    color:  <?php echo $archived ?>;
}  

/******************************************************************************/
/*                                  LEGACY CSS                                */
/*                                                                            */
/* Note: I am removing elements from here as I replace them with new CSS      */
/*       elements above.  Eventually, there shouldn't be any CSS left here.   */
/******************************************************************************/

/* /////////////// Table modifiers  /////////////// */

.fieldName, .fieldNameWrap {
    background-color: <?php echo $tertiaryLight ?>;
    color: <?php echo $secondaryDark ?>;
    font-weight: bold;
    text-align: left;
    width: 35%;
}
.fieldName {
    white-space: nowrap;
}
.fieldValue {
    background-color: <?php echo $primaryTextLight ?>;
    line-height: 16px;
    text-align: left;
    width: 65%;
}
.newsItem {
    border: 1px solid <?php echo $tertiaryDark ?>;
    margin: 0 auto;
    padding: 0 10px 10px 10px;
    background-color: <?php echo $errorTextLight ?>;
}
fieldset {
    width: 80%;
    margin: 0 auto;
    color: <?php echo $secondaryDark ?>;
    font-size: 1.1em;
    font-weight: bold;
}
legend {
    color: <?php echo $secondaryDark ?>;
}

/* /////////////// Links  /////////////// */

.linkCrumbsAlt1 {
    text-decoration: none;
    color: <?php echo $secondaryDark ?>;
    font-weight: normal;
    letter-spacing: 0.5px;
}
.linkCrumbsAlt1:hover {
    text-decoration: underline;
    color: <?php echo $secondaryDark ?>;
}

/* /////////////// Various /////////////// */

#sgl #bugReporter {
    position: absolute;
    right: 4px;
    top: 12px;
}


div.pinstripe table {
    background-color: <?php echo $tertiaryLight ?>;
    width: 90%;
}
div.pinstripe td {
    background-color: <?php echo $primaryTextLight ?>;
}
div.pinstripe img {
    padding: 10px;
}
div.pinstripe button {
    padding: 10px 0;
}
.noBorder {
    border: none;
    font-size: 10px;
}
ul.noindent {
    margin-left: 5px;
    padding-left: 5px;
}
ul.bullets li {
    list-style-image: url('<?php echo $baseUrl ?>/images/bullet.gif');
}
.pager {
    background-color: <?php echo $errorTextLight ?>;
    white-space: nowrap;
    text-align: center;
    width: 90%;
    margin: 0 auto;
    border: 1px dashed <?php echo $errorDark ?>;
}
.narrowButton {
    text-align: center;
    width: 100px;
}
.wideButton {
    text-align: center;
    width: 150px;
}
.errorContainer, .messageContainer {
    margin: 0 auto;
    width: 300px;
}
.errorHeader {
    background-color: <?php echo $error ?>;
    color: <?php echo $errorTextLight ?>;
    font-weight: bold;
    letter-spacing: 1px;
    text-align: center;
    text-transform: uppercase;
}
.errorContent {
    border: 1px dotted <?php echo $errorDark ?>;
    border-top: 1px solid <?php echo $error ?>;
    color: <?php echo $errorText ?>;
    background-color: <?php echo $errorLight ?>;
    text-align: left;
    padding: 0 10px;
}
.errorMessage {
    margin: 0 auto;
    border: 1px dotted <?php echo $errorDark ?>;
    background-color: <?php echo $errorLight ?>;
    text-align: center;
    width: 50%;
}
.messageHeader {
    color: <?php echo $primaryText ?>;
    background-color: <?php echo $primary ?>;
    font-weight: bold;
    font-size: 1.2em;
    line-height: 1.5em;
    text-align: center;
}
.messageContent {
    background-color: <?php echo $primaryTextLight ?>;
    color: <?php echo $secondaryDark ?>;
    border: 1px solid <?php echo $primary ?>;
    border-top: none;
    text-align: center;
}
.messageContent div {
    padding: 5px;
}
.messageContent input {
    width: 150px;
}
#navPreview {
    position: relative;
    border: 1px dashed <?php echo $tertiary ?>;
}
#navPreview span {
    z-index: 5;
    position: absolute;
    right: 5px;
    bottom: 5px;
    font-size: 2em;
    color: <?php echo $tertiary ?>;
    text-transform: uppercase;
}
.bgnd {
    background-color: <?php echo $secondaryLight ?>;
    border: 1px solid <?php echo $tertiaryDark ?>;
}
.treeMenuDefault {
    font-size: 11px;
}

/* /////////////// Tooltips /////////////// */

span.tipOwner {
    position: relative;
    cursor: help;
}
span.tipOwner span.tipText {
    display: none;
    position: absolute;
    top: 0;
    left: 105%;
    border: 1px solid transparent;
    border-color: <?php echo $buttonBorderColors ?>;
    background-color: <?php echo $tertiaryLight ?>;
    color: <?php echo $secondaryDark ?>;
    text-align: center;
    width: 15em;
    padding: 2px 5px;
    -moz-opacity: 0.85;
    filter: alpha(opacity=85);
    filter: progid: DXImageTransform.Microsoft.Alpha(opacity=85);
}
span.tipOwner:hover span.tipText {
    display: block;
}
/* IE javascript workaround */
span.tipOwner {
    behavior: url(<?php echo $baseUrl ?>/css/tooltipHover.htc);
}

<?php
    // copied from PEAR HTTP Header.php (comments stripped)
    // Author: Wolfram Kriesing <wk@visionp.de>
    // Changes: mktime() to gmmktime() to make work in timezones other than GMT
    function dateToTimestamp($date) {
        $months = array_flip(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'));
        preg_match('~[^,]*,\s(\d+)\s(\w+)\s(\d+)\s(\d+):(\d+):(\d+).*~', $date, $splitDate);
        $timestamp = @gmmktime($splitDate[4], $splitDate[5], $splitDate[6], $months[$splitDate[2]]+1, $splitDate[1], $splitDate[3]);
        return $timestamp;
    }

    // copied from PEAR HTTP.php Date function (comments stripped)
    // Author: Stig Bakken <ssb@fast.no>
    function timestampToDate($time) {
        if (ini_get("y2k_compliance") == true) {
            return gmdate("D, d M Y H:i:s \G\M\T", $time);
        } else {
            return gmdate("F, d-D-y H:i:s \G\M\T", $time);
        }
    }
?>
