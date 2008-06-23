<?php

    // Get some info from style.php
    // send default cacheing headers and content type
    header('Pragma: cache');
    header('Cache-Control: public');
    header('Content-Type: text/css');

    // get browser family
    $browserFamily = 'None';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    if (!empty($ua)) {
        if (strstr($ua, 'Opera')) {
            $browserFamily = 'Opera';
        } else if (strstr($ua, 'MSIE')) {
            $browserFamily = 'MSIE';
        } else {
            $browserFamily = 'Gecko';
        }
    }

    //  get base url for css classes that include images
    $path = dirname($_SERVER['PHP_SELF']);
    $aPath = explode('/', $path);
    $aPath = array_filter($aPath);
    array_pop($aPath);
    $baseUrl = join('/', $aPath);
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  == 'on')
        ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $baseUrl;

    // Get info from vars
    $fontFamily             = '"Bitstream Vera Sans", Trebuchet MS, Verdana, Arial, Helvetica, sans-serif';
    $fontSize               = 'small';

    $primary                = '#99cc00'; // lime green
    $primaryLight           = '#bbe713'; // light green
    $primaryText            = '#e6ffa2'; // pale white for text on lime green
    $primaryTextLight       = '#ffffff'; // white
    $secondaryLight         = '#e5f1ff'; // baby blue
    $secondary              = '#9dcdfe'; // blue
    $secondaryMedium        = '#3399ff'; // medium blue
    $secondaryDark          = '#006699'; // dark blue
    $secondaryDarker        = '#184a84'; // darker blue
    $tertiary               = '#d9d9d9'; // normal gray
    $tertiaryLight          = '#eeeeee'; // light gray
    $tertiaryMedium         = '#bcbcbc'; // medium gray
    $tertiaryDark           = '#999999'; // dark gray
    $tertiaryDarker         = '#3f3f3f'; // darker gray

    $blocksMarginTop        = '107px';
    $blocksWidthLeft        = '180px';
    $blocksWidthRight       = '180px';
    $blocksBorderBody       = $tertiaryMedium;
    $blocksBorderTitle      = $tertiaryMedium;
    $blocksBackgroundBody   = $tertiaryLight;
    $blocksBackgroundTitle  = $primary;
    $blocksColorBody        = $secondaryDarker;
    $blocksColorTitle       = $primaryTextLight;

    $tableRowLight          = 'transparent';
    $tableRowDark           = $tertiaryLight;

    /* Publisher */
    $sectionHeaderBackground = $tertiary;
    $sectionHeaderColor     = $secondaryDarker;
    $colHeaderBackground    = $tertiaryLight;
    $colHeaderColor         = $secondaryDarker;
    $navigatorBackground    = $tertiaryDarker;
    $navigatorColor         = $tertiaryMedium;

    $forApproval            = '#ff0000';
    $approved               = '#ff9933';
    $published              = '#00cc00';
    $archived               = '#909090';

    $error                  = '#ffcc00';
    $errorLight             = '#ffff99';
    $errorDark              = '#ff9600';
    $errorText              = $secondaryDarker;
    $errorTextLight         = '#ffffcc';
    $errorTextMedium        = '#ff0000';

    /* Button like border colors */
    $button     = '#ffffff #333333 #333333 #ffffff';
    $buttonAlt     = '#333333 #ffffff #ffffff #333333';
?>

/* Put old core.php here */
body {
    margin: 0;
    padding: 0;
    font: <?php echo $fontSize ?> <?php echo $fontFamily ?>;
    color: <?php echo $tertiaryDarker ?>;
    background: <?php echo $primaryTextLight ?>;
}

/******************************* LAYOUT : HEADER ******************************/

#sgl #header {
    background-color: <?php echo $primary ?>;
    height: 60px;
}
#sgl #logo {
    float: left;
    margin: 5px 0 0 10px;
    font-size: 2em;
    font-weight: normal;
    color: <?php echo $primaryTextLight ?>;
    text-decoration: none;
}
#sgl #logo img {
    vertical-align: middle;
    <?php if ($browserFamily == 'Opera') {?>
    vertical-align: text-middle;
    <?php } ?>
}
#sgl #login {
    float: right;
    margin: 10px 10px 0 0;
    font-size: 0.9em;
    color: <?php echo $primaryTextLight ?>;
    /* Workaround for IE hiding bottom border of logAction. */
    height: 50%;
}
#sgl #login a {
    padding: 0 5px;
    text-decoration: none;
    color: <?php echo $primaryTextLight ?>;
}
#sgl #login a:hover {
    text-decoration: underline;
}
#sgl #login #logAction {
    margin-left: 0.5em;
    padding: 0.2em;
    border: 1px solid transparent;
    border-color: <?php echo $button ?>;
}

