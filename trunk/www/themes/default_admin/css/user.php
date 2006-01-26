/*************** PERMISSION MANAGER TEMPLATES ****************/

/**** permManager.html ****/
#frmFilterSwitcher fieldset {
    float: right;
}
#frmFilterSwitcher p label {
    width: 100px;
}

/**** permAdd.html ****/
#frmPermAdd p label {
    width: 10%;
}
p input.longText, p textarea.longText {
    width: 30%;
}

/**** permEdit.html ****/
#frmPermEdit p label {
    width: 10%;
}

/**** permScan.html ****/
#frmPermScan p label {
    width: 25%;
}
#frmPermScan select.longText {
    width: 50%;
}

/****************** ROLE MANAGER TEMPLATES *******************/

/**** roleAddEdit.html ****/
#frmRoleAddEdit p label {
    width: 10%;
}
/**** roleEditPerms.html ****/
#main_form p label {
    width: 15%;
}
#main_form h4 {
    text-align: center;
    font-weight: normal;
    color: <?php echo $primary ?>;
}
div#remainingPerms {
    width: 30%;
    height: 200px;
}
select#frmRemainingPerms {
    width: 100%;
    height: 100%;
}
div#addRemovePerms {
    width: 15%;
    padding-top: 80px;
    line-height: 3em;
    text-align: center;
}
div#selectedPerms {
    width: 30%;
    height: 200px;
}
select#frmRolePerms {
    width: 100%;
    height: 100%;
}

/*************** PREFERENCE MANAGER TEMPLATES ****************/

/**** prefManager.html ****/

/**** prefAddEdit.html ****/
#frmPrefAddEdit p label {
    width: 15%;
}

/****************** USER MANAGER TEMPLATES *******************/

/**** userManager.html ****/
thead th.id {
    width: 15px;
}
thead th.select {
    width: 15px;
}
fieldset#userCommands {
    float: right;
    width: 30%;
    margin-top: -0.5em;
}
fieldset#userCommands p label{
    width: 15%;
}

/**** userChangeStatus.html ****/
#frmUserStatusChange p label {
    width: 20%;
}

/**** userAdd.html ****/
#frmUserAdd p label {
    width: 170px;
    padding-right: 10px;
    text-align: right;
}
#userDetails, #userContact {
    width: 46%;
}
#userDetails {
    float: left;
}
#userContact {
    float: right;
}

#userContact p label {
    width: 25%;
}
#userContact input.longText {
    width: 70%;
}

/*************** USERSEARCH MANAGER TEMPLATES ****************/

/**** userManagerSearch.html ****/
#frmSearch p label {
    width: 20%;
}
#registerDateOptions {
    float: left;
}