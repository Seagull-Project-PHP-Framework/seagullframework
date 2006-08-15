<?php
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//Varico ...one stop source for software
//http://www.varico.com ... since 1989
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


// this library depends on PEAR's SQL_Parser and Spreadsheet_Excel_Writer

// KK 26940 count of rows comes from aPrefs
define ('SGL_DATAGRID_ALL_ROWS_IN_SELECT', 10000);

require_once 'Column.php';

/**
 * SGL_DataGrid for browsing tables.
 *
 * @package SGL
 * @author Varico
 * @version $Id: DataGrid.php,v 1.28.2.2 2006/03/20 08:42:33 danielk Exp $
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
    var $hasIdColumn = false;

    /**
     * SGL_DataGrid::SGL_DataGrid()
     * Initialize dataGrid object
     * @param string $Id - unique dataGrid id
     * @access public
     * @return void
     **/
    function SGL_DataGrid($Id = '0', $name = '', $emptyTitle = '',
        $defaultPerPage = 0)
    {
        $this->dataGridID     = $Id;
        $this->emptyTitle     = $emptyTitle;
        $this->defaultPerPage = $defaultPerPage;
        if ($name) {
            $this->dataGridName = $name;
        } else {
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
     * @param boolean $avgable - indicate if column may be averaged per page
     * @param boolean $avgTotalable - indicate if column may be averaged per
                                      total
     * @param mixed $filterType - specify filter type
     * @param array $valueTransformArray - for transform selected data from
                                            database
     * @access public
     * @return void
     **/
    function addColumn($params)
    {
        /*TP it is not returned by reference because otherwise there is problem
        * returning copy of variable $column (it return copy instead of
        * reference and in PHP cannot be returned reference to object)
        */

	    if (isset($params['type'])) {
            $type = $params['type'];
        } else {
            $type = 'id';
        }
	    if (isset($params['name'])) {
            $name = $params['name'];
        } else {
            $name = '';
        }
	    if (isset($params['dbName'])) {
            $dbName = $params['dbName'];
        } else {
            $dbName = '';
        }
	    if (isset($params['sortable'])) {
            $sortable = $params['sortable'];
        } else {
            $sortable = false;
        }
	    if (isset($params['filterable'])) {
            $filterable = $params['filterable'];
        } else {
            $filterable = false;
        }
	    if (isset($params['sumable'])) {
            $sumable = $params['sumable'];
        } else {
            $sumable = false;
        }
	    if (isset($params['avgable'])) {
            $avgable = $params['avgable'];
        } else {
            $avgable = false;
        }
	    if (isset($params['sumTotalable'])) {
            $sumTotalable = $params['sumTotalable'];
        } else {
            $sumTotalable = false;
        }
	    if (isset($params['avgTotalable'])) {
            $avgTotalable = $params['avgTotalable'];
        } else {
            $avgTotalable = false;
        }
	    if (isset($params['align'])) {
            $align = $params['align'];
        } else {
            $align = '';
        }
	    if (isset($params['tooltip'])) {
            $tooltip = $params['tooltip'];
        } else {
            $tooltip = array();
        }

        $column = new SGL_DataGridColumn($type, $name, $dbName, $sortable,
                                     $filterable, $sumable, $sumTotalable,
                                     $avgable, $avgTotalable, $align, $tooltip);

        if ($type == 'id') {
            $this->hasIdColumn = true;
        }
        $this->columns[] = $column;

        return $column;
    }


    /**
     * DataGrid::filterValidate()
     * Get filter value from form and if set put it into specified dataGrid
     * field or put empty section if not set
     * @param object $column - one column from dataGrid
     * @param object $inReq - sgl_http_request object
     * @access public
     * @return void
     **/
    function filterValidate(&$column, &$inReq, $useSession = false)
    {
        $module = $inReq->get('moduleName');
        $manager = $inReq->get('managerName');
        $action = $inReq->get('action');
        if ($inReq->get('resetFilters_' . $this->dataGridID) == '') {
            $filterArray = SGL_Session::get('filterArray');
            if (isset($filterArray[$module . '_' . $manager . '_' . $action .
                    '_' . $this->dataGridID . '_' . $this->dataGridName])) {
                    unset($filterArray[$module . '_' . $manager . '_' .
                          $action . '_' . $this->dataGridID . '_' .
                          $this->dataGridName]);
            }
            SGL_Session::set('filterArray', $filterArray);
        }
        //for "_from" and "_to" date filters
        if (($column->type == 'date') || ($column->type == 'integer') ||
            ($column->type == 'real') || ($column->type == 'hour')) {
            $columnTempFrom = $column->dbName . '__from__';
            $columnTempTo   = $column->dbName . '__to__';

            //set names for filter -GET variables
            $filterSetNameFrom = $this->dataGridName . $column->dbName .
                                    '__from__';
            $filterSetNameTo   = $this->dataGridName . $column->dbName .
                                    '__to__';

            //put filter -GET variables or empty section
            //into the specified dataGrid fields
            $s = $inReq->get($filterSetNameFrom);
            $s = str_replace('\%', '%', $s);
            if ($inReq->get($filterSetNameFrom)) {
                $this->filters[$columnTempFrom] = str_replace('%', '\%', $s);

                if ($useSession) {
                    $filterArray = SGL_Session::get('filterArray');
                    $filters = $filterArray[$module . '_' . $manager . '_' .
                                            $action . '_' . $this->dataGridID .
                                            '_' . $this->dataGridName . '_' .
                                            $columnTempFrom];
                    if ($filters) {
                        $this->filters[$columnTempFrom] = $filters;
                    }
                } else {
                    $this->filters[$columnTempFrom] = str_replace('%', '\%', $s);
                    $filterArray = SGL_Session::get('filterArray');
                    $filterArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                 $this->dataGridName . '_' . $columnTempFrom] =
                    str_replace('%', '\%', $s);
                    SGL_Session::set('filterArray', $filterArray);
                }
            } elseif ($column->filter) {
                $this->filters[$columnTempFrom] = "";

                if ($useSession) {
                    $filterArray = SGL_Session::get('filterArray');
                    $filters = isset($filterArray[$module . '_' . $manager .
                                                  '_' . $action . '_' .
                                                  $this->dataGridID . '_' .
                                                  $this->dataGridName . '_' .
                                                  $columnTempFrom])
                                ? $filterArray[$module . '_' . $manager . '_' .
                                               $action . '_' .
                                               $this->dataGridID . '_' .
                                               $this->dataGridName . '_' .
                                               $columnTempFrom]
                                : '';
                    if ($filters) {
                        $this->filters[$columnTempFrom] = $filters;
                    }
                }

                if (isset($_POST[$filterSetNameFrom])) {
                    $this->filters[$columnTempFrom] = str_replace('%', '\%', $s);
                    $filterArray = SGL_Session::get('filterArray');
                    $filterArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                 $this->dataGridName . '_' . $columnTempFrom] =
                        str_replace('%', '\%', $s);
                    SGL_Session::set('filterArray', $filterArray);
                }
            }

            $s = $inReq->get($filterSetNameTo);
            $s = str_replace('\%', '%', $s);
            if ($inReq->get($filterSetNameTo)) {
                $this->filters[$columnTempTo] = str_replace('%', '\%', $s);

                if ($useSession) {
                    $filterArray = SGL_Session::get('filterArray');
                    $filters = $filterArray[$module . '_' . $manager . '_' .
                                            $action . '_' . $this->dataGridID .
                                            '_' . $this->dataGridName . '_' .
                                            $columnTempTo];
                    if ($filters)
                        $this->filters[$columnTempTo] = $filters;
                } else {
                    $this->filters[$columnTempTo] = str_replace('%', '\%', $s);
                    $filterArray = SGL_Session::get('filterArray');
                    $filterArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                 $this->dataGridName . '_' . $columnTempTo] =
                        str_replace('%', '\%', $s);
                    SGL_Session::set('filterArray', $filterArray);
                }
            } elseif ($column->filter) {
                $this->filters[$columnTempTo] = "";

                if ($useSession) {
                    $filterArray = SGL_Session::get('filterArray');
                    $filters = isset($filterArray[$module . '_' . $manager .
                                                  '_' . $action . '_' .
                                                  $this->dataGridID . '_' .
                                                  $this->dataGridName . '_' .
                                                  $columnTempTo])
                                ? $filterArray[$module . '_' . $manager . '_' .
                                               $action . '_' .
                                               $this->dataGridID . '_' .
                                               $this->dataGridName . '_' .
                                               $columnTempTo]
                                : '';
                    if ($filters) {
                        $this->filters[$columnTempTo] = $filters;
                    }
                }

                if (isset($_POST[$filterSetNameTo])) {
                    $this->filters[$columnTempTo] = str_replace('%', '\%', $s);
                    $filterArray = SGL_Session::get('filterArray');
                    $filterArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                 $this->dataGridName . '_' . $columnTempTo] =
                        str_replace('%', '\%', $s);
                    SGL_Session::set('filterArray', $filterArray);
                }
            }

            if ($column->type == 'date') {
                if ((!empty($this->filters[$columnTempFrom])) &&
        (is_null(SGL_DataGrid::formatDate2DB($this->filters[$columnTempFrom])))) {
                    $column->cError = "<span class='error'>" .
                                SGL_String::translate('incorrect date format') .
                                " !!</span>";
                    $this->filterError  = true;
                } elseif (!empty($this->filters[$columnTempFrom])) {
                    $this->filters[$columnTempFrom] =
                     SGL_DataGrid::formatDate2DB($this->filters[$columnTempFrom]);
                }

                if ((!empty($this->filters[$columnTempTo])) &&
        (is_null(SGL_DataGrid::formatDate2DB($this->filters[$columnTempTo])))) {
                    $column->cError = "<span class='error'>" .
                                SGL_String::translate('incorrect date format') .
                                " !!</span>";
                    $this->filterError  = true;
                } elseif (!empty($this->filters[$columnTempTo])) {
                    $this->filters[$columnTempTo] =
                       SGL_DataGrid::formatDate2DB($this->filters[$columnTempTo]);
                }
            }

            if ($column->type == 'hour') {
                if ((!empty($this->filters[$columnTempFrom])) &&
    (is_null(SGL_DataGrid::formatDateTime2DB($this->filters[$columnTempFrom])))) {
                    $column->cError = "<span class='error'>" .
                           SGL_String::translate('incorrect date/time format') .
                           " !!</span>";
                    $this->filterError  = true;
                } elseif (!empty($this->filters[$columnTempFrom])) {
                    $this->filters[$columnTempFrom] =
                 SGL_DataGrid::formatDateTime2DB($this->filters[$columnTempFrom]);
                }

                if ((!empty($this->filters[$columnTempTo])) &&
      (is_null(SGL_DataGrid::formatDateTime2DB($this->filters[$columnTempTo])))) {
                    $column->cError = "<span class='error'>" .
                           SGL_String::translate('incorrect date/time format') .
                           " !!</span>";
                    $this->filterError  = true;
                } elseif (!empty($this->filters[$columnTempTo])) {
                    $this->filters[$columnTempTo] =
                   SGL_DataGrid::formatDateTime2DB($this->filters[$columnTempTo]);
                }
            }

            if (($column->type == 'integer') || ($column->type == 'real')) {
                if ((!empty($this->filters[$columnTempFrom])) &&
                            (!is_numeric($this->filters[$columnTempFrom]))) {
                    $column->cError = "<span class='error'>" .
                            SGL_String::translate('incorrect numeric format') .
                            " !!</span>";
                    $this->filterError = true;
                } elseif (!empty($this->filters[$columnTempFrom])) {
                    $this->filters[$columnTempFrom] =
                                                $this->filters[$columnTempFrom];
                }

                if ((!empty($this->filters[$columnTempTo])) &&
                                (!is_numeric($this->filters[$columnTempTo]))) {
                    $column->cError = "<span class='error'>" .
                            SGL_String::translate('incorrect numeric format') .
                            " !!</span>";
                    $this->filterError = true;
                } elseif (!empty($this->filters[$columnTempTo])) {
                    $this->filters[$columnTempTo] =
                                                $this->filters[$columnTempTo];
                }
            }
        } else { //for text and select filters
            $columnTemp = $column->dbName;
            //set names for filter -GET variables
            $filterSetName = $this->dataGridName . $column->dbName;

            //put filter -GET variables or empty section
            //into the specified dataGrid fields
            // DK filter column value can not by array (hidden form elements
            $s = $inReq->get($filterSetName);

            $s = str_replace('\%', '%', $s);
            if (($inReq->get($filterSetName)) &&
                                    (!is_array($inReq->get($filterSetName)))) {
                $this->filters[$columnTemp] = str_replace('%', '\%', $s);

                if ($useSession) {
                    $filterArray = SGL_Session::get('filterArray');
                    $filters = $filterArray[$module . '_' . $manager . '_' .
                                            $action . '_' . $this->dataGridID .
                                            '_' . $this->dataGridName . '_' .
                                            $columnTemp];
                    if ($filters) {
                        $this->filters[$columnTemp] = $filters;
                    }
                } else {
                    $this->filters[$columnTemp] = str_replace('%', '\%', $s);
                    $filterArray = SGL_Session::get('filterArray');
                    $filterArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                 $this->dataGridName . '_' . $columnTemp] =
                        str_replace('%', '\%', $s);
                    SGL_Session::set('filterArray', $filterArray);
                }
            } elseif ($column->filter) {
                $this->filters[$columnTemp] = "";

                if ($useSession) {
                    $filterArray = SGL_Session::get('filterArray');
                    $filters = isset($filterArray[$module . '_' . $manager .
                                                  '_' . $action . '_' .
                                                  $this->dataGridID . '_' .
                                                  $this->dataGridName . '_' .
                                                  $columnTemp])
                                ? $filterArray[$module . '_' . $manager . '_' .
                                               $action . '_' .
                                               $this->dataGridID . '_' .
                                               $this->dataGridName . '_' .
                                               $columnTemp]
                                : '';
                    if ($filters) {
                        $this->filters[$columnTemp] = $filters;
                    }
                }

                if (isset($_POST[$filterSetName])) {
                    $this->filters[$columnTemp] = str_replace('%', '\%', $s);
                    $filterArray = SGL_Session::get('filterArray');
                    $filterArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                 $this->dataGridName . '_' . $columnTemp] =
                        str_replace('%', '\%', $s);
                    SGL_Session::set('filterArray', $filterArray);
                }
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
        $module = $inReq->get('moduleName');
        $manager = $inReq->get('managerName');
        $action = $inReq->get('action');
        $columnTemp = $column->dbName;
        $sortSetName = $this->dataGridName . 'sort_' . $column->dbName;
        if ($inReq->get($sortSetName)) {
            $sortArray = SGL_Session::get('sortArray');
            if (isset($sortArray[$module . '_' . $manager . '_' . $action .
                                 '_' . $this->dataGridID . '_' .
                                $this->dataGridName]['column'])) {
                    unset($this->sorts[$sortArray[$module . '_' . $manager .
                                                  '_' . $action . '_' .
                                                  $this->dataGridID . '_' .
                                               $this->dataGridName]['column']]);
            }
            $this->sorts[$columnTemp] = $inReq->get($sortSetName);
            $sortArray[$module . '_' . $manager . '_' . $action . '_' .
                       $this->dataGridID . '_' . $this->dataGridName] = array(
                        'column' => $columnTemp,
                        'direction' => $inReq->get($sortSetName)
                       );
            SGL_Session::set('sortArray', $sortArray);
        } else {
          if ($s = SGL_DataGrid::sortColumnFromInSession($columnTemp, $inReq)) {
              $this->sorts[$s['column']] = $s['direction'];
          }
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
        if ($this->defaultPerPage>0) {
            $this->perPage = ($inReq->get($perPageSetName))
                                ? $inReq->get($perPageSetName)
                                : $this->defaultPerPage;
        } else {
            $this->perPage = ($inReq->get($perPageSetName))
                                ? $inReq->get($perPageSetName)
                                : $_SESSION['aPrefs']['resPerPage'];
        }
        if (!is_numeric($this->perPage)) {
            $this->perPage = 0;
        }

        //for specify witch page number display
        $setIDSetName = 'setID_' . $this->dataGridID;
        $this->setID = ($inReq->get($setIDSetName))
                            ? $inReq->get($setIDSetName)
                            : "";
        if ($inReq->get($setIDSetName)) {
            $_GET[$setIDSetName]  = "";
            $_POST[$setIDSetName] = "";
        }

        //pager  ID for each dataGrid
        $pageIDSetName = 'pageID_' . $this->dataGridID;
        $this->pageID = ($inReq->get($pageIDSetName))
                            ? $inReq->get($pageIDSetName)
                            : 1;
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
        $this->export = ($inReq->get($exportSetName))
                            ? $inReq->get($exportSetName)
                            : '';
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
        $useSessionFilters = true;
        foreach($this->columns as $key => $column) {
            $filterSetName = $this->dataGridName . $column->dbName;
            if (isset($_POST[$filterSetName])) {
                $useSessionFilters = false;
            }
        }
        //sorts and filters Form elements names are created by each
        //sortable or filterable dataGrid column
        $this->filterError = false;
        foreach($this->columns as $key => $column) {
            $this->filterValidate($this->columns[$key], $inReq, $useSessionFilters);
            $this->sortValidate($this->columns[$key], $inReq);
        }
        $this->pageValidate($inReq);
        $this->exportValidate($inReq);
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
	    $this->perPageOptions[$_SESSION['aPrefs']['resPerPage']] =
                                            $_SESSION['aPrefs']['resPerPage'];
        $this->perPageOptions[25] = 25;
        $this->perPageOptions[50] = 50;
        $this->perPageOptions[100] = 100;
        $this->perPageOptions[250] = 250;

        $this->perPageOptions[SGL_DATAGRID_ALL_ROWS_IN_SELECT] =
                                                  SGL_Output::translate('ALL');
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

    function sortColumnFromInSession($columnName, $inReq)
    {
        $module = $inReq->get('moduleName');
        $manager = $inReq->get('managerName');
        $action = $inReq->get('action');
        $sortArray = SGL_Session::get('sortArray');
        if (!empty($sortArray)) {
            foreach($sortArray as $key => $sort) {
                if (($module . '_' . $manager . '_' . $action . '_' .
                     $this->dataGridID . '_' . $this->dataGridName) == $key) {
                    if (!empty($sort)) {
                        if ($sort['column'] == $columnName) {
                            return $sort;
                        }
                    }
                }
            }
        }
        return false;
    }

    function sortAnyColumnExistsInSession($inReq)
    {
        $module = $inReq->get('moduleName');
        $manager = $inReq->get('managerName');
        $action = $inReq->get('action');
        $sortArray = SGL_Session::get('sortArray');
        if (!empty($sortArray)) {
            if (!empty($sortArray[$module . '_' . $manager . '_' . $action .
                                  '_' . $this->dataGridID . '_' .
                                  $this->dataGridName])) {
                return true;
            }
        }
        return false;
    }

    function setInitialOrder(&$inputReq, $columnName, $sortType)
    {
        $orderVariable = $inputReq->get($this->dataGridName . 'sort_' . $columnName);
        if (!isset($orderVariable)) {
            if (!SGL_DataGrid::sortAnyColumnExistsInSession($inputReq)) {
                $this->sorts[$columnName] = $sortType;
            }
        }
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

        $dataSource->setSort($this->sorts, $this->dataGridID, $this->dataGridName);
        if ($this->filterError) {
            $emptyFilters = $this->setEmptyFilters($this->filters);
            $dataSource->setFilter($emptyFilters);
        } else {
            $dataSource->setFilter($this->filters);
        }
        //$this->allRows = $dataSource->getNumberOfAllRows();

        $actualPage = $dataSource->getActualPage($this->setID, $this->perPage,
                                                    $this->columns);

        //get data from given source
        $this->results = $dataSource->fill($this->dataGridID, $this->perPage,
                                           $actualPage);
        $this->allRows = count($this->results);
        foreach ($this->columns as $keyColumn => $column) {
            //if transform array given in column
            if ((is_array($column->transform)) && (count($column->transform) > 0)) {
                foreach ($this->results as $keyRow => $row) {
                    foreach ($column->transform as $keyTransform => $transform) {
                        if ($row[$column->dbName] == $keyTransform) {

                            // replace actual value of current row in column
                            // selected from database
                            // with given value in transform array
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

        if ($fileName == '') {
            $fileName = $this->dataGridName . $this->export;
        }
        $dataSource->columns = & $this->columns;
        switch($this->export) {
            case 'page_XLS':
                $dataSource->exportPageToExcel($fileName.'.xls');
                break;

            case 'total_XLS':
                $dataSource->exportTotalToExcel($fileName.'.xls');
                break;

            case 'page_DOC':
                $dataSource->exportPageToWord($fileName.'.doc');
                break;

            case 'total_DOC':
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
    function display(&$output, $masterTemplate = 'masterLeftCol.html')
    {
        //set field name for current dataGrid
        $dataGrigSetName = $this->dataGridName;

        //put the data into specified dataGrid fields and then send them to given output
        foreach ($this->filters as $k=>$v) {
            $this->filters[$k] = str_replace('\%', '%', $this->filters[$k]);
        }
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
        $output->dataGridData->$dataGrigSetName->hasIdColumn    = $this->hasIdColumn;
        $output->dataGridData->$dataGrigSetName->perPageOptions = $this->perPageOptions;
        $output->dataGridData->$dataGrigSetName->dataGridHeader = $this->dataGridHeader;
        $output->dataGridData->$dataGrigSetName->dataGridButton = $this->dataGridButton;
        $output->dataGridData->$dataGrigSetName->dataGridButtonDelete =
                                                    $this->dataGridButtonDelete;
        //PS 28517 hidden right column when display datagrid
        $output->masterTemplate    = $masterTemplate;
        //PS export dataGrid to print

        $printRequest = $output->inputReq->get('print');
        if (isset($printRequest) && ($printRequest == 1)) {
            $output->masterTemplate = 'blank.html';
            $output->print = true;
        }
        $output->javascriptSrc = array('js/dataGrid.js', 'js/overlib/overlib.js');
        $output->path = SGL_BASE_URL;
        $output->dataGridData->$dataGrigSetName->emptyTitle= $this->emptyTitle;
        if ($this->dataGridDeleteMessage <> '') {
            $output->dataGridData->$dataGrigSetName->dataGridDeleteMessage =
                $this->dataGridDeleteMessage;
        } else {
            $output->dataGridData->$dataGrigSetName->dataGridDeleteMessage =
                SGL_string::translate('Do you want to delete: ');
        }
        $emptyFilters = true;
        foreach ($this->filters as $key=>$value) {
            if ($value !== '') {
                $emptyFilters = false;
            }
        }
        if ($emptyFilters) {
            if ($this->allRows > 9) {
                $output->dataGridData->$dataGrigSetName->showFilters = true;
            } else {
                $output->dataGridData->$dataGrigSetName->showFilters = false;
            }
        } else {
            $output->dataGridData->$dataGrigSetName->showFilters = true;
        }

        if (($this->allRows == 0) && ($this->emptyTitle != '')) {
            $output->dataGridData->$dataGrigSetName->showDataGrid = false;
            if (!$emptyFilters) {
                $output->dataGridData->$dataGrigSetName->showDataGrid = true;
            }
        } else {
            $output->dataGridData->$dataGrigSetName->showDataGrid = true;
        }

        $showSummary = false;
        foreach ($this->columns as $column) {
            if ($column->sumTot || $column->sum || $column->avg || $column->avgTot) {
                $showSummary = true;
            }
        }

        $output->dataGridData->$dataGrigSetName->showSummary = $showSummary;
    }

    /**
     * Formats date for the current user
     * @param   string  $sDate  Date in user or DB format  (YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Date formatted for the DB format (YYYY-mm-dd)
     *                   if date is not proper - return null
     */
    function formatDate2DB($sDate)
    {
        //check if date is in correct format
        if (preg_match("/([0-9]{4}-[0-9]{2}-[0-9]{2})$/", $sDate)) {
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

    function makeValidLinks($links)
    {
        return str_replace("&nbsp;&nbsp;&nbsp;", "&nbsp;", $links);
    }

    /**
     * Formats datetime for the current user
     * @param   string  $sDateTime  Datetime in user or DB format
     * (YYYY-mm-dd HH:mm:ss or dd.mm.yyyy HH:mm:ss or YYYY-mm-dd or dd.mm.yyyy)
     * @return  string  Datetime formatted for the DB format (YYYY-mm-dd)
     * or (YYYY-mm-dd HH:mm:ss) if hours set; if date is not proper - return null
     */
    function formatDateTime2DB($sDateTime)
    {
        //check if date is in correct format
        $sResult = null;
        $aDateTime = explode(" ", $sDateTime);
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
}

?>