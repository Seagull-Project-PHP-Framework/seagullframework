/******************************************************************************/
/*                         MAIN LAYOUT CSS FILE                               */
/******************************************************************************/
/*
Theme  : Classic Seagull Theme
Author : Julien Casanova <julien_casanova@yahoo.fr>
Version: 1.0
Date   : 2006/03/20
*/

/*
==========================General=============================*/
html {
    height: 100%;
    margin-bottom: 1px;
}
body, h1, h2, h3, h4, p, ul, li, form, fieldset {
    margin: 0;
    padding: 0;
}
body {
    font-size: <?php echo $fontSize ?>;
    font-family: <?php echo $fontFamily ?>;
    margin: 0;
    padding: 10px 0;
    color: <?php echo $greyDarkest ?>;
    background-color: <?php echo $grey ?>;
    text-align: center;
	background-image: url(../images/grey_bgnd.gif);
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
======================Global layaout==========================*/
#outer-wrapper {
    max-width: 1000px;
    width: 900px;
    margin: 0 auto;
    text-align: left;
}
#header {
    position: relative;
}
#top-nav {
    position: relative;
}
#inner-wrapper {
    clear: both;
    width: 896px;
    /* 896 is for mainWrapper width - borders width : 900 - (2 x 2) */
}
#footer {
    clear: both;
}

/*
======================2 Cols Fluid============================*/
#middleCol {
    float: left;
    background: <?php echo $greyLightest ?>;
}
#middleCol .inner {
    padding: 5px 10px;
}
#ensureMinHeight {
    float: left;
    width: 1px;
    height: <?php echo $contentMinHeight ?>;
}
#layout-3Cols #middleCol {
    width: <?php echo ($mainWrapperWidth - $leftColWidth - $rightColWidth - 6) . 'px' ?>;
    /* 6 is for borders width : (2+1) x 2 */
}
#layout-leftCol #middleCol {
    width: <?php echo ($mainWrapperWidth - $leftColWidth - 6) . 'px' ?>;
}
#layout-rightCol #middleCol {
    width: <?php echo ($mainWrapperWidth - $rightColWidth -6) . 'px' ?>;
}
#layout-noCols #middleCol {
    width: <?php echo ($mainWrapperWidth -6) . 'px' ?>;
}
#leftCol {
    float: left;
    width: <?php echo $leftColWidth . 'px' ?>;
    /*background: url('<?php echo $baseUrl ?>/images/backgrounds/v4-bubbles.png') left top no-repeat;*/
}
#leftCol .inner {
    padding: 5px;
    padding-top: 0.8em;
}
#rightCol {
    float: right;
    width: <?php echo $rightColWidth . 'px' ?>;
    background: <?php echo $greyLightest ?>;
}
#rightCol .inner {
    margin: 2.5em 4px 4px 0;
    padding: 5px;
    padding-top: 0.8em;
    border: 1px solid <?php echo $grey ?>;
}

/*
=========================Header===============================*/
#header {
    border-bottom: 2px solid <?php echo $greyLightest ?>;
}
#header .wrapLeft {
    background: url('<?php echo $baseUrl ?>/images/backgrounds/header_tl.gif') left top no-repeat;
}
#header .wrapRight {
    background: url('<?php echo $baseUrl ?>/images/backgrounds/header_tr.gif') right top no-repeat;
}
#header .wrap {
    position: relative;
    height: 70px;
    margin: 0 20px;
    background: <?php echo $primary ?> url('<?php echo $baseUrl ?>/images/backgrounds/header_tm.gif') left top repeat-x;
}
#header span#logo {
    font-size: 30px;
    font-family: "Trebuchet MS";
}
#header a#logo {
    color: <?php echo $greyLightest ?>;
    text-decoration: none;
}
#header #logo img {
    position: relative;
    top: 7px;
    left: 0;
}
#header #bugReporter {
    position: absolute;
    bottom: 0px;
    right: -10px;
}

/*
======================Inner Wrapper===========================*/
#inner-wrapper {
    background: <?php echo $greyLightest ?>;
    border: 2px solid <?php echo $greyLightest ?>;
    border-top: none;
}
#inner-wrapper .inner-container {
    border: 1px solid <?php echo $grey ?>;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/column_tm.gif') left top repeat-x;
}

/*
=======================Breadcrumbs============================*/
#breadcrumbs {
    background: <?php echo $greyLightest ?>;
    border: 2px solid <?php echo $greyLightest ?>;
    border-top: none;
    font-family: <?php echo $fontFamilyAlt ?>;
    font-size: 0.8em;
}
#breadcrumbs .inner {
    padding: 0.4em 0 0.4em 1em;
    border: 1px solid <?php echo $grey ?>;
}
#breadcrumb {
    float: left;
}
a.breadcrumbs {
    font-weight: bold;
    color: <?php echo $primaryDark ?>;
}

