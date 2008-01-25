/* first level list */
div#nav ul {
    list-style: none;
    padding: 0px;
    margin: 0px;
}

/* first level list item */
div#nav li {
    margin: 3px 0 3px 0;
    padding: 3px 0 3px 0;
    background: #9dcdfe;
    color: #006699;
    text-align: left;
}

/* second level list */
div#nav ul ul {
    list-style: disc inside;
    background: #9dcdfe;
    padding: 3px 0 0 0;
    margin: 3px 0 -6px 0;
}

/* second level list item */
#nav li li {
    text-transform: none;
    margin: 0 0 3px 0;
    padding: 3px 0 3px 5px;
}

/* menu item link */
div#nav a:link,
div#nav a:visited,
div#nav a:hover,
div#nav a:active,
div#nav li.current li a:link,
div#nav li.current li a:visited,
div#nav li.current li a:active {
    color: #006699;
    font-size: 11px;
    font-weight: normal;
    text-decoration: none;
    padding: 0 5px 0 5px;
}
div#nav li a:hover {
    color: #ffffff;
}
div#nav a:active,
div#nav li.current li a:active {
    background: #9dcdfe;
}

/* current menu item link */
div#nav li.current a:link,
div#nav li.current a:visited,
div#nav li.current a:active {
    color: #006699;
    font-weight: bold;
}
div#nav li.current a:active {
    background: #ffecec;
}