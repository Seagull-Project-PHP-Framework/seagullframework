/******************************************************************************/
/*                                  LAYOUT CSS                                */
/******************************************************************************/

body {
    margin: 0;
    padding: 0;
    font: 0.8em <?php echo $fontFamily ?>;
    color: <?php echo $tertiaryDarker ?>;
    background: <?php echo $primaryTextLight ?>;
    width: 100%;
}
p, h1, h2, h3, h4, h5, h6, ul, form {
    margin:0;
    font-size: 1em;
}
ul {
    list-style: none;
    padding: 0;
}
a {
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
a.button {
    min-width: 8em;
    padding: 4px;
    background: #fff;
    color: #afafaf;
    border: 1px solid #dfdfdf;
    border-color: #cfcfcf #cfcfcf #808080 #808080;
}
.floatLeft {
    float: left;
}
.floatRight {
    float: right;
}
.clear {
    clear: both;
}
.clearLeft {
    clear: left;
}
.clearRight {
    clear: right;
}
.center {
    text-align: center;
}
.spacer {
    clear: both;
    visibility: hidden;
}
/******************************* LAYOUT : HEADER ******************************/

#header {
    background: #339ed3 url('<?php echo $baseUrl ?>/images/banner_bg_px.gif') repeat-x;
    height: 60px;
    padding: 0px 10px;
}
#header-left {
    float: left;
    height: 60px;
    background: url('<?php echo $baseUrl ?>/images/logo.gif') no-repeat 0 5px;
    margin: 0;
    padding-left: 100px;
    font-size: 2em;
    color: <?php echo $primaryTextLight ?>;
}
#header-right {
    float: right;
    margin: 10px;
    margin-right: 10px;
    line-height: 150%;
    font-size: 0.9em;
    color: <?php echo $primaryTextLight ?>;
}
#header-right a {
    padding: 0 5px;
    color: <?php echo $primaryTextLight ?>;
}
#header-right #headerUserLabels {
    float: left;
    width: 200px;
    margin-right: 5px;
    text-align: right;
    font-weight: bold;
    color: <?php echo $primaryDark ?>;
}
#header-right #headerUserInfo {
    float: left;
    text-align: left;
}
#header-right #headerUserInfo .guest {
    font-weight: bold;
}
#header-right #headerAction {
    float: left;
    margin-left: 1em;
}
#header-right #headerAction a {
    color: <?php echo $tertiaryDarkest ?>;
}
/***************************** LAYOUT : TABLES ********************************/

#container table {
    /* Actually concerns all table but #container specialization is required not to interfere with FCKeditor css */
    background: #fff;
    border: none;
    border-collapse: collapse;
    /* This is not a typo, we want first set a fallback for IE, then set the
     * real margin for real browsers ;) */
    margin: 0 5%;
    margin: 0 auto;
}
td, th {
    padding: 2px;
    border: 1px solid #fff;
}
th img, td img {
    vertical-align: middle;
    text-align: center;
}
th {
    background: <?php echo $secondary ?> url('<?php echo $baseUrl ?>/images/th_bg.gif') repeat-x;
    text-align: center;
    color: <?php echo $primaryTextLight ?>;
    font-size: 1em;
    letter-spacing: 0.1px;
    line-height: 1.75em;
}
th.noBg {
    background: none;
}
th a {
    color: <?php echo $primaryTextLight ?>;
}
th a:hover {
    text-decoration: underline;
    color: <?php echo $primaryTextLight ?>;
}
tfoot tr{
    
}
tfoot tr input{
    vertical-align: middle;
}
#imRead {
    background-color: <?php echo $tertiaryMedium ?>;
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
.backLight {
    background-color: #ffdaa9;
}
.backDark {
    background-color: #ffeed7;
}
.bold {
    font-weight: bold;
}
.backHighlight {
    background-color: <?php echo $secondaryLight ?>;
}

/****************************** LAYOUT : FORMS ********************************/
form {
    margin: 0 auto;
    padding: 5px;
    font-weight: normal;
}
fieldset {
    margin: 0 0 2em;
    padding: 10px;
    border: 1px solid <?php echo $tertiaryDark ?>;
}
fieldset legend {
    color: <?php echo $secondary ?>;
    font-weight: normal;
}
form h3 {
    background: <?php echo $secondary ?> url('<?php echo $baseUrl ?>/images/th_bg.gif') repeat-x;
    margin-bottom: 1em; 
    padding: 0.5em 0;
    text-align: center;
    color: <?php echo $primaryTextLight ?>;
    font-size: 1.2em;
    font-weight: bold;
}
fieldset p {
    clear: left;
    margin: 0.3em 0 0.2em;
    line-height: 1.8;
}
fieldset p label {
    float: left;
    color: <?php echo $primary ?>;
}
input {
    font-weight: normal;
}
/*********** Column layout within forms **************/
/* Put some fieldset(s) inside a div class="formOptions"
/* Main part of the form in a div class="formContent" */
div.formOptions {
    float: left;
    width: 33%;
    margin: 0 15px 0 5px;
}
.formOptions h4 {
    padding: 5px;
    background: <?php echo $tertiary ?>;
    text-align: right;
    color: <?php echo $secondary ?>;
}
.formOptions fieldset {
    border: 1px solid <?php echo $tertiary ?>;
}
div.formContent {
    width: auto;
}
.formContent fieldset {
    padding: 0;
    border: none;
}
.formContent legend {
    padding: 0 0.5em;
}
/*********************** LAYOUT : MODULES QUICK ACCESS ************************/
div#modules-quickaccess {
    position: absolute;
    top: 11px;
    left: -134px;
    <?php if ($browserFamily == 'MSIE') {?>
    left: -144px;
    <?php } ?>
    background: #fff;
    color: #ffffcc;
    border-right: 10px solid #339ed3;
    z-index:10000;
}
div.hide#modules-quickaccess {
    display: block;
    left: -105px;
}
div.show#modules-quickaccess {
    position: absolute;
    top: 10px;
    left: 0px;
}

