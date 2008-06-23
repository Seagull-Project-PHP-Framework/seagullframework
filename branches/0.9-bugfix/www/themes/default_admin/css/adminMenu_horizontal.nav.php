#nav {
    position: relative;
    height: 24px;
    margin: 0;
    background: #1F6899 url('../image/menu_bg_px.gif') repeat-x;
    font-family: arial;
    font-size: 1.1em;
    z-index: 10;
}
#navPreview #nav {
    margin-top: 10px;
}
    #nav ul {
        position: absolute;
        margin-left: 70px;
        padding: 0;
        list-style: none; 
        line-height: 1;
        z-index: 10000;
    }
    #nav ul li {
        float: left;
    }
    #nav ul a {
        display: block;
        padding: 5px 10px;
        font-weight: bold;
        color: #fff;
        cursor: default;
    }
    #nav ul a:hover {
        background-color: #ffffff;
        color: #339ecc;
    }
    #nav ul a.deroule {
        background-image: url('../images/arrow_black.gif');
        background-repeat: no-repeat;
        background-position: 95% 50%;
    }
    #nav ul a.checked {
        background-image: url('../images/checked_s.gif');
        background-repeat: no-repeat;
        background-position: 95% 50%;
    }
    #nav ul a.unauth {
        color: #cfcfcf;
    }
    #nav ul a.unauth:hover, #nav ul  li.sfhover a.unauth{

    }
    #nav ul li a.separator {
        background-image: url('../images/pixel.gif');
        background-repeat: repeat-x;
        background-position: 0% 50%;
    }
    #nav ul li a.separator:hover, #nav ul li.sfhover a.separator{
        background-color: #ffffff;
    }
    #nav ul li ul {
        position: absolute;
        width: 12em;
        left: -999em;
        margin: 0 0 0 0;
        background: #f5f5f5;
        -moz-opacity: 0.95;
        border: 1px solid #afafaf;
    }
    #nav li ul a {
        font-size: 0.9em;
        font-weight: normal;
        background-color: #f5f5f5;
        color: #606060;
        cursor: default;

    }
    #nav li ul a:hover {
        background-color: #f5f5f5;
        color: #606060;
        border: none;
        text-decoration: underline;
    }
    #nav ul li:hover ul, #nav ul li.sfhover ul {
        left: 1px;
    }
    #nav ul li ul ul {
        margin: -1.5em 0 0 12em;
    }
    #nav ul li ul li {
        width: 12em;
    }
    #nav ul li:hover ul ul, #nav ul li:hover ul ul ul, #nav ul li.sfhover ul ul, #nav ul li.sfhover ul ul ul {
        left: -999em;
    }
    #nav ul li:hover ul, #nav ul li li:hover ul, #nav ul li li li:hover ul, #nav ul li.sfhover ul, #nav ul li li.sfhover ul, #nav ul li li li.sfhover ul {
        left: auto;
    }