/****************************** LAYOUT : MAIN *********************************/

#sgl #container {
    top: <?php echo $blocksMarginTop ?>;
}

/************************ LAYOUT : LEFT & RIGHT BLOCKS ************************/

#sgl #leftSidebar, #sgl #rightSidebar {
    position: absolute;
    margin-top: <?php echo $blocksMarginTop ?>;
    top: 0;
    z-index: 1;
}
#sgl #leftSidebar {
    width: <?php echo $blocksWidthLeft ?>;
    left: 0;
}
#sgl #rightSidebar {
    width: <?php echo $blocksWidthRight ?>;
    right: 0;
}
#sgl .blockContainer {
    margin: 4px 1px 0 1px;
}
#sgl .blockHeader {
    background-color: <?php echo $blocksBackgroundTitle ?>;
    color: <?php echo $blocksColorTitle ?>;
    line-height: 1.5em;
    font-weight: bold;
    text-align: center;
    border: 1px solid <?php echo $blocksBorderTitle ?>;
    margin: 0;
}
#sgl .blockContent {
    background-color: <?php echo $blocksBackgroundBody ?>;
    color: <?php echo $blocksColorBody ?>;
    font-size: 0.9em;
    padding: 10px;
    border: 1px solid <?php echo $blocksBorderBody ?>;
    border-top: none;
}

/*************************** LAYOUT : MIDDLE BLOCKS ***************************/

#sgl #content, #sgl #content-nocols, #sgl #content-leftcol, #sgl #content-rightcol {
    position: relative;
    margin: 0 <?php echo $blocksWidthRight; ?> 0 <?php echo $blocksWidthLeft ?>;
    width: auto;
    min-width: 20%;
    font-size: 0.9em;
    padding: 0 20px;
}
#sgl #content #options {
    float: right;
    width: 28%;
}
#sgl #content-nocols {
    margin: 0;
}
#sgl #content-leftcol {
    margin: 0 0 0 <?php echo $blocksWidthLeft ?>;
}
#sgl #content-rightcol {
    margin: 0 <?php echo $blocksWidthRight ?> 0 0;
}
/* Holly Hack here so that tooltips don't act screwy:
 * http://www.positioniseverything.net/explorer/threepxtest.html */
/* Hide next from Mac IE plus non-IE \*/
* html #sgl #content {
    height: 1%;
}
/* End hide from IE5/mac plus non-IE */

/******************************* LAYOUT : FOOTER ******************************/

#sgl #footer {
    clear: both;
    padding-top: 10px;
    font-size: 0.8em;
    text-align: center;
}

/***************************** CONTENT : HEADINGS *****************************/

h1 {
    font-size: 2em;
    font-weight: normal;
}
h1.pageTitle {
    font-weight: normal;
    text-align: center;
    color: <?php echo $secondaryDarker ?>;
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
    color: <?php echo $secondaryDarker ?>;
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
    color: <?php echo $secondaryDarker ?>;
    text-decoration: none;
}

/***************************** CONTENT : TABLES *******************************/

#content table {
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
    background-color: <?php echo $tertiaryLight ?>;
    color: <?php echo $tertiaryDarker ?>;
    text-align: left;
    font-size: 1.1em;
    line-height: 1.75em;
}
#imRead {
    background-color: <?php echo $tertiaryMedium ?>;
}

/******************************* CONTENT : BLOCKS *****************************/

