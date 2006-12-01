/******************************************************************************/
/*                        BLOCKS STYLE CSS FILE                               */
/******************************************************************************/
/*
Theme  : Night City Seagull Theme 
Author : Sergey Pozhilov <gt@gettemplate.com>
Author : Neil Mather <ngmather@gmail.com>
Version: 0.5 
Date   : 2006/08/07
*/

/*
====================Default Block Styling=====================*/
.block {
    margin-bottom: 1.5em;
}

.block ul {
    list-style-type: square;
    list-style-position: inside;
    margin-left: 0.9em;
}
.block li {
    margin-bottom: 0.5em;
}
.block .header {
    margin: 0 0 1.1em 0;         
    padding: 0.3em 0;  
    font-weight: bold;
    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif; 
    background-color: <?php echo $primaryDark ?>;  
    border-bottom: 1px solid <?php echo $primaryLight ?>;
    text-align: center;  
    font-weight: bold;  
}
.block .header h2 {
    color: <?php echo $greyLightest ?>;  
    font-size: 1em;
    font-family: <?php echo $fontFamilyAlt ?>;
}
.block .content {
    font-size: 0.9em;
    text-align: center;
}
#leftCol .content a {
    color: <?php echo $secondary ?>;
}
/*
=====================Lang Switcher Block======================*/
#lang-switcher {
    position: relative; top: 38px;
    float: right;
}

/*
====================Top Navigation Block======================*/
#top-nav {
    height: 100%;
    border-top: none;
    background-position: bottom left;
    background-repeat: no-repeat;
}
#top-nav .inner {
    height: 70px;
}
#top-nav ul {
    position: relative; top:87px;
    float:right;
    clear:right;
    height: 25px;
    margin: 0 0 0 0; padding: 0; 
    border-bottom: 5px solid <?php echo $primaryDark ?>;  
    list-style: none; 
}
#top-nav li {
    display: block;  
    padding: 0 0 0 0.5em;
    float:left;
    width: 4.5em;
    height: 25px;  
    list-style: none; 
    background-image: url('<?php echo $baseUrl ?>/images/backgrounds/bg_topnav_li.gif');  
    background-repeat: repeat-y;    
    line-height: 9px; 
}

#top-nav li a {
    display: block;  
    width: 100%; height: 100%;
    color: <?php echo $greyLightest ?>; 
    text-decoration: none;  
    font-size: 11px;  
    font-family: Tahoma, Arial, Helvetica, sans-serif;
}
#top-nav li a:hover, #top-nav li.current a {
    background-color: <?php echo $greyDarkest ?>;
    color: <?php echo $greyLightest ?>;
    text-decoration: none; 
    position: relative; top: -1px; 
}
#top-nav li.current a {
    color: <?php echo $greyLightest ?>;
    font-weight: 700;
}
