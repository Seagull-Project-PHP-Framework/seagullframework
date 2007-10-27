<?php
    $fontFamily             = 'arial, sans-serif';
    $fontFamilyAlt          = 'arial';
    $fontSize               = 'small';

    $primary                = '#99CC00'; // lime green
    $primaryDark            = '#66A326'; //
    $primaryLight           = '#EEF7D4'; //

    $secondary              = '#0099CC'; // blue
    $secondaryDark          = '#2666A3'; //
    $secondaryDarker        = '#184a84'; // darker blue
    $secondaryLight         = '#E5F8FF'; //

    $tertiary               = '#CC0099'; // cyan
    $tertiaryDark           = '#A32666'; //
    $tertiaryLight          = '#FFE5F8'; //

    $grey                   = '#BBBBBB'; // grey
    $greyDark               = '#666666'; //
    $greyDarkest            = '#000000'; //
    $greyLight              = '#EEEEEE'; //
    $greyLightest           = '#FFFFFF'; //

/* Columns widths set in px
    ------------------------------------------------- */
    $mainWrapperWidth       = 900;
    $leftColWidth           = 180;
    $rightColWidth          = 180;
    /* middle col width will be calculated */

    $contentMinHeight       = '320px';

/* Messages and errors
    ------------------------------------------------- */
    $error                  = '#FF3300'; //

/* Links related vars
    ------------------------------------------------- */
    $linkColor              = $secondary;
    $linkDecoration         = 'none';
    $linkHoverColor         = $secondary;
    $linkHoverDecoration    = 'underline';

/* Borders related vars
    ------------------------------------------------- */
    $borderDark             = $greyDark;
    $borderLight            = $greyLightest;

/* Block related vars
    ------------------------------------------------- */
    $blocksBorderBody       = $greyLightest;
    $blocksBorderTitle      = $greyLightest;
    $blocksBackgroundBody   = $greyLightest; // not used yet
    $blocksBackgroundTitle  = $greyLightest;
    $blocksColorBody        = $greyLightest; // ^ ^ ^
    $blocksColorTitle       = $greyLightest; // | | |

/* Table related vars
    ------------------------------------------------- */
    $tableRowLight          = $greyLightest;
    $tableRowDark           = $grey;

/* Button related vars
    ------------------------------------------------- */
    $button     = '#ffffff #333333 #333333 #ffffff';
    $buttonAlt     = '#333333 #ffffff #ffffff #333333';

/* Set urls */
//  get base url for css classes that include images
//  This file is included  in CssLoader so this refers to CssLoader object
$path = dirname($_SERVER['PHP_SELF']);
$aPath = explode('/', $path);
$aPath = array_filter($aPath);
array_pop($aPath);
$webRootUrl = join('/', $aPath);
$baseUrl = $webRootUrl . '/themes/' . $this->theme;
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']  == 'on')
    ? 'https' : 'http';
$themeRootUrl = $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $baseUrl;
$webRootUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $webRootUrl;

$isFormSubmitted = !empty($_GET['isFormSubmitted']);
?>