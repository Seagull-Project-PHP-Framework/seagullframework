/******************************************************************************/
/*                         MAIN LAYOUT CSS FILE                               */
/******************************************************************************/
/*
Theme  : Default Seagull Theme
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
    padding: 0 0 10px;
    color: <?php echo $greyDarkest ?>;
}
body.sgl {
    background-color: <?php echo $grey ?>;
    text-align: center;
    background-image: url(../images/grey_bgnd.gif);
}
dl {
    margin: 0 0 0.5em;
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
hr {
    border-top: 1px dotted #999;
    border-bottom: 0px;
    height: 1px;
}

/*
======================Global layaout==========================*/
#outer-wrapper {
    max-width: 1000px;
    clear: both;
    width: 900px;
    margin: 10px auto 0;
    text-align: left;
}
#header {
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
    height: 380px; /* Sets min height for IE */
}
html > body #middleCol {
    /* Sets min height for gecko */
    height: auto;
    min-height: 380px;
}
#middleCol .inner {
    padding: 5px 10px;
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
h1 {
    font-size: 1.2em;
    margin: 0em 0 1em;
    padding-bottom: 0.5em;
    border-bottom: 1px solid <?php echo $greyDark ?>;
    color: <?php echo $greyDark ?>;
}
h2 {
    margin-top: 0.5em;
    font-size: 1.1em;
}
ul {
    margin: 0.5em 0 0.5em 1em;
    padding-left: 0.5em;
    list-style-position: inside;
    list-style-image: url('<?php echo $baseUrl ?>/images/bullet.gif');
}
li {
    padding-left: 0.5em;
}

/*
===============Generic columns presentation===================*/
.two-cols {
    height: 100%;
    overflow: hidden;
}
.two-cols .col {
    float : left;
	width : 49%;
	margin-left : 1%;
}
.two-cols .first {
	width : 50%;
	margin-left : 0;
}

/*
==================Default Forms Styling=======================*/

fieldset {
    padding: 10px 0;
    border: none;
}
fieldset h3 {
    font-size: 1em;
    color: <?php echo $greyDark ?>;
}
#content form ul {
    position: relative;
    margin: 0;
    padding: 0;
    width: 99%;
    list-style-position: outside;
    list-style-image: none;
}

#content form li {
    display: block;
    margin: 0;
    padding: 1px 4px 6px 9px;
    line-height: 1.8em;
    list-style-type: none;
}
form li.focused {
    background: <?php echo $primaryLight ?>;
}

form li div {
    float: left;
    display: inline;
    margin-right: 5px;
    color: <?php echo $greyDark ?>;
}
form li:after {
    content: ".";
    display: block;
    height: 0;
    clear: both;
    visibility: hidden;
}
form li p {
    clear: both;
    font-size: 9px;
    line-height: 13px;
}


/*
===================Form Elements Styling======================*/
input, select, textarea {
    font-size: 100%;
}
textarea {
    font-family: <?php echo $fontFamily ?>;
}

#content input.text, #content select.select, #content textarea.textarea {
    border-width: 1px;
    border-style: solid;
    border-color: #7c7c7c #c3c3c3 #ddd #c3c3c3;
    background: #fff url(../images/fieldbg.gif) repeat-x top;
}

input.text {
    padding: 2px 0;
}
form label {
    font-weight: bold;
    color: <?php echo $greyDark ?>;
}

/* SIZES */
.third {
	width:32% !important;
}
.half {
	width:48% !important;
}
.full {
	width:100% !important;
}
input.small, select.small {
	width:25%;
}
input.medium, select.medium {
	width:50%;
}
input.large, select.large, textarea.textarea {
	width:99%;
}
input.tags {
	width:315px;
}
textarea.small {
	height:5.5em;
}
textarea.medium {
	height:10em;
}
textarea.large {
	height:20em;
}

/* BUTTONS */
.button {
    font-size: 110%;
    margin-right: 5px;
    /*border: 1px solid #666;*/
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
======================No forms layout=========================*/
div.fieldsetlike { /*
--------------------- as some pages don't use forms/fieldsets
- e.g. user/profile, we have to put data in a fieldset like
- div to have same render ------------------------------------*/
    padding: 10px 0;
}
div.fieldsetlike h3 {
    font-size: 1em;
    color: <?php echo $greyDark ?>;
}

/*
==================Default Tables Styling======================*/
/*  In a transition period, we'll use a sglTable class to style tables
    Everyone is encouraged not to use tables for layout purposes
    TODO: remove this when all layout tables have been replaced */
.wide {
    width: 60%;
}
.large {
    width: 85%;
}
table {
    margin: 0 0 1em;
    border-collapse: collapse;
    font-size: 1em;
}
table .nowrap {
    white-space: nowrap;
}
.sglTable td, .sglTable th {
    border-width : 0 0 1px 0;
	border-style : solid;
	border-color : <?php echo $grey ?>;
	padding : 2px 5px;
	vertical-align : top;
}
.sglTable th {
    text-align: left;
    border-bottom-color: <?php echo $greyDark ?>;
}
th a {
    display: block;
    color: #666 !important;
}
tr.expand td {
    border-bottom: none;
}
td.expand {
    padding: 1em 5px;
}
th.sortedAsc a {
    background: url('<?php echo $baseUrl ?>/images/th-sortAsc.gif') 95% 50% no-repeat;
    color: <?php echo $primaryDark ?> !important;
}
th.sortedDesc a {
    background: url('<?php echo $baseUrl ?>/images/th-sortDesc.gif') 95% 50% no-repeat;
    color: <?php echo $primaryDark ?> !important;
}
th a:hover {
    text-decoration: none;
    color: <?php echo $primaryDark ?> !important;
}
tr.alternateRow td, tr.backDark {
    background-color: #FBFFEF; /*F0FFD9*/
}
tr.selectedRow td {
    background: #F6F5F2; /*D0DCE0*/
}
tr.rowHover td, tr:hover td {
  background: #F0FFD9; /*E0EFB8*/
}

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
.error, .required {
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
    display: block;
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
.button {

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
========================Comments=========================*/

#addComment fieldset {
    padding: 10px;
    border: 1px solid grey;
}
#addComment input[type="text"] {
    width: 200px;
}

/*
========================Miscellaneous2=========================*/

.tipOwner {
    position: relative;
    cursor: help;
    <?php if (isBrowserFamily('MSIE7', '<')) { ?>
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
    <?php if (isBrowserFamily('Gecko')) { ?>
    -moz-opacity: 0.85;
    <?php } else if (isBrowserFamily('MSIE')) { ?>
    filter: alpha(opacity=85);
    filter: progid: DXImageTransform.Microsoft.Alpha(opacity=85);
    <?php } ?>
}
.tipOwner:hover .tipText {
    display: block;
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

/*
TO REMOVE WHEN ALL TEMPLATES ARE CONSOLIDATED
======================Default Theme BC========================*/
.wideButton {
    /* use button instead */
    width: 8em;
}