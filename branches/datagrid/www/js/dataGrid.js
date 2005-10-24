    <!--   1 -> ASC   2 -> DESC   -->
    function setOrderBy(dataGridName, elementName) {
        var col = document.getElementsByTagName('input');
        var form = null;
        for (i = 0; i< col.length; i++) {
            element = col[i];
            if (element.id == dataGridName) {
                if (element.name == elementName) {
                    if (element.value == '') {
                        element.value = 'ASC';
                    }
                    else {
                        if (element.value == 'ASC') {
                            element.value = 'DESC';
                        }
                        else {
                            element.value = 'ASC';
                        }
                    }
                    form = element.form;
                }
                else {
                    element.value = '';
                }
            }
        }
        form.submit();
    }

function resetFilters(dataGridId) {
        var col1 = document.getElementsByTagName('input');
        var col2 = document.getElementsByTagName('select');
        var form = null;
        if (col1 != null) {
            for (i = 0; i< col1.length; i++) {
            element = col1[i];
            if (element.id == dataGridId) {
                element.value = '';
            }
}
            form = element.form;
        }
        if (col2 != null) {
            for (i = 0; i< col2.length; i++) {
            element = col2[i];
            if (element.id == dataGridId) {
                element.value = '';
            }
}
            form = element.form;
        }
        form.submit();
}

    function setExport(dataGridElement,exportValue) {
        var element = document.getElementById(dataGridElement);
        var form = null;
        element.value = exportValue;
        form = element.form;
        form.submit();
        element.value = "";
    }

    //  JavaScript - for selecting rows (Netscape or Mozilla)

    // We have to capture events in Netscape - ctrl Key
    if(document.layers){
        document.captureEvents(Event.KEYDOWN);
    }


    // when using single selection list box
    // == -1 indicates that we must check every row
    // != -1 number of selected row
    //var iSingleRow = -1;
    var iSingleRow = new Array;
    var iAllSelected = new Array;
    // Gets checkbox object
    // sCheckboxName  - name of checkboxes
    // iRow  - number of row
    function GetCB(sCheckboxName, iRow) {
        if (iRow >= 0) {
            return document.getElementsByName(sCheckboxName + "[]")[iRow];
        }
    }

    // Function - determine TR from descending object
    // oObject - descending object
    // returns TR object
    function GetTR(oObject) {
        // determine row - TR
        oRow = oObject;
        while ((oRow.tagName.toLowerCase() != "tr")) {
            oRow = oRow.parentNode;                      // in IE is parentElement
        }
        return oRow;
    }
    // Function - determine TR from descending object
    // oObject - descending object
    // returns TR object
    function GetTable(oObject) {
        // determine row - TR
        oRow = oObject;
        while ((oRow.tagName.toLowerCase() != "table")) {

            oRow = oRow.parentNode;                      // in IE is parentElement
        }
        return oRow;
    }

    // Function - set style of TR object
    // oTR - TR object
    // bSelected - if selected then sets name of CSS style, otherwise clear style name
    function SetTRStyle(oTR, bSelected) {
        if (bSelected) {
            oTR.className = "dataGrid_selected_row"; // classes from style.php
        } else {
            oTR.className = "dataGrid_row";
        }
    }

    // Function: counts selected rows and clears selected rows for single selection
    // Fills xSelected table
    //  sCheckboxName  - name of checkboxes
    // iStartRow  - number of start row
    // iIncrement - how to change row
    // returns number of selected rows
    function CountSelected(oEvent, sCheckboxName, iStartRow, iIncrement) {
        var iChecked = 0;
        oCB = GetCB(sCheckboxName, iStartRow);
        while (oCB) {
            if (oCB.checked) {
                iChecked++;
                // for single selection clear chceckboxes and styles in rows other than current row
                // do it only if checked
                if (!oEvent || !oEvent.ctrlKey) {
                    oCB.checked = false;
                    SetTRStyle(GetTR(oCB), false);
                }
            }
            iStartRow = iStartRow + iIncrement;
            oCB = GetCB(sCheckboxName, iStartRow);
        }
        return iChecked;
    }

    //  Function:
    //  on click changes selection from one row to that clicked
    //  on click with CONTROL key down selects clicked row (multi selection)
    //  sCheckboxName  - name of checkboxes
    //  iRowNumber     - is counted from 0
    //  oEvent         - used to tetermine Ctrl key pressed
    function ChangeSelection(oEvent, sCheckboxName, iRowNumber) {

        // determine checkbox object
        oCheckBox = GetCB(sCheckboxName, iRowNumber);
        if (oCheckBox) {
            // how many is checked before action
            var iChecked;
            var tr = GetTable(oCheckBox);
            var table = tr.getAttribute('dataGridName');
            if (!iSingleRow[table]) {
                iSingleRow[table] = -1;
            }
            if (!iAllSelected[table]) {
                iAllSelected[table] = -1;
            }
            if (iAllSelected[table] != -1) {
                DeselectAllRows(sCheckboxName);
                iAllSelected[table] = -1;
            }
            if ((iSingleRow[table] != -1) && (!oEvent || !oEvent.ctrlKey)) {
                // we are sure that this was single seletion and now is also single selection
                // deselect that row
                oCB = GetCB(sCheckboxName, iSingleRow[table]);
                oCB.checked = false;
                SetTRStyle(GetTR(oCB), false);
                iChecked = 1;
            } else {
                // for multiselection -> count how many checkboxes are checked besides that row
                // for sigleselection -> deselect all rows
                // count down
                iChecked = CountSelected(oEvent, sCheckboxName, iRowNumber - 1, -1);
                // count up
                iChecked += CountSelected(oEvent, sCheckboxName, iRowNumber + 1, +1);
            }
            // if CONTROL depressed - then multiselection list
            // otherwise single selection
            if (oEvent && oEvent.ctrlKey) {
                // MULTI SELECTION
                // if CONTROL pressed during click then change select of row
                if (iChecked > 0) {
                    // invert selection
                    oCheckBox.checked = !oCheckBox.checked;
                } else {
                    // just for sure
                    oCheckBox.checked = true;
                }
                iSingleRow[table] = -1;
            } else {
                // SINGLE SELECTION
                // always check current row
                oCheckBox.checked = true;
                iSingleRow[table] = iRowNumber;
            }
            // set proper style for current row
            SetTRStyle(GetTR(oCheckBox), oCheckBox.checked);
        }
    }

    //select all rows
    function SelectAllRows(sCheckboxName) {
        var iRow = 0;
        oCB = GetCB(sCheckboxName, iRow);
        var tr = GetTable(oCB);
        var table = tr.getAttribute('dataGridName');
        while (oCB) {
            oCB.checked = true;
            SetTRStyle(GetTR(oCB), true);
            iRow = iRow + 1;
            oCB = GetCB(sCheckboxName, iRow);
        }
        if (!iAllSelected[table]) {
            iAllSelected[table] = 0;
        }
        iAllSelected[table] = 0;
    }

    //deselect all rows
    function DeselectAllRows(sCheckboxName) {
        var iRow = 0;
        oCB = GetCB(sCheckboxName, iRow);
        while (oCB) {
            oCB.checked = false;
            SetTRStyle(GetTR(oCB), false);
            iRow = iRow + 1;
            oCB = GetCB(sCheckboxName, iRow);
        }
    }

    function getSelectedRowsValue(dataGridData) {
        dataGridDataOut = '';
        cbs = document.getElementsByName('dg0_id');
        for (i = 0; i < cbs.length; i ++) {
            if (cbs[i].checked) {
                return dataGridData.replace("{id}", cbs[i].value);
            }
        }
        if (dataGridData.indexOf("{id}") != -1 ) {
            alert('Prosz� wybra� pozycj� na li�cie.');
            return null;
        }
        return dataGridData;
    }
    function OnDataGridClick(action, newWindow) {
        action = getSelectedRowsValue(action);
        if (action == null) {
            return;
        }
        if (newWindow) {
            openWindow(action, 800, 600);
        }
        else {
            if (action.indexOf("{id}") != -1 || document.forms.count == 0) {
                document.location.href = action;
            }
            else {
                document.forms[0].submit();
            }
        }
    }

    function deleteConfirm(sCheckboxName, url, dataGridDelMsg) {
        var iRow = 0;
        var countRow = 0;
        oCB = GetCB(sCheckboxName, iRow);
        while (oCB) {
            if(oCB.checked == true) {
                countRow++;
            }
            iRow++;
            oCB = GetCB(sCheckboxName, iRow);
        }
        if(countRow > 0) {
            if(confirm(dataGridDelMsg + countRow)) {
                document.getElementById('dataGrid').setAttribute('action', url);
                document.getElementById('dataGrid').submit();
            }
        }
        else {
            alert('No rows selected');
        }
    }