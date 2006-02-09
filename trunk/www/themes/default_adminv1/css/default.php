/*
=======================Module Manager=========================*/

/*
-- moduleList.html -------------------------------------------*/
#moduleList a.tipOwner {
    display: block; /* FIXME
    ------------------ IE hack for bg-image to display correctly */
    text-decoration: none;
}
#moduleList a.tipOwner:hover {
    border: transparent;
}
#moduleList .tipOwner {
    position: relative;
    padding-left: 15px;
    background: url('<?php echo $baseUrl ?>/images/tooltip.gif') 0 50% no-repeat;
}
#moduleList .tipOwner span.tipText {
    display: none;
    position: absolute;
    top: 1em;
    left: 15em;
    width: 250px;
    padding: 2px 5px;
    border: 1px solid <?php echo $borderLight ?>;
    background-color: <?php echo $primaryBackground ?>;
    color: <?php echo $primaryText ?>;
    line-height: normal;
    text-align: left;
    text-decoration: none;
    <?php if ($browserFamily == 'MSIE') {?>
    filter: alpha(opacity=100);
    <?php } else { ?>
    -moz-opacity: 1;
    <?php } ?>
}
#moduleList .tipOwner:hover span.tipText {
    display: block;
}

/*
-- moduleEdit.html -------------------------------------------*/
#module p label {
    width: 20%;
}
#module input.text {
    width: 20%;
}
#module textarea {
    width: 50%;
    height: 5em;
}
#module span.tipText {
    width: 167%;
}

/*
====================Maintenance Manager=======================*/

/*
-- maintenance.html -------------------------------------------*/
#moduleCreator p label{
    float: left;
    width: 250px;
}
#moduleCreator div {
    margin-left: 270px; /* INFO
    ---------------------- The above p label width
    ---------------------- + the standard p label padding-right (20px) */
}
#translationList p {
    margin-top: 1em;
}

/*
===================Configuration Manager======================*/

/*
-- configEdit.html.html -------------------------------------------*/
#configuration p label {
    width: 35%;
    text-align: left;
}
#configuration #optionsLinks a {
    padding: 5px 5px 4px;
}
#configuration input.longText, #configuration textarea.longText{
    width: 58%;
}
#configuration textarea {
    height: 7em;
}
#configuration span.tipText {
    width: 142%;
}