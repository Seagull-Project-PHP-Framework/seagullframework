/******************************************************************************/
/*                                  LAYOUT CSS                                */
/******************************************************************************/

/*
==========================General=============================*/
html {
    height: 100%;
    margin-bottom: 1px;
}
body, h1, h2, h3, h4, p, ul, li {
    margin: 0;
    padding: 0;
}
body {
    font-size: <?php echo $fontSize ?>;
    font-family: <?php echo $fontFamily ?>;
    line-height: 140%;
    margin: 0;
    padding: 0;
    color: <?php echo $primaryText ?>;
    background-color: <?php echo $tertiaryBg ?>;
    text-align: center;
}
ul {
    list-style: none;
}
dl {
    margin: 0.5em 0;
    line-height: 140%;
}
p {
    margin-bottom: 0.5em;
}
a {
    color: <?php echo $linkColor ?>;
    text-decoration: <?php echo $linkDecoration ?>;
}
a:hover {
    color: <?php echo $linkHoverColor ?>;
    text-decoration: <?php echo $linkHoverDecoration ?>;
}
a:focus {
    outline: none;
}
img {
    border: none;
}

/*
=======================Buttons like===========================*/
a.sgl-button, input.sgl-button {
    margin: 0;
    padding: 2px 4px;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_buttons_blue.gif') 0 50% repeat-x;
    border: 1px outset;
    color: <?php echo $tertiaryDarkest ?>;
    font-size: 1em;
}
input.sgl-button[disabled], input.sgl-button[disabled]:hover {
    background: <?php echo $tertiary ?>;
    border: 1px inset;
    color: <?php echo $tertiaryDarkest ?>;
}
a.sgl-button:hover, input.sgl-button:hover, input.sfhover {
    border: 1px inset;
    color: <?php echo $tertiaryDarkest ?>;
    text-decoration: none;
}
a.narrow, input.narrow {
    width: 8em;
}

/*
======================Global layaout==========================*/
#outer-wrapper {
    margin: 0 auto;
    text-align: left;
<?php if (isBrowserFamily('MSIE7', '<')) { ?>
    width: expression((documentElement.clientWidth || document.body.clientWidth) > 740 ? "auto" : "740px");
<?php } ?>
}
#header {
    position: relative;
}
#inner-wrapper {
    clear: both;
    padding: 10px 0 10px;
    border-top: 1px solid <?php echo $borderLight ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
}
#container {

}
#left-column {
    float: left;
    width: 200px;
}
#middle-column {
    margin-left: 200px;
    margin-right: 5px;
}
#main {
    float: left;
    width: 100%;
    font-size: 1em;
    border: 1px solid <?php echo $borderDark ?>;
    -moz-border-radius: 0.4em;
    margin-right: -1px; /* hides annoying horizontal scrolling in ie6 */
<?php if (isBrowserFamily('MSIE7')) { ?>
    margin-right: -2px; /* hides annoying horizontal scrolling in ie7 */
<?php } ?>
}
#content {
    clear: both;
    padding: 5px 8px 0;
    background: <?php echo $tertiaryLight ?>; /* FIXME tertiaryLight? */
    -moz-border-radius: 0 0 0.4em 0.4em;
    border-top: 1px solid <?php echo $borderLight ?>;
    padding-bottom: 40px; /* TO REMOVE */
}
#footer {
    text-align: center;
    border-top: 1px solid <?php echo $borderLight ?>;
    background: <?php echo $tertiary ?>;
}

