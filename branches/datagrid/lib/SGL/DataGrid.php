<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Varico ...na wszystko mamy program/one stop source for software
//http://www.varico.com ... od 1989/since 1989
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


// KK 26940 count of rows comes from aPrefs
define ('SGL_DATAGRID_ALL_ROWS_IN_SELECT', 10000);	// KK 26938 standardy kodowania PEAR

require_once 'DataGrid/DataGridColumn.php';

/**
 * SGL_DataGrid
 * For browsing tables
 * @package DataGrid
 * @author Varico
 * @copyright Copyright (c) 2004, Varico, Poznan, Poland
 * @version $Id: DataGrid.php,v 1.9 2005/10/20 10:47:19 krzysztofk Exp $
 * @access public
 **/
class SGL_DataGrid {

    var $dataGridID;
    var $dataGridName;
    var $columns   = array();
    var $results   = array();
    var $filters   = array();
    var $sorts     = array();
    var $sums      = array();
    var $sumsTotal = array();
    var $pageLinks;
    var $pageTotal;
    var $perPage;
    var $pageID;
    var $setID;
    var $export;
    var $allRows;
    var $perPageOptions = array();
    var $filterError = false;
    var $dataGridHeader;
    var $dataGridButton = array();
    var $dataGridButtonDelete = array();
    var $emptyTitle = '';
    var $showFilters = false;
    var $dataGridDeleteMessage = '';
    var $defaultPerPage;

    /**
     * SGL_DataGrid::SGL_DataGrid()
     * Initialize dataGrid object
     * @param string $Id - unique dataGrid id
     * @access public
     * @return void
     **/
    function SGL_DataGrid($Id = '0', $name = '', $emptyTitle = '', $defaultPerPage = 0)
    {	// KK 26938 standardy kodowania PEAR
        $this->dataGridID     = $Id;
        $this->emptyTitle     = $emptyTitle;
        $this->defaultPerPage = $defaultPerPage;
        if ($name) {
            $this->dataGridName = $name;
        }
        else {
            $this->dataGridName = 'dg' . $this->dataGridID . '_';
        }
    }


    /**
     * DataGrid::addColumn()
     * Add single column object to columns collection (array)
     * @param string $type - column type
     * @param string $name - column name
     * @param mixed $dbName - column name in database
     * @param boolean $sortable - indicate if column may be sorted
     * @param boolean $filterable - indicate if column may be filtered
     * @param boolean $sumable - indicate if column may be summed per page
     * @param boolean $sumTotalable - indicate if column may be summed per total
     * @param mixed $filterType - specify filter type
     * @param array $valueTransformArray - for transform selected data from database
     * @access public
     * @return void
     **/
    function addColumn($params=array())
    {
        /*TP it is not returned by reference because otherwise there is problem returning copy
        * of variable $column (it return copy instead of reference and in PHP cannot be returned
        * reference to object)
        * */
        $column = new SGL_DataGridColumn($params);
        /*$column->type          = $type;
        $column->name          = $name;
        $column->dbName        = $dbName;
        $column->sort          = $sortable;
        $column->filter        = $filterable;
        $column->sum           = $sumable;
        $column->sumTot        = $sumTotalable;*/

        $this->columns[] = $column;

        return $column;
    }


