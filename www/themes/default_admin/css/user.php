
/*
========================User Manager==========================*/

/*
-- userManager.html ------------------------------------------*/
#users p label{
    width: 80px;
}
#users select {
    width: 250px;
}

/**** userChangeStatus.html ****/
#frmUserStatusChange p label {
    width: 170px;
}

/*
-- userAdd.html ----------------------------------------------*/
#frmUserAdd p label {
    width: 170px;
}

#frmUserAdd input.longText {
    width: 200px;
}

/*
-- accountSummary.html --------------------------------------*/
#frmMyAccount p label {
    width: 170px;
}

/*
-- userPasswordEdit.html ------------------------------------*/
#frmUpdatePasswd p label {
    width: 170px;
}
-- userPasswordReset.html ------------------------------------*/
#userPasswordReset p label {
    width: 170px;
}

/*
-- userPermsEdit.html ----------------------------------------*/
#updatePerms p label {
    width: 170px;
}
#updatePerms a.checkAll {
    float: right;
    margin: 0 3px;
    padding-left: 24px;
    background: url('<?php echo $baseUrl ?>/images/16/action_enable.gif') 5px 50% no-repeat;
}
#updatePerms a.uncheckAll {
    float: right;
    margin: 0 3px;
    padding-left: 24px;
    background: url('<?php echo $baseUrl ?>/images/16/action_disable.gif') 5px 50% no-repeat;
}

/*
=====================Permission Manager=======================*/

/*
-- permManager.html ------------------------------------------*/
#frmFilterSwitcher p label {
    width: 170px;
}

/*
-- permAdd.html ----------------------------------------------*/
#frmPermAdd p label {
    width: 150px;
}
p input.longText, p textarea.longText {
    width: 250px;
}

/*
-- permEdit.html ---------------------------------------------*/
#frmPermEdit p label {
    width: 150px;
}
p input.longText, p textarea.longText {
    width: 250px;
}

/*
-- permScan.html ---------------------------------------------*/
#frmPermScan p label {
    width: 250px;
    text-align: left;
}
#frmPermScan select.longText {
    width: 300px;
}

/*
========================Role Manager==========================*/

/*
-- roleEdit.html ---------------------------------------------*/
#frmRoleEdit p label {
    width: 170px;
}

/*
-- roleEditPerms.html ----------------------------------------*/
#main_form h4 {
    text-align: center;
    font-weight: normal;
    color: <?php echo $primary ?>;
}
div#remainingPerms {
    width: 35%;
    height: 200px;
}
select#frmRemainingPerms {
    width: 100%;
}
div#addRemovePerms {
    padding: 60px 20px 0 20px;
    line-height: 3em;
    text-align: center;
}
#addRemovePerms a.moveRight {
    display: block;
    padding: 0 20px 0 0;
    background: url('<?php echo $baseUrl ?>/images/16/move_right.gif') 95% 50% no-repeat;
}
#addRemovePerms a.moveLeft {
    display: block;
    padding: 0 0 0 20px;
    background: url('<?php echo $baseUrl ?>/images/16/move_left.gif') 5% 50% no-repeat;
}
div#selectedPerms {
    width: 35%;
    height: 200px;
}
select#frmRolePerms {
    width: 100%;
}

/*
=====================Preference Manager=======================*/

/**** prefEdit.html ****/
#frmPrefEdit p label {
    width: 170px;
}
#frmPrefEdit .longText {
    width: 200px;
}

/*
=====================UserSearch Manager=======================*/

/**** userManagerSearch.html ****/
#frmSearch p label {
    width: 20%;
}
#registerDateOptions {
    float: left;
}
