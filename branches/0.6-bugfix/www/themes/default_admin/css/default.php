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
-- configEdit.html -------------------------------------------*/
#configuration p label {
    width: 250px;
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
#configuration h3.show {
    margin-left: 270px;
}

/*
========================Pear Manager==========================*/

/*
-- pearList.html ---------------------------------------------*/
#pearPackages p label {
    width: 170px;
}
#pearPackages .sgl-button {
    margin-right: 10px;
}
#pearPackages tbody tr {
    line-height: normal;
}

/*
====================Translation Manager=======================*/

/*
-- langCheckAll.html -----------------------------------------*/
#translation_list table tr {
    text-indent: 3px;
}
/*
-- langEdit.html / langDiff.html -----------------------------*/
#translations table {
    padding: 0 0 5px;
}
#translations table tr {
    text-indent: 3px;
    line-height: normal;
}
