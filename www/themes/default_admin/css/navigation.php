/************* SectionList template *******************/
#frmSectionMgr table {
    margin-top: 1em;
}
#frmSectionMgr span.tipOwner {
    cursor: auto;
}

/************* SectionEdit template *******************/
p.errorMsg {
    color: #ff3300;
    text-align: center;
}
#sectionEdit p label {
    width: 20%;
    padding-right: 2em;
}
#navigationOptions .tipText {
    width: 110%;
}
#sectionEdit #navigationOptions p label {
    width: 35%;
}
#navigationContent fieldset fieldset {
    padding: 5px;
    border: 1px solid <?php echo $tertiary ?>;
}
#navigationContent fieldset fieldset p label{
    width: 20%;
}
#navigationContent input.longText{
    width: 50%;
}
/************* CategoryMgr template *******************/
#frmCategoryMgr fieldset {
    border: none;
    padding: 5px;
}
#frmCategoryMgr p label {
    width: 25%;
    
}
#frmCategoryMgr input.longText {
    width: 40%;
}
#frmCategoryMgr span.tipText {
    width: 150%;
}
#frmCategoryMgr fieldset#rightManagement {
    width: 40%;
    margin-top: 1em;
    margin-left: 25%;
    border: 1px solid <?php echo $tertiaryDark ?>;
}
#frmCategoryMgr #rightManagement p label {
    width: 40%;
    padding-right: 2em;
    text-align: right;
}
/*********** frmCategoryReorder template **************/
#frmCategoryReorder {
    padding: 0;
}
#frmCategoryReorder fieldset {
    width: 90%;
    margin: 0 auto;
    padding: 0;
    border: none;
}
#frmCategoryReorder td a {
    color: <?php echo $tertiaryDarker ?>;
    font-weight: bold;
}