/*
=========================Header===============================*/
#header #left {
    height: 60px;
    background: <?php echo $primary ?> url('<?php echo $baseUrl ?>/images/backgrounds/bg_header_blue.gif') repeat-x;

}
#header h1, #header h1 a {
    float: left;
    margin: 0;
    padding: 0;
}
#header h1 {
    height: 60px;
    background: url('<?php echo $baseUrl ?>/images/logo.png') no-repeat 10px 50%;
    font-size: 2.4em;
}
#header h1 a {
    position: relative;
    padding: 10px 0 10px;
    text-indent: 100px;
    letter-spacing: 0.1em;
    color: <?php echo $secondary ?>;
}
#header h1 a:hover {
    text-decoration: none;
}
#header h1 a span {
    position: relative;
    top: 15px;
    left: -20px;
    font-size: 0.5em;
    font-weight: bold;
    letter-spacing: 0.2em;
    color: <?php echo $primary ?>;
}
#header #right {
    background-color: <?php echo $tertiary ?>;
    border-top: 1px solid <?php echo $borderLight ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
    text-align: right;
}
#header #right .info {
    height: 20px;
    line-height: 20px;
    padding: 0 10px;
}
#header #right img {
    vertical-align: middle;
}

/*
====================Left column block=========================*/
.container {
    min-height: 300px;
    margin: 0 5px;
    padding-bottom: 3em;
    background-color: <?php echo $tertiaryDarker ?>;
    background-image: url('<?php echo $baseUrl ?>/images/backgrounds/bg_leftcol.jpg');
    background-position: top left;
    background-repeat: repeat-x;
    border: 1px solid <?php echo $borderDark ?>;
    -moz-border-radius: 0.4em;
}
.block .header {
    margin: 0;
    height: 30px;
    line-height: 30px;
    background: <?php echo $tertiaryLight ?>;
    font-weight: bold;
    font-size: 1.1em;
    text-align: center;
    color: buttonshadow;
    border-bottom: 1px solid <?php echo $borderDark ?>;
    -moz-border-radius: 0.4em 0.4em 0 0;
}
.block .content {
    border-bottom: 1px solid <?php echo $borderLight ?>;
}

/*
========================Main block============================*/
#manager-infos {
    float: left;
    width: 100%;
    background: <?php echo $tertiaryLighter ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
    -moz-border-radius: 0.4em 0.4em 0 0;
}
#manager-infos h1 {
    float: left;
    height: 30px;
    line-height: 30px;
    text-indent: 2em;
    color: <?php echo $tertiaryDarkest ?>;
    font-size: 1.1em;
}
a#module-conf {
    float: right;
    margin-right: 10px;
    padding-left: 25px;
    height: 30px;
    line-height: 30px;
    background: <?php echo $tertiary ?>;
    background: url('<?php echo $baseUrl ?>/images/22/action_config.gif') 0 50% no-repeat;
    color: <?php echo $primary ?>;
}
#breadcrumbs {
    float: left;
    width: 100%;
    background: <?php echo $tertiary ?>;
    border-top: 1px solid <?php echo $borderLight ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
}
#breadcrumbs p {
    text-indent: 2em;
}
#breadcrumbs a  {
    padding-right: 20px;
    color: <?php echo $primary ?>;
}
#manager-actions {
    position: relative;
    float: left;
    width: 100%;
    height: 32px;
    padding: 1px 0;
    background: <?php echo $tertiary ?>;
    border-top: 1px solid <?php echo $borderLight ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
}
html>body #manager-actions {
    padding: 1px 0 0;
}
#manager-actions span {
    float: left;
    text-indent: 2em;
    padding-right: 10px;
    height: 30px;
    line-height: 30px;
    font-weight: bold;
    color: <?php echo $tertiaryDarkest ?>;
}
#manager-actions a {
    float: left;
    display: block;
    margin-right: 0.5em;
    padding: 0 4px 0 28px;
    height: 28px;
    line-height: 28px;
    border: 1px solid <?php echo $tertiary ?>;
    color: <?php echo $tertiaryDarkest ?>;
    text-decoration: none;
    /* -- See below for each action backgroud image
    -----------------------------------------------*/
}
#manager-actions a:hover {
    background-color: <?php echo $tertiaryLightest ?>;
    border-style: solid;
    border-width: 1px;
    border-color: <?php echo $tertiaryDarkest ?>;
}
#manager-actions a:active {
    background-color: <?php echo $tertiaryLightest ?>;
    border: none;
}
#manager-actions a:focus {
    background-color: <?php echo $tertiaryLightest ?>;
    border: none;
}

