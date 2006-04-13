<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Varico ...na wszystko mamy program/one stop source for software
//http://www.varico.com ... od 1989/since 1989
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

// KK 26938 poprawa standardu kodowania zgodnie z PEAR
define('SGL_DATAGRID_DELTA_LINKS_PAGE', 1);


/**
 * SGL_DataGridDataSource
 * For selecting and preparing manually added data
 * @package SGL_DataGridDataSource
 * @author Varico
 * @copyright Copyright (c) 2004, Varico, Poznan, Poland
 * @version $Id
 * @access public
 **/
class SGL_DataGridDataSource {

    var $_pagedData;
    var $_itemsData;
    var $_sortElement;

    /**
     * SGL_DataGridDataSource::addRow()
     * Add given $rowArray into dataGrid data collection: _itemsData array
     * @param array $rowArray
     * @access public
     * @return void
     **/
    function addRow($rowArray)
    {
        $this->_itemsData[] = $rowArray;
    }

    /**
     * SGL_DataGridDataSource::_compareDataSource()
     * Compare two elements, used in usort() function
     * @param mixed $a
     * @param mixed $b
     * @access public
     * @return void
     **/
    function _compareDataSource($a, $b)  // KK 26941 funkcja przeniesiona jako metoda klasy
    {
        return strnatcasecmp($a[$this->_sortElement], $b[$this->_sortElement]);
    }


    /**
     * SGL_DataGridDataSource::setSort()
     * Sort dataGrid data in _itemsData array
     * @param array $sortsArray - given from dataGrid with sort values for each sortable column
     * @access public
     * @return void
     **/
    function setSort($sortsArray)
    {
        //no sort
        $sortByElement = "";
        $sortOrder = "";

        //to find sort
        foreach($sortsArray as $key => $sort){
            if ($sort != "") {
                $sortOrder = $sort;
                $sortByElement = $key;
            }
        }

        //if sort finded - sort data
        if ($sortByElement != "") {
            $_element = $sortByElement;
	    $this->_sortElement = $_element;
            usort($this->_itemsData, array("SGL_DataGridDataSource","_compareDataSource"));
            //set sort order
            if ($sortOrder == 'DESC') {
                //reverse data
                $this->_itemsData = array_reverse($this->_itemsData);
            }
        }
    }

    /**
     * SGL_DataGridDataSource::setFilter()
     * Filter dataGrid data in _itemsData array
     * @param array $filtersArray - given from dataGrid with filter values for each filterable column
     * @access public
     * @return void
     **/
     function setFilter($filtersArray)
     {
        $numberOfFilters = count($filtersArray);
        $newItemData = array();
            foreach ($this->_itemsData as $key => $data) { //for each row in data array
                $numberOfFitFilters = 0; //number of matching filters
                foreach($filtersArray as $keyFilter => $filter) { //for each given filter
                    //for "_from" and "_to" date filters
                    if ((strstr($keyFilter, '__from__')) || (strstr($keyFilter, '__to__'))) {
                        if (strstr($keyFilter, '__from__') &&
                             (($data[str_replace('__from__','',$keyFilter)] >= $filter )
                             || $filter == "")) {
                                    $numberOfFitFilters++; //filter match
                        }
                        if (strstr($keyFilter, '__to__') &&
                             (($data[str_replace('__to__','',$keyFilter)] <= $filter )
                             || $filter == "")) {
                                    $numberOfFitFilters++; //filter match
                        }
                    } elseif (($filter == "")||(strstr(strtoupper($data[$keyFilter]), strtoupper($filter)))) {
                            $numberOfFitFilters++; //filter match
                    }
                }

                if ($numberOfFitFilters == $numberOfFilters) { //if all filters match in row
                    $newItemData[] = $data; //add this row to new data array
                }
            }
        $this->_itemsData = $newItemData; //create new filtered data array
    }

    /**
     * SGL_DataGridDataSource::getPageLinks()
     * Get links for current page
     * @access public
     * @return actual page links
     **/
    function getPageLinks()
    {
        return $this->_pagedData['links'];
    }

    /**
     * SGL_DataGridDataSource::getNumberOfPages()
     * Get number of total pages
     * @access public
     * @return actual number of pages
     **/
    function getNumberOfPages()
    {
        return $this->_pagedData['page_numbers']['total'];
    }

    /**
     * SGL_DataGridDataSource::getNumberOfRows()
     * Get number of all rows i data collection
     * @access public
     * @return total number of rows
     **/
    function getNumberOfAllRows()
    {
        return count($this->_itemsData);
    }

