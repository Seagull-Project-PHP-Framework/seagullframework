#nav {
    height: 50px;
    font-size: 0.75em;
}
#nav ul {
    position: absolute;
    width: 100%;
    padding: 0;
    margin: 0;
    list-style: none;
    background-color: <?php echo $primary ?>;
    z-index: 3;
}
#nav ul li {
    position: relative;
    float: left;
    margin: 0;
    <?php if ($browserFamily == 'MSIE') {?>
    behavior: url(<?php echo $baseUrl ?>/css/listItemHover.htc);
    <?php } ?>
}
#nav ul li a {
    display: block;
    position: relative;
    padding: 0.2em 1.5em;
    background-color: <?php echo $secondary ?>;
    font-size: 1.3em;
    font-weight: bold;
    color: <?php echo $primaryTextLight ?>;
    text-align: center;
    text-decoration: none;
    letter-spacing: 0.05em;
    border-right: 1px solid <?php echo $primary ?>;
}
#nav ul li a:hover {
    color: <?php echo $secondaryDark ?>;
    text-decoration: underline;
}
/* This one doesn't affect IE */
#nav ul li:hover > ul {
    display: block;
}
#nav ul ul li {
    width: 100%;
}
#nav ul ul li a {
    border: 1px solid <?php echo $secondaryDark ?>;
    border-top: none;
    padding: 0.2em;
    font-size: 1.1em;
    color: <?php echo $secondaryDark ?>;
    background-color: <?php echo $secondaryLight ?>;
    width: 100%;
}
#nav ul ul li a:hover {
    background-color: <?php echo $secondaryLight ?>;
    text-decoration: none;
}
#nav ul ul li:first-child > a {
    border-top: 1px solid <?php echo $secondaryDark ?>;
}
/*hide all sublevels*/
#nav ul ul {
    display: none;
}
#nav ul ul ul {
    top: 0.6em;
    left: 100%;
}
