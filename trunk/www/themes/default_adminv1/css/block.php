
/*
=======================Block Manager==========================*/
/*
-- blockList.html --------------------------------------------*/

/*
-- blockEdit.html --------------------------------------------*/
#frmBlockEdit p label {
    width: 240px;
}
#frmBlockEdit p select {
    width: 200px;
}

/*
-- blockReorder.html -----------------------------------------*/
#blockSelector {
    float: left;
}
#blockReorder {
    float: left;
    margin-left: 10px;
}
a.blockMoveUp {
    display: block;
    margin-top: 80px;
    padding: 0 20px;
    background: url('<?php echo $baseUrl ?>/images/16/sort_desc.gif') 0 50% no-repeat;
}
a.blockMoveDown {
    display: block;
    margin-top: 10px;
    padding: 0 20px;
    background: url('<?php echo $baseUrl ?>/images/16/sort_asc.gif') 0 50% no-repeat;
}