img.blocksAvatar {
    /* move the image up to be flush with bottom of title */
    position: relative;
    top: -5px;
    float: right;
    padding-left: 5px;
}
.navWidget {
    overflow: auto;
}
.options-block {
    margin: 20px 0;
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
.codeExample {
    background: #f7f7f7;
    border: 1px solid <?php echo $tertiary ?>;
    margin: 1em 1.75em;
    padding: 0.25em;
    overflow: auto;
    font-size: large;
}
.alignCenter {
    text-align: center;
}
.backLight {
    background-color: <?php echo $tableRowLight ?>;
}
.backDark {
    background-color: <?php echo $tableRowDark ?>;
}
.bold {
    font-weight: bold;
}
.error {
    color: <?php echo $errorTextMedium ?>;
}
.hide {
    display: none;
}
.small {
    font-size: 0.8em;
}
.narrow {
    width: 60%;
}
.wide {
    width: 90%;
}
.full {
    width: 100%;
}
.detail {
    color: <?php echo $tertiaryDark ?>;
    font-weight: normal;
    font-size: 0.8em;
}
.navigator {
    color: <?php echo $navigatorColor ?>;
    background-color: <?php echo $navigatorBackground ?>;
    padding-left: 10px;
    font-weight: bold;
    text-align: right;
    line-height: 18px;
}
.pager {
    background-color: <?php echo $errorTextLight ?>;
    white-space: nowrap;
    text-align: center;
    width: 90%;
    margin: 0 auto;
    padding: 2px 0;
    border: 1px dashed <?php echo $errorDark ?>;
}
.title {
    color: <?php echo $tertiaryDark ?>;
    font-weight: normal;
    font-size: 1.5em;
}
.toolBtnSeparate {
    margin-left: 20px;
}
.treeMenuDefault {
    font-size: 11px;
}
.dateSelector {
    cursor: pointer;
}

/******************************* CONTENT : FORMS ******************************/

fieldset {
    width: 80%;
    margin: 0 auto;
    color: <?php echo $secondaryDarker ?>;
    font-size: 1.1em;
    font-weight: bold;
}
legend {
    color: <?php echo $secondaryDarker ?>;
}
.fieldName, .fieldNameWrap {
    background-color: <?php echo $tertiaryLight ?>;
    color: <?php echo $secondaryDarker ?>;
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
.noBorder {
    border: none;
    font-size: 10px;
}
.narrowButton {
    text-align: center;
    width: 9em;
}
.wideButton {
    text-align: center;
    width: 13em;
}

/***************************** CONTENT : MESSAGES *****************************/

.errorContainer, .messageContainer {
    margin: 0 auto;
    width: 50%;
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
    width: 60%;
}
.messageHeader {
    color: <?php echo $primaryTextLight ?>;
    background-color: <?php echo $primary ?>;
    font-weight: bold;
    font-size: 1.2em;
    line-height: 1.5em;
    text-align: center;
}
.messageContent {
    background-color: <?php echo $primaryTextLight ?>;
    color: <?php echo $secondaryDarker ?>;
    border: 1px solid <?php echo $primary ?>;
    text-align: center;
}
.messageContent div {
    padding: 5px;
}
.message div{
    margin: 0 15% 10px;
    padding: 5px;
    background-color: <?php echo $primaryTextLight ?>;
    text-align: center;
}
.infoMessage {
    border: 1px solid <?php echo $primary ?>;
    color: <?php echo $primary ?>;
}
.errorMessage {
    border: 1px solid <?php echo $errorDark ?>;
    color: <?php echo $errorDark ?>;
}

/* /////////////// Lists /////////////// */

ul.noindent {
    margin-left: 5px;
    padding-left: 5px;
}
ul.bullets li {
    list-style-image: url('<?php echo $baseUrl ?>/images/bullet.gif');
}

/* /////////////// Tooltips /////////////// */

.tipOwner {
    position: relative;
    cursor: help;
    <?php if ($browserFamily == 'MSIE') {?>
    behavior: url(<?php echo $baseUrl ?>/css/tooltipHover.htc);
    <?php } ?>
}
.tipOwner .tipText {
    display: none;
    position: absolute;
    top: 0;
    left: 105%;
    border: 1px solid transparent;
    border-color: <?php echo $button ?>;
    background-color: <?php echo $tertiaryLight ?>;
    color: <?php echo $secondaryDarker ?>;
    text-align: center;
    width: 15em;
    padding: 2px 5px;
    <?php if ($browserFamily == 'Gecko') {?>
    -moz-opacity: 0.85;
    <?php } else if ($browserFamily == 'MSIE') {?>
    filter: alpha(opacity=85);
    filter: progid: DXImageTransform.Microsoft.Alpha(opacity=85);
    <?php } ?>
}
.tipOwner:hover .tipText {
    display: block;
}

/******************************* DEPRECATED ***********************************/

.bgnd {
    background-color: <?php echo $secondaryLight ?>;
    border: 1px solid <?php echo $tertiaryDark ?>;
}
.bgnd a, a.noDecoration {
    text-decoration: none;
}
.bgnd a {
    color: <?php echo $secondaryDarker ?>;
    font-weight: normal;
}
.moduleOverview {
    width: 20.5em;
    height: 8em;
}
.newsItem {
    border: 1px solid <?php echo $tertiaryDark ?>;
    margin: 0 auto;
    padding: 0 10px 10px 10px;
    background-color: <?php echo $errorTextLight ?>;
}
.pinstripe table {
    background-color: <?php echo $tertiaryLight ?>;
    width: 90%;
}
.pinstripe td {
    background-color: <?php echo $primaryTextLight ?>;
}
.pinstripe img {
    padding: 10px;
}
.pinstripe button {
    padding: 10px 0;
}
