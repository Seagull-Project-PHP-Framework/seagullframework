/**
    * REMINDER
    * Progressively change functions so they use document.getElementById() func
    * instead of document.<form name>
    * as forms must not have a "name" value according to xHTML STRICT DOCTYPE
    */

/**
    * Errors checking
    * Searching for <span class="error">,
    *  Modifying parent node className
    *  Setting appropriate left margin to other fields
    */
function formErrorCheck()
{
    var labels = document.getElementsByTagName("label");
    if (labels) {
        var labelWidth = labels[0].offsetWidth;
        if (document.all && !window.sidebar) {
           //  this check should recognise only IE
           labelWidth += 3;
        }
    }
    for (i=0; i<document.getElementsByTagName("form").length; i++) {
        var errorSpans = document.forms[i].getElementsByTagName("span");
        if (errorSpans) {
            for (j=0; j<errorSpans.length; j++) {
                if (errorSpans[j].className == "error") {
                    var parentObject = errorSpans[j].parentNode;
                    parentObject.className += " errorBlock";
                    for (k=0; k<parentObject.childNodes.length; k++) {
                        if (parentObject.childNodes[k].nodeName == "INPUT"
                            || parentObject.childNodes[k].nodeName == "SELECT") {
                            parentObject.childNodes[k].style.marginLeft = labelWidth +"px";
                        }
                    }
                    //  check if this node is in a tab
                    if (field = parentObject.parentNode) {
                        if (field.className == "options") {
                            var tabId = field.id;
                            var tabs = document.getElementById("optionsLinks");
                            var tabElements = tabs.getElementsByTagName("li");
                            for (l=0; l<tabElements.length; l++) {
                                if (tabElements[l].className.match(new RegExp(tabId+"\\b"))) {
                                    var errorTab = tabElements[l].childNodes;
                                    errorTab[0].className = "error";
                                    var thisForm = document.forms[0].id;
                                    showSelectedOptions(thisForm,tabId);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
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
function formSubmit(formId, fieldName, fieldValue, doCreate)
{
    var selectedForm = document.getElementById(formId);
    if (typeof doCreate != "undefined" && doCreate == 1) {
        newInput = document.createElement("input");
        newInput.setAttribute('name', fieldName);
        newInput.setAttribute('value', fieldValue);
        newInput.setAttribute('type', 'hidden');
        selectedForm.appendChild(newInput);
    } else {
        if (fieldName) {
            var elm = selectedForm.elements[fieldName];
            elm.value = fieldValue;
        }
    }
    selectedForm.submit();
}

//  Allows to reset a form
function formReset(formId)
{
    var selectedForm = document.getElementById(formId);
    if (!selectedForm) {
        return;
    }
    selectedForm.reset();
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

    var items = document.getElementById("optionsLinks").getElementsByTagName("li");
    for (i=0; i<items.length; i++) {
        if (items[i].className.match(new RegExp(" current\\b"))) {
            items[i].className = items[i].className.replace(new RegExp(" current\\b"), "");
        }
        if (items[i].className.match(new RegExp(option +"\\b"))) {
            items[i].className +=" current";
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
            optionsLinks += "<li class=\""+elms[i].id+"\"><a href='javascript:showSelectedOptions(\""+formId +"\",\""+elms[i].id +"\")'>"+elms[i].getElementsByTagName(titleTag)[0].innerHTML +"</a></li>";
        }
    }
    optionsLinks += "</ul>";
    document.getElementById("optionsLinks").innerHTML += optionsLinks;
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

function MoveOption(MoveFrom, MoveTo, ToDo)
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
    alert("Please select a permission(s) to move.");
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

function checkDuplicates(AddListContainer, RemoveListContainer)
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

function confirmAction(msg)
{
    var isConfirmed = confirm(msg);
    if (isConfirmed != '') {
        return true;
    } else {
        return false;
    }
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
