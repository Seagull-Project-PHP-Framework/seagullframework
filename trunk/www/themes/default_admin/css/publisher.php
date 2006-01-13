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