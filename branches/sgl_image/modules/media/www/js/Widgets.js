//  Various widgets
//  Requires Prototype framework

// TODO: When there will be several widgets, implement loading method inspired from scriptaculous
 
//  Constructor
var Table = Class.create();

//  Options
Table.DefaultOptions = {
  //  Table header index to sort by
  initialSortedCol: 1,
  secondSortCol: 1,
  secondSortColOrder: 'ASC', // ASC OR DESC
  // Look and feel
  showSortedCol: true,
  sortedColClassName: 'sortedColumn',
  alternateRows: true,
  alternateRowClassName: 'alternateRow'
  
};
Table.prototype = {
  widgetName: "Table widget",
  version: "1.0",
  initialize: function(id, options) {
    //  Get table
    if(typeof id == 'undefined') {
      alert('Table widget object needs an id');
    } else {
      this.id = id;
      this.tableElt = $(id);
    }

    // This code is necessary for browsers that don't reflect the DOM constants
    // (like IE).
    if (document.ELEMENT_NODE == null) {
      document.ELEMENT_NODE = 1;
      document.TEXT_NODE = 3;
    }

    // Retrieve options
    this.options = Object.extend(Object.extend({},Table.DefaultOptions), options || {});

    // Prepare table TODO still something to fix, should be able to remove some table cols
    this.prepareTable();
  },

  //-----------------------------------------------------------------------------
  // DEBUG METHOD : renders some debug info at bottom of <body>
  //-----------------------------------------------------------------------------

  debug: function() {
    var ret = '<div style="background:#fff;margin:20px;padding:4px;text-align:left;">';
    ret += '<h2 style="margin:10px 0;">Debug info</h2>';
    ret += '<pre>';
    // Add info here
    ret += '<p>Widget : ' +this.widgetName +'</p>';
    ret += '<p>Version : ' +this.version +'</p>';
    ret += '<hr />';
    ret += '<p>Table id : ' +this.id +'</p>';
    ret += '<p>Table element found? : ' +((this.tableElt) ? true : false) +'</p>';
    ret += '<hr />';
    //  Options
    ret += '<p>Options : <br />';
    for(var i in this.options) {
      ret += '  ' +i +' : ' +this.options[i] +'<br />';
    }
    ret += '</p>';
    ret += '</div>';
    var body = document.getElementsByTagName('body')[0];
    if(!document.all) {
      //  IE doesn't manage this !!
      new Insertion.Bottom(body, ret);
    }
  },

  //-----------------------------------------------------------------------------
  // Prepare table to add callback on events, remove some columns, ...
  //-----------------------------------------------------------------------------

  prepareTable: function() {
    this.header = this.tableElt.tHead;
    this.footer = this.tableElt.tFoot;
    this.body = this.tableElt.tBodies[0];

    // Add event observers
    var rows = this.body.rows;
    rows = $A(rows);
    rows.each( function(row) {
      Event.observe(row, 'mouseover', function(event){
        row.className += " rowHover";
      });
      Event.observe(row, 'mouseout', function(event){
        row.className = row.className.replace(new RegExp("[\s]*rowHover\\b"), "");
      });
      Event.observe(row, 'click', function(event){
        if(row.className.match(new RegExp("[\s]*selectedRow\\b"))) {
          row.className = row.className.replace(new RegExp("[\s]*selectedRow\\b"), "");
        } else {
          row.className += " selectedRow";
        }
        // Check the box
        var aInputs = document.getElementsByClassName('checkbox', row);
        aInputs = $A(aInputs);
        aInputs.each(function(checkbox) {
          var trigger = Event.element(event);
          if(trigger.type != 'checkbox') {
            if(row.className.match(new RegExp("[\s]*selectedRow\\b"))) {
              checkbox.checked = true;
            } else {
              checkbox.checked = false;
            }
          }
          // Add multiple select on shift key press only
          if(event.shiftKey) {alert('shift key pressed')};
        });
      });
    });
  },

  //-----------------------------------------------------------------------------
  // Sort tables row by header indexes (columns).
  //-----------------------------------------------------------------------------

  sortTable: function(col, rev) {

  // Get the table or table section to sort.
  var tableElt = this.body;
  
  // The first time this function is called for a given table,
  // set up an array of reverse sort flags.
  if (tableElt.reverseSort == null) {
    tableElt.reverseSort = new Array();
    // Also, set the initially sorted column.
    tableElt.lastSortedColumn = this.options.initialSortedCol;
  }
  
  // If this column has not been sorted before, set the initial sort direction.
  if (tableElt.reverseSort[col] == null) {
    tableElt.reverseSort[col] = rev;
  }
  // If this column was the last one sorted, reverse its sort direction.
  if (col == tableElt.lastSortedColumn) {
    tableElt.reverseSort[col] = !tableElt.reverseSort[col];
  }
  // Remember this column as the last one sorted.
  tableElt.lastSortedColumn = col;
  
  // Set the table display style to "none" - necessary for Netscape 6 
  // browsers.
  var oldDisplay = tableElt.style.display;
  tableElt.style.display = "none";
  
  // Sort the rows based on the content of the specified column using a
  // selection sort.
  
  var tmpEl;
  var i, j;
  var minVal, minIdx;
  var testVal;
  var cmp;
  
  for(i = 0; i < tableElt.rows.length - 1; i++) {
    // Assume the current row has the minimum value.
    minIdx = i;
    minVal = this.getTextValue(tableElt.rows[i].cells[col]);

    // Search the rows that follow the current one for a smaller value.
    for (j = i + 1; j < tableElt.rows.length; j++) {
      testVal = this.getTextValue(tableElt.rows[j].cells[col]);
      cmp = this.compareValues(minVal, testVal);
      // Negate the comparison result if the reverse sort flag is set.
      if (tableElt.reverseSort[col]) {
        cmp = -cmp;
      }
      // Sort by the second column if those values are equal.
      if (cmp == 0 && col != this.options.secondSortCol) {
        cmp = this.compareValues(this.getTextValue(tableElt.rows[minIdx].cells[this.options.secondSortCol]),
                   this.getTextValue(tableElt.rows[j].cells[this.options.secondSortCol]));
        if(this.options.secondSortColOrder == 'DESC') {
          cmp = -cmp;
        }
      }
      // If this row has a smaller value than the current minimum, remember its
      // position and update the current minimum value.
      if (cmp > 0) {
        minIdx = j;
        minVal = testVal;
      }
    }
    
    // Ok we have the row with the smallest value. Remove it from the
    // table and insert it before the current row.
    if (minIdx > i) {
      tmpEl = tableElt.removeChild(tableElt.rows[minIdx]);
      tableElt.insertBefore(tmpEl, tableElt.rows[i]);
    }
  }
  
  // Apply some css rules depending on options params.
  this.makePretty(tableElt, col);

  // Restore the table's display style.
  tableElt.style.display = oldDisplay;

  // In case a link was clicked, to disable it
  return false;
  },

  //-----------------------------------------------------------------------------
  // Functions to get and compare values during a sort.
  //-----------------------------------------------------------------------------
  getTextValue: function(el) {

    var i;
    var s;

    // Find and concatenate the values of all text nodes contained within the
    // element.
    s = "";
    for (i = 0; i < el.childNodes.length; i++) {
      if (el.childNodes[i].nodeType == document.TEXT_NODE) {
        s += el.childNodes[i].nodeValue;
      } else if (el.childNodes[i].nodeType == document.ELEMENT_NODE &&
           el.childNodes[i].tagName == "BR") {
        s += " ";
      } else {
        // Use recursion to get text within sub-elements.
        s += this.getTextValue(el.childNodes[i]);
      }
    }
    return this.normalizeString(s);
  },
  
  compareValues: function(v1, v2) {

    var f1, f2;

    // If the values are numeric, convert them to floats.
    f1 = parseFloat(v1);
    f2 = parseFloat(v2);
    if (!isNaN(f1) && !isNaN(f2)) {
      v1 = f1;
      v2 = f2;
    }

    // Compare the two values.
    if (v1 == v2) {
      return 0;
    }
    if (v1 > v2) {
      return 1;
    }
    return -1;
  },
  
  // TODO: This could be replaced by String.strip(), and another addition
  // to replace multiple whites spaces
  // Regular expressions for normalizing white space.
  whiteSpaceEnds: new RegExp("^\\s*|\\s*$", "g"),
  whiteSpaceMult: new RegExp("\\s\\s+", "g"),
  
  normalizeString: function(s) {

    s = s.replace(new RegExp("\\s\\s+", "g"), " ");  // Collapse any multiple whites space.
    s = s.replace(new RegExp("^\\s*|\\s*$", "g"), "");   // Remove leading or trailing white space.

    return s;
  },

  //-----------------------------------------------------------------------------
  // Function to update the table appearance after a sort.
  //-----------------------------------------------------------------------------

  makePretty: function(tableElt, col) {

    var i, j;
    var rowEl, cellEl;

    // Set style classes on each row to alternate their appearance.
    for (i = 0; i < tableElt.rows.length; i++) {
      rowEl = tableElt.rows[i];
      if(this.options.alternateRows) {
        rowEl.className = rowEl.className.replace(new RegExp("[\s]*" +this.options.alternateRowClassName +"\\b"), "");
        if (i % 2 == 0) {
          rowEl.className += " " + this.options.alternateRowClassName;
          rowEl.className = this.normalizeString(rowEl.className);
        }
      }
      if(this.options.showSortedCol) {
        // Set style classes on each column (other than the name column) to
        // highlight the one that was sorted.
        for (j = 1; j < tableElt.rows[i].cells.length; j++) {
          cellEl = rowEl.cells[j];
          cellEl.className = cellEl.className.replace(new RegExp("[\s]*" +this.options.sortedColClassName +"\\b"), "");
          if (j == col) {
            cellEl.className += " " + this.options.sortedColClassName;
            cellEl.className = this.normalizeString(cellEl.className);
          }
        }
    }
    }
  
    // Find the table header and highlight the column that was sorted.
    var el = tableElt.parentNode.tHead;
    rowEl = el.rows[el.rows.length - 1];
    // Set style classes for each column as above.
    for (i = 0; i < rowEl.cells.length; i++) {
      cellEl = rowEl.cells[i];
      cellEl.className = cellEl.className.replace(new RegExp("[\s]*" +this.options.sortedColClassName +"\\b"), "");
      cellEl.className = cellEl.className.replace(new RegExp("[\s]*" +"sortedDesc|sortedAsc" +"\\b"), "");
      // Highlight the header of the sorted column.
      if (i == col) {
        if(this.options.showSortedCol) {
          cellEl.className += " " + this.options.sortedColClassName;
        }
        (tableElt.reverseSort[col])
          ? cellEl.className += " sortedDesc"
          : cellEl.className += " sortedAsc";
        cellEl.className = this.normalizeString(cellEl.className);
      }
    }
  },
  // Unused currently
  toggleSelectedRow: function(row) {
    if(this.previousSelectedRow != null) {
      this.previousSelectedRow.className = "";
    }
    row.className = "selectedRow";
    this.previousSelectedRow = row;
  }
};


