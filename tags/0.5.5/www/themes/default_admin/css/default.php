form {
    width: 60%;
}
fieldset legend {
    color: <?php echo $secondary ?>;
    font-size: 1.3em;
    font-weight: bold;
}
fieldset p.center {
    margin: 10px;
    text-align: center;
}
/******** Module overview template ****************/
#moduleOverview {
    padding: 10px;
}
#moduleOverview .moduleBlock {
    float: left;
    width: 45%;
    margin: 0 10px;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #dfdfdf;    
}
#moduleOverview h2 {
    color: <?php echo $primaryLight ?>;
}
    .moduleManageLinks {
        float: right;
    }
    .moduleManageLinks a {
        color: <?php echo $tertiaryDark ?>
    }
#moduleOverview .moduleName {
    
    float: left;
    width: 60px;
}
#moduleOverview .moduleName img {
    width: 48px;
}
#moduleOverview .moduleDesc {
    margin: 5px 10px 10px 0;
    color: <?php echo $secondaryDark ?>
}
/******** Module add/edit form ****************/
#module p label {
    width: 25%;
    padding-right: 2em;
    text-align: right;
}
#module input.text {
    width: 50%;
}
#module textarea {
    width: 65%;
    height: 5em;
}
#module span.tipText {
    width: 167%;
}
/******** Maintenance form ****************/
#moduleCreator div {
    margin-left: 35%;
}
#moduleCreator p label{
    float: left;
    width: 35%;
}
#translationList p {
    margin-top: 1em;
}
/******** Config form ****************/
form#configuration {
    float: left;
    width: 74%;
    z-index: 1;
}
form#configuration table {
    margin-top: 2em;
}
#configuration input.longText, #configuration textarea.longText{
    width: 58%;
}
#configuration textarea {
    height: 7em;
    overflow: auto;
}
#configuration span.tipText {
    width: 142%;
}
#configuration img {
    vertical-align: middle;
}
#configuration p {

}
#configuration p label {
    width: 40%;
    position: relative;
}
#optionsLinks {
    width: 22%;
    margin: 10px 5px 0;
    padding: 5px;
    line-height: 1.5;
}
#optionsLinks h2 {
    margin-bottom: 10px;
    background: <?php echo $tertiary ?>;
    border-top: 2px solid <?php echo $tertiaryDark ?>;
    border-bottom: 2px solid <?php echo $tertiaryDark ?>;
    font-size: 1.1em;
    color: <?php echo $tertiaryDarker ?>;
    text-align: center;
}