/*
=======================Module Manager=========================*/

/*
-- moduleList.html -------------------------------------------*/
#moduleList tbody tr {
    height: 40px;
    line-height: normal;
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
#configuration input.longText, #configuration textarea.longText{
    width: 58%;
}
#configuration textarea {
    height: 7em;
}
#configuration span.tipText {
    width: 142%;
}
