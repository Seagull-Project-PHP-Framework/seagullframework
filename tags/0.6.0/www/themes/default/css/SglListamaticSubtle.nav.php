#nav {
    font-size: 1em;
}
#nav ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
}
#nav a {
    display: block;
    padding: 5px 10px;
    width: 158px;
    color: #000000;
    background-color: <?php echo $primary ?>;
    text-decoration: none;
    border: 1px solid;
    border-color: <?php echo $button ?>;
    font-weight: bold;
    font-size: 0.8em;
}
#nav a:hover {
    color: #000000;
    background-color: <?php echo $primaryText ?>;
    text-decoration: none;
    border: 1px solid;
    border-color: <?php echo $buttonAlt ?>;
}
#nav ul ul a {
    display: block;
    padding: 5px 5px 5px 30px;
    width: 143px;
    background-color: <?php echo $primaryLight ?>;
    font-weight: normal;
}