#modules-quickaccess div.item {
    width: 100px;
    margin: 4px 15px;
    padding: 15px 0;
    background: #efefef;
    color: #808080;
    border: 2px solid #e5e5e5;
    -moz-border-radius: 0.3em;
    cursor: pointer;
    text-align: center;
}
#modules-quickaccess div.item:hover {
    background: <?php echo $palette['secondary']['pastel'] ?>;
    color: #808080;
    border: 2px solid #d3d3d3;
}

/****************************** LAYOUT : MAIN *********************************/

#container {
    background: url('<?php echo $baseUrl ?>/images/block_bg_px.gif') repeat-x;
    padding: 10px 10px 25px;
}

/************************** LAYOUT : MODULE HEADER ****************************/

#sgl-module-header {
    padding: 5px 0;
    background: url('<?php echo $baseUrl ?>/images/block_small_bg.gif') repeat-x;
    border-bottom: 1px solid #cfcfcf;
}
.module-desc {
    height: 50px;
    padding-left: 60px;
    color: <?php echo $tertiaryDarkest ?>;
}
#sgl-module-header > .module-desc {
    height: auto; /*special gecko*/
    min-height: 50px;
}
.module-desc h1 {
    margin-bottom: 0.5em;
    font-size: 1.3em;
    font-weight: bold;
    color: <?php echo $secondary ?>;
}
.module-desc p{

}
#sgl-module-header-right {
    
}
.actions-desc {
    float: right;
    margin: 0 10px 0 5px;
    padding-left: 10px;
    border-left: 1px solid <?php echo $tertiaryMedium ?>;
    line-height: 120%;

}
.actions-desc h2{
    margin-bottom: 0.3em;
    font-size: 1.1em;
    letter-spacing: 0.2em;
    color: #ea0c0c;
}
.actions-desc a {
    display: block;
    line-height: 16px;
    margin-bottom: 0.3em;
    font-size: 1em;
    font-weight: normal;
    color: <?php echo $primary ?>;
}
.actions-desc a:hover{
    color: <?php echo $primary ?>;
    text-decoration: underline;
}

/************************** LAYOUT : MODULE IMAGES ****************************/
#module-default {
    background: url('<?php echo $baseUrl ?>/images/sm_default.png') no-repeat 5px 0;
}
#module-navigation {
    background: url('<?php echo $baseUrl ?>/images/sm_navigation.png') no-repeat 5px 0;
}
#module-user {
    background: url('<?php echo $baseUrl ?>/images/sm_users.png') no-repeat 5px 0;
}
#module-guestbook {
    background: url('<?php echo $baseUrl ?>/images/sm_core.png') no-repeat 5px 0;
}
#module-publisher {
    background: url('<?php echo $baseUrl ?>/images/sm_publisher.png') no-repeat 5px 0;
}
#module-block {
    background: url('<?php echo $baseUrl ?>/images/sm_block.png') no-repeat 5px 0;
}
#module-maintenance {
    background: url('<?php echo $baseUrl ?>/images/sm_maintenance.png') no-repeat 5px 0;
}
#module-admin {
    background: url('<?php echo $baseUrl ?>/images/sm_navigation.png') no-repeat 5px 0;
}

/************************ LAYOUT : LEFT & RIGHT BLOCKS ************************/
#leftSidebar, #sgl-blocks-right {
    margin: 1px 0px 0px;
}
#leftSidebar {
    float: left;
    width: <?php echo $blocksWidthLeft ?>;
}
#sgl-blocks-right {
    width: <?php echo $blocksWidthRight ?>;
}
.navWidget {
    overflow: auto;
    padding: 5px;
    <?php if ($browserFamily == 'Gecko')
    echo "margin-top: -15px;\n";
    ?>
}
.options-block {
    margin: 20px 0;
}
#leftSidebar .blockContainer, #sgl-blocks-right .blockContainer {
    margin: 0 10px 10px 0;
    border: 1px solid #fff;
}
.blockContainer h2 {
    background: url('<?php echo $baseUrl ?>/images/block_xsmall_bg.gif') repeat-x;
    padding: 3px 5px 3px;
    font-size: 1.2em;
    color: #339ecc;
}
.blockContainer .blockContent {
    background: #fcfcfc;

    width: 100%;
    overflow: auto;
}