#manager-actions select {
    float: left;
    margin-top: 5px;
    margin-right: 5px;
}
#content-header {

}
#content-header h2 {
    margin: 5px 0 20px;
    font-size: 1.5em;
    font-weight: normal;
    color: <?php echo $primary ?>;
    text-align: center;
}
/*
======================No forms layout=========================*/
div.fieldsetlike { /*
--------------------- as some pages don't use forms/fieldsets
- e.g. module/overview, we have to put data in a fieldset like
- div to have same render ------------------------------------*/
    padding: 0;
}
div.fieldsetlike h3 {
    margin-bottom: 0.5em;
    font-size: 1.2em;
    color: <?php echo $tertiaryDark ?>;
}
/*
===================Forms default layout=======================*/
form {
    float: left;
    width: 100%; /* FIXME
    ------2 lines-- determine if could be removed */
    margin: 0;
    padding: 0;
}
fieldset {
    margin: 0 0 1em;
    padding: 10px;
    border: 1px solid <?php echo $borderDark ?>;
}
fieldset.options h3 {
    visibility: hidden;
}
fieldset.options h3.show {
    visibility: visible;
}
select, input, textarea {
    font-size: 1.1em;
    z-index: 1;
}
html>body p select, html>body p input, html>body p textarea {
    border: 1px solid <?php echo $primary ?>;
}
html>body p input[type="text"] {
    text-indent: 2px;
}
p input:focus, p textarea:focus {
    background: <?php echo $primaryLightest ?>;
}
fieldset.noBorder {
    padding: 0;
    border: none;
}
fieldset.inside { /*
    -------------- also for pages without form (e.g. module/overview) */
    background: <?php echo $tertiaryLightest ?>;
}
fieldset.options {
    clear: left;
    background: <?php echo $tertiaryLightest ?>;
    border-top: none;
}
form h3 {
    margin-bottom: 0.5em;
    font-size: 1.2em;
    color: <?php echo $tertiaryDark ?>;
}
fieldset h3 {
    font-size: 1.1em;
}
fieldset p {
    line-height: 1.8;
    margin: 0.5em 0 0.5em;
}
fieldset p label {
    float: left;
    text-align: right;
    padding-right: 20px;
    color: <?php echo $primaryDark ?>;
}
fieldset textarea {
    font-family: <?php echo $fontFamily ?>;
}
input.smallText {
    width: 70px;
}
input.mediumText, textarea.mediumText, select.mediumText {
    width: 120px;
}
input.longText, textarea.longText {
    width: 450px;
}
textarea.largeText {
    width: 450px;
    height: 200px;
}
/* --
Definition lists (<dl>) will progressively replace "p label" to display fields labels and values
-----*/
dl.onSide dt {
    float: left;
    width: 140px;
    text-align: right;
}
dl.onSide dt label {
    padding-right: 15px;
}
dl.onSide dd{
    margin-left: 160px;
    margin-bottom: 0.5em;
}
dl.onTop dd {
    margin: 0;
}
dl.buttonsBottom {
    clear: both;
    float: left;
}

