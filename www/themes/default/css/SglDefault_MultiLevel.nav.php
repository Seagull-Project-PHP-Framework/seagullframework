/*
To work correctly in MSIE you need to specify the valid path for the behaviour element in
#nav ul li, in other words, either the correct absolute or relative path.
*/

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
    background-color: #99cc00;
    z-index: 3;
}
#nav ul li {
    position: relative;
    float: left;
    margin: 0;
    /* IE :hover silly javascript workaround */
    behavior: url(/themes/default/css/listItemHover.htc);
}
#nav ul li a {
    display: block;
    position: relative;
    padding: 0.2em 1.5em;
    background-color: #9dcdfe;
    font-size: 1.3em;
    font-weight: bold;
    color: #ffffff;
    text-align: center;
    text-decoration: none;
    letter-spacing: 0.05em;
    border-right: 0.1em solid #99cc00;
}
#nav ul li a:hover {
    color: #006699;
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
    border: 1px solid #006699;
    border-top: none;
    padding: 0.2em;
    font-size: 1.1em;
    color: #006699;
    background-color: #e5f1ff;
    width: 100%;
}
#nav ul ul li a:hover {
    background-color: #e5f1ff;
    text-decoration: none;
}
#nav ul ul li:first-child > a {
    border-top: 1px solid #006699;
}
/*hide all sublevels*/
#nav ul ul {
    display: none;
}
#nav ul ul ul {
    top: 0.6em;
    left: 100%;
}
