//  for block manager
function orderRightModule(down) 
{
    sl = document.frmBlockMgr.rightItem.selectedIndex;
    if (sl != -1) {
        oText = document.frmBlockMgr.rightItem.options[sl].text;
        oValue = document.frmBlockMgr.rightItem.options[sl].value;
        if (sl > 0 && down == 0) {
            document.frmBlockMgr.rightItem.options[sl].text = document.frmBlockMgr.rightItem.options[sl-1].text;
            document.frmBlockMgr.rightItem.options[sl].value = document.frmBlockMgr.rightItem.options[sl-1].value;
            document.frmBlockMgr.rightItem.options[sl-1].text = oText;
            document.frmBlockMgr.rightItem.options[sl-1].value = oValue;
            document.frmBlockMgr.rightItem.selectedIndex--;
        } else if (sl < document.frmBlockMgr.rightItem.length-1 && down == 1) {
            document.frmBlockMgr.rightItem.options[sl].text = document.frmBlockMgr.rightItem.options[sl+1].text;
            document.frmBlockMgr.rightItem.options[sl].value = document.frmBlockMgr.rightItem.options[sl+1].value;
            document.frmBlockMgr.rightItem.options[sl+1].text = oText;
            document.frmBlockMgr.rightItem.options[sl+1].value = oValue;
            document.frmBlockMgr.rightItem.selectedIndex++;
        }
    } else {
        alert("you must select an item to move");
    }    
    return false;
}  

function orderLeftModule(down)
{
    sl = document.frmBlockMgr.leftItem.selectedIndex;
    if (sl != -1) {
        oText = document.frmBlockMgr.leftItem.options[sl].text;
        oValue = document.frmBlockMgr.leftItem.options[sl].value;
        if (sl > 0 && down == 0) {
            document.frmBlockMgr.leftItem.options[sl].text = document.frmBlockMgr.leftItem.options[sl-1].text;
            document.frmBlockMgr.leftItem.options[sl].value = document.frmBlockMgr.leftItem.options[sl-1].value;
            document.frmBlockMgr.leftItem.options[sl-1].text = oText;
            document.frmBlockMgr.leftItem.options[sl-1].value = oValue;
            document.frmBlockMgr.leftItem.selectedIndex--;
        } else if (sl < document.frmBlockMgr.leftItem.length-1 && down == 1) {
            document.frmBlockMgr.leftItem.options[sl].text = document.frmBlockMgr.leftItem.options[sl+1].text;
            document.frmBlockMgr.leftItem.options[sl].value = document.frmBlockMgr.leftItem.options[sl+1].value;
            document.frmBlockMgr.leftItem.options[sl+1].text = oText;
            document.frmBlockMgr.leftItem.options[sl+1].value = oValue;
            document.frmBlockMgr.leftItem.selectedIndex++;
        }
    } else {
        alert("you must select an item to move");
    }
    
    return false;
}  

function doSubBlock()
{
    leftVal = "";
    for (i=0;i<document.frmBlockMgr.leftItem.length;i++) {
        if (i!=0) { leftVal += ","; }
        leftVal += document.frmBlockMgr.leftItem.options[i].value;
    }
    document.frmBlockMgr["_leftItems"].value = leftVal;
    
    rightVal = "";
    for (i=0;i<document.frmBlockMgr.rightItem.length;i++) {
        if (i!=0) { rightVal += ","; }
        rightVal += document.frmBlockMgr.rightItem.options[i].value;
    } 
    document.frmBlockMgr["_rightItems"].value = rightVal;    
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