/*
==================Tables default layout=======================*/
#container table { /*
------------------- Actually concerns all table but #container specialization is required not to interfere ------------------- with FCKeditor or JsCalendar css */
    margin: 5px 0;
    background: #fff;
    border-collapse: collapse;
    border-width: 1px;
    border-style: solid;
    border-color: <?php echo $tertiaryLightest ?> <?php echo $tertiaryDarker ?> <?php echo $tertiaryDarker ?> <?php echo $tertiaryLightest ?>;
    font-family: helvetica;
}
html>body #container table {
<?php if (!isBrowserFamily('MSIE')) { ?>
    border-collapse: separate;
<?php } ?>
    border-spacing: 0px;
}
#container table.noBorder {
    border: none;
}
#container table.transparent {
    background-color: transparent;
    border: none;
}
#container tr {
    height: 24px;
    line-height: 24px;
    vertical-align: middle;
}
tr img, tr input {
    vertical-align: middle;
}
tr th, tr td {
    text-align: center;
    border: none;
}
tr th {
    color: #afafaf;
    font-weight: bold;
}
thead tr {
    background: <?php echo $primary; ?>;
    color: <?php echo $tertiaryLightest ?>;
}
thead tr.infos, tfoot tr.infos {
    background: <?php echo $tertiaryBg ?>;
    font-size: 10px;
    color: <?php echo $primary; ?>;
}
thead td {
    padding: 0 12px;
}
thead th, thead th a {
    color: <?php echo $tertiaryLightest ?>;
}
thead tr a:hover {
    color: <?php echo $primaryDark ?>;
}
tr.backLight {
    background: <?php echo $tableRowLight ?>;
    border-bottom: 1px solid <?php echo $primaryLight ?>;
}
tr.backDark {
    background: <?php echo $tableRowDark ?>;
    border-bottom: 1px solid <?php echo $primaryLight ?>;
}
tr.backHighlight {
    background: <?php echo $primaryLightest ?>;
}

/*
==========================Tip boxes===========================*/
span.tipOwner, label.tipOwner, input.tipOwner {
    position: relative;
    cursor: help;
}
label.tipOwner {
    background: url('<?php echo $baseUrl ?>/images/tooltip.gif') 98% 50% no-repeat;
}
span.tipOwner span.tipText, label.tipOwner span.tipText, input.tipOwner span.tipText {
    display: none;
    position: absolute;
    top: 1.8em;
    left: 100%;
    border: 1px solid <?php echo $borderDark ?>;
    background-color: <?php echo $primaryBackground ?>;
    color: <?php echo $primaryText ?>;
    text-align: center;
    line-height: normal;
    width: 20em;
    padding: 2px 5px;
    -moz-opacity: 1;
    z-index: 100;
    <?php if (isBrowserFamily('MSIE')) { ?>
    filter: alpha(opacity=100);
    filter: progid: DXImageTransform.Microsoft.Alpha(opacity=100);
    <?php } ?>
}
span.tipOwner:hover span.tipText, label.tipOwner:hover span.tipText, input.tipOwner:hover span.tipText {
    display: block;
}
<?php if (isBrowserFamily('MSIE7', '<')) { ?>
/* IE javascript workaround */
span.tipOwner, label.tipOwner, input.tipOwner {
    behavior: url(<?php echo $baseUrl ?>/css/tooltipHover.htc);
}
<?php } ?>
/* Holly Hack here so that tooltips don't act screwy:
 * http://www.positioniseverything.net/explorer/threepxtest.html */
/* Hide next from Mac IE plus non-IE \*/
* html #content {
    height: 1%;
}
/* End hide from IE5/mac plus non-IE */

/*-- Special TipText boxes ----------------------------------*/
span#becareful {
    top: -35px;
    left: -3.5em;
    width: 6em;
    padding: 5px;
    background: #fff;
    border: 1px solid #ff3300;
    color: #ff3300;
    z-index: 150;
}

/*
===================Manager-actions images=====================*/
/*-- Each action link (<a> tag) has a standard "action" class name
  -- plus a specific <action-type> class name e.g. "add", "edit",...
  -- to define which image to use. This allows to change assigned
  -- images in a single location : here. ---------------------*/
a.action {
    background-position: 3px 50%;
    background-repeat: no-repeat;
}
a.add {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_add.gif');
}
a.edit {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_edit.gif');
}
a.delete {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_delete.gif');
}
a.save {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_save.gif');
}
a.validate {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_validate.gif');
}
a.cancel {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_cancel.gif');
}
a.undo {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_undo.gif');
}
a.download {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_download.gif');
}
a.upload {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_upload.gif');
}
a.reorder {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_reorder.gif');
}
a.search {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_search.gif');
}
a.addcat {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_addcat.gif');
}
a.addrootcat {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_addrootcat.gif');
}
a.adduser {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_adduser.gif');
}
a.scannew {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_scannew.gif');
}
a.delorphaned {
    background-image: url('<?php echo $baseUrl ?>/images/22/action_delorphaned.gif');
}