    /**
     * DataGrid::filterValidate()
     * Get filter value from form and if set put it into specified dataGrid field
     * or put empty section if not set
     * @param object $column - one column from dataGrid
     * @param object $inReq - sgl_http_request object
     * @access public
     * @return void
     **/
    function filterValidate(&$column, &$inReq) {
        //for "_from" and "_to" date filters
        if (($column->type == 'date') || ($column->type == 'integer') || ($column->type == 'real')) {
            $columnTempFrom = $column->dbName . '__from__';
            $columnTempTo   = $column->dbName . '__to__';

            //set names for filter -GET variables
            $filterSetNameFrom = $this->dataGridName . $column->dbName . '__from__';
            $filterSetNameTo   = $this->dataGridName . $column->dbName . '__to__';

            //put filter -GET variables or empty section
            //into the specified dataGrid fields
            if ($inReq->get($filterSetNameFrom)) {
                $this->filters[$columnTempFrom] = $inReq->get($filterSetNameFrom);
            } elseif ($column->filter) {
                    $this->filters[$columnTempFrom] = "";
            }

            if ($inReq->get($filterSetNameTo)) {
                $this->filters[$columnTempTo] = $inReq->get($filterSetNameTo);
            } elseif ($column->filter) {
                    $this->filters[$columnTempTo] = "";
            }

            if ($column->type == 'date') {
                if ((!empty($this->filters[$columnTempFrom])) && (is_null(SGL_DataGrid::formatDate2DB($this->filters[$columnTempFrom])))) {
                    $column->cError = "<br><b> incorrect date format !!</b>";
                    $this->filterError  = true;
                } elseif (!empty($this->filters[$columnTempFrom])) {
                    $this->filters[$columnTempFrom] = SGL_DataGrid::formatDate2DB($this->filters[$columnTempFrom]);
                }

                if ((!empty($this->filters[$columnTempTo])) && (is_null(SGL_DataGrid::formatDate2DB($this->filters[$columnTempTo])))) {
                    $column->cError = "<br><b> incorrect date format !!</b>";
                    $this->filterError  = true;
                } elseif (!empty($this->filters[$columnTempTo])) {  // KK 26938 standardy kodowania PEAR
                    $this->filters[$columnTempTo] = SGL_DataGrid::formatDate2DB($this->filters[$columnTempTo]);
                }
            }

            if (($column->type == 'integer') || ($column->type == 'real')) {
                if ((!empty($this->filters[$columnTempFrom])) && (!is_numeric($this->filters[$columnTempFrom]))) {
                    $column->cError = "<br><b> incorrect numeric format !!</b>";
                    $this->filterError = true;
                } elseif (!empty($this->filters[$columnTempFrom])) {
                    $this->filters[$columnTempFrom] = $this->filters[$columnTempFrom];
                }

                if ((!empty($this->filters[$columnTempTo])) && (!is_numeric($this->filters[$columnTempTo]))) {
                    $column->cError = "<br><b> incorrect numeric format !!</b>";
                    $this->filterError = true;
                } elseif (!empty($this->filters[$columnTempTo])) {
                    $this->filters[$columnTempTo] = $this->filters[$columnTempTo];
                }
            }
        } else { //for text and select filters
            $columnTemp = $column->dbName;

            //set names for filter -GET variables
            $filterSetName = $this->dataGridName . $column->dbName;

            //put filter -GET variables or empty section
            //into the specified dataGrid fields
            if ($inReq->get($filterSetName)) {
                $this->filters[$columnTemp] = $inReq->get($filterSetName);
            } elseif ($column->filter) {
                    $this->filters[$columnTemp] = "";
            }
        }
    }

    /**
     * DataGrid::sortValidate()
     * Get sort value from form and if set put it into specified dataGrid field
     * @param object $column - one column from dataGrid
     * @param object $inReq - sgl_http_request object
     * @return void
     **/
    function sortValidate(&$column, &$inReq)
    {
        //set names for sort -GET variables
        //put sort -GET variables
        //into the specified dataGrid fields
        $columnTemp = $column->dbName;
        $sortSetName = $this->dataGridName . 'sort_' . $column->dbName;
        if ($inReq->get($sortSetName)) {
            $this->sorts[$columnTemp] = $inReq->get($sortSetName);
        }
    }

