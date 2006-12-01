/******************************************************************************/
/*                         MAIN LAYOUT CSS FILE                               */
/******************************************************************************/
/*
Theme  : Night City Seagull Theme
Author : Sergey Pozhilov <gt@gettemplate.com>
Author : Neil Mather <ngmather@gmail.com>
Version: 0.5
Date   : 2006/08/07
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
    width: 100%;
    font-size: <?php echo $fontSize ?>;
    font-family: <?php echo $fontFamily ?>;
    color: <?php echo $greyDarkest ?>;
    background-color: <?php echo $greyLightest ?>;
    text-align: center;
	background-image: url('<?php echo $baseUrl ?>/images/backgrounds/bg_body.gif');
    background-repeat: repeat-x;
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
    width: 100%; 
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
    width: <?php echo ($mainWrapperWidth - $leftColWidth - $rightColWidth - 6) . '%' ?>;
}
#layout-leftCol #middleCol {
    width: <?php echo ($mainWrapperWidth - $leftColWidth - 6) . '%' ?>;
}
#layout-rightCol #middleCol {
    width: <?php echo ($mainWrapperWidth - $rightColWidth -6) . '%' ?>;
}
#layout-noCols #middleCol {
    width: <?php echo ($mainWrapperWidth -6) . '%' ?>;
}
#leftCol {
    float: left;
    width: <?php echo $leftColWidth . '%' ?>;
    padding: 0 1em;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_vline.gif') right top repeat-y;
}
#leftCol .inner {
    width: 85%;
}
#rightCol {
    float: right;
    width: <?php echo $rightColWidth . '%' ?>;
}
#rightCol .inner {
    margin: 2.5em 4px 4px 0;
    padding: 5px;
    padding-top: 0.8em;
}

/*
=========================Header===============================*/
#header {
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_header.jpg') right top no-repeat;
    width:100%;
    height: 112px;
}

#header #left {
    float: left;
    width: 39%;
    height: 100%;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_logo_deep.gif') left no-repeat;
}
#header #right {
    float: right;
    width: 60%;
    height: 100%;
}

#header h1#logo {
    display: block;
    width: 8.8em;
    height: 52px;
    margin-left: 1em;
    font-size: 1.2em;
    color: white;
    background-color: <?php echo $secondaryDark ?>;
    background-repeat: no-repeat;
    -moz-border-radius: 0  0 0.5em 0.5em;
}

#header h1#logo a {
    display: block;
    width: 100%;
    height: 100%;
    color: white;
    padding: 0 1em;
    text-decoration: none;
    background-color: <?php echo $secondaryDark ?>;
    -moz-border-radius: 0  0 0.5em 0.5em;
    border-top: 3px solid <?php echo $secondaryDarker ?>; 
}

#header h1#logo a:hover {
    background-color: <?php echo $secondaryLight ?>;
}

#header #logo img {
    position: relative;
    top: 7px;
    left: 0;
}

#banner2 {
    float: left;
    width: 100%;
    height: 52px;
}

div#bigArrow { 
    margin: 32px 0 0 0;
    display: block;
    width: 20px;
    height: 19px;
    font-size: 10pt;
    position: absolute; right: 1px;
    z-index: 100;
}
div#bigArrow a {
    width: 20px;
    height: 19px;
    background-color: #CD0000;
    background-image: url('<?php echo $baseUrl ?>/images/big_arrow.gif');
    display: block;
    color: <?php echo $secondaryDark ?>;
    font-size: 10pt;
    z-index: 100;
}
div#bigArrow a:hover {
    background-color: <?php echo $secondaryLight?>;
    background-image: url('<?php echo $baseUrl ?>/images/big_arrow.gif');
    background-repeat: no-repeat;
    color: <?php echo $secondaryLight?>;
    font-size: 10pt;
}

/*
=======================Breadcrumbs============================*/
.arrow{ 
    position: relative;
    top: -1px;
    margin: 0 2px 0 2px;
    color: <?php echo $greyDarkest ?>; 
}
#breadcrumbs {
    float: left;
    width: 50%;
    margin-left: 24%;
    font-size: 0.8em; 
    position: relative; top: 30px;
}
#breadcrumbs { 
    font-size: 0.8em;
}
#breadcrumbs .inner {
}
#breadcrumbs span#current {
    font-weight: bold;
    color: <?php echo $secondary ?>;
}
#breadcrumb {
}
a.breadcrumbs {
    color: <?php echo $greyDarkest ?>;
}

/*
======================Inner Wrapper===========================*/
#inner-wrapper {
    padding-top: 0.7em;
}
#inner-wrapper .inner-container {
    padding-top: 20px;
}

/*
======================Main Content============================*/
#content {
    margin-left: 2em;
}
#content h1 {
    font-size: 1.2em;
}
#content h1.pageTitle {
	margin-bottom: 0.5em;
    font-size: 1.6em; 
    line-height: 1.6em;
    font-family: Arial, Helvetica, sans-serif; 
    font-weight: normal; 
    color: <?php echo $primary ?>;    
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
    color: <?php echo $secondary ?>;
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
	background: #fff url('<?php echo $baseUrl ?>/images/fieldbg.gif') repeat-x top;
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
#footer {
    float: right;
    width: <?php echo ($mainWrapperWidth - $leftColWidth - 2) . '%' ?>;
    height: 70px;
    margin-top: 3em;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_footer.gif') bottom right no-repeat;
}
#footer .wrap {
    position: relative;
    margin: 0 20px;
    background: url('<?php echo $baseUrl ?>/images/backgrounds/bg_hline.gif') top repeat-x;
    text-align: left;
}
#footer ul {  
    display: block;              
    margin: 0 0 0.9em 0;
    padding: 0.2em 0 0 0;   
    background-repeat: repeat-x;
    list-style: none;
}
#footer li { 
    display: inline;                  
    margin: 0; 
    padding: 0;
    list-style: none;
}
#footer li:after { 
    content: '|';
}
#footer li a { 
    margin: 0 6px;   
    color: <?php echo $primaryDark ?>;
    text-decoration: none;
    font-size: 0.9em;
    font-family: "MS Sans Serif", Geneva, sans-serif;
}
#footer li a:hover{ 
    text-decoration: underline; 
}
#footer p { 
    color: black;
    font-family: "MS Sans Serif", Geneva, sans-serif;
    font-size: 9pt; 
    margin-bottom: 0.1em;
    font-size: 0.8em;
}
#footer a {
    font-family: "MS Sans Serif", Geneva, sans-serif;
    font-size: 0.9em;
    text-decoration: none; 	 
    color: <?php echo $greyDark ?>;
}
#footer a:hover { 
    text-decoration: underline; 
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
========================Miscellaneous2=========================*/

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