/*
======================CategoryNav Block=======================*/
div#categorySelect {
    float: left;
    width: 20%;
}
a.catSelect {
    margin-left: 1em;
    padding: 2px 0 2px 20px;
    background: url('<?php echo $baseUrl ?>/images/16/folder.gif') 0 80% no-repeat;
}
a.catSelect:hover {
    background: url('<?php echo $baseUrl ?>/images/16/folder_open.gif') 0 80% no-repeat;
    text-decoration: none;
    cursor: pointer;
}
div#categoryNav {
    display: none;
    position: absolute;
    width: 200px;
    background: <?php echo $tertiaryLightest ?>;
    border-style: solid;
    border-color: <?php echo $primary ?>;
    border-width: 1px 2px 2px 1px;
    z-index: 100;
    <?php if (isBrowserFamily('MSIE')) { ?>
    filter: alpha(opacity=90);
    <?php } else { ?>
    -moz-opacity: 0.9;
    <?php } ?>
}

div.close {
    border-bottom: 1px solid <?php echo $primary ?>;
    text-align: right;
}
div.close span {
    padding-right: 20px;
    background: url('<?php echo $baseUrl ?>/images/close.gif') 92% 50% no-repeat;
    color: <?php echo $primary ?>;
    cursor: pointer;
}

/*
========================Options Links=========================*/
#optionsLinks {
    float:left;
    width:100%;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_tabs.gif') repeat-x left bottom;
    font-size: 0.9em;
}
#optionsLinks ul {
    padding:10px 7px 0;
}
#optionsLinks li {
    float:left;
    width: auto;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/tab_right.gif') no-repeat right top;
}
#optionsLinks li.current {
    background-image: url('<?php echo $baseUrl ?>/images/backgrounds/tab_right_on.gif');
}
#optionsLinks a {
    display: block;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/tab_left.gif') no-repeat left top;
    padding: 5px 10px 4px;
}
#optionsLinks li.current a {
    background-image: url('<?php echo $baseUrl ?>/images/backgrounds/tab_left_on.gif');
    padding-bottom: 5px;
}

/*
===========================Messages==============================*/

/*-- Seagull Errors ---------------------------------------------*/
.message {
    text-align: center;
    font-size: 0.9em;
    z-index: 1;
}
.message div {
    width: 60%;
    margin: 0 auto 15px;
    padding: 5px 25px;
    background-color: <?php echo $tertiaryLightest ?>;
    background-position: 0 50%;
    background-repeat: no-repeat;
    border-width: 2px;
    border-style: solid;
    -moz-border-radius: 0.4em;
}
.infoMessage {
    background-image: url('<?php echo $baseUrl ?>/images/22/dialog_info.gif');
    border-color: <?php echo $infoMessage ?>;
}
.errorMessage {
    background-image: url('<?php echo $baseUrl ?>/images/22/dialog_error.gif');
    border-color: <?php echo $errorMessage ?>;
}
.warningMessage {
    background-image: url('<?php echo $baseUrl ?>/images/22/dialog_warning.gif');
    border-color: <?php echo $warningMessage ?>;
}
.error, .required {
    color: <?php echo $errorMessage ?>;
}
.warning {
    color: <?php echo $warningMessage ?>;
    font-size: 1.2em;
}

/*-- PHP Errors by ErrorHandler.php -----------------------------*/