    /**
     * SGL_DataGridDataSource::getSummarysOfPage()
     * Get page sum for each sumable column
     * @param array $columnsArray
     * @param mixed $sumData - data for summing
     * @access public
     * @return summary of actual page for $columnsArray
     **/
    function getSummarysOfPage($columnsArray, $sumData = false)
    {
        //if sumData is not given by programmer, function will sum current page
        if (!($sumData)) {
            $dataToSum = $this->_pagedData['data'];
        } else {   //else function will export given data
            if (is_array($sumData)) {
                $dataToSum = $sumData;
            }
            else {
                $dataToSum = array();
            }
        }

        $summarys = array();
        if ((is_array($dataToSum)) && (count($dataToSum) > 0)) {
            foreach ($columnsArray as $keyArray => $column) {
                if ($column->sumTot) {
                    if (!(is_array($column->dbName))) {
                        $summaryOfPage = 0;
                        foreach ($dataToSum as $key => $data) {
                            //for each cell from row in actual column
                            //add its value to global sum for column
                            $summaryOfPage += $data[$column->dbName];
                        }

                        //set summary of actual column
                        $summarys[$column->dbName] = number_format($summaryOfPage, 2, '.', '');
                    }
                }
		
		if ($column->avgTot) {
                    if (!(is_array($column->dbName))) {
                        $summaryOfPage = 0;
			$counter = 0;
                        foreach ($dataToSum as $key => $data) {
			    $counter++;
                            //for each cell from row in actual column
                            //add its value to global sum for column
                            $summaryOfPage += $data[$column->dbName];
                        }

                        //set summary of actual column
			if ($counter>0)
                            $summarys[$column->dbName] = number_format(($summaryOfPage/$counter), 2, '.', '');
			else
			    $summarys[$column->dbName] = number_format($summaryOfPage, 2, '.', '');
                    }
                }
            }
        }

        return $summarys;
    }

    /**
     * DataGridSQLDataSource::getSummarysTotal()
     * Get total sum for each sumable column
     * @param array $columnsArray
     * @access public
     * @return total summary for $columnsArray
     **/
    function getSummarysTotal($columnsArray)
    {
        return $this->getSummarysOfPage($columnsArray, $this->_itemsData);
    }

    /**
     * SGL_DataGridDataSource::getActualPage()
     * Get page to display in dataGrid with given id in row
     * @param numeric $setIDLoc
     * @param numeric $perPageLoc
     * @access public
     * @return page to display
     **/
    function getActualPage($setIDLoc, $perPageLoc)
    {
        if (($setIDLoc != "") && (is_numeric($setIDLoc))) {
            $searchValue = $setIDLoc;
            $positionInArray = 0;
            $valueFound = false;
            foreach ($this->_itemsData as $keyArray => $newArray) {
                $positionInArray++;
                if (in_array($searchValue, $newArray)) {
                    $valueFound = true;
                    break;
                }
            }

            if (($valueFound) && ($perPageLoc >= 1)) {
                return round($positionInArray/$perPageLoc);
            }
        }

        return "";
    }

    /**
     * SGL_DataGridDataSource::fill()
     * Fill current page with data in dataGrid
     * @param string $pageID - dataGrid id to different pages for more than one dataGrid in form
     * @param numeric $perPage - indicate how many rows can be displayed on single page
     * @param numeric $actualPage - if given set page to display in dataGrid
     * @access public
     * @return actual data for dataGrid
     **/
    function fill($pageID, $perPage, $actualPage = "")
    {
        if ($actualPage != "") {
            $_GET['pageID_' . $pageID] = $actualPage;
        }

        $pagerOptions = array(
            'mode'      => 'Sliding',
            'delta'     => SGL_DATAGRID_DELTA_LINKS_PAGE,
            'perPage'   => $perPage,
            'urlVar'    => 'pageID_' . $pageID,
            'itemData'  => $this->_itemsData
        );

        require_once 'Pager/Pager.php';

        //create pager instance
        $pager = Pager::factory($pagerOptions);

        //set data collection for current page
        $this->_pagedData['data'] = $pager->getPageData();
        $this->_pagedData['links'] = $pager->links;
        $this->_pagedData['page_numbers']['total'] = $pager->numPages();
        $this->_pagedData['totalItems'] = $pager->numItems();
        if (($perPage <= 0) || (!is_numeric($perPage))) {
            $this->_pagedData['data'] = array();
            $this->_pagedData['links'] = "";
            $this->_pagedData['page_numbers']['total'] = "";
        }
        return $this->_pagedData['data'];
    }
    /** AM get column real name 28526
     * SGL_DataGridDataSource::getColumnName($key)
     * @param string $key  - dbName for column
     * @access private
     * @return string - name for column
     **/
    function _getColumnName($key)
    {
        $name = '';
        if (is_array($this->columns)) {
            foreach ($this->columns as $column) {
                if ($key == $column->dbName) {
                    $name = $column->name;
                    break;
                }
            }
        }
        if ($name == '') {
            $name = SGL_string::translate($key);
        }
        return $name;
    }
    /**
     * SGL_DataGridDataSource::exportPageToExcel()
     * Allow to export data from current page or data given by second parameter to Excel file
     * @param string $fileName
     * @param mixed $exportData - data to export
     * @access public
     * @return void
     **/
    function exportPageToExcel($fileName = 'dataGridPageXLS.xls', $exportData = null)
    {
        //if exportData is not given by programmer, function will export current page
        if (is_null($exportData)) {
            $dataToExport = $this->_pagedData['data'];
        } else {     //else function will export given data
            if (is_array($exportData)) {
                $dataToExport = $exportData;
            }
            else {
                $dataToExport = array();
            }
        }

        require_once 'Spreadsheet/Excel/Writer.php';
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);

