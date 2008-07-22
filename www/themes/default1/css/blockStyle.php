/******************************************************************************/
/*                        BLOCKS STYLE CSS FILE                               */
/******************************************************************************/
/*
Theme  : Default Seagull Theme
Author : Julien Casanova <julien_casanova@yahoo.fr>
Version: 1.0
Date   : 2006/03/20
*/

/*
====================Default Block Styling=====================*/
.block {
    margin-bottom: 1.5em;
}
.block .header {
    margin: 0 0.4em;
    padding-bottom: 0.4em;
    border-bottom: 1px solid <?php echo $secondary ?>;
    color: <?php echo $secondary ?>;
    font-weight: bold;
}
.block .header h2 {
    font-size: 1.1em;
    font-family: <?php echo $fontFamilyAlt ?>;
}
.block .content {
    padding: 0.4em;
    font-size: 0.9em;
}

/*
=====================Lang Switcher Block======================*/
#lang-switcher {
    float: right;
}

/*
====================Top Navigation Block======================*/
#top-nav {
    position: relative;
    background: <?php echo $primaryLight ?>;
    border: 2px solid <?php echo $greyLightest ?>;
    border-top: none;
}
#top-nav .inner {
    height: 2.2em;
    padding-left: <?php echo $leftColWidth . 'px' ?>;
    border: 1px solid <?php echo $grey ?>;
}
#top-nav ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
#top-nav li {
    float: left;
    padding-left: 0;
}
#top-nav li a {
    display: block;
    padding: 0.5em 1em;
    border-right: 1px solid <?php echo $greyLightest ?>;
    font-weight: bold;
    color: <?php echo $primaryDark ?>;
}
#top-nav li a:hover, #top-nav li.current a {
    background-color: <?php echo $primary ?>;
    color: <?php echo $greyLightest ?>;
    text-decoration: none;
}
