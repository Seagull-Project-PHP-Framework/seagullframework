/******************************************************************************/
/*                                  LAYOUT CSS                                */
/******************************************************************************/

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
    height: 50px;
}
#sgl #logo {
    float: left;
    margin: 5px 5px 0 10px;
    font-size: 2em;
    font-weight: normal;
    color: <?php echo $primaryTextLight ?>;
    text-decoration: none;
}
#sgl #login {
    float: right;
    margin: 10px 10px 0 0;
    font-size: 0.9em;
    color: <?php echo $primaryTextLight ?>;
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
    width: 20.5em;
    height: 8em;
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
.backLight, .backLightBold {
    background-color: <?php echo $tableRowLight ?>;
}
.backDark, .backDarkBold {
    background-color: <?php echo $tableRowDark ?>;
}
.backLightBold, .backDarkBold {
    font-weight: bold;
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
.navWidget {
    overflow: auto;
}
.options-block {
    margin: 20px 0;
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
    /*z-index: 2;*/
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
.hide {
    display: none;
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
.newsItem {
    border: 1px solid <?php echo $tertiaryDark ?>;
    margin: 0 auto;
    padding: 0 10px 10px 10px;
    background-color: <?php echo $errorTextLight ?>;
}
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

/* /////////////// Links  /////////////// */

.linkCrumbsAlt1 {
    text-decoration: none;
    color: <?php echo $secondaryDarker ?>;
    font-weight: normal;
    letter-spacing: 0.5px;
}
.linkCrumbsAlt1:hover {
    text-decoration: underline;
    color: <?php echo $secondaryDarker ?>;
}

/* /////////////// Various /////////////// */

.navigator {
    color: <?php echo $navigatorColor ?>;
    background-color: <?php echo $navigatorBackground ?>;
    padding-left: 10px;
    font-weight: bold;
    text-align: right;
    line-height: 18px;
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
    padding: 2px 0;
    border: 1px dashed <?php echo $errorDark ?>;
}
.narrowButton {
    text-align: center;
    width: 9em;
}
.wideButton {
    text-align: center;
    width: 13em;
}
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
    color: <?php echo $primaryText ?>;
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
    border-top: none;
    text-align: center;
}
.messageContent div {
    padding: 5px;
}
.messageContent input {
    width: 13em;
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
.bgnd a, a.noDecoration {
    text-decoration: none;
}
.bgnd a {
    color: <?php echo $secondaryDarker ?>;
    font-weight: normal;
}
.treeMenuDefault {
    font-size: 11px;
}

/* /////////////// Tooltips /////////////// */

.tipOwner {
    position: relative;
    cursor: help;
    /* IE :hover javascript workaround */
    behavior: url(<?php echo $baseUrl ?>/css/tooltipHover.htc);
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
    -moz-opacity: 0.85;
    filter: alpha(opacity=85);
    filter: progid: DXImageTransform.Microsoft.Alpha(opacity=85);
}
.tipOwner:hover .tipText {
    display: block;
}
