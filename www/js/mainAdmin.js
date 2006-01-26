/**
    * Allows to create/modify a field value within a form before submitting it.
    * Launches the above function depending on the status of a trigger checkbox 
 
    * @param   string   formName Obviously the form name you want to submit
    * @param   string   fieldToUpdate The element name you want to modify
    * @param   string   fieldValue
    * @param   bool      doCreate If you want to create a hidden input element instead of modifying an existing one
    *
    * @return  void Submit the form
    */
function formSubmit(formName, fieldName, fieldValue, doCreate)
{
    var form = document.forms[formName];
    if (typeof doCreate != "undefined" && doCreate == 1) {
        newInput = document.createElement("input");
        newInput.setAttribute('name', fieldName);
        newInput.setAttribute('value', fieldValue);
        newInput.setAttribute('type', 'hidden');
        form.appendChild(newInput);
    } else {
        if (fieldName) {
            var elm = form.elements[fieldName];
            elm.value = fieldValue;
        }
    }
    form.submit();
}
//  Allows to show/hide a block of options (defined within a fieldset) in any form
function showSelectedOptions(formId, option)
{

    var selectedForm = document.getElementById(formId);
    if (!selectedForm) return true;
    var elms = selectedForm.getElementsByTagName("fieldset");
    for (i=0; i<elms.length; i++) {
        if (elms[i].className.match(new RegExp("options\\b"))) {
            if (elms[i].id == option) {
                elms[i].style.display = "block";
            } else {
                elms[i].style.display = "none";
            }
        }
    }
}

//  Mandatory function when using showConfigOptions() above
//  Dynamically creates links to display selected block of options
function createAvailOptionsLinks(formId, titleTag)
{
    var selectedForm = document.getElementById(formId);
    if (typeof titleTag == "undefined") var titleTag = 'h3';
    if (!selectedForm) return true;
    if (!document.getElementById("optionsLinks")) {
        alert('The Div container with id set to "optionsLinks" wasn\'t found' );
        return true;
    }
    var elms = selectedForm.getElementsByTagName("fieldset");
    var optionsLinks = '<ul>';
    for (i=0; i<elms.length; i++) {
        if (elms[i].className.match(new RegExp("options\\b"))) {
            optionsLinks += "<li><a href='javascript:showSelectedOptions(\""+formId +"\",\""+elms[i].id +"\")'>"+elms[i].getElementsByTagName(titleTag)[0].innerHTML +"</a></li>";
        }
    }
    optionsLinks += "</ul>";
    document.getElementById("optionsLinks").innerHTML += optionsLinks;
}

//  for block manager

var oldDate;
oldDate = new Array();

