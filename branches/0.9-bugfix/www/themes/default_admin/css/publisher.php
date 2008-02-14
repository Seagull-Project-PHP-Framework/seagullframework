/*
======================Article Manager=========================*/

/*
-- articleManager.html ---------------------------------------*/
#frmAddArticle p label {
    width: 140px;
}
#articleTypeSelector {
    display: none;
    position: absolute;
    top: 2.4em;
    left: 6.25em;
    background: <?php echo $tertiaryLightest ?>;
    border-style: solid;
    border-color: <?php echo $primary ?>;
    border-width: 1px;
}
#articleTypeSelector a {
    display: block;
    clear: left;
    float: left;
    margin: 0;
    padding: 0 5px;
    border: none;
    background: none;
    text-indent: 10px;
}
#articleTypeSelector a:hover {
    border: none;
}

/*
-- articleMgrAdd.html / articleMgrEdit.html ------------------*/
#articleAddOptions p label {
    width: 200px;
}
#articleAddContent p label {
    width: 80px;
    text-align: left;
}
#articleAddContent p input.longText {
    width: 80%;
}
#articleAddContent span.tipText {
    width: 250%;
}
img.calendar{
    border: none;
    vertical-align: middle;
    cursor: pointer;
}

/*
=====================Document Manager=========================*/

/*
-- documentManager.html ---------------------------------------*/
#newAsset p label {
    width: 140px;
}

/*
-- documentMgrAdd.html ---------------------------------------*/
#uploadAsset p label {
    width: 20%;
}
#uploadAsset input.longText, #uploadAsset textarea.longText {
    width: 40%;
}
#uploadAsset textarea.longText {
    height: 6em;
}

/*
-- documentMgrEdit.html ---------------------------------------*/
#editAsset p label {
    width: 20%;
    padding-right: 2em;
}
#editAsset .longText {
    width: 50%;
}
#editAsset textarea.longText {
    height: 6em;
}

/*
-- contentTypeAdd.html / contentTypeEdit.html ---------------------------------------*/
#addContentType p input.longText, #editContentType p input.longText {
    width: 60%;
}
