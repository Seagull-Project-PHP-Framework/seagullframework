//  Allows to show/collapse any block element given its #id
//  Usage: setup a checkbox and call this function with onclick event
//  ex: <input type="checkbox" name="foo" id="foo" onclick="collapseElement(this.checked,'id_of_block_to_collapse')" />
function collapseElement(display,elementId)
{
    var blockToCollapse = document.getElementById(elementId);
    if (display){
        blockToCollapse.style.display = 'block';
    } else {
        blockToCollapse.style.display = 'none';
    }
}

//  Allows to highlight a row when hovering it with mouse
//  Needs every row to have a "back..." class name
function switchRowColorOnHover()
{
	var table = document.getElementsByTagName("table");
    for (var i=0; i<table.length; i++) {
        var row = table[i].getElementsByTagName("tr");
        for (var j=0; j<row.length; j++) {
            row[j].onmouseover=function() {
                if (this.className.search(new RegExp("back"))>=0) {
                    this.className+=" backHighlight";
                }

            }
            row[j].onmouseout=function() {
                this.className=this.className.replace(new RegExp(" backHighlight\\b"), "");
            }
        }
    }
}

function lockButtons(whichform)
{
    ua = new String(navigator.userAgent);
    if (ua.match(/IE/g)) {
        for (i=1; i<whichform.elements.length; i++) {
            if ((whichform.elements[i].type == 'submit') || (whichform.elements[i].type == 'button'))
                whichform.elements[i].disabled = true;
        }
    }
    whichform.submit();
}

function openWindow()
{
    var newWin = null;
    var url = openWindow.arguments[0];
    nArgs = openWindow.arguments.length;
    var width = openWindow.arguments[1];
    var height = openWindow.arguments[2];

    //  if dynamic window size args are passed
    if (nArgs > 1)
        newWin =  window.open ("","newWindow","toolbar=no,width=" + width + ",height=" + height + ",directories=no,status=no,scrollbars=yes,resizable=no,menubar=no");
    else
        newWin =  window.open ("","newWindow","toolbar=no,width=" + SGL_JS_WINWIDTH + ",height=" + SGL_JS_WINHEIGHT + ",directories=no,status=no,scrollbars=yes,resizable=no,menubar=no");
    newWin.location.href = url;
}

function confirmSubmit(item, formName)
{
    var evalFormName = eval('document.' + formName)
    var flag = false
    for (var count = 0; count < evalFormName.elements.length; count++) {
        var tipo = evalFormName.elements[count].type
        if (tipo == 'checkbox' && evalFormName.elements[count].checked == true && evalFormName.elements[count].name != '')
            flag = true
    }
    if (flag == false) {
        alert('You must select an element to delete')
        return false
    }
    var agree = confirm("Are you sure you want to delete this " + item + "?");
    if (agree)
        return true;
    else
        return false;
}

function confirmSubmitAndUpdate(myAction, formId)
{
    var selectedForm = document.getElementById(formId);
    var flag = false
    for (var count = 0; count < selectedForm.elements.length; count++) {
        var myType = selectedForm.elements[count].type
        if (myType == 'checkbox' && selectedForm.elements[count].checked == true && selectedForm.elements[count].name != '')
            flag = true
    }
    if (flag == false) {
        alert('You must select at least one element to update')
        return false
    }
    newInput = document.createElement("input");
    newInput.setAttribute('name', 'action');
    newInput.setAttribute('value', myAction);
    newInput.setAttribute('type', 'hidden');
    selectedForm.appendChild(newInput);
    selectedForm.submit();
}

function confirmDelete(item, formName)
{
    var evalFormName = eval('document.' + formName)
    var flag = false
    var agree = confirm("Are you sure you want to delete this " + item + "?");
    if (agree)
        return true;
    else
        return false;
}

function confirmDeleteWithMsg(msg)
{
    var agree = confirm(msg);
    if (agree)
        return true;
    else
        return false;
}

function confirmSave(formName)
{
    var evalFormName = eval('document.' + formName)
    var flag = false
    for (var count = 0; count < evalFormName.elements.length; count++) {
        var tipo = evalFormName.elements[count].type
        if (tipo == 'checkbox' && evalFormName.elements[count].checked == true && evalFormName.elements[count].name != '')
            flag = true
    }
    if (flag == false) {
        alert('You must select an element to save')
        return false
    }
}