/*
======================Main Content============================*/
#content h1 {
    font-size: 1.2em;
}
#content h1.pageTitle {
    margin: 0em 0 1em;
    padding-bottom: 0.5em;
    border-bottom: 1px solid <?php echo $greyDark ?>;
    color: <?php echo $greyDark ?>;
}
#content ul {
    margin: 0.5em 0 0.5em 1em;
    padding-left: 0.5em;
    list-style-position: inside;
    list-style-image: url('<?php echo $baseUrl ?>/images/bullet.gif');
}
#content li {
    padding-left: 0.5em;
}
#content a {
    color: <?php echo $primaryDark ?>;
}

/*
==================Default Forms Styling=======================*/
form {

}
fieldset {
    padding: 10px 0;
    border: none;
}

/*
===================Form Elements Styling======================*/
input, select, textarea {
    font-size: 0.9em;
}
textarea {
    font-family: <?php echo $fontFamily ?>;
    font-size: 0.9em;
}

input[type="text"], input[type="password"]
{
	border-top: 1px solid #7c7c7c;
	border-left: 1px solid #c3c3c3;
	border-right: 1px solid #c3c3c3;
	border-bottom: 1px solid #ddd;
	background: #fff url(../images/fieldbg.gif) repeat-x top;
}

/*
====================Form Fields Layout========================*/
/* --
Definition lists are used to display fields labels and values
-----*/
dl.onSide dt {
    float: left;
    width: 120px;
    padding-right: 20px;
    text-align: right;
}
dl.onSide dd{
    margin-left: 140px;
    margin-bottom: 0.5em;
}
dl.onTop dd {
    margin: 0;
}
dd .error {
    display: block;
}

/*
==================Default Tables Styling======================*/


/*
=========================Footer===============================*/
#footer .wrapLeft {
    background: url('<?php echo $baseUrl ?>/images/backgrounds/footer_bl.gif') left bottom no-repeat;
}
#footer .wrapRight {
    background: url('<?php echo $baseUrl ?>/images/backgrounds/footer_br.gif') right bottom no-repeat;
}
#footer .wrap {
    position: relative;
    margin: 0 20px;
    padding: 10px 0 5px;
    background: <?php echo $primary ?> url('<?php echo $baseUrl ?>/images/backgrounds/footer_bm.gif') left bottom repeat-x;
    text-align: center;
}
#footer p {
    margin-bottom: 0.1em;
    color: <?php echo $greyDark ?>;
    font-size: 0.8em;
}

/*
======================Messages & Errors=======================*/
.message {
    text-align: center;
}
.message div {
    width: 60%;
    margin: 1em auto;
    padding: 0.5em;
    -moz-border-radius: 0.3em;
}
.errorMessage {
    border: 2px solid <?php echo $error ?>;
    color: <?php echo $error ?>;
}
.infoMessage {
    border: 2px solid <?php echo $primaryDark ?>;
    color: <?php echo $primary ?>;
}
.error {
    color: <?php echo $error ?>;
}

/* PEAR Errors
  --------------------*/
div.errorContainer {
    width: 80%;
    margin: 1em auto;
    padding: 0.5em;
    border: 2px solid <?php echo $error ?>;
    -moz-border-radius: 0.3em;
    font-family: <?php echo $fontFamilyAlt ?>;
}
div.errorHeader {
    margin-bottom: 0.5em;
    font-size: 1.1em;
    text-transform: uppercase;
    font-weight: bold;
    letter-spacing: 0.3em;
    color: <?php echo $error ?>;
}
div.errorContent {
    text-align: left;
}

/*
============================Flags=============================*/
a.langFlag {
    margin: 0 5px;
}

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
.hide {
    display: none;
}
.narrow {
    width: 45%;
}
.full {
    width: 100%;
}
.wideButton {
    width: 8em;
}
.noBg {
    background: none;
}
pre.codeExample {
    padding: 1em;
    background-color: <?php echo $greyLight ?>;
    border: 1px solid <?php echo $greyDark ?>;
    border-left: 5px solid <?php echo $greyDark ?>;
    font-size: 1em;
}

/*
========================Miscellaneous=========================*/

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

/*
TO REMOVE WHEN ALL TEMPLATES ARE CONSOLIDATED
======================Default Theme BC========================*/
