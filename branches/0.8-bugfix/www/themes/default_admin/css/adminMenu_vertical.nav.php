

/*
========================Vertical Admin Menu============================*/
#nav a {
    display: block;
    margin: 0;
    text-indent: 15px;
    height: 30px;
    line-height: 30px;
    font-size: 1em;
    font-weight: bold;
    color: <?php echo $tertiaryDarkest ?>;
    text-decoration: none;
    border-top: 1px solid <?php echo $borderLight ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
}
#nav a img {
    float: left;
    padding: 4px 3px;
    text-align: center;
}
#nav a:hover {
    background: <?php echo $tertiaryDarker ?>;
    color: <?php echo $tertiaryLightest ?>;
}
#nav li ul {
    display: none;
}
#nav li.current ul {
    display: block;
    border: none;
}
#nav li.current a {
    color: <?php echo $primary ?>;
}
#nav li.current a:hover {
    color: <?php echo $tertiaryLightest ?>;
}

/*
-- Second level ------------------------------------------------------ */
#nav li li a {
    font-weight: normal;
    text-indent: 30px;
}
#nav li.current li a {
    color: <?php echo $tertiaryDarkest ?>;
}
#nav li.current li a:hover {
    color: <?php echo $tertiaryLightest ?>;
}
#nav li.current li.current a {
    color: <?php echo $primary ?>;
    background: <?php echo $tertiaryLightest ?>;
}

/*
-- Third level ------------------------------------------------------- */
#nav li.current li ul {
    display: none;
    border-top: 1px solid <?php echo $borderLight ?>;
    border-bottom: 1px solid <?php echo $borderDark ?>;
}
#nav li.current li.current ul {
    display: block;
}
#nav li.current li.current li a {
    background: <?php echo $tertiaryLight ?> url('<?php echo $baseUrl ?>/images/bullet_square_empty.gif') 32px 50% no-repeat;
    border: none;
    height: 24px;
    line-height: 24px;
    text-indent: 45px;
    font-size: 0.9em;
}
#nav li.current li.current li.current a {
    background: <?php echo $tertiaryLight ?> url('<?php echo $baseUrl ?>/images/bullet_square_full.gif') 32px 50% no-repeat;
}
#nav li.current li.current li a:hover {
    background: <?php echo $tertiaryLight ?> url('<?php echo $baseUrl ?>/images/bullet_square_full.gif') 32px 50% no-repeat;
    color: <?php echo $primary ?>;
}