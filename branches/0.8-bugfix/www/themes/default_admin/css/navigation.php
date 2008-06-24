
/*
========================Page Manager==========================*/
/*
-- sectionList.html ------------------------------------------*/

/*
-- sectionEdit.html ------------------------------------------*/
p.errorMsg {
    color: <?php echo $errorMessage ?>;
    text-align: center;
}
#sectionEdit p label {
    width: 200px;
}
#sectionEdit .longText {
    width: 250px;
}

/*
======================Category Manager========================*/

/*
-- categoryMgr.html -----------------------------------------*/
#frmCategoryMgr p label {
    width: 170px; 
}
#frmCategoryMgr input.longText {
    width: 40%;
}
#frmCategoryMgr span.tipText {
    width: 300px;
}
#frmCategoryMgr fieldset#permissions {
    width: 80%;
    margin-top: 1em;
    border: 1px solid <?php echo $borderDark ?>;
}
#frmCategoryMgr #permissions p label {
    width: 160px;
}

/*
-- categoryReorder.html --------------------------------------*/
#frmCategoryReorder a {
    text-decoration: none; /* FIXME
    ------------------------- There shouldn't be any space to be undelined */
}