function confirmSend(formName)
{
    var evalFormName = eval('document.' + formName)
    var flag = false
    for (var count = 0; count < evalFormName.elements.length; count++) {
        var tipo = evalFormName.elements[count].type
        if (tipo == 'checkbox' && evalFormName.elements[count].checked == true && evalFormName.elements[count].name != '')
            flag = true
    }
    if (flag == false) {
        alert('You must select at least one recipient')
        return false
    }
}

function confirmCategoryDelete(item)
{
    var agree = confirm("Are you sure you want to delete this " + item + "?");
    if (agree)
        return true;
    else
        return false;
}

function verifySelectionMade()
{
    var moveForm = document.moveCategory.frmNewCatParentID
    var selectedCat = moveForm.value
    if (selectedCat == '') {
        alert('Please select a new parent category')
        return false;
    } else
        return true;
}

function checkInput(formName, fieldName)
{
    var f = eval('document.' + formName + '.' + fieldName)
    if (f.value == '') {
        alert('Please enter a value in the field before submitting');
        return false;
    } else
        return true;
}

function getSelectedValue(selectObj)
{
    return (selectObj.options[selectObj.selectedIndex].value);
}


function toggleDisplay(myElement)
{
	boxElement = document.getElementById(myElement);

	if (boxElement.style.display == 'none') {
		boxElement.style.display = 'block';
	} else {
    	// ... otherwise collapse box
		boxElement.style.display = 'none';
	}
}

function confirmCustom(alertText, confirmText, formName)
{
    var evalFormName = eval('document.' + formName)
    var flag = false
    for (var count = 0; count < evalFormName.elements.length; count++) {
        var tipo = evalFormName.elements[count].type
        if (tipo == 'checkbox' && evalFormName.elements[count].checked == true && evalFormName.elements[count].name != '')
            flag = true
    }
    if (flag == false) {
        alert(alertText)
        return false
    }
    var agree = confirm(confirmText);
    if (agree)
        return true;
    else
        return false;
}

//  for block manager

var oldDate;
oldDate = new Array();

function time_select_reset(prefix, changeBack) {
    //  TODO: Rewrite this whole function (time_select_reset()) when adminGui is implemented.
    function setEmpty(id) {
        if (dateSelector = document.getElementById(id)) {
            oldDate = dateSelector.value;
            dateSelectorToShow = document.getElementById("frmExpiryDateToShow");
            oldDateToShow = dateSelectorToShow.innerHTML;
            if (dateSelector.value != ''){
                //alert(dateSelector.value);
                dateSelector.value = '';
                dateSelectorToShow.innerHTML = '';
            }
        }
    }

    function setActive(id) {
        if (dateSelector = document.getElementById(id)) {
            dateSelector.value = oldDate;
            dateSelectorToShow.innerHTML = oldDateToShow;
        }

    }

    if (document.getElementById(prefix+'NoExpire').checked) {
        setEmpty('frmExpiryDate');
    } else {
        if (changeBack == true) {
            setActive('frmExpiryDate');
        }
    }
}

/**
 * Checks/unchecks all tables, modified from phpMyAdmin
 *
 * @param   string   the form name
 * @param   boolean  whether to check or to uncheck the element
 *
 * @return  boolean  always true
 */
function setCheckboxes(the_form, element_name, do_check)
{
    var elts      = (typeof(document.forms[the_form].elements[element_name]) != 'undefined')
                  ? document.forms[the_form].elements[element_name]
                  : '';
    var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
    //var applyToWholeForm =
    //alert(element_name)


    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            elts[i].checked = do_check;
        }
    //  tick all checkboxes per form
    } else if (element_name == false) {
        var f = document.forms[the_form];
        for (var c = 0; c < f.elements.length; c++)
        if (f.elements[c].type == 'checkbox') {
          f.elements[c].checked = do_check;
        }

    } else {
        elts.checked        = do_check;
    }
    return true;
}

/**
 * Launches the above function depending on the status of a trigger checkbox
 *
 * @param   string   the form name
 * @param   string   the element name
 * @param   boolean   the status of triggered checkbox
 *
 * @return  void
 */
function applyToAllCheckboxes(formName, elementName, isChecked)
{
    if (isChecked) {
        setCheckboxes(formName, elementName, true)
    } else {
        setCheckboxes(formName, elementName, false)
    }
}

//  select/deselect options in a combobox
function toggleSelected(elem, state)
{
	var i;
	for (i = 0; i< elem.length; i++) {
		elem[i].selected = state;
	}
}