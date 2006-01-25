/********* articleManager.html template ********/
#frmAddArticle {
    padding-bottom: 0;
}
#frmAddArticle fieldset {
    margin-bottom: 0;
    border: none;
}
#frmAddArticle p label {
    width: 20%;
    padding-right: 2em;
}

#frmDeleteArticle {
    padding-top: 0;
}
#frmDeleteArticle fieldset {
    border: none;
}

/********* articleMgrAdd.html template ********/
/******** also for articleMgrEdit.html ********/
/*** as they both have same form name/id ******/
#articleAddOptions {
    width: 33%;
}
#articleAddOptions p label {
    width: 42%;
}
#articleAddContent fieldset{
    border: 1px solid <?php echo $tertiary ?>;
}
#articleAddContent p {
    margin-left: 5px;
    margin-right: 5px;
}
#articleAddContent p label {
    width: 20%;
}
#articleAddContent p input {
    width: 78%;
}
#articleAddContent span.tipText {
    width: 250%;
}
img.calendar{
    border: none;
    vertical-align: middle;
}

/********* documentManager.html template ********/
#newAsset fieldset {
    margin-bottom: 0;
    border: none;
}
#newAsset p label {
    width: 20%;
    padding-right: 2em;
}

/********* documentMgrAdd.html template ********/
#uploadAsset fieldset {
    margin-bottom: 0;
    border: none;
}
#uploadAsset p label {
    width: 20%;
    padding-right: 2em;
}
#uploadAsset input.longText, #uploadAsset textarea.longText {
    width: 50%;
}
#uploadAsset textarea.longText {
    height: 6em;
}

/********* documentMgrEdit.html template ********/
#editAsset fieldset {
    margin-bottom: 0;
    border: none;
}
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