/*************************** LAYOUT : MIDDLE BLOCKS ***************************/
#content, #content-nocols, #content-leftcol, #content-rightcol {
    position: relative;
    margin: 0 <?php echo $blocksWidthRight; ?> 0 <?php echo $blocksWidthLeft ?>;
    min-width: 20%;
    padding: 10px 0 5px;
    font-size: 0.9em;
    border: 1px solid #e3e3e3;
    background: #fff;
}
#content #options {
    float: right;
    width: 28%;
}
#content-nocols {
    width: 100%;
    margin: 0;
}
#content-leftcol {
    margin: 0 0 0 <?php echo $blocksWidthLeft ?>;
}
#content-rightcol {
    margin: 0 <?php echo $blocksWidthRight ?> 0 0;
}
/* Holly Hack here so that tooltips don't act screwy:
 * http://www.positioniseverything.net/explorer/threepxtest.html */
/* Hide next from Mac IE plus non-IE \*/
* html #content {
    height: 1%;
}
/* End hide from IE5/mac plus non-IE */

/******************************* LAYOUT : FOOTER ******************************/

#footer {
    margin: 0 1%;
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
    margin-top: 10px;
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
}
a:visited {
    
}
a:hover {
    color: <?php echo $primaryDark ?>;
    text-decoration: none;
}

/******************************* CONTENT : BLOCKS *****************************/

img.blocksAvatar {
    /* move the image up to be flush with bottom of title */
    position: relative;
    top: -5px;
    float: right;
    padding-left: 5px;
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
.clear {
    clear: both;
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
.nobr {
    white-space: nowrap;
}
.editLink a {
    color: <?php echo $tertiaryDark ?>;
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
}
ul.noindent {
    margin-left: 5px;
    padding-left: 5px;
}
ul.bullets li {
    list-style-image: url('<?php echo $baseUrl ?>/images/bullet.gif');
}
.pager {
    white-space: nowrap;
    text-align: center;
    margin: 0 auto 10px;
    padding: 1px 2em;
    border: 1px solid <?php echo $primary ?>;
}
.pager .currentPage {
    font-weight: bold;
    padding: 0 0.5em;
}
.pager a {
    padding: 0 0.5em;
    color: <?php echo $primary ?>;
    font-weight: bold;
}
.narrowButton {
    text-align: center;
    width: 100px;
}
.wideButton {
    text-align: center;
    width: 150px;
}
.warning {
    color: #ff3300;
}

input.button-calendar {
    border: none;
    background: url('<?php echo $baseUrl ?>/images/calendrier1.gif') no-repeat;
    position: relative;
    top: 2px;
    left: 5px;
    width: 34px;
    height: 21px;
}
input.button-calendar-hover:hover, input.button-calendar-hover {
    border: none;
    background: url('<?php echo $baseUrl ?>/images/calendrier1_hover.gif') no-repeat;
    position: relative;
    top: 2px;
    left: 5px;
    width: 34px;
    height: 21px;
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
    background-color: <?php echo $primaryText ?>;
    border: 1px solid <?php echo $tertiaryDark ?>;
}
.bgnd img{
    border: none;
}
.treeMenuDefault {
    font-size: 11px;
}

/* /////////////// Tooltips /////////////// */

span.tipOwner, label.tipOwner, input.tipOwner {
    position: relative;
    cursor: help;
}
label.tipOwner {
    background: url('<?php echo $baseUrl ?>/images/help-browser.gif') no-repeat 96% 50%;
}
span.tipOwner span.tipText, label span.tipText, input.tipOwner span.tipText {
    display: none;
    position: absolute;
    top: 2em;
    left: 100%;
    border: 1px solid transparent;
    border-color: <?php echo $primary ?>;
    background-color: <?php echo $secondaryLight ?>;
    color: <?php echo $primary ?>;
    text-align: center;
    line-height: 1.4;
    width: 20em;
    padding: 2px 5px;
    -moz-opacity: 1;
    z-index: 100;
    <?php if ($browserFamily == 'MSIE') {?>
    filter: alpha(opacity=100);
    filter: progid: DXImageTransform.Microsoft.Alpha(opacity=85);
    <?php } ?>
}
span.tipOwner:hover span.tipText, label:hover span.tipText, input.tipOwner:hover span.tipText {
    display: block;
}
<?php if ($browserFamily == 'MSIE') {?>
/* IE javascript workaround */
span.tipOwner, label.tipOwner, input.tipOwner {
    behavior: url(<?php echo $baseUrl ?>/css/tooltipHover.htc);
}
<?php } ?>
/************* Special TipText boxes ********************/
span#becareful {
    top: -2em;
    left: -6.5em;
    width: 6em;
    padding: 5px;
    background: #fff;
    border: 1px solid #ff3300;
    color: #ff3300;
}