// To fix
//-----------------------
// PREPARE TABLE
//-----------------------
//  prepareTable: function() {
//    this.header = this.tableElt.tHead;
//    this.footer = this.tableElt.tFoot;
//    this.rows = this.tableElt.rows;
//    // In thead
//    this.rows.colIndexToAdd = 0;
//    
//    for(var i = 0; i < this.header.rows[0].cells.length; i++) {
//      headerCell = this.header.rows[0].cells[i];
//      // check for colspan
//      for(var j in headerCell.attributes) {
//        if(headerCell.attributes[j].nodeName == 'colspan') {
//          this.rows.colIndexToAdd += headerCell.attributes[j].nodeValue - 1;
//        }
//      }
//      if(headerCell.className.match(new RegExp('jsHide'))) {
//        headerCell.parentNode.removeChild(headerCell);
//      }
//    }
//
//    // In tbody
//    for(var i = 1; i < this.rows.length; i++) {
//      // Skip   ^ first row which is header
//      var bodyRow = this.rows[i];
//      // Execute on rows
//      var oldBodyCellsLength = bodyRow.cells.length;
//      for(var j = 0; j < oldBodyCellsLength; j++) {
//        
//        var rowCell = bodyRow.cells[j];
//        //alert('j = ' +j +' cellule = ' +rowCell.innerHTML +' longueur = ' +bodyRow.cells.length);
//        if(rowCell.className.match(new RegExp('jsHide'))) {
//          rowCell.parentNode.removeChild(rowCell);
//          oldBodyCellsLength -= 1;
//        }
//      }
//    }
//  }