function time_select_reset(prefix, changeBack){
    
    if (typeof SGL_JS_ADMINGUI == "undefined") {
        /*
         * adds a empty node as first node of a date selector
         * @param object dateSelector Select object to add node
         * returns old value of selector
         */
        function setEmpty(id) {
            if (dateSelector = document.getElementById(id)) {
                if (dateSelector.firstChild.value != ''){
                    newNode = document.createElement("option");
                    newNode.value = '';
                    newNode.appendChild(document.createTextNode(" -- "));
                    dateSelector.insertBefore(newNode, dateSelector.options[0]);
                }
                oldValue = dateSelector.value;
                dateSelector.options[0].selected = true;
                dateSelector.disabled = true;
                return oldValue;
            }
        }
    
        function setActive(id) {
            if (dateSelector = document.getElementById(id)) {
                relocate_select(dateSelector, oldDate[id]);
                dateSelector.disabled = false;
            }
    
        }
    
        selectors = new Array(prefix+'_year', prefix+'_month', prefix+'_day', prefix+'_hour', prefix+'_minute', prefix+'_second');
        d = new Date();
    
        if (oldDate.length == 0){
        }
    
        if( document.getElementById(prefix+'NoExpire').checked ){
            for (var i = 0; i <= selectors.length; i++){
                oldDate[(selectors[i])] = setEmpty(selectors[i]);
            }
        }else{
            if(changeBack == true){
                for (var i = 0; i <= selectors.length; i++){
                    setActive(selectors[i]);
                }
            }
        }
    } else if (SGL_JS_ADMINGUI == 1) {
        //  Equivalent function if we are in "admin mode". 
        //  TODO: Remove or rewrite this whole function (time_select_reset()) when adminGui is implemented.
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
}

function relocate_select(obj, value){
    if( obj ){
        for( i=0; i<obj.options.length; i++ ){
            if( obj.options[i].value==value )
                obj.options[i].selected = true;
            else
                obj.options[i].selected = false;
        }
    }

}

function orderItems(down)
{
    sl = document.frmBlockMgr.item.selectedIndex;
    if (sl != -1) {
        oText = document.frmBlockMgr.item.options[sl].text;
        oValue = document.frmBlockMgr.item.options[sl].value;
        if (sl > 0 && down == 0) {
            document.frmBlockMgr.item.options[sl].text = document.frmBlockMgr.item.options[sl-1].text;
            document.frmBlockMgr.item.options[sl].value = document.frmBlockMgr.item.options[sl-1].value;
            document.frmBlockMgr.item.options[sl-1].text = oText;
            document.frmBlockMgr.item.options[sl-1].value = oValue;
            document.frmBlockMgr.item.selectedIndex--;
        } else if (sl < document.frmBlockMgr.item.length-1 && down == 1) {
            document.frmBlockMgr.item.options[sl].text = document.frmBlockMgr.item.options[sl+1].text;
            document.frmBlockMgr.item.options[sl].value = document.frmBlockMgr.item.options[sl+1].value;
            document.frmBlockMgr.item.options[sl+1].text = oText;
            document.frmBlockMgr.item.options[sl+1].value = oValue;
            document.frmBlockMgr.item.selectedIndex++;
        }
    } else {
        alert("you must select an item to move");
    }

    return false;
}

function doSubBlock()
{
    blocksVal = "";
    for (i=0;i<document.frmBlockMgr.item.length;i++) {
        if (i!=0) { blocksVal += ","; }
        blocksVal += document.frmBlockMgr.item.options[i].value;
    }
    document.frmBlockMgr["_items"].value = blocksVal;

    return true;
}

//  same fns again for faq & section managers!
function orderModule(down)
{
    sl = document.fm.item.selectedIndex;
    if (sl != -1) {
     oText = document.fm.item.options[sl].text;
     oValue = document.fm.item.options[sl].value;
     if (sl > 0 && down == 0) {
      document.fm.item.options[sl].text = document.fm.item.options[sl-1].text;
      document.fm.item.options[sl].value = document.fm.item.options[sl-1].value;
      document.fm.item.options[sl-1].text = oText;
      document.fm.item.options[sl-1].value = oValue;
      document.fm.item.selectedIndex--;
     } else if (sl < document.fm.item.length-1 && down == 1) {
      document.fm.item.options[sl].text = document.fm.item.options[sl+1].text;
      document.fm.item.options[sl].value = document.fm.item.options[sl+1].value;
      document.fm.item.options[sl+1].text = oText;
      document.fm.item.options[sl+1].value = oValue;
      document.fm.item.selectedIndex++;
     }
    } else {
     alert("you must select an item to move");
    }
    return false;
}

function doSub()
{
    val = '';
    for (i=0;i<document.fm.item.length;i++) {
        if (i!=0) {
            val += ",";
        }
        val += document.fm.item.options[i].value;
    }
    document.fm[".items"].value = val;
    return true;
}

var delimiter = ":";

function MoveOption (MoveFrom, MoveTo, ToDo)
{
  var SelectFrom = eval('document.main_form.'+MoveFrom);
  var SelectTo = eval('document.main_form.'+MoveTo);
  var SelectedIndex = SelectFrom.options.selectedIndex;
  var container;
  if (ToDo=='Add') {
    container=eval('document.main_form.' + ToDo + MoveTo);
  }
  if (ToDo=='Remove') {
    container=eval('document.main_form.' + ToDo + MoveFrom);
  }
  if (SelectedIndex == -1) {
    alert("Please select a person(s) to move.");
  } else {
    for (i=0; i<SelectFrom.options.length; i ++) {
      if (SelectFrom.options[i].selected) {
        var name = SelectFrom.options[i].text;
        var ID = SelectFrom.options[i].value;
        SelectFrom.options[i] = null;
        SelectTo.options[SelectTo.options.length]= new Option (name,ID);
        i=i-1;
        if (ToDo=='Add'||ToDo=='Remove') {
          container.value=container.value+name+'^' + ID + delimiter;
        }
      }
    }
  }
}

function checkDuplicates (AddListContainer, RemoveListContainer)
{
    var AddList = eval('document.main_form.'+AddListContainer);
    var RemoveList = eval('document.main_form.'+RemoveListContainer);
    var TempAddList = AddList.value;
    var TempRemoveList = RemoveList.value;
    if (TempAddList>''&&TempRemoveList>'') {
        TempAddList = TempAddList.substring(0,TempAddList.length-1);
        TempRemoveList = TempRemoveList.substring(0,TempRemoveList.length-1);
        var AddArray = TempAddList.split(delimiter);
        var RemoveArray = TempRemoveList.split(delimiter);
        for (i=0; i<AddArray.length; i++) {
          for (j=0; j<RemoveArray.length; j++) {
            if (AddArray[i]==RemoveArray[j]) {
              AddArray[i]='';
              RemoveArray[j]='';
              break;
            }
          }
        }
        AddList.value='';
        for (i=0; i<AddArray.length; i++) {
          if (AddArray[i]>'') {
            AddList.value = AddList.value + AddArray[i] + delimiter;
          }
        }
        RemoveList.value='';
        for (i=0; i<RemoveArray.length; i++) {
          if (RemoveArray[i]>'') {
            RemoveList.value = RemoveList.value + RemoveArray[i] + delimiter;
          }
        }
    }
}

function lockChanges()
{
    checkDuplicates('AddfrmRolePerms','RemovefrmRolePerms');
}

 // simple confirm box, incl list of any child node(s) of selected node(s)
function confirmDelete(item, formName)
{
 var evalFormName = eval('document.' + formName)
 var flag = false;
 var childrenPresent = false;
 var childNodes = new Array();
 var toDelete = '';
 var msg = '';
 var childrenString = '';
 for (var cont = 0; cont < evalFormName.elements.length; cont++) {
     var elType = evalFormName.elements[cont].type
     if (elType == 'checkbox' && evalFormName.elements[cont].checked == true && evalFormName.elements[cont].name != ''){
         flag = true;
         var elementString = evalFormName.elements[cont].name;
         var openBracket = elementString.indexOf("[") + 1;
         var closeBracket = elementString.lastIndexOf("]");
         nodeId = elementString.substring(openBracket,closeBracket);
         toDelete += nodeArray[nodeId][2] + ", ";
         if (!contains(nodeId,childNodes)){
          childNodes[childNodes.length] =  nodeId;
         }

         for(id in nodeArray)
         {
             if ( nodeArray[id][0] > nodeArray[nodeId][0] && nodeArray[id][1] < nodeArray[nodeId][1]  && nodeArray[id][4] == nodeArray[nodeId][4]){
                 if (!contains(id,childNodes)){
                     childNodes[childNodes.length] = id;
                     childrenPresent = true;
                 }
             }
         }
     }
 }
 toDelete = toDelete.substring(0,toDelete.lastIndexOf(","));
 msg = "Are you sure you wish to permanently delete the " + item + "(s): " + toDelete + "?\n(If you anticipate using a " + item + " later, simply mark it \"disabled\" instead; it will no longer be displayed but can easily be reactivated later.)\n";

 if (childrenPresent == true){
     for(b=0;b<childNodes.length;b++){
         var indent = '';
         for(g=1;g<nodeArray[childNodes[b]][3];g++){
             indent = indent + "\t";
         }
         childrenString = childrenString + indent + "-" + nodeArray[childNodes[b]][2] + "\n";
     }
     msg = msg + "\nCAUTION: One or more of the " + item + "s you selected contains subordinate " + item + "s. If you proceed, all of the following " + item + "s will be deleted:\n\n" + childrenString + "\nAre you sure you want to do this?";
 }

 if (flag == false) {
     alert('You must select an element to delete')
     return false
 }
 var agree = confirm(msg);
 if (agree)
     return true;
 else
     return false;
}

// used by confirmDelete(); sees if array already contains a value
function contains(tmpVal, tmpArray)
{
    for (j=0; j < tmpArray.length; j++) {
        if (tmpArray[j] == tmpVal) {
            return true;
        }
    }
    return false;
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

    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            elts[i].checked = do_check;
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
 * @param   boolean   the status of trigger checkbox
 *
 * @return  void 
 */
function applyToAllCheckboxes(formName, elementName, isChecked)
{
    if(isChecked) {
        setCheckboxes(formName, elementName, true)
    } else {
        setCheckboxes(formName, elementName, false)
    }
}