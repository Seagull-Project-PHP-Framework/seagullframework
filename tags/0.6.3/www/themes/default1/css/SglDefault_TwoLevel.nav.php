#nav {
    height: 50px;
    font-size: 0.75em;
}
#nav ul {
    position: absolute;
    width: 100%;
    padding: 0;
    margin: 0;
    background-color: <?php echo $primary ?>;
}
#nav ul li {
    float: left;
    display: inline;
    margin: 0;
}
#nav ul li a {
    display: block;
    position: relative;
    padding: 0.2em 1.5em;
    background-color: <?php echo $secondary ?>;
    font-size: 1.3em;
    font-weight: bold;
    color: <?php echo $primaryLight ?>;
    text-align: center;
    text-decoration: none;
    letter-spacing: 0.05em;
    border-right: 1px solid <?php echo $primary ?>;
}
#nav ul li a:hover {
    color: <?php echo $secondaryDark ?>;
    text-decoration: underline;
}
#nav ul li.current a {
    color: <?php echo $secondaryDark ?>;
    background-color: <?php echo $secondaryLight ?>;
}
#nav ul li.current a:visited {
    color: <?php echo $secondaryDark ?>;
}
/* hide the sublevels */
#nav ul ul {
    display: none;
}
#nav ul li.current ul {
    position: absolute;
    left: 0;
    display: block;
    /* use max-width emulation hack from
     * http://www.svendtofte.com/code/max_width_in_ie/, using the IE5.0+ dynamic
     * properties syntax, see
     * http://msdn.microsoft.com/workshop/author/dhtml/overview/recalc.asp */
    <?php if (isBrowserFamily('MSIE')) { ?>
    width: expression(document.body.clientWidth);
    <?php } ?>
    padding: 0;
    background-color: <?php echo $secondaryLight ?>;
}
#nav ul li.current li {
    padding: 0.4em 0;
}
#nav ul li.current li a {
    /* hide first pipe */
    left: -1px;
    padding: 0 1em;
    font-weight: normal;
    font-size: 1.1em;
    color: <?php echo $secondaryDark ?>;
    border-right: none;
    border-left: 1px solid <?php echo $secondaryDark ?>;
}
/* hide 3rd or more levels */
#nav ul ul ul li {
    display: none;
}
