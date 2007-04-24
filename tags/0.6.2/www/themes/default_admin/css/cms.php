
/*
==================Core Module overwrites======================*/

dt .required {
    color: <?php echo $primaryDark ?>;
}
dt .required:after {
    content: " *";
    color: #ff0000;
}
dt.onSide, dl.onSide dt {
    float: left;
    width: 150px;
    text-align: right;
}
dt label {
    padding-right: 20px;
}
dd.onSide, dl.onSide dd {
    margin-left: 150px;
    margin-bottom: 0.5em;
}
dl.onTop dt label, dt.onTop label {
    font-weight: bold;
    color: <?php echo $tertiaryDarkest ?>;
}
dl.onTop dd {
    margin: 0;
}
input.mediumButton {
    width: 8em;
}

/*
====================ContentType Manager=======================*/
/*
-- contentTypeList.html --------------------------------------*/
#addType dt.onSide, #addType dl.onSide dt {
    width: 80px;
}
#addType dd.onSide, #addType dl.onSide dd {
    margin-left: 80px;
}
.attributesPopUp {
    display: none;
    float: left;
    padding: 2px 5px;
    border: 1px solid <?php echo $primary ?>;
}
.attributesPopUp h4 {
    border-bottom: 2px solid <?php echo $primary ?>;
}
.toggleAttributeList {
    cursor: pointer;
    float: left;
}

/*
-- contentTypeEdit.html --------------------------------------*/
#contentTypeEdit dl.onTop dd {
    margin: 0;
}
div.box {
    margin: 5px 0 10px;
}
.box-contentType {

}
.box-attributes {

}
input.longText {
    width: 250px;
}
#content table.contentTypeAttributesList {
    border-color: <?php echo $tertiaryDarker ?>;
}
table.contentTypeAttributesList thead tr.infos {
    background: <?php echo $tertiaryLight; ?>;
}
table.contentTypeAttributesList thead tr.infos input {
    padding: 0 3px;
}
table.contentTypeAttributesList thead tr.infos th {
    font-size: 1.3em;
    font-weight: normal;
    color: <?php echo $tertiaryDarkest ?>;
}