        //send HTML headers
        $workbook->send($fileName);
        $worksheet =& $workbook->addWorksheet('DataGrid Worksheet');

        $worksheet->setInputEncoding($GLOBALS['_SGL']['CHARSET']);
        $format_bold =& $workbook->addFormat();
        $format_bold->setBold();

        //column and row offset from which function will be wrighting in excel document
        $columnOffset = 1;
        $rowOffset = 2;
        $columnNotAdded = true; //to wright column names in excel document only once
        $actualRow = $rowOffset;

        if ((is_array($dataToExport)) && (count($dataToExport) > 0)) {
            foreach ($dataToExport as $key => $row) {
                $actualColumn = $columnOffset;
                foreach ($row as $keyRow => $data) {
                    if ($columnNotAdded) { //if columns names haven't been written yet
                        $worksheet->write($actualRow, $actualColumn, $this->_getColumnName($keyRow), $format_bold);
                    }
                    //write actual cell in current column
                    $worksheet->write($actualRow+1 ,$actualColumn, _enc($data));
                    $actualColumn++;
                }
                $columnNotAdded = false;
                $actualRow++;
            }
        }
        //close document
        $workbook->close();
    }

    /**
     * SGL_DataGridDataSource::exportTotalToExcel()
     * Allow to export all data collection _itemsData to Excel file
     * @param string $fileName
     * @access public
     * @return void
     **/
    function exportTotalToExcel($fileName = 'dataGridTotalXLS.xls')
    {
        $this->exportPageToExcel($fileName, $this->_itemsData);
    }

    /**
     * SGL_DataGridDataSource::exportPageToWord()
     * Allow to export data from current page or data given by second parameter to Word file
     * @param string $fileName
     * @param mixed $exportData - data to export
     * @access public
     * @return void
     **/
    function exportPageToWord($fileName = 'dataGridPageDOC.doc', $exportData = false)
    {
        //if exportData is not given by programmer, function will export current page
        if (!($exportData)) {
            $dataToExport = $this->_pagedData['data'];
        } else { //else function will export given data
            if (is_array($exportData)) {
                $dataToExport = $exportData;
            }
            else {
                $dataToExport = array();
            }
        }
        $wordSize = 14; //for equal columns in document
        $titleSep = str_repeat("=", 15);
        $wordSep  = 0;

        //send HTML headers
        header("MIME-Version: 1.0");
        header("Content-Type: application/msword");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: no-cache"); //public

        //header("Content-transfer-encoding: base64");
        echo '<html><head><meta http-equiv="content-type" content="application/msword; charset=Windows-1250"></head>'.
             '<body><b><font color="red">Dane z dataGrid</font></b><br/><br/><table>';

        //to decode string use this
        //echo iconv("ISO-8859-2","UTF-8","Zaï¿½ï¿½ï¿½ gï¿½lï¿½ jaï¿½ï¿½");
        if ((is_array($dataToExport)) && (count($dataToExport) > 0)) {
            $columnNotAdded = true; //to wright column names in word document only once
            foreach ($dataToExport as $key => $row) {
                echo '<tr>';
                foreach ($row as $keyRow => $data) {
                    if ($columnNotAdded) { //if columns names haven't been written yet
                        echo '<th>' .  _isoToWin($this->_getColumnName($keyRow)) . '</th>';
                    } else { //write actual data word in current line and position
                        echo '<td>' .  _isoToWin($data) . '</td>';
                    }
                }
                echo '</tr>';
                $columnNotAdded = false;
            }
        }
        echo '</table></body></html>';
        //close document
        Exit;
    }

    /**
     * SGL_DataGridDataSource::exportTotalToWord()
     * Allow to export all data collection _itemsData to Word file
     * @param string $fileName
     * @access public
     * @return void
     **/
    function exportTotalToWord($fileName = 'dataGridPageDOC.doc')
    {
        $this->exportPageToWord($fileName, $this->_itemsData);
    }
}

/**
 * This function
 * @param
 * @return
**/
function _enc($text) {
    $text = str_replace('&Oacute;', 'Ó', $text);
    $text = str_replace('&oacute;', 'ó', $text);
    return $text;
}
?>