    /**
     * DataGrid::pageValidate()
     * Get page values from form and if set put it into specified dataGrid field
     * @param object $inReq - sgl_http_request object
     * @access public
     * @return void
     **/
    function pageValidate(&$inReq)
    {
        //set perPage, pageID and setID -GET names, check it,
        //and if they are set put theirs values into the dataGrid fileds
        $perPageSetName = $this->dataGridName . 'perPage';
        if ($this->defaultPerPage>0)
            $this->perPage = ($inReq->get($perPageSetName)) ? $inReq->get($perPageSetName) : $this->defaultPerPage;
        else
            $this->perPage = ($inReq->get($perPageSetName)) ? $inReq->get($perPageSetName) : $_SESSION['aPrefs']['resPerPage'];

        if (!is_numeric($this->perPage)) {
            $this->perPage = 0;
        }

        //for specify witch page number display
        $setIDSetName = 'setID_' . $this->dataGridID;
        $this->setID = ($inReq->get($setIDSetName)) ? $inReq->get($setIDSetName) : "";
        if ($inReq->get($setIDSetName)) {
            $_GET[$setIDSetName]  = "";
            $_POST[$setIDSetName] = "";
        }

        //pager  ID for each dataGrid
        $pageIDSetName = 'pageID_' . $this->dataGridID;
        $this->pageID = ($inReq->get($pageIDSetName)) ? $inReq->get($pageIDSetName) : 1;
    }

    /**
     * DataGrid::exportValidate()
     * Get export values from form and if set put it into specified dataGrid field
     * @param object $inReq - sgl_http_request object
     * @access public
     * @return void
     **/
    function exportValidate(&$inReq)
    {
        //export to other documents like DOC, XLS
        //check what type of document dataGrid data should be exported to
        $exportSetName = $this->dataGridName . 'export';
        $this->export = ($inReq->get($exportSetName)) ? $inReq->get($exportSetName) : "";
    }

    /**
     * DataGrid::validate()
     * Prepare and gets values from Form
     * @param object $inReq - sgl_http_request object
     * @access public
     * @return void
     **/
    function validate(&$inReq)
    {
        //sorts and filters Form elements names are created by each
        //sortable or filterable dataGrid column
        $this->filterError = false;
        foreach($this->columns as $key => $column) {
            $this->filterValidate($this->columns[$key], $inReq);
            $this->sortValidate($this->columns[$key], $inReq);
        }
        $this->pageValidate($inReq);
        $this->exportValidate($inReq);
    }

    /**
     * Formats date for the current user
     * @param   string  $sDate  Date in user or DB format  (YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Date formatted for the DB format (YYYY-mm-dd)
     *                   if date is not proper - return null
     */
    function formatDate2DB($sDate) {
        //check if date is in correct format
        if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $sDate)) {
            $aDate = explode("-", $sDate);
            if (checkdate($aDate[1], $aDate[2], $aDate[0])) {
                return date("Y-m-d", mktime (0, 0, 0, $aDate[1], $aDate[2], $aDate[0]));
            }
        } elseif (preg_match("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/", $sDate)) {
            $aDate = explode(".", $sDate);
            if (checkdate($aDate[1], $aDate[0], $aDate[2])) {
                    return date("Y-m-d", mktime (0, 0, 0, $aDate[1], $aDate[0], $aDate[2]));
            }
        } elseif (strcmp($sDate, "now()") == 0) {
            return $sDate;
        }
        return null;
    }
    
    /**
     * Formats datetime for the current user
     * @param   string  $sDateTime  Datetime in user or DB format
     * (YYYY-mm-dd HH:mm:ss or dd.mm.yyyy HH:mm:ss or YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Datetime formatted for the DB format (YYYY-mm-dd)
     * or (YYYY-mm-dd HH:mm:ss) if hours set; if date is not proper - return null
     */
    function formatDateTime2DB($sDateTime) {
        //check if date is in correct format
        $sResult = null;
        $aDateTime = explode("\ ", $sDateTime);
        $sDate = $aDateTime[0];
        $sTime = $aDateTime[1];
        if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $sDate)) {
            $aDate = explode("-", $sDate);
            if (checkdate($aDate[1], $aDate[2], $aDate[0])) {
                $sResult = date("Y-m-d", mktime (0,0,0, $aDate[1], $aDate[2], $aDate[0]));
            }
        } elseif (preg_match("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/", $sDate)) {
            $aDate = explode(".", $sDate);
            if (checkdate($aDate[1], $aDate[0], $aDate[2])) {
                $sResult = date("Y-m-d", mktime (0,0,0, $aDate[1], $aDate[0], $aDate[2]));
            }
        } elseif (strcmp($sDate, "now()") == 0) {
            $sResult = $sDate;
        }
        if ($sTime != "") {
            $sResult .= " ".$sTime;
        }
        return $sResult;
    }
    
    /**
     * DataGrid::setPerPageSelectOptions()
     * Prepare per page select array
     * @param object $dataSource
     * @access public
     * @return void
     **/
    function setPerPageSelectOptions(&$dataSource)
    {
        //Prepare per page select array for display in template
        if ($this->defaultPerPage > 0) {
            $this->perPageOptions[$this->defaultPerPage] = $this->defaultPerPage;
        }
	    $this->perPageOptions[$_SESSION['aPrefs']['resPerPage']] = $_SESSION['aPrefs']['resPerPage']; // KK 26940
        $this->perPageOptions[25] = 25;
        $this->perPageOptions[50] = 50;
        $this->perPageOptions[100] = 100;
        $this->perPageOptions[250] = 250;
        $this->perPageOptions[SGL_DATAGRID_ALL_ROWS_IN_SELECT] = SGL_Output::translate('ALL');

/*        $allRows = $dataSource->getNumberOfRows();
        if (($allRows > 0) && ($allRows <= 25)) {
            $this->perPageOptions[$allRows] = $allRows;
        }
        if (($allRows > 25) && ($allRows <= 50)) {
            $this->perPageOptions[25] = 25;
            $this->perPageOptions[$allRows] = $allRows;
        }
        if (($allRows > 50) && ($allRows <= 100)) {
            $this->perPageOptions[25] = 25;
            $this->perPageOptions[50] = 50;
            $this->perPageOptions[$allRows] = $allRows;
        }
        if (($allRows > 100) && ($allRows <= 250)) {
            $this->perPageOptions[25] = 25;
            $this->perPageOptions[50] = 50;
            $this->perPageOptions[100] = 100;
            $this->perPageOptions[$allRows] = $allRows;
        }
        if ($allRows > 250) {
            $this->perPageOptions[25]  = 25;
            $this->perPageOptions[50]  = 50;
            $this->perPageOptions[100] = 100;
            $this->perPageOptions[250] = 250;
            $this->perPageOptions[$allRows] = $allRows;
        }*/
    }

    /**
     * DataGrid::setEmptyFilters()
     * Input empty section in every filter in dataGrid
     * @param $filtersArray
     * @access public
     * @return empty filter array
     **/
    function setEmptyFilters($filtersArray)
    {
        foreach ($filtersArray as $key => $tempFiltervalue) {
            $filtersArray[$key] = '';
        }

        return $filtersArray;
    }

    function setInitialOrder(&$inputReq, $columnName, $sortType)
    {
        $orderVariable = $inputReq->get($this->dataGridName . 'sort_' . $columnName);
        if (!isset($orderVariable))
            $this->sorts[$columnName] = $sortType;
    }
    
    /**
     * DataGrid::setDataSource()
     * Set sorts, filters, sums, generate data and links results for current page
     * @param object $dataSource - reference to object of data source
     * @access public
     * @return void
     **/
    function setDataSource(&$dataSource)
    {
        //for given as parameter dataSource set specified sorts, filters
       
        $dataSource->setSort($this->sorts);
        if ($this->filterError) {
            $emptyFilters = $this->setEmptyFilters($this->filters);
            $dataSource->setFilter($emptyFilters);
        } else {
            $dataSource->setFilter($this->filters);
        }
        $this->allRows = $dataSource->getNumberOfAllRows();
        
        $actualPage = $dataSource->getActualPage($this->setID, $this->perPage, $this->columns);

        //get data from given source
        $this->allRows = count($dataSource->fill($this->dataGridID, $this->allRows, $actualPage));
        $this->results = $dataSource->fill($this->dataGridID, $this->perPage, $actualPage);
        foreach ($this->columns as $keyColumn => $column) {
            //if transform array given in column
            if ((is_array($column->transform)) && (count($column->transform) > 0)) {
                foreach ($this->results as $keyRow => $row) {
                    foreach ($column->transform as $keyTransform => $transform) {
                        if ($row[$column->dbName] == $keyTransform) {

                            //replace actual value of current row in column selected from database
                            //with given value in transform array
                            $this->results[$keyRow][$column->dbName] = $transform;
                        }
                    }
                }
            }
        }

        $this->setPerPageSelectOptions($dataSource);

        //get page links and number of total pages from filled source
        $this->pageLinks = $dataSource->getPageLinks();
        $this->pageTotal = (int) $dataSource->getNumberOfPages();

        //get page and total summary for dataGrid columns
        if ($this->pageTotal != 1) {
            $this->sums = $dataSource->getSummarysOfPage($this->columns);
        }
        $this->sumsTotal = $dataSource->getSummarysTotal($this->columns);
//echo $dataSource->dataGridQuery.'<br>';
        //if selected - export current page or total data to specified document
        
       $trans = array(' ' => '_',
                       chr(177) => 'a',
                       chr(161) => 'A',
                       chr(230) => 'c',
                       chr(198) => 'C',
                       chr(234) => 'e',
                       chr(202) => 'E',
                       chr(179) => 'l',
                       chr(163) => 'L',
                       chr(241) => 'n',
                       chr(209) => 'N',
                       chr(182) => 's',
                       chr(166) => 'S',
                       chr(243) => 'o',
                       chr(211) => 'O',
                       chr(191) => 'z',
                       chr(175) => 'Z',
                       chr(188) => 'z',
                       chr(172) => 'Z',
                       '.' => '_',
                       ',' => '_',
                       '?' => '_',
                       ':' => '_',
                       ';' => '_',
                       '=' => '_',
                       '+' => '_',
                       '<' => '_',
                       '>' => '_',
                       '|' => '_',
                       '\\' => '_',
                       '/' => '_',
                       '[' => '_',
                       ']' => '_'
                       );
		$fileName=strtr(SGL_String::translate($this->dataGridHeader),$trans);
		$fileName = strtr($fileName,'"','_');
		if (($fileName == '') || ($fileName == '__')) {
            $fileName = $this->dataGridName . $this->export;       
		}
			
        switch($this->export) {
            case 'page_XLS':
                // $fileName = $this->dataGridName . 'page_XLS.xls';
                $dataSource->exportPageToExcel($fileName.'.xls');
                break;

            case 'total_XLS':
                //$fileName = $this->dataGridName . 'total_XLS.xls';
                $dataSource->exportTotalToExcel($fileName.'.xls');
                break;

            case 'page_DOC':
                //$fileName = $this->dataGridName . 'page_DOC.doc';
                $dataSource->exportPageToWord($fileName.'.doc');
                break;

            case 'total_DOC':
                //$fileName = $this->dataGridName . 'total_DOC.doc';
                $dataSource->exportTotalToWord($fileName.'.doc');
                break;

        }
    }

    /**
     * DataGrid::display()
     * Set necessary fileds with data, declared in template for object $output
     * @param object $output - reference to output object
     * @access public
     * @return void
     **/
    function display(&$output)
    {
        //print_r ($this->columns); echo '<br>';
        //set field name for current dataGrid
        $dataGrigSetName = $this->dataGridName;
        //$this->perPageOptions = array_merge(array(2=>2),$this->perPageOptions);
        
        //put the data into specified dataGrid fields and then send them to given output
        $output->dataGridData->$dataGrigSetName->id             = $this->dataGridID;
        $output->dataGridData->$dataGrigSetName->name           = $this->dataGridName;
        $output->dataGridData->$dataGrigSetName->columns        = $this->columns;
        $output->dataGridData->$dataGrigSetName->results        = $this->results;
        $output->dataGridData->$dataGrigSetName->filters        = $this->filters;
        $output->dataGridData->$dataGrigSetName->sorts          = $this->sorts;
        $output->dataGridData->$dataGrigSetName->sums           = $this->sums;
        $output->dataGridData->$dataGrigSetName->sumsTotal      = $this->sumsTotal;
        $output->dataGridData->$dataGrigSetName->links          = $this->pageLinks;
        $output->dataGridData->$dataGrigSetName->total          = $this->pageTotal;
        $output->dataGridData->$dataGrigSetName->perPage        = $this->perPage;
        $output->dataGridData->$dataGrigSetName->pageID         = $this->pageID;
        $output->dataGridData->$dataGrigSetName->setID          = $this->setID;
        $output->dataGridData->$dataGrigSetName->allRows        = $this->allRows;
        $output->dataGridData->$dataGrigSetName->perPageOptions = $this->perPageOptions;
        $output->dataGridData->$dataGrigSetName->dataGridHeader = $this->dataGridHeader;
        $output->dataGridData->$dataGrigSetName->dataGridButton = $this->dataGridButton;
        $output->dataGridData->$dataGrigSetName->dataGridButtonDelete = $this->dataGridButtonDelete;
        // DK messages to datagrid (messages.html - flexy)
        //$output->messageNoRowSelectedPHP = SGL_String::translate('No row selected');
        //$output->messagePositionListPHP = SGL_String::translate('Please select position from the list');
        //$output->messageSelectOneOptionPHP = SGL_String::translate('Please select one option');
        //PS export dataGrid to print
        $output->selfUrl = $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'];
        $output->selfUrlParam = $GLOBALS['HTTP_SERVER_VARS']['QUERY_STRING'];
        //echo '<pre>'; print_r($GLOBALS['_REQUEST']['print']); echo '</pre>';die;
        if(isset($GLOBALS['_REQUEST']['print']) && ($GLOBALS['_REQUEST']['print'] == 1)) {
            $output->masterTemplate = 'blank.html';
            $output->print = true;
        }
        $output->javascriptSrc = array('dataGrid.js', 'overlib/overlib.js', 'dialog.js');
        $output->path = SGL_BASE_URL;
        $output->dataGridData->$dataGrigSetName->emptyTitle= $this->emptyTitle;
        if ($this->dataGridDeleteMessage <> '') {
            $output->dataGridData->$dataGrigSetName->dataGridDeleteMessage = $this->dataGridDeleteMessage;
        } else {
            $output->dataGridData->$dataGrigSetName->dataGridDeleteMessage = SGL_String::translate('Do you want to delete: ');
        }

        $emptyFilters = true;
        foreach ($this->filters as $key=>$value) {
            if ($value !== '')
                $emptyFilters = false;        
        }
        
        if ($emptyFilters) {
            if ($this->allRows > 9)
                $output->dataGridData->$dataGrigSetName->showFilters = true;
            else
                $output->dataGridData->$dataGrigSetName->showFilters = false;
        } else {
            
            $output->dataGridData->$dataGrigSetName->showFilters = true;
        }
        
        if (($this->allRows == 0) && ($this->emptyTitle != '')) { 
            $output->dataGridData->$dataGrigSetName->showDataGrid = false;
            if (!$emptyFilters)
                $output->dataGridData->$dataGrigSetName->showDataGrid = true;
        } else {
            $output->dataGridData->$dataGrigSetName->showDataGrid = true;
        }
        
        $showSummary = false;
        foreach ($this->columns as $column) {
            if ($column->sumTot || $column->sum)
                $showSummary = true;
        }
        
        $output->dataGridData->$dataGrigSetName->showSummary = $showSummary;
        
       /* $dataGridCount = 0;
        foreach ($output->dataGridData as $key=>$datagrid) {
            if (count($datagrid->results) > 0)
                $dataGridCount++;
        }
        if ($dataGridCount == 0) 
            $dataGridCount = 1;
        
        $perPage = ceil(100 / $dataGridCount);
        echo $perPage.' ';
        if ($perPage < 5)
            $perPage = 5;
        foreach ($output->dataGridData as $key=>$datagrid) {
            unset($datagrid->perPageOptions[key($datagrid->perPageOptions)]);
            $datagrid->perPageOptions = array_merge(array($perPage=>$perPage),$datagrid->perPageOptions);

            $output->dataGridData->$key->perPageOptions = $datagrid->perPageOptions;
            $output->dataGridData->$key->results = array_slice($output->dataGridData->$key->results,0,$perPage);
            $output->dataGridData->$key->perPage=0;
        }*/
    }
}

?>