/*-- PEAR Errors ------------------------------------------------*/
.errorContainer {
    text-align: left;
}
.errorContainer div{
    width: auto;
    margin: 0;
    padding: 5px 0;
    border: none;
}
.errorContainer .errorHeader {
    padding-left: 30px;
    background-image: url('<?php echo $baseUrl ?>/images/22/dialog_error.gif');
    text-transform: uppercase ;
    font-size: 1.2em;
    font-weight: bold;
    letter-spacing: 0.3em;
    color: <?php echo $errorMessage ?>;
}
.errorContainer .errorContent {

}

/*-- Errors in submitted forms ----------------------------------*/
p.errorBlock label {
    background: url('<?php echo $baseUrl ?>/images/16/dialog_error.gif') 98% 50% no-repeat;
    font-weight: bold;
}
p.errorBlock span.required {
    display: none;
}
p.errorBlock span.error {
    display: block;
    line-height: normal;
}
p.errorBlock input, p.errorBlock select {
    display: block;
    border: 1px solid <?php echo $errorMessage ?>;
}
<?php
    if (!empty($isFormSubmitted)) { ?>
.required {
    display: none;
}
.helpRequire {
    display: none;
}
    <?php }
?>

/*
========================Miscellaneous=========================*/
.floatLeft {
    float: left;
}
.floatRight {
    float: right;
}
.clear {
    clear: both;
}
.spacer {
    clear: both;
    visibility: hidden;
    line-height: 1px;
}
.left {
    text-align: left;
}
.right {
    text-align: right;
}
.center {
    text-align: center;
}
.altFont {
    font-family: <?php echo $fontFamilyAlt ?>;
}
.hide {
    display: none;
}
.narrow {
    width: 45%;
}
.wide {
    width: 60%;
}
.full {
    width: 100%;
}
.noBg {
    background: none;
}
.pager {
    white-space: nowrap;
    font-size: 1.1em;
}
.pager .results {
    padding-right: 0.5em;
    color: <?php echo $tertiaryDarkest ?>;
}
.pager .currentPage {
    font-weight: bold;
    padding: 0 0.75em;
    color: <?php echo $primaryDark ?>;
    font-weight: bold;
}
.pager a {
    padding: 0 0.75em;
    color: <?php echo $primary ?>;
    font-weight: bold;
}
.pager a:hover {
    background: <?php echo $primaryDark ?>;
    color: <?php echo $tertiaryLightest ?>;
}
a.clearSearch {
    background: url('<?php echo $baseUrl ?>/images/16/action_no.gif') no-repeat 5px 50%;
    margin-left: 10px;
    padding: 3px 5px 3px 25px;
    color: <?php echo $tertiaryDarkest ?>;
    font-size: 1.1em;
}
a.clearSearch:hover {
    text-decoration: none;
    color: <?php echo $tertiaryDarkest ?>;
}
.disabled, a.disabled, a.disabled:visited {
    color: grey;
}


#debug {
    color: #333333;
    position: absolute;
    z-index: 999;
    top: 0px;
    right: 0px;
    border: 1px black solid;
    margin: 10px;
    padding: 5px 20px;
    width: 120px;
    height: 300px;
    background-color: grey;
    opacity:0.9;
    text-align: left;
    overflow: auto;
}
#debug a, #debug a:visited {
    color: #CCCCCC;
}


/* Debug panel */
div#debugPanel {
    position: absolute;
    z-index: 9999;
    top: 0;
    right: 0;
    overflow: auto;
    border: 1px black solid;
    margin: 10px;
    min-width: 150px;
    height: 300px;
    padding: 5px 10px;
    background-color: #808080;
    opacity: 0.9;
    font-size: 0.9em;
    color: #333;
    text-align: left;
}
    div#debugPanel h3 {
        margin-bottom: 0.5em;
        color: #fff;
    }
    div#debugPanel a {
        color: #ccc;
        text-decoration: none;
    }
    div#debugPanel dl {
        margin-bottom: 0;
    }
        div#debugPanel dl dt {
            margin-bottom: 0.2em;
            font-weight: normal
        }
        div#debugPanel dl dd {
            margin-left: 0;
            margin-bottom: 0.5em;
        }
/* END